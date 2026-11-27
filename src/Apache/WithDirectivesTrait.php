<?php

declare(strict_types=1);

namespace WonderNetwork\SlimKernel\Apache;

use WonderNetwork\SlimKernel\Apache\Directive\Composite;
use WonderNetwork\SlimKernel\Apache\Directive\Literal;

trait WithDirectivesTrait {
    private readonly Composite $customDirectives;

    public function with(Directive|DirectiveCollection|string $item): static {
        if ($item instanceof Directive) {
            return $this->withDirective($item);
        }

        if ($item instanceof DirectiveCollection) {
            return $this->withDirective(Composite::fromCollection($item));
        }

        return $this->withDirective(Literal::block($item));
    }

    private function initCustomDirectives(Spacing $spacing = Spacing::Group): Composite {
        return Composite::empty()->toSpacing($spacing);
    }

    private function withDirective(Directive $directive): static {
        return clone($this, ['customDirectives' => $this->customDirectives->with($directive)]);
    }
}
