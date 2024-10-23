<?php
declare(strict_types=1);

namespace WonderNetwork\SlimKernel;

use Psr\Container\ContainerInterface;

final class HookCollection {
    /** @var StartupHook[]  */
    private array $hooks = [];
    public function __construct() {
    }

    public function add(StartupHook $hook): void {
        $this->hooks[] = $hook;
    }

    public function boot(ContainerInterface $container): ContainerInterface {
        foreach ($this->hooks as $hook) {
            $hook($container);
        }
        return $container;
    }
}
