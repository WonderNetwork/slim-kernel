<?php

declare(strict_types=1);

namespace WonderNetwork\SlimKernel\EventDispatcher;

use Psr\Container\ContainerInterface;

final readonly class LazyListenerFactory {
    /**
     * @param class-string $subscriber
     */
    public static function of(ContainerInterface $container, string $subscriber): self {
        return new self($container, $subscriber);
    }

    /**
     * @param class-string $subscriberClass
     */
    public function __construct(
        private ContainerInterface $container,
        private string $subscriberClass,
    ) {
    }

    public function create(string $method): callable {
        return new LazyListener($this->container, $this->subscriberClass, $method);
    }
}
