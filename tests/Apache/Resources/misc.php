<?php

declare(strict_types=1);

use WonderNetwork\SlimKernel\Apache\Vhost;

return Vhost::secure('example.org')
    ->withServerAlias('example.com')
    ->withCaName('non-standard-ca-name')
    ->withDowngradedTls()
    ->requireCanonicalDomain()
    ->withCustomDirectory(
        'docroot/private',
        fn ($directory) => $directory
            ->withComment('Only for authenticated: ')
            ->withClientCertificateRequirement(
                optional: true,
                export: true,
            )
            ->withPhpFallbackHandler('handler.php'),
    )
    ->withComment('expose some vendor files')
    ->withAlias('/some', 'vendor/some/public')
    ->withEnv('APP_ENDPOINT', 'https://foo.example.com', quoted: true)
    ->withProxyPass('/blog', 'https://blog.example.com')
    ->withUtf()
    ->withPhpFpm('proxy:unix:/var/run/php-fpm.sock|fcgi://localhost');
