# Wonder Slim Kernel

This package aims to provide a set of configuration helpers for Slim v4 microframework.
By using the `KernelBuilder`, one can easily bootstrap the `Slim\App` service,
along with a `DI\Container` dependency injection container, declaratively define
a bunch of services, register symfony console commands, middlewares, routes, etc.

See [basic usage](#basic-usage) to get an overview how to use it. 

## Installation

```
composer require wondernetwork/slim-kernel
```

## Basic Usage

```php
use WonderNetwork\SlimKernel\KernelBuilder;
use WonderNetwork\SlimKernel\ServiceFactory\SymfonyConsoleServiceFactory;

// configure the container:
$container = KernelBuilder::start(
        // all the paths will be calculated relative to this one
        __DIR__.'/..',
    )
    // just add inline definitions if you’d like
    ->add([
        'environment' => $environment
    ])
    // specify a directory or directories to search definition files in
    ->glob(
        // include all *.php files in some `services` folder
        __DIR__.'/../services/*.php',
        // overrides for specific environment, given we have $environment defined:
        __DIR__."/../services/{$environment}/*.php",
    )
    // register more advanced definition providers implementing the ServiceFactory interface
    ->register(
        new SymfonyConsoleServiceFactory(
            // for example, this provider registers a symfony application
            // and adds commands matching this glob pattern:
            __DIR__.'/../src/Cli/**/*Command.php',
        ),
    )
    // only pass a path for prod environments, null otherwise
    ->useCache(__DIR__.'/../.cache')
    ->build();

// get the slim application from the container
$app = $container->get(Slim\App::class)
$app->run();
```

## Declarative service definitions

As mentioned in the basic usage section, the `KernelBuilder` has a `glob()` method,
which accepts a list of glob patterns that will be used to build the container. Each
of the matched php files **needs to return** either of:

 * An array or other iterable of `key => value` pairs. 
   See [PHP DI documentation][php-di-definition-syntax] for details
 * [A `ServiceFactory` instance (see below)](#service-factory)

Example:

```php
// given it’s configured:
// $kernelBuilder->glob('app/services/*.php');
// next, in app/services/some.php:
use Psr\Container\ContainerInterface;

return [
  'foo.scalar.dependency' => 10,
  FooService::class => fn (ContainerInterface $container) => new FooService(
    $container->get(FooDependency::class),
    $container->get('foo.scalar.dependency'),
  ),
];
```


[php-di-definition-syntax]: https://php-di.org/doc/php-definitions.html#syntax

## On Startup hooks

You might want some initialization code to run on each request and each invocation
of a CLI command. This is best place to setup some global error handlers, boot some
static properties, etc. To do so, pass an object implementing `StartupHook` to the
`onStartup()` method

## Error handling

The default error handling middleware is added. It’s configured to silently log
errors with details, but do not display the details in the response. You can change
that behavour, for example if you’d like to have more verbose logging in certain
environments, by overriding the `ErrorMiddlewareConfiguration` service:

```php
// somewhere in app/services/test/errors.php
use WonderNetwork\SlimKernel\SlimExtension\ErrorMiddlewareConfiguration as Configuration;
return [
   Configuration::class => static fn () => Configuration::verbose();
];
```

Beside the factory methods (either `silent()` or `verbose()`) you can also just `create()` and
set each individual setting using `with*()` and `without*()` methods.

## Service Factory

`WonderNetwork\SlimKernel\ServiceFactory`

This represents a more advanced service definition than a simple array,
by passing in a `ServiceBuilder` instance. It’s helpful to use it when
you’d like to autowire a bunch of files matching a pattern, or handle
a list of configuration files with some post-processing. 

You can also add service factories directly using the `KernelBuilder::register()`
method.

See the following list of provided Service Factories for inspiration:

### Slim Applcation Service Factory

This service factory is built-in and gets registered automatically.
It defines the `Slim\App` service by using the PHP DI Slim Bridge.

### Symfony Command Service Factory

`WonderNetwork\SlimKernel\ServiceFactory\SymfonyConsoleServiceFactory`

This service factory will find all files matching a specified glob pattern,
autowire them in the container, and then register a Symfony console application
with all these commands added to it.

> [!NOTE]
> Make sure that your command classes are autoloaded using a valid PSR-4
> configuration in your `composer.json` file

```php
// app/services/cli.php
return new WonderNetwork\SlimKernel\ServiceFactory\SymfonyConsoleServiceFactory(
    // glob to find command files
    __DIR__.'/../../src/Cli/**/*Command.php',
    // name for your symfony console application
    'acme v1.0', 
);
```

### Routes Service Factory

`WonderNetwork\SlimKernel\ServiceFactory\RouteServiceFactory`

If you would like to get your routes registered in a declarative manner,
use this service factory to point to files containing your route definitions.
Each of the files matched by the glob pattern **needs to return** a closure,
which takes `Slim\App`, or more precisely `Slim\Interfaces\RouteCollectorProxyInterface`.
This means you can use it for more than routes: for example global middlewares.
The closures will be evaluated whenever the Slim Application is fetched from
the container.

```php
// app/services/routes.php
return new WonderNetwork\SlimKernel\ServiceFactory\RoutesServiceFactory(
    __DIR__.'/../routes/*.php',
);
// app/routes/some.php
use Psr\Http\Message\ResponseInterface;
return static function (Slim\App $app) {
  $app->get('/hello/{message}', function (ResponseInterface $response, string $message) {
    return $response->withHeader("X-Hello", $message);
  });
}
```
