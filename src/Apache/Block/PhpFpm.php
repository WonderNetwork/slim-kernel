<?php

declare(strict_types=1);

namespace WonderNetwork\SlimKernel\Apache\Block;

use WonderNetwork\SlimKernel\Apache\Directive;
use WonderNetwork\SlimKernel\Apache\Directive\Named;
use WonderNetwork\SlimKernel\Apache\DirectiveCollection;
use WonderNetwork\SlimKernel\Apache\DirectiveCollection\Conditional;
use WonderNetwork\SlimKernel\Apache\DirectiveCollection\FilesMatch;
use WonderNetwork\SlimKernel\Apache\DirectiveCollection\IfModule;

final readonly class PhpFpm implements DirectiveCollection {
    public static function fromHandler(string $handler): self {
        return new self($handler);
    }

    private function __construct(private string $handler) {
    }

    public function toDirectives(): iterable {
        yield Directive\Composite::fromCollection(
            IfModule::fromCollection(
                value: 'proxy_fcgi_module',
                collection: FilesMatch::fromCollection(
                    value: '.+\.ph(ar|p|tml)$',
                    collection: Conditional::fromDirective(
                        value: '-f %{SCRIPT_FILENAME}',
                        directive: Named::quoted(
                            name: 'SetHandler',
                            value: $this->handler,
                        ),
                    ),
                ),
            ),
        );
    }
}
