<?php

declare(strict_types=1);

namespace WonderNetwork\SlimKernel\Apache\DirectiveCollection;

use WonderNetwork\SlimKernel\Apache\Directive;
use WonderNetwork\SlimKernel\Apache\DirectiveCollection;

final readonly class ContainerCollection implements DirectiveCollection {
    public static function fromDirective(
        string $name,
        string $value,
        Directive $directive,
        bool $quoted = false,
    ): self {
        return new self(
            name: $name,
            value: $value,
            inner: $directive,
            quoted: $quoted,
        );
    }

    private function __construct(
        private string $name,
        private string $value,
        private Directive $inner,
        private bool $quoted,
    ) {
    }

    public function toDirectives(): iterable {
        $value = $this->quoted ? sprintf('"%s"', $this->value) : $this->value;

        yield Directive\Literal::line("<$this->name $value>");
        yield Directive\Indent::fromDirective($this->inner);
        yield Directive\Literal::line("</$this->name>");
    }
}
