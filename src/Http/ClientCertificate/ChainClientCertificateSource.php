<?php

declare(strict_types=1);

namespace WonderNetwork\SlimKernel\Http\ClientCertificate;

use Psr\Http\Message\ServerRequestInterface;

final readonly class ChainClientCertificateSource implements ClientCertificateSource {
    public function __construct(
        /** @var list<ClientCertificateSource> */
        private array $sources,
    ) {
    }

    public function findPem(ServerRequestInterface $request): ?string {
        foreach ($this->sources as $source) {
            $pem = $source->findPem($request);

            if (null !== $pem) {
                return $pem;
            }
        }

        return null;
    }
}
