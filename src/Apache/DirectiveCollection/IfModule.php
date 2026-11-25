<?php

declare(strict_types=1);

namespace WonderNetwork\SlimKernel\Apache\DirectiveCollection;

use WonderNetwork\SlimKernel\Apache\Directive;
use WonderNetwork\SlimKernel\Apache\DirectiveCollection;

final readonly class IfModule implements DirectiveCollection {
    public static function fromCollection(string $value, DirectiveCollection $collection): self {
        return self::fromDirective($value, Directive\Composite::fromCollection($collection));
    }

    public static function fromDirective(string $value, Directive $directive): self {
        return new self(
            ContainerCollection::fromDirective(
                name: 'IfModule',
                value: $value,
                directive: $directive,
            ),
        );
    }

    private function __construct(private ContainerCollection $collection) {
    }

    public function toDirectives(): iterable {
        return $this->collection->toDirectives();
    }
}
