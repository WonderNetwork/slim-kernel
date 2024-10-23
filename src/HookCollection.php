<?php
declare(strict_types=1);

namespace WonderNetwork\SlimKernel;

use Psr\Container\ContainerInterface;

final class HookCollection {
    /** @var StartupHook[]  */
    private array $hooks = [];
    public function __construct() {
    }

    public function add(StartupHook ...$hooks): void {
        foreach ($hooks as $hook) {
            $this->hooks[] = $hook;
        }
    }

    public function boot(ServicesBuilder $builder, ContainerInterface $container): ContainerInterface {
        foreach ($this->hooks as $hook) {
            $hook($builder, $container);
        }
        return $container;
    }
}
