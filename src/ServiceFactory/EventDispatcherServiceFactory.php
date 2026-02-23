<?php

declare(strict_types=1);

namespace WonderNetwork\SlimKernel\ServiceFactory;

use Psr\EventDispatcher as Psr;
use Symfony\Component\EventDispatcher\EventDispatcher;
use Symfony\Component\EventDispatcher\EventDispatcherInterface;
use Symfony\Contracts\EventDispatcher as Contracts;
use WonderNetwork\SlimKernel\ServiceFactory;
use WonderNetwork\SlimKernel\ServicesBuilder;
use function DI\autowire;
use function DI\get;

final readonly class EventDispatcherServiceFactory implements ServiceFactory {
    public function __invoke(ServicesBuilder $builder): iterable {
        yield EventDispatcher::class => autowire();
        yield EventDispatcherInterface::class => get(EventDispatcher::class);
        yield Psr\EventDispatcherInterface::class => get(EventDispatcher::class);
        yield Contracts\EventDispatcherInterface::class => get(EventDispatcher::class);
    }
}
