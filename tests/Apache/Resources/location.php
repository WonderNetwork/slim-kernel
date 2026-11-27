<?php

declare(strict_types=1);

use WonderNetwork\SlimKernel\Apache\Vhost;

return Vhost::secure('example.com')
    ->withDowngradedTls()
    ->withLocation(
        '/secure',
        fn ($location) => $location
            ->withClientCertificateRequirement(
                signerOrganization: 'Acme corp',
            )
            ->withHandler('server-status')
            ->withRewriteDisabled(),
    );
