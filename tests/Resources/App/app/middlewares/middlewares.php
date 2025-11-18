<?php

declare(strict_types=1);

use Psr\Http\Message\ResponseInterface;
use Psr\Http\Message\ServerRequestInterface;
use Psr\Http\Server\MiddlewareInterface;
use Psr\Http\Server\RequestHandlerInterface;
use Slim\App;

return function (App $app): void {
    $app->add(new class () implements MiddlewareInterface {
        public function process(
            ServerRequestInterface $request,
            RequestHandlerInterface $handler,
        ): ResponseInterface {
            return $handler->handle($request->withAttribute('middleware', __FILE__));
        }
    });
};
