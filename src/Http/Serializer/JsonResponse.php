<?php

declare(strict_types=1);

namespace WonderNetwork\SlimKernel\Http\Serializer;

use Fig\Http\Message\StatusCodeInterface;
use JsonSerializable;

#[Json]
final readonly class JsonResponse implements HasStatusCode, JsonSerializable {
    public static function of(mixed $data, int $statusCode = StatusCodeInterface::STATUS_OK): self {
        return new self($data, $statusCode);
    }

    private function __construct(private mixed $data, private int $statusCode) {
    }

    public function jsonSerialize(): mixed {
        return $this->data;
    }

    public function getStatusCode(): int {
        return $this->statusCode;
    }
}
