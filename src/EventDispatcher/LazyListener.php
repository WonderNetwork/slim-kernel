<?php

declare(strict_types=1);

namespace WonderNetwork\SlimKernel\EventDispatcher;

use Psr\Container\ContainerInterface;

final readonly class LazyListener {
    public function __construct(
        private ContainerInterface $container,
        private string $className,
        private string $method,
    ) {
    }

    public function __invoke(mixed ...$arguments): void {
        $listener = $this->container->get($this->className);
        $listener->{$this->method}(...$arguments);
    }
}
