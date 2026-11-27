<?php

declare(strict_types=1);

namespace WonderNetwork\SlimKernel\Apache\Directive;

use WonderNetwork\SlimKernel\Apache\ExtendedDirective;

final readonly class Comment implements ExtendedDirective {
    public static function tryFrom(?string $comment): ?self {
        return $comment ? new self($comment) : null;
    }

    public static function from(string $comment): self {
        return new self($comment);
    }

    private function __construct(
        public string $value,
    ) {
    }

    public function toString(): string {
        return (string) preg_replace('/^/m', '# ', $this->value);
    }

    public function isGlued(): bool {
        return true;
    }
}
