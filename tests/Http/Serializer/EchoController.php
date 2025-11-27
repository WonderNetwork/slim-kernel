<?php

declare(strict_types=1);

namespace WonderNetwork\SlimKernel\Http\Serializer;

final readonly class EchoController {
    public function __invoke(
        #[Payload] SamplePostInput $post,
        #[Payload(source: PayloadSource::Get)] SampleGetInput $get,
    ): JsonResponse {
        $payload = compact('post', 'get');

        return JsonResponse::of($payload);
    }
}
