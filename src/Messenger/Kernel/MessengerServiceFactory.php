<?php

declare(strict_types=1);

namespace WonderNetwork\SlimKernel\Messenger\Kernel;

use Closure;
use DI\Container;
use DI\Definition\Helper\DefinitionHelper;
use DI\Definition\Reference;
use Psr\Cache\CacheItemPoolInterface;
use Psr\Container\ContainerInterface;
use Psr\Log\LoggerInterface;
use Psr\Log\NullLogger;
use Symfony\Component\Cache\Adapter\ArrayAdapter;
use Symfony\Component\EventDispatcher\EventDispatcher;
use Symfony\Component\Messenger\Command\ConsumeMessagesCommand;
use Symfony\Component\Messenger\Command\StopWorkersCommand;
use Symfony\Component\Messenger\EventListener\StopWorkerOnRestartSignalListener;
use Symfony\Component\Messenger\Handler\HandlersLocatorInterface;
use Symfony\Component\Messenger\MessageBus;
use Symfony\Component\Messenger\MessageBusInterface;
use Symfony\Component\Messenger\Middleware\HandleMessageMiddleware;
use Symfony\Component\Messenger\Middleware\SendMessageMiddleware;
use Symfony\Component\Messenger\RoutableMessageBus;
use Symfony\Component\Messenger\Transport\Sender\SendersLocator;
use Symfony\Component\Messenger\Transport\Sender\SendersLocatorInterface;
use Symfony\Component\Messenger\Transport\Serialization\Serializer;
use Symfony\Component\Messenger\Transport\Serialization\SerializerInterface;
use WonderNetwork\SlimKernel\Messenger\CommandBus;
use WonderNetwork\SlimKernel\Messenger\QueryBus;
use WonderNetwork\SlimKernel\ServiceFactory;
use WonderNetwork\SlimKernel\ServicesBuilder;
use function DI\autowire;
use function DI\create;
use function DI\factory;
use function DI\get;
use function WonderNetwork\SlimKernel\Collection\collection;

final readonly class MessengerServiceFactory implements ServiceFactory {
    public function __construct(
        private string $commandPath = '/src/Application/Command/**/*Handler.php',
        private string $queryPath = '/src/Application/Query/**/*Handler.php',
        private null|Closure|Reference|DefinitionHelper|TransportLocatorBuilder $transports = null,
        private null|Closure|Reference|DefinitionHelper|EventDispatcher $eventDispatcher = null,
        private null|Closure|Reference|DefinitionHelper|LoggerInterface $logger = null,
        private null|Closure|Reference|DefinitionHelper|CacheItemPoolInterface $cachePool = null,
    ) {
    }

    public function __invoke(ServicesBuilder $builder): iterable {
        // region handlers
        yield from $commands = $builder->autowire()->glob($this->commandPath);
        yield from $queries = $builder->autowire()->glob($this->queryPath);
        yield AutowiredHandlerLocator::class => autowire()
            ->constructor(
                get(ContainerInterface::class),
                collection($commands)->concat($queries)->keys()->toArray(),
            );
        yield HandlersLocatorInterface::class => get(AutowiredHandlerLocator::class);
        yield HandleMessageMiddleware::class => autowire()->constructor(
            handlersLocator: get(HandlersLocatorInterface::class),
        );
        // endregion

        // region utilities
        yield CommandBusDependencies::Serializer->value => factory(fn () => Serializer::create());
        yield SerializerInterface::class => get(CommandBusDependencies::Serializer->value);
        yield CommandBusDependencies::EventDispatcher->value => $this->eventDispatcher ?? new EventDispatcher();
        yield CommandBusDependencies::Logger->value => $this->logger ?? new NullLogger();
        yield CommandBusDependencies::CachePool->value => $this->cachePool ?? new ArrayAdapter();
        // endregion

        // region senders
        yield TransportLocatorBuilder::class => $this->transports ?? TransportLocatorBuilder::empty();
        yield SendersLocator::class => function (TransportLocatorBuilder $config, ContainerInterface $container) {
            return new SendersLocator(
                sendersMap: [],
                sendersLocator: $config->sendersLocator($container),
            );
        };

        yield SendersLocatorInterface::class => get(SendersLocator::class);
        yield SendMessageMiddleware::class => autowire()->constructor(
            sendersLocator: get(SendersLocatorInterface::class),
            eventDispatcher: get(CommandBusDependencies::EventDispatcher->value),
        )->method('setLogger', get(CommandBusDependencies::Logger->value));
        // endregion

        yield MessageBusInterface::class => create(MessageBus::class)
            ->constructor([
                get(SendMessageMiddleware::class),
                get(HandleMessageMiddleware::class),
            ]);
        yield CommandBus::class => autowire();
        yield QueryBus::class => autowire();

        yield ConsumeMessagesCommand::class => function (TransportLocatorBuilder $config, ContainerInterface $container) {
            /** @var LoggerInterface $logger */
            $logger = $container->get(CommandBusDependencies::Logger->value);
            /** @var CacheItemPoolInterface $pool */
            $pool = $container->get(CommandBusDependencies::CachePool->value);
            /** @var EventDispatcher $eventDispatcher */
            $eventDispatcher = $container->get(CommandBusDependencies::EventDispatcher->value);
            $eventDispatcher->addSubscriber(
                new StopWorkerOnRestartSignalListener(
                    cachePool: $pool,
                    logger: $logger,
                ),
            );

            return new ConsumeMessagesCommand(
                // there is only one bus, and we pass it as fallback
                // the locator would never find anything, because it’s an empty container
                routableBus: new RoutableMessageBus(
                    busLocator: new Container(),
                    fallbackBus: $container->get(MessageBusInterface::class),
                ),
                receiverLocator: $config->receiversLocator($container),
                eventDispatcher: $eventDispatcher,
                logger: $logger,
                receiverNames: array_keys($config->receivers),
            );
        };

        yield StopWorkersCommand::class => autowire()->constructor(
            restartSignalCachePool: get(CommandBusDependencies::CachePool->value),
        );
    }
}
