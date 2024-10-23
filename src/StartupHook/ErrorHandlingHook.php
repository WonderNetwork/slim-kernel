<?php
declare(strict_types=1);

namespace WonderNetwork\SlimKernel\StartupHook;

use Psr\Container\ContainerExceptionInterface;
use Psr\Container\ContainerInterface;
use Psr\Container\NotFoundExceptionInterface;
use Slim\App;
use Slim\Middleware\ErrorMiddleware;
use WonderNetwork\SlimKernel\ServicesBuilder;
use WonderNetwork\SlimKernel\StartupHook;

final class ErrorHandlingHook implements StartupHook {
    /**
     * @throws ContainerExceptionInterface
     * @throws NotFoundExceptionInterface
     */
    public function __invoke(ServicesBuilder $builder, ContainerInterface $container): void {
        $container->get(App::class)->add($container->get(ErrorMiddleware::class));
    }
}
