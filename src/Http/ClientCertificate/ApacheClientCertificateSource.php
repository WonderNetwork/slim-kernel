<?php

declare(strict_types=1);

namespace WonderNetwork\SlimKernel\Http\ClientCertificate;

use Psr\Http\Message\ServerRequestInterface;
use WonderNetwork\SlimKernel\Http\RequestParams;

final readonly class ApacheClientCertificateSource implements ClientCertificateSource {
    public function findPem(ServerRequestInterface $request): ?string {
        $params = RequestParams::of($request);

        return $params->server->maybeString('SSL_CLIENT_CERT')
            ?? $params->server->maybeString('REDIRECT_SSL_CLIENT_CERT');
    }
}
