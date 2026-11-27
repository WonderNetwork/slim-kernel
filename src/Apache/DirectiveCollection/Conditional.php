<?php

declare(strict_types=1);

namespace WonderNetwork\SlimKernel\Apache\DirectiveCollection;

use WonderNetwork\SlimKernel\Apache\Directive;
use WonderNetwork\SlimKernel\Apache\DirectiveCollection;

final readonly class Conditional implements DirectiveCollection {
    public static function fromDirective(string $value, Directive $directive): self {
        return new self(
            ContainerCollection::fromDirective(
                name: 'If',
                value: $value,
                directive: $directive,
                quoted: true,
            ),
        );
    }

    private function __construct(private ContainerCollection $collection) {
    }

    public function toDirectives(): iterable {
        return $this->collection->toDirectives();
    }
}
