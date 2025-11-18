<?php

declare(strict_types=1);

use Acme\CustomErrorMiddleware;
use Acme\ErrorHandlingSpy;
use WonderNetwork\SlimKernel\SlimExtension\ErrorMiddlewareConfiguration;
use function DI\autowire;

return [
    ErrorHandlingSpy::class => autowire(),
    CustomErrorMiddleware::class => autowire(),
    ErrorMiddlewareConfiguration::class => static fn () => ErrorMiddlewareConfiguration::verbose(),
];
