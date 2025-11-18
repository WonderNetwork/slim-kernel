<?php

declare(strict_types=1);

namespace WonderNetwork\SlimKernel\Http\Serializer;

use Attribute;

#[Attribute(Attribute::TARGET_PARAMETER)]
final readonly class Payload {
    /** @param array<mixed> $context */
    public function __construct(
        public PayloadSource $source = PayloadSource::Post,
        public array $context = [],
    ) {
    }
}
