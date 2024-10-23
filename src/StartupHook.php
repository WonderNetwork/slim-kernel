<?php
declare(strict_types=1);

namespace WonderNetwork\SlimKernel;

use Psr\Container\ContainerInterface;

interface StartupHook {
    public function __invoke(ServicesBuilder $builder, ContainerInterface $container): void;
}
