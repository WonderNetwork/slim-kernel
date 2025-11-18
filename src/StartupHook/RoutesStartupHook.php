<?php

declare(strict_types=1);

namespace WonderNetwork\SlimKernel\StartupHook;

use Psr\Container\ContainerInterface;
use Slim\App;
use WonderNetwork\SlimKernel\ServicesBuilder;
use WonderNetwork\SlimKernel\SlimExtension\SlimClosuresCollection;
use WonderNetwork\SlimKernel\StartupHook;

final class RoutesStartupHook implements StartupHook {
    private string $path;

    public function __construct(string $path) {
        $this->path = $path;
    }

    public function __invoke(ServicesBuilder $builder, ContainerInterface $container): void {
        $closures = SlimClosuresCollection::of(...$builder->files()->glob($this->path));

        $app = $container->get(App::class);
        $closures->applyTo($app);
    }
}
