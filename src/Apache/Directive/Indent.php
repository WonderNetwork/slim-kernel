<?php

declare(strict_types=1);

namespace WonderNetwork\SlimKernel\Apache\Directive;

use WonderNetwork\SlimKernel\Apache\Directive;

final readonly class Indent implements Directive {
    private const int Spaces = 4;

    public static function fromDirective(Directive $directive): self {
        return new self($directive);
    }

    private function __construct(private Directive $inner) {
    }

    public function toString(): string {
        $leftPad = str_repeat(" ", self::Spaces);

        // indent non-empty lines:
        return (string) preg_replace(
            '/^(?=.)/m',
            $leftPad,
            $this->inner->toString(),
        );
    }
}
