<?php
declare(strict_types=1);

namespace WonderNetwork\SlimKernel\Http\Serializer;

use Psr\Http\Message\ResponseInterface;
use Slim\Psr7\Factory\StreamFactory;

final readonly class EchoController {
    public function __invoke(
        #[Payload] SamplePostInput $post,
        #[Payload(source: PayloadSource::Get)] SampleGetInput $get,
        ResponseInterface $response,
    ): ResponseInterface {
        $streamFactory = new StreamFactory();
        $payload = compact('post', 'get');
        $json = json_encode($payload, JSON_THROW_ON_ERROR);
        $body = $streamFactory->createStream($json);

        return $response->withBody($body);
    }
}
