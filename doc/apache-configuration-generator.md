# Generating Apache `<Vhost>` configurations

The package provides an opinionated builder for Apache vhost config files.

```php
WonderNetwork\SlimKernel\Apache\ApacheConfigurationGenerator::generate(
    WonderNetwork\SlimKernel\Apache\Vhost::http('example.com'),
    WonderNetwork\SlimKernel\Apache\Vhost::secure('example.com')
        ->withAlias('www.example.com'),
)
```

## Basics

Goals of this package:

 * To allow building apache vhost config files in a declarative way
 * The provided interface is in no way exhaustive, it only serves our most common use cases
 * There are “escape hatches” to allow adding any custom content, see [Literal](#vhost-api)

The resulting document is meant not only to be functional, but also elegant,
so a lot of effort went into formatting.

## Fluent interface

Every provided builder is stateless, and is best suited for fluent interface:

```php
use WonderNetwork\SlimKernel\Apache\Vhost;

Vhost::secure('example.com')
    ->withServerAlias('www.example.com')
    ->with("# Custom block")
```

Some methods produce child elements, such as the `<Directory>` block. A closure
is used to configure it without leaving scope, and keeping the fluent interface:

```php
use WonderNetwork\SlimKernel\Apache\Vhost;

Vhost::secure('example.com')
    ->withCustomDirectory(
        'public/assets',
        fn ($directory) => $directory
            ->withRewriteComment('Allow putting asset versions in the path:')
            ->withRewriteCondition('%{DOCUMENT_ROOT}%{REQUEST_URI} !-f')
            ->withRewriteRule('^(.*)\.\d+\.(js|css)$ $1.$2'),
    )
```
## Vhost API

Configures main vhost directives, such as server names and aliases, SSL certificates,
logging, and document root. Some common configuration blocks also go here: adding
deflate encoding or expire headers. There is also a “custom” block at the bottom for
any and all directives one might want to add. 

To add custom blocks:

 * Implement `Directive` or `DirectiveCollection` interfaces
 * Use the generic `Literal::line()` and `Literal::block()` to add entire parts of configuration
 * And append them using `Vhost::with(…)`

Browse through the public methods available on the class to learn about 

### Conditionals

As a helper method, to keep the fluent interface, there’s a `->conditional()` method
to add parts based on a boolean condition. These are available on all major directives.
For example:

```php
use WonderNetwork\SlimKernel\Apache\Vhost;

Vhost::secure('example.com')
    ->conditional(
        condition: $isStaging,
        fn: fn ($vhost) => $vhost->withRewriteRule('robots.txt robots-ban.txt [L]'),
    )
```

### Subresources

 * `<Directory>` via `withDocumentRoot()` or `withCustomDirectory()`
 * `<Location>` added via `withLocation()`

They are configured by passing in a closure to the method. The closure
takes a resource, and needs to return one. Since they use fluent interface,
it’s best to use a short closure like:

```php
use WonderNetwork\SlimKernel\Apache\Vhost;

Vhost::secure('example.com')
    ->withCustomDirectory(
        'some/path',
        fn ($directory) => $directory->withRewriteRule("^ https://example.com [L]")
    )
```

## Content Security Policy Builder

To make it easier to build and document the CSP header, a specialized
builder is offered. It allows to merge the header from multiple named
rules, so you won’t keep track of where each part comes from.

```php
use WonderNetwork\SlimKernel\Apache\Vhost;

Vhost::secure('example.com')->withContentSecurityPolicy(fn ($builder) => $builder)
```
