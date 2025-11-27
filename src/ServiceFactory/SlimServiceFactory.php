<?php

declare(strict_types=1);

namespace WonderNetwork\SlimKernel\ServiceFactory;

use DI\Bridge\Slim\Bridge;
use DI\Bridge\Slim\ControllerInvoker;
use Invoker\Invoker;
use Invoker\InvokerInterface;
use Invoker\ParameterResolver\AssociativeArrayResolver;
use Invoker\ParameterResolver\Container\TypeHintContainerResolver;
use Invoker\ParameterResolver\DefaultValueResolver;
use Invoker\ParameterResolver\ResolverChain;
use Invoker\ParameterResolver\TypeHintResolver;
use Psr\Container\ContainerInterface;
use Psr\Http\Message\ResponseFactoryInterface;
use Psr\Http\Message\StreamFactoryInterface;
use RuntimeException;
use Slim\App;
use Slim\Interfaces\CallableResolverInterface;
use Slim\Middleware\ErrorMiddleware;
use Slim\Psr7\Factory\ResponseFactory;
use Slim\Psr7\Factory\StreamFactory;
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
use WonderNetwork\SlimKernel\Http\Serializer\JsonSerializingInvoker;
use WonderNetwork\SlimKernel\Http\Serializer\JsonSerializingInvokerOptions;
use WonderNetwork\SlimKernel\ServiceFactory;
use WonderNetwork\SlimKernel\ServicesBuilder;
use WonderNetwork\SlimKernel\SlimExtension\ErrorMiddlewareConfiguration;
use function DI\autowire;
use function DI\get;

final class SlimServiceFactory implements ServiceFactory {
    public const string INPUT_DENORMALIZER = self::class.':input-denormalizer';
    public const string INVOKER = self::class.':invoker';

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

        yield Invoker::class => static function (ContainerInterface $container) {
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

            return new Invoker(
                new ResolverChain([
                    new TypeHintResolver(),
                    new AssociativeArrayResolver(),
                    new DeserializeParameterResolver($serializer),
                    new TypeHintContainerResolver($container),
                    new DefaultValueResolver(),
                ]),
                $container,
            );
        };

        yield JsonSerializingInvoker::class => autowire()->constructor(
            get(Invoker::class),
        );

        yield self::INVOKER => get(JsonSerializingInvoker::class);

        yield ControllerInvoker::class => static function (ContainerInterface $container) {
            $invoker = $container->get(SlimServiceFactory::INVOKER);

            if (false === $invoker instanceof InvokerInterface) {
                throw new RuntimeException(
                    sprintf(
                        'Service registered under %s key is expected to implement %s interface, %s given',
                        SlimServiceFactory::INVOKER,
                        InvokerInterface::class,
                        get_debug_type($invoker),
                    ),
                );
            }

            return new ControllerInvoker($invoker);
        };

        yield JsonSerializingInvokerOptions::class => static fn () => JsonSerializingInvokerOptions::onlyExplicitlyMarked();

        yield ResponseFactoryInterface::class => static fn () => new ResponseFactory();
        yield StreamFactoryInterface::class => static fn () => new StreamFactory();
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
