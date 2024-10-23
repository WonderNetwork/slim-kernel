<?php
declare(strict_types=1);

namespace Acme;

use Psr\Http\Message\ResponseInterface;
use Psr\Http\Message\ServerRequestInterface;
use Psr\Http\Message\StreamFactoryInterface;

final class HelloWorldController {
    private StreamFactoryInterface $streamFactory;

    public function __construct(StreamFactoryInterface $streamFactory) {
        $this->streamFactory = $streamFactory;
    }

    public function __invoke(
        ServerRequestInterface $request,
        ResponseInterface $response,
        string $message
    ): ResponseInterface {
        return $response
            ->withBody($this->streamFactory->createStream("Hello {$message}"))
            ->withHeader('X-Middleware', $request->getAttribute('middleware') ? 'true' : 'false');
    }
}
