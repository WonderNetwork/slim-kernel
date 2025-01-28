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
        return $response->withBody(
            (new StreamFactory())->createStream(
                json_encode(compact('post', 'get'), JSON_THROW_ON_ERROR),
            ),
        );
    }
}
