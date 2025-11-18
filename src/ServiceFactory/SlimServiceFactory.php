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
use RuntimeException;
use Slim\App;
use Slim\Interfaces\CallableResolverInterface;
use Slim\Middleware\ErrorMiddleware;
use Slim\Psr7\Factory\ResponseFactory;
use Symfony\Component\PropertyInfo\Extractor\PhpDocExtractor;
use Symfony\Component\PropertyInfo\Extractor\ReflectionExtractor;
use Symfony\Component\PropertyInfo\PropertyInfoExtractor;
use Symfony\Component\Serializer\Normalizer\ArrayDenormalizer;
use Symfony\Component\Serializer\Normalizer\BackedEnumNormalizer;
use Symfony\Component\Serializer\Normalizer\DenormalizerInterface;
use Symfony\Component\Serializer\Normalizer\NormalizerInterface;
use Symfony\Component\Serializer\Normalizer\ObjectNormalizer;
use Symfony\Component\Serializer\Serializer;
use Symfony\Component\Serializer\SerializerInterface;
use WonderNetwork\SlimKernel\Http\Serializer\DeserializeParameterResolver;
use WonderNetwork\SlimKernel\ServiceFactory;
use WonderNetwork\SlimKernel\ServicesBuilder;
use WonderNetwork\SlimKernel\SlimExtension\ErrorMiddlewareConfiguration;
use function DI\get;

final class SlimServiceFactory implements ServiceFactory {
    public const string INPUT_DENORMALIZER = self::class.':input-denormalizer';

    public function __invoke(ServicesBuilder $builder): iterable {
        yield Serializer::class => static fn () => new Serializer([
            new ArrayDenormalizer(),
            new BackedEnumNormalizer(),
            new ObjectNormalizer(
                propertyTypeExtractor: new PropertyInfoExtractor(
                    typeExtractors: [
                        new PhpDocExtractor(),
                        new ReflectionExtractor(),
                    ],
                ),
            ),
        ]);
        yield DenormalizerInterface::class => get(Serializer::class);
        yield NormalizerInterface::class => get(Serializer::class);
        yield SerializerInterface::class => get(Serializer::class);
        yield self::INPUT_DENORMALIZER => get(DenormalizerInterface::class);

        yield ControllerInvoker::class => static function (ContainerInterface $container) {
            $serializer = $container->get(SlimServiceFactory::INPUT_DENORMALIZER);

            if (false === $serializer instanceof DenormalizerInterface) {
                throw new RuntimeException(
                    sprintf(
                        'Service registered under %s key is expected to implement %s interface, %s given',
                        SlimServiceFactory::INPUT_DENORMALIZER,
                        DenormalizerInterface::class,
                        get_debug_type($serializer),
                    ),
                );
            }

            return new ControllerInvoker(
                new Invoker(
                    new ResolverChain([
                        new TypeHintResolver(),
                        new AssociativeArrayResolver(),
                        new DeserializeParameterResolver($serializer),
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
            ErrorMiddlewareConfiguration $configuration,
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
