<?php

declare(strict_types=1);

namespace WonderNetwork\SlimKernel\Apache\Directive;

use WonderNetwork\SlimKernel\Apache\Directive;

final readonly class Named implements Directive {
    public static function tryFrom(string $name, null|string|int $value): ?self {
        return $value ? new self($name, $value) : null;
    }

    public static function from(string $name, string|int $value): self {
        return new self($name, $value);
    }

    public static function quoted(string $name, string $value): self {
        return new self($name, sprintf('"%s"', $value));
    }

    private function __construct(
        private string $name,
        private string|int $value,
    ) {
    }

    public function toString(): string {
        return "$this->name $this->value";
    }
}
