<?php

declare(strict_types=1);

use Acme\HelloWorldController;
use function DI\autowire;

return [
    HelloWorldController::class => autowire(),
];
