<?php

declare(strict_types=1);

namespace WonderNetwork\SlimKernel\Http\Serializer;

final readonly class JsonSerializingInvokerOptions {
    public static function simpleTypes(): self {
        return new self(
            serializeSimpleTypes: true,
            serializeJsonSerializable: false,
            serializeObjects: false,
        );
    }

    public static function onlyExplicitlyMarked(): self {
        return new self(
            serializeSimpleTypes: false,
            serializeJsonSerializable: false,
            serializeObjects: false,
        );
    }

    public static function all(): self {
        return new self(
            serializeSimpleTypes: true,
            serializeJsonSerializable: true,
            serializeObjects: true,
        );
    }

    public function __construct(
        public bool $serializeSimpleTypes = true,
        public bool $serializeJsonSerializable = true,
        public bool $serializeObjects = true,
    ) {
    }
}
