<?php

declare(strict_types=1);

namespace WonderNetwork\SlimKernel\Apache\Directive;

use WonderNetwork\SlimKernel\Apache\ExtendedDirective;

final readonly class RewriteCond implements ExtendedDirective {
    public static function from(string $condition): self {
        return new self($condition);
    }

    private function __construct(
        private string $condition,
    ) {
    }

    public function toString(): string {
        return "RewriteCond $this->condition";
    }

    public function isGlued(): bool {
        return true;
    }
}
