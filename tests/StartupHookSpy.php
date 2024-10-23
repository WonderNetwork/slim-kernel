<?php
declare(strict_types=1);

namespace WonderNetwork\SlimKernel;

use Psr\Container\ContainerInterface;

final class StartupHookSpy implements StartupHook {
    public ?ContainerInterface $container = null;

    public function __invoke(ServicesBuilder $builder, ContainerInterface $container): void {
        $this->container = $container;
    }
}
