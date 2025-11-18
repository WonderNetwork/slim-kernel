<?php

declare(strict_types=1);

use Slim\Interfaces\RouteCollectorProxyInterface;

return function (RouteCollectorProxyInterface $app): void {
    $app->get('/', static function (): void {
        throw new RuntimeException(__FILE__);
    });
};
