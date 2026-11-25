<?php

declare(strict_types=1);

namespace WonderNetwork\SlimKernel\Apache\Block;

use WonderNetwork\SlimKernel\Apache\Directive\Named;
use WonderNetwork\SlimKernel\Apache\DirectiveCollection;

final readonly class ProxyPass implements DirectiveCollection {
    public static function from(string $path, string $endpoint): self {
        return new self($path, $endpoint);
    }

    private function __construct(private string $path, private string $endpoint) {
    }

    public function toDirectives(): iterable {
        $value = sprintf('%s %s', $this->path, $this->endpoint);

        yield Named::from('SSLProxyEngine', 'On');
        yield Named::from('ProxyPreserveHost', 'Off');
        yield Named::from('ProxyPass', $value);
        yield Named::from('ProxyPassReverse', $value);
    }
}
