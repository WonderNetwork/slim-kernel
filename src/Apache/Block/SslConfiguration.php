<?php

declare(strict_types=1);

namespace WonderNetwork\SlimKernel\Apache\Block;

use WonderNetwork\SlimKernel\Apache\Directive\Named;
use WonderNetwork\SlimKernel\Apache\DirectiveCollection;
use WonderNetwork\SlimKernel\Apache\Filesystem\Path;

final readonly class SslConfiguration implements DirectiveCollection {
    public static function from(
        Path $certificateFile,
        Path $certificateKeyFile,
        Path $caCertificateFile,
        Path $caRevocationFile,
        bool $downgrade,
    ): self {
        return new self(
            certificateFile: $certificateFile,
            certificateKeyFile: $certificateKeyFile,
            caCertificateFile: $caCertificateFile,
            caRevocationFile: $caRevocationFile,
            downgrade: $downgrade,
        );
    }

    private function __construct(
        private Path $certificateFile,
        private Path $certificateKeyFile,
        private Path $caCertificateFile,
        private Path $caRevocationFile,
        private bool $downgrade,
    ) {
    }

    public function toDirectives(): iterable {
        yield Named::from('SSLEngine', 'On');

        $this->downgrade && yield Named::from('SSLProtocol', 'TLSv1.2');
        yield Named::from('SSLCertificateFile', $this->certificateFile->value);
        yield Named::from('SSLCertificateKeyFile', $this->certificateKeyFile->value);

        yield Named::from('SSLCACertificateFile', $this->caCertificateFile->value);
        yield Named::from('SSLCARevocationCheck', 'leaf');
        yield Named::from('SSLCARevocationFile', $this->caRevocationFile->value);

        yield Named::from('Header always set Strict-Transport-Security', 'max-age=31536000');
    }
}
