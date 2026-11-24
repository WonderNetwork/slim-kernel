<?php

declare(strict_types=1);

namespace WonderNetwork\SlimKernel\Http\ClientCertificate;

use Psr\Http\Message\ServerRequestInterface;

final readonly class TraefikClientCertificateSource implements ClientCertificateSource {
    public function findPem(ServerRequestInterface $request): ?string {
        $body = $request->getHeaderLine('X-Forwarded-Tls-Client-Cert');

        if (!$body) {
            return null;
        }

        return "-----BEGIN CERTIFICATE-----\n$body\n-----END CERTIFICATE-----";
    }
}
