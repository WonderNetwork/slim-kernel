<?php
declare(strict_types=1);

use Acme\CustomErrorMiddleware;
use Slim\App;

return function (App $app) {
    $app->add(CustomErrorMiddleware::class);
};
