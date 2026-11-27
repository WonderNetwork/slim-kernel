<?php

declare(strict_types=1);

namespace WonderNetwork\SlimKernel\Apache\Block;

use WonderNetwork\SlimKernel\Apache\Directive\Comment;
use WonderNetwork\SlimKernel\Apache\Directive\Composite;
use WonderNetwork\SlimKernel\Apache\Directive\Literal;
use WonderNetwork\SlimKernel\Apache\Directive\Named;
use WonderNetwork\SlimKernel\Apache\DirectiveCollection;
use WonderNetwork\SlimKernel\Apache\DirectiveCollection\IfModule;

final readonly class DeflateCompression implements DirectiveCollection {
    public function toDirectives(): iterable {
        yield Comment::from('https://github.com/h5bp/server-configs-apache');

        $acceptEncodingCheck = IfModule::fromCollection(
            value: 'mod_setenvif.c',
            collection: IfModule::fromDirective(
                value: 'mod_headers.c',
                directive: Composite::fromDirectives(
                    Named::from(
                        'SetEnvIfNoCase',
                        '^(Accept-EncodXng|X-cept-Encoding|X{15}|~{15}|-{15})$ ^((gzip|deflate)\s*,?\s*)+|[X~-]{4,13}$ HAVE_Accept-Encoding',
                    ),
                    Named::from(
                        'RequestHeader append Accept-Encoding',
                        '"gzip,deflate" env=HAVE_Accept-Encoding',
                    ),
                ),
            ),
        );

        // <editor-fold defaultstate="collapsed" desc="AddOuputFilterByType DEFLATE">
        $outputType = Literal::block(
            <<<OUTPUTTYPE
            AddOutputFilterByType DEFLATE "application/atom+xml" \
                                          "application/javascript" \
                                          "application/json" \
                                          "application/ld+json" \
                                          "application/manifest+json" \
                                          "application/rdf+xml" \
                                          "application/rss+xml" \
                                          "application/schema+json" \
                                          "application/vnd.geo+json" \
                                          "application/vnd.ms-fontobject" \
                                          "application/x-font-ttf" \
                                          "application/font-sfnt" \
                                          "application/font-woff2" \
                                          "application/font-woff" \
                                          "application/x-javascript" \
                                          "application/x-web-app-manifest+json" \
                                          "application/xhtml+xml" \
                                          "application/xml" \
                                          "font/eot" \
                                          "font/otf" \
                                          "font/ttf" \
                                          "font/opentype" \
                                          "image/bmp" \
                                          "image/svg+xml" \
                                          "image/vnd.microsoft.icon" \
                                          "image/x-icon" \
                                          "text/cache-manifest" \
                                          "text/css" \
                                          "text/html" \
                                          "text/javascript" \
                                          "text/plain" \
                                          "text/vcard" \
                                          "text/vnd.rim.location.xloc" \
                                          "text/vtt" \
                                          "text/x-component" \
                                          "text/x-cross-domain-policy" \
                                          "text/xml"
            OUTPUTTYPE,
        );
        // </editor-fold>

        yield from IfModule::fromDirective(
            value: 'mod_deflate.c',
            directive: Composite::group(
                Composite::fromCollection($acceptEncodingCheck),
                Composite::fromCollection(
                    IfModule::fromDirective(
                        value: 'mod_filter.c',
                        directive: $outputType,
                    ),
                ),
                Composite::fromCollection(
                    IfModule::fromDirective(
                        value: 'mod_mime.c',
                        directive: Named::from('AddEncoding', 'gzip svgz'),
                    ),
                ),
            ),
        )->toDirectives();
    }
}
