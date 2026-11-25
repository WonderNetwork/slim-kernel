<?php

declare(strict_types=1);

namespace WonderNetwork\SlimKernel\Apache\Directive;

use WonderNetwork\SlimKernel\Apache\Directive;
use WonderNetwork\SlimKernel\Apache\DirectiveCollection;
use WonderNetwork\SlimKernel\Apache\Exception\ApacheConfigurationException;
use WonderNetwork\SlimKernel\Apache\Exception\CompositeException;
use WonderNetwork\SlimKernel\Apache\ExtendedDirective;
use WonderNetwork\SlimKernel\Apache\Spacing;
use function WonderNetwork\SlimKernel\Collection\collection;

final readonly class Composite implements ExtendedDirective {
    /**
     * @throws ApacheConfigurationException
     */
    public static function fromCollection(DirectiveCollection $collection): self {
        return self::fromDirectives(...$collection->toDirectives());
    }

    /**
     * @throws ApacheConfigurationException
     */
    public static function tryFromCollection(?DirectiveCollection $collection): ?self {
        return $collection ? self::fromCollection($collection) : null;
    }

    public static function fromDirectives(?Directive ...$directives): self {
        return new self(array_values($directives), spacing: Spacing::Compact);
    }

    public static function empty(): self {
        return self::fromDirectives();
    }

    public static function emptyGroup(): self {
        return self::group();
    }

    public static function group(?Directive ...$directives): self {
        return new self(array_values($directives), spacing: Spacing::Group);
    }

    /**
     * @param list<null|Directive> $directives
     */
    private function __construct(
        private array $directives,
        private Spacing $spacing,
    ) {
    }

    public function toSpacing(Spacing $spacing): self {
        return new self($this->directives, $spacing);
    }

    public function some(): bool {
        return 0 !== count($this->directives);
    }

    public function with(Directive $directive): self {
        $directives = $this->directives;

        if ($last = $this->getLastGluedDirective()) {
            // flatten glued directives:
            array_pop($directives);

            if ($last instanceof self) {
                $directive = $last->with($directive);
            } else {
                $directive = self::fromDirectives($last, $directive);
            }
        }

        return new self(
            directives: [...$directives, $directive],
            spacing: $this->spacing,
        );
    }

    public function prepend(Directive $directive): self {
        return new self(
            directives: [$directive, ...$this->directives],
            spacing: $this->spacing,
        );
    }

    public function last(): ?Directive {
        return array_last($this->directives);
    }

    public function toString(): string {
        if ($this->isGlued()) {
            throw CompositeException::ofLastLineGlued();
        }

        return collection($this->directives)
            ->map(fn (?Directive $directive) => $directive?->toString())
            ->filter(static fn (?string $line) => "" !== (string) $line)
            ->interpose($this->spacing->toNewlines())
            ->toString();
    }

    public function isGlued(): bool {
        return null !== $this->getLastGluedDirective();
    }

    private function getLastGluedDirective(): ?ExtendedDirective {
        return ($last = $this->last()) && $last instanceof ExtendedDirective && $last->isGlued() ? $last : null;
    }
}
