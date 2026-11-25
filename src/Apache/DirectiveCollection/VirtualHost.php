<?php

declare(strict_types=1);

namespace WonderNetwork\SlimKernel\Apache\DirectiveCollection;

use WonderNetwork\SlimKernel\Apache\Directive\Comment;
use WonderNetwork\SlimKernel\Apache\Directive\Composite;
use WonderNetwork\SlimKernel\Apache\DirectiveCollection;
use WonderNetwork\SlimKernel\Apache\Spacing;
use WonderNetwork\SlimKernel\Apache\Vhost;

final readonly class VirtualHost implements DirectiveCollection {
    public static function fromVhost(Vhost $vhost): self {
        return new self(
            collection: ContainerCollection::fromDirective(
                name: 'VirtualHost',
                value: sprintf('*:%d', $vhost->endpoint->value),
                directive: Composite::fromCollection($vhost)->toSpacing(Spacing::Group),
            ),
            comment: $vhost->comment,
        );
    }

    private function __construct(
        private ContainerCollection $collection,
        private ?Comment $comment,
    ) {
    }

    public function toDirectives(): iterable {
        yield $this->comment;
        yield from $this->collection->toDirectives();
    }
}
