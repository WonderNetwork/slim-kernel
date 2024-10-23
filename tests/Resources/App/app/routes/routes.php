<?php
declare(strict_types=1);

use Acme\HelloWorldController;
use Slim\Interfaces\RouteCollectorProxyInterface;

return function (RouteCollectorProxyInterface $app) {
    $app->get('/hello/{message}', HelloWorldController::class);
};
