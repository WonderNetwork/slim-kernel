<?php

declare(strict_types=1);

namespace WonderNetwork\SlimKernel\EventDispatcher;

use Psr\Container\ContainerInterface;
use RuntimeException;
use Symfony\Component\EventDispatcher\EventDispatcher;
use Symfony\Component\EventDispatcher\EventSubscriberInterface;
use WonderNetwork\SlimKernel\ServiceFactory;
use WonderNetwork\SlimKernel\ServicesBuilder;
use function DI\decorate;

final readonly class EventSubscribersCollection implements ServiceFactory {
    public static function start(): self {
        return new self([]);
    }

    /**
     * @param list<class-string<EventSubscriberInterface>> $subscribers
     */
    public function __construct(private array $subscribers) {
    }

    public function __invoke(ServicesBuilder $builder): iterable {
        yield from $this->register();
    }

    /**
     * @param class-string<EventSubscriberInterface> ...$subscribers
     */
    public function add(string ...$subscribers): self {
        return new self([...$this->subscribers, ...array_values($subscribers)]);
    }

    public function addLazyListeners(EventDispatcher $dispatcher, ContainerInterface $container): EventDispatcher {
        foreach ($this->subscribers as $subscriber) {
            $factory = LazyListenerFactory::of($container, $subscriber);

            /**
             * @see EventDispatcher::addSubscriber()
             */
            foreach ($subscriber::getSubscribedEvents() as $eventName => $params) {
                if (is_string($params)) {
                    $dispatcher->addListener($eventName, $factory->create($params));
                } elseif (\is_string($params[0])) {
                    $dispatcher->addListener($eventName, $factory->create($params[0]), (int) ($params[1] ?? 0));
                } else {
                    foreach ($params as $listener) {
                        if (is_string($listener)) {
                            $dispatcher->addListener($eventName, $factory->create($listener));
                        } elseif (is_array($listener)) {
                            $priority = (int) ($listener[1] ?? 0);
                            $dispatcher->addListener($eventName, $factory->create($listener[0]), $priority);
                        } else {
                            throw new RuntimeException('Invalid event listener: '.$subscriber);
                        }
                    }
                }
            }
        }

        return $dispatcher;
    }

    /**
     * @return iterable<string,mixed>
     */
    public function register(): iterable {
        yield self::class => $this;
        yield EventDispatcher::class => decorate(
            fn (EventDispatcher $dispatcher, ContainerInterface $container) => $container
                ->get(EventSubscribersCollection::class)
                ->addLazyListeners($dispatcher, $container),
        );
    }
}
