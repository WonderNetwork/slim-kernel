<?php

declare(strict_types=1);

namespace WonderNetwork\SlimKernel\Apache\Block;

use WonderNetwork\SlimKernel\Apache\Directive;
use WonderNetwork\SlimKernel\Apache\Directive\Composite;
use WonderNetwork\SlimKernel\Apache\DirectiveCollection;

final readonly class PublicAccess implements DirectiveCollection {
    public static function asDirective(): Directive {
        return Composite::fromCollection(new self());
    }

    public function toDirectives(): iterable {
        yield Directive\Literal::line('Require all granted');
    }
}
