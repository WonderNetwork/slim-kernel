<?php

declare(strict_types=1);

namespace WonderNetwork\SlimKernel\Http\Serializer;

use Invoker\Invoker;
use Slim\Factory\Psr17\SlimPsr17Factory;

final readonly class JsonSerializingInvokerMother {
    public static function all(): JsonSerializingInvoker {
        return self::withOptions(JsonSerializingInvokerOptions::all());
    }

    public static function onlyMarked(): JsonSerializingInvoker {
        return self::withOptions(JsonSerializingInvokerOptions::onlyExplicitlyMarked());
    }

    private static function withOptions(JsonSerializingInvokerOptions $options): JsonSerializingInvoker {
        return new JsonSerializingInvoker(
            inner: new Invoker(),
            options: $options,
            responseFactory: SlimPsr17Factory::getResponseFactory(),
            streamFactory: SlimPsr17Factory::getStreamFactory(),
        );
    }
}
