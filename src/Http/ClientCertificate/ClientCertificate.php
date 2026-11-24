<?php

declare(strict_types=1);

namespace WonderNetwork\SlimKernel\Http\ClientCertificate;

final readonly class ClientCertificate {
    public function __construct(
        public string $commonName,
        public ?string $emailAddress,
    ) {
    }
}
