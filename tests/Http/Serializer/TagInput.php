<?php
declare(strict_types=1);

namespace WonderNetwork\SlimKernel\Http\Serializer;

final readonly class TagInput {
    public function __construct(public string $name) {
    }
}
