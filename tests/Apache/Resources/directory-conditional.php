<?php

declare(strict_types=1);

use WonderNetwork\SlimKernel\Apache\Vhost;

return Vhost::secure('example.com')
    ->toUnsecured()
    ->withDocumentRoot(
        fn ($directory) => $directory
            ->directoryIndex('index.php')
            ->conditional(
                condition: true,
                fn: fn ($directory) => $directory->withRewriteRule('robots.txt /robots-true.txt [L]'),
            )
            ->conditional(
                condition: false,
                fn: fn ($directory) => $directory->withRewriteRule('robots.txt /robots-false.txt [L]'),
            ),
    );
