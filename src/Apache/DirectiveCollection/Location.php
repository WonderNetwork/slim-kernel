<?php

declare(strict_types=1);

namespace WonderNetwork\SlimKernel\Apache\DirectiveCollection;

use Closure;
use WonderNetwork\SlimKernel\Apache\Block\ClientCertificate;
use WonderNetwork\SlimKernel\Apache\Directive;
use WonderNetwork\SlimKernel\Apache\Directive\Composite;
use WonderNetwork\SlimKernel\Apache\DirectiveCollection;
use WonderNetwork\SlimKernel\Apache\Exception\SecureSiteException;
use WonderNetwork\SlimKernel\Apache\Vhost;
use WonderNetwork\SlimKernel\Apache\WithDirectivesTrait;
use WonderNetwork\SlimKernel\Apache\WithSpacing;

final readonly class Location implements DirectiveCollection {
    use WithDirectivesTrait;
    use WithSpacing;

    private RewriteEngine $rewriteEngine;

    public static function from(string $location, Vhost $vhost): self {
        return new self($location, $vhost);
    }

    private function __construct(
        private string $location,
        private Vhost $vhost,
    ) {
        $this->rewriteEngine = RewriteEngine::auto();
        $this->spacing = $this->initSpacing();
        $this->customDirectives = $this->initCustomDirectives($this->spacing);
    }

    public function withRewriteDisabled(): self {
        return clone($this, ['rewriteEngine' => $this->rewriteEngine->disable()]);
    }

    public function withHandler(string $handler): self {
        return $this->with(Directive\Named::from('SetHandler', $handler));
    }

    /**
     * Require client certificate to access this location. Make sure to downgrade
     * TLS to use this option. Specify `$signerOrganization` to only allow certificates
     * signed by a specific CA
     * @throws SecureSiteException
     */
    public function withClientCertificateRequirement(?string $signerOrganization = null): self {
        SecureSiteException::ensureSiteSecured($this->vhost, 'client certificate requirement');
        SecureSiteException::ensureTlsDowngraded($this->vhost);

        return $this->with(ClientCertificate::with(signerOrganization: $signerOrganization));
    }

    /**
     * @param Closure(self):self $fn
     */
    public function conditional(bool $condition, Closure $fn): self {
        return $condition ? $fn($this) : $this;
    }

    /**
     * @internal
     */
    public function toDirectives(): iterable {
        return ContainerCollection::fromDirective(
            name: 'Location',
            value: $this->location,
            directive: Composite::fromDirectives(
                Composite::fromCollection($this->rewriteEngine),
                $this->customDirectives->toSpacing($this->spacing),
            )->toSpacing($this->spacing),
        )->toDirectives();
    }
}
