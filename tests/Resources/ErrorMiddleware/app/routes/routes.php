<?php
declare(strict_types=1);

use Slim\Interfaces\RouteCollectorProxyInterface;

return function (RouteCollectorProxyInterface $app) {
    $app->get('/', static function () {
        throw new RuntimeException(__FILE__);
    });
};
