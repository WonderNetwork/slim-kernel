<?php

declare(strict_types=1);

namespace WonderNetwork\SlimKernel\Apache\Directive;

use WonderNetwork\SlimKernel\Apache\Directive;

final readonly class Literal implements Directive {
    public static function line(string $value): self {
        return new self($value);
    }

    public static function block(string $value): self {
        return new self($value);
    }

    private function __construct(private string $value) {
    }

    public function toString(): string {
        return $this->value;
    }
}
