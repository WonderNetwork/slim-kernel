<?php

declare(strict_types=1);

use Acme\HelloWorldController;
use Psr\Http\Message\StreamFactoryInterface;
use Slim\Psr7\Factory\StreamFactory;
use function DI\autowire;

return [
    HelloWorldController::class => autowire(),
    StreamFactoryInterface::class => autowire(StreamFactory::class),
];
