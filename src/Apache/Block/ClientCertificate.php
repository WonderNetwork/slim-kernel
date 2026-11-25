<?php

declare(strict_types=1);

namespace WonderNetwork\SlimKernel\Apache\Block;

use WonderNetwork\SlimKernel\Apache\Directive\Named;
use WonderNetwork\SlimKernel\Apache\DirectiveCollection;

final readonly class ClientCertificate implements DirectiveCollection {
    public static function with(
        ?bool $optional = null,
        ?bool $export = null,
        ?string $signerOrganization = null,
    ): self {
        return new self(
            optional: $optional ?? false,
            export: $export ?? false,
            signerOrganization: $signerOrganization,
        );
    }

    private function __construct(
        private bool $optional,
        private bool $export,
        private ?string $signerOrganization,
    ) {
    }

    public function toDirectives(): iterable {
        yield Named::from('SSLVerifyClient', $this->optional ? 'optional' : 'require');
        yield Named::from('SSLVerifyDepth', 1);
        $this->export && yield Named::from('SSLOptions', '+ExportCertData');
        $this->optional && yield Named::from('Require', 'ssl-verify-client');
        $this->signerOrganization && yield Named::quoted('Require expr %{SSL_CLIENT_I_DN_O} ==', $this->signerOrganization);
    }
}
