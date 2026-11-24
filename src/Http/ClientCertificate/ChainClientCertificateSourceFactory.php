<?php

declare(strict_types=1);

namespace WonderNetwork\SlimKernel\Http\ClientCertificate;

use WonderNetwork\SlimKernel\Http\HttpEnvironment;

final readonly class ChainClientCertificateSourceFactory {
    public static function create(HttpEnvironment $environment): ChainClientCertificateSource {
        $sources = [new ApacheClientCertificateSource()];

        if ($environment->trustsHeaders()) {
            $sources[] = new TraefikClientCertificateSource();
        }

        return new ChainClientCertificateSource($sources);
    }
}
