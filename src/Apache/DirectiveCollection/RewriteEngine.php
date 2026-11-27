<?php

declare(strict_types=1);

namespace WonderNetwork\SlimKernel\Apache\DirectiveCollection;

use WonderNetwork\SlimKernel\Apache\Directive;
use WonderNetwork\SlimKernel\Apache\DirectiveCollection;
use WonderNetwork\SlimKernel\Apache\Exception\RewriteEngineException;
use WonderNetwork\SlimKernel\Apache\Spacing;
use WonderNetwork\SlimKernel\Apache\WithDirectivesTrait;

final readonly class RewriteEngine implements DirectiveCollection {
    use WithDirectivesTrait;

    public static function auto(): self {
        return new self();
    }

    public static function compact(): self {
        return new self(spacing: Spacing::Compact);
    }

    private function __construct(
        Spacing $spacing = Spacing::Group,
        private bool $enabled = true,
    ) {
        $this->customDirectives = $this->initCustomDirectives($spacing);
    }

    public function withCondition(string $condition): self {
        return $this->with(
            Directive\RewriteCond::from($condition),
        );
    }

    public function withRules(string ...$rules): self {
        return $this->with(
            Directive\Composite::fromDirectives(
                ...
                array_map(
                    static fn (string $rule) => Directive\Named::from('RewriteRule', $rule),
                    $rules,
                ),
            ),
        );
    }

    public function withComment(string $comment): self {
        return $this->with(Directive\Comment::from($comment));
    }

    public function disable(): self {
        return clone($this, ['enabled' => false]);
    }

    public function toDirectives(): iterable {
        if (false === $this->enabled) {
            yield Directive\Named::from('RewriteEngine', 'off');

            return;
        }

        if (false === $this->customDirectives->some()) {
            return;
        }

        $last = $this->customDirectives->last();

        if ($last instanceof Directive\RewriteCond) {
            throw RewriteEngineException::ofUnusedCondition();
        }

        yield $this->customDirectives->prepend(Directive\Named::from('RewriteEngine', 'on'));
    }
}
