<?php

declare(strict_types=1);

namespace Acme;

use Psr\Http\Message\ResponseInterface;
use Psr\Http\Message\ServerRequestInterface;
use Psr\Http\Message\StreamFactoryInterface;

final readonly class HelloWorldController {
    public function __construct(private StreamFactoryInterface $streamFactory) {
    }

    public function __invoke(
        ServerRequestInterface $request,
        ResponseInterface $response,
        string $message,
    ): ResponseInterface {
        return $response
            ->withBody($this->streamFactory->createStream("Hello {$message}"))
            ->withHeader('X-Middleware', $request->getAttribute('middleware') ? 'true' : 'false');
    }
}
