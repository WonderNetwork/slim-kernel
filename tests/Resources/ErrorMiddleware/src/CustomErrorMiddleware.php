<?php

declare(strict_types=1);

namespace Acme;

use Psr\Http\Message\ResponseInterface;
use Psr\Http\Message\ServerRequestInterface;
use Psr\Http\Server\MiddlewareInterface;
use Psr\Http\Server\RequestHandlerInterface;
use Throwable;

final class CustomErrorMiddleware implements MiddlewareInterface {
    private ErrorHandlingSpy $spy;

    public function __construct(ErrorHandlingSpy $spy) {
        $this->spy = $spy;
    }

    public function process(ServerRequestInterface $request, RequestHandlerInterface $handler): ResponseInterface {
        try {
            return $handler->handle($request);
        } catch (Throwable $e) {
            $this->spy->handleError($e);

            throw $e;
        }
    }
}
