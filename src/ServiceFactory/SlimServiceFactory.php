<?php
declare(strict_types=1);

namespace WonderNetwork\SlimKernel\ServiceFactory;

use DI\Bridge\Slim\Bridge;
use DI\Bridge\Slim\ControllerInvoker;
use Invoker\Invoker;
use Invoker\ParameterResolver\AssociativeArrayResolver;
use Invoker\ParameterResolver\Container\TypeHintContainerResolver;
use Invoker\ParameterResolver\DefaultValueResolver;
use Invoker\ParameterResolver\ResolverChain;
use Invoker\ParameterResolver\TypeHintResolver;
use Psr\Container\ContainerInterface;
use Psr\Http\Message\ResponseFactoryInterface;
use Slim\App;
use Slim\Interfaces\CallableResolverInterface;
use Slim\Middleware\ErrorMiddleware;
use Slim\Psr7\Factory\ResponseFactory;
use WonderNetwork\SlimKernel\ServiceFactory;
use WonderNetwork\SlimKernel\ServicesBuilder;
use WonderNetwork\SlimKernel\SlimExtension\ErrorMiddlewareConfiguration;

final class SlimServiceFactory implements ServiceFactory {
    public function __invoke(ServicesBuilder $builder): iterable {
        yield ControllerInvoker::class => static function (ContainerInterface $container) {
            return new ControllerInvoker(
                new Invoker(
                    new ResolverChain([
                        new TypeHintResolver(),
                        new AssociativeArrayResolver(),
                        new TypeHintContainerResolver($container),
                        new DefaultValueResolver(),
                    ]),
                    $container,
                ),
            );
        };

        yield ResponseFactoryInterface::class => static fn () => new ResponseFactory();
        yield ErrorMiddlewareConfiguration::class => static fn () => ErrorMiddlewareConfiguration::silent();

        yield ErrorMiddleware::class => static fn (
            CallableResolverInterface $callableResolver,
            ResponseFactoryInterface $responseFactory,
            ErrorMiddlewareConfiguration $configuration
        ) => new ErrorMiddleware(
            $callableResolver,
            $responseFactory,
            $configuration->isDisplayErrors(),
            $configuration->isLogErrors(),
            true /* log error details */,
        );

        yield App::class => static function (ContainerInterface $container) {
            $app = Bridge::create($container);
            $app->addRoutingMiddleware();
            $app->getRouteCollector()->setDefaultInvocationStrategy(
                $container->get(ControllerInvoker::class),
            );

            return $app;
        };
    }
}
