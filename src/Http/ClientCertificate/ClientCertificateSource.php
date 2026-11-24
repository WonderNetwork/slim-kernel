<?php

declare(strict_types=1);

namespace WonderNetwork\SlimKernel\Http\ClientCertificate;

use Psr\Http\Message\ServerRequestInterface;

interface ClientCertificateSource {
    public function findPem(ServerRequestInterface $request): ?string;
}
