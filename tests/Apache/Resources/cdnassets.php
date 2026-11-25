<?php

declare(strict_types=1);

use WonderNetwork\SlimKernel\Apache\Vhost;

return Vhost::secure('assets.example.com')
    ->withServerAdmin('support@example.com')
    ->withReleaseDir('example.com')
    ->withDocumentRoot(
        fn ($directory) => $directory
            ->withRewriteComment(
                <<<EOF
                in comparison to the regular vhost rule, this does not have
                the [L(ast)] flag, because we’d want to catch non-existent files
                in the rule next to it
                EOF,
            )
            ->withRewriteCondition('%{DOCUMENT_ROOT}%{REQUEST_URI} !-f')
            ->withRewriteRule('^(.*)\.\d+\.(js|css)$ $1.$2')

            ->withRewriteComment('Block requests to all non-files and any PHP scripts')
            ->withRewriteCondition('%{DOCUMENT_ROOT}%{REQUEST_FILENAME} !-f [or]')
            ->withRewriteCondition('%{DOCUMENT_ROOT}%{REQUEST_FILENAME} .php$')
            ->withRewriteRule('^ - [G,L]'),
    )
    ->withLongExpirationDatesForAssets()
    ->withCertificateName('example-apache.crt', 'example.key')
    ->withStandarLogs(nofail: true);
