<?php

declare(strict_types=1);

namespace WonderNetwork\SlimKernel\Apache\Block;

use WonderNetwork\SlimKernel\Apache\Directive\Literal;
use WonderNetwork\SlimKernel\Apache\DirectiveCollection;

final readonly class Expires implements DirectiveCollection {
    public function toDirectives(): iterable {
        yield Literal::block(
            <<<EXPIRES
            ExpiresActive On

            ExpiresByType image/png "access plus 6 months"
            ExpiresByType image/svg+xml "access plus 6 months"
            ExpiresByType image/jpeg "access plus 6 months"
            ExpiresByType image/x-icon "access plus 6 months"
            ExpiresByType text/css "access plus 6 months"
            ExpiresByType font/ttf "access plus 6 months"
            ExpiresByType font/woff2 "access plus 6 months"
            ExpiresByType font/otf "access plus 6 months"
            ExpiresByType application/javascript "access plus 6 months"
            ExpiresByType application/font-sfnt "access plus 6 months"
            ExpiresByType application/font-woff "access plus 6 months"
            ExpiresByType application/font-woff2 "access plus 6 months"

            # No expires for dynamic files:
            ExpiresByType text/html "access plus 0 seconds"
            ExpiresByType application/json "access plus 0 seconds"
            EXPIRES,
        );
    }
}
