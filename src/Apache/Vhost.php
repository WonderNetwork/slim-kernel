<?php

declare(strict_types=1);

namespace WonderNetwork\SlimKernel\Apache;

use Closure;
use WonderNetwork\SlimKernel\Apache\Block\ClientCertificate;
use WonderNetwork\SlimKernel\Apache\Block\DeflateCompression;
use WonderNetwork\SlimKernel\Apache\Block\Expires;
use WonderNetwork\SlimKernel\Apache\Block\PhpFpm;
use WonderNetwork\SlimKernel\Apache\Block\ProxyPass;
use WonderNetwork\SlimKernel\Apache\Block\SslConfiguration;
use WonderNetwork\SlimKernel\Apache\ContentSecurityPolicy\ContentSecurityPolicyBuilder;
use WonderNetwork\SlimKernel\Apache\Directive\Comment;
use WonderNetwork\SlimKernel\Apache\Directive\Composite;
use WonderNetwork\SlimKernel\Apache\Directive\Literal;
use WonderNetwork\SlimKernel\Apache\Directive\Named;
use WonderNetwork\SlimKernel\Apache\DirectiveCollection\Directory;
use WonderNetwork\SlimKernel\Apache\DirectiveCollection\Location;
use WonderNetwork\SlimKernel\Apache\DirectiveCollection\RewriteEngine;
use WonderNetwork\SlimKernel\Apache\Exception\ConfigurationOrderException;
use WonderNetwork\SlimKernel\Apache\Exception\SecureSiteException;

final readonly class Vhost implements DirectiveCollection {
    use WithDirectivesTrait;

    private Filesystem\Path $logDir;
    private Filesystem\Path $wwwDir;
    private Filesystem\Path $caDir;
    private Filesystem\Path $certsDir;
    private RewriteEngine $rewriteEngine;

    /**
     * Creates a regular HTTPS vhost
     */
    public static function secure(
        string $serverName,
        ?string $comment = null,
    ): self {
        return new self(
            serverName: $serverName,
            endpoint: Endpoint::Secure,
            comment: Directive\Comment::tryFrom($comment),
        );
    }

    /**
     * Creates HTTP vhost redirecting to its HTTPS counterpart (keeping host)
     */
    public static function upgradeToSecure(string $serverName, ?string $comment = null): self {
        $vhost = new self(
            serverName: $serverName,
            endpoint: Endpoint::Http,
            comment: Directive\Comment::tryFrom($comment),
        );

        return $vhost->withRewriteRule('^(.*) https://%{HTTP_HOST}$1 [R=301]');
    }

    /**
     * Creates HTTP vhost with aliases which redirects to a canonical HTTPS URL
     * @param list<string> $serverAlias
     */
    public static function upgradeToCanonical(string $serverName, array $serverAlias, ?string $comment = null): self {
        $vhost = new self(
            serverName: $serverName,
            endpoint: Endpoint::Http,
            comment: Directive\Comment::tryFrom($comment),
        );

        return $vhost
            ->withServerAlias(...$serverAlias)
            ->with(Literal::line("Redirect permanent / https://$serverName/"));
    }

    /**
     * Creates a regular HTTP host
     */
    public static function http(
        string $serverName,
        ?string $comment = null,
    ): self {
        return new self(
            serverName: $serverName,
            endpoint: Endpoint::Http,
            comment: Directive\Comment::tryFrom($comment),
        );
    }

    private function __construct(
        public string $serverName,
        public Endpoint $endpoint,
        public ?Directive\Comment $comment = null,
        private ?string $serverAdmin = null,
        private ?string $serverAlias = null,
        private ?string $customLog = null,
        private ?LogLevel $logLevel = null,
        private ?string $errorLog = null,
        private ?Filesystem\Path $certificateFile = null,
        private ?Filesystem\Path $certificateKeyFile = null,
        public bool $downgradeTls = false,
        private ?ClientCertificate $clientCertificateRequirement = null,
        private ?Filesystem\Path $documentRoot = null,
        private ?Directory $directory = null,
        private ?PhpFpm $phpFpm = null,
        private ?Expires $expires = null,
        private ?DeflateCompression $deflate = null,
        private ?string $releaseDir = null,
        private bool $oneLiveRelease = false,
        private string $caName = 'wonderproxy-ca',
    ) {
        $this->logDir = Filesystem\Path::of('/var/log/apache2');
        $this->wwwDir = Filesystem\Path::of('/var/www');
        $this->caDir = Filesystem\Path::of('/etc/ca');
        $this->certsDir = Filesystem\Path::of('/etc/certs');
        $this->rewriteEngine = RewriteEngine::compact();
        $this->customDirectives = $this->initCustomDirectives();
    }

    /**
     * @internal
     */
    public function toDirectives(): iterable {
        $baseDomainName = array_first(array_slice(explode(".", $this->serverName), -2));
        $defaultCertPath = $this->certsDir->resolve("$baseDomainName-apache.crt");
        $defaultKeyPath = $this->certsDir->resolve("$baseDomainName.key");

        yield Composite::fromDirectives(
            Named::tryFrom('ServerName', $this->serverName),
            Named::tryFrom('ServerAdmin', $this->serverAdmin),
            Named::tryFrom('ServerAlias', $this->serverAlias),
        );

        yield Composite::fromCollection($this->rewriteEngine);

        yield Composite::tryFromCollection($this->phpFpm);

        if ($this->endpoint->isSecure()) {
            yield Composite::fromCollection(
                SslConfiguration::from(
                    certificateFile: $this->certificateFile ?? $defaultCertPath,
                    certificateKeyFile: $this->certificateKeyFile ?? $defaultKeyPath,
                    caCertificateFile: $this->caDir->resolve("$this->caName.crt"),
                    caRevocationFile: $this->caDir->resolve("$this->caName.crl"),
                    downgrade: $this->downgradeTls,
                ),
            );

            yield Composite::tryFromCollection($this->clientCertificateRequirement);
        }

        yield Composite::fromDirectives(
            Named::tryFrom('DocumentRoot', $this->documentRoot?->value),
            Composite::tryFromCollection($this->directory),
        );

        yield Composite::fromDirectives(
            Named::tryFrom('CustomLog', $this->customLog),
            Named::tryFrom('LogLevel', $this->logLevel?->value),
            Named::tryFrom('ErrorLog', $this->errorLog),
        );

        yield Composite::tryFromCollection($this->expires);
        yield Composite::tryFromCollection($this->deflate);

        yield $this->customDirectives;
    }

    public function withServerAdmin(string $serverAdmin): self {
        return clone($this, ['serverAdmin' => $serverAdmin]);
    }

    public function withServerAlias(string ...$aliases): self {
        return clone($this, ['serverAlias' => implode(' ', $aliases)]);
    }

    /**
     * Vhosts can have a numer of unconditional rewrite rules by default
     * Use {@see withDocumentRoot} or {@see withCustomDirectory} for more
     * advanced RewriteEngine usage
     */
    public function withRewriteRule(string $rule): self {
        return clone($this, ['rewriteEngine' => $this->rewriteEngine->withRules($rule)]);
    }

    public function requireCanonicalDomain(): self {
        return clone(
            $this,
            [
                'rewriteEngine' => $this->rewriteEngine
                    ->withCondition(sprintf('%%{SERVER_NAME} !=%s', $this->serverName))
                    ->withRules(sprintf('^(.*) https://%s$1 [R=301,L]', $this->serverName)),
            ],
        );
    }

    /**
     * @throws ConfigurationOrderException
     */
    public function withLogDirectory(string $logDir): self {
        if ($this->errorLog || $this->customLog) {
            throw ConfigurationOrderException::ofLoggingAlreadyDefined();
        }

        return clone($this, ['logDir' => Filesystem\Path::of($logDir)]);
    }

    /**
     * @throws ConfigurationOrderException
     */
    public function withWwwDirectory(string $wwwDir): self {
        if ($this->documentRoot) {
            throw ConfigurationOrderException::ofDocumentRootAlreadyDefined();
        }

        return clone($this, ['wwwDir' => Filesystem\Path::of($wwwDir)]);
    }

    public function withCertsDirectory(string $certsDirectory): self {
        return clone($this, ['certsDir' => Filesystem\Path::of($certsDirectory)]);
    }

    public function withCaDirectory(string $caDirectory): self {
        return clone($this, ['caDir' => Filesystem\Path::of($caDirectory)]);
    }

    /**
     * Use a specific CA authority file name to verify local certificates
     * @throws SecureSiteException
     */
    public function withCaName(string $caName): self {
        SecureSiteException::ensureSiteSecured($this, 'certificate authority name');

        return clone($this, ['caName' => $caName]);
    }

    /**
     * Automatically use Lets Encrypt certificates.
     * Optionally provide a custom name to use instead the server name
     * @throws SecureSiteException
     */
    public function withLetsEncrypt(?string $name = null): self {
        SecureSiteException::ensureSiteSecured($this, 'certificate file path (let’s encrypt)');

        $name ??= $this->serverName;

        $lePath = Filesystem\Path::of('/etc/letsencrypt/live')->resolve($name);

        return clone(
            $this,
            [
                'certificateFile' => $lePath->resolve('fullchain.pem'),
                'certificateKeyFile' => $lePath->resolve('privkey.pem'),
            ],
        );
    }

    /**
     * @throws SecureSiteException
     */
    public function withCertificateName(string $certificate, string $privateKey): self {
        SecureSiteException::ensureSiteSecured($this, 'certificate file path');

        return clone(
            $this,
            [
                'certificateFile' => $this->certsDir->resolve($certificate),
                'certificateKeyFile' => $this->certsDir->resolve($privateKey),
            ],
        );
    }

    public function withCustomLog(string $customLog): self {
        return clone($this, ['customLog' => $customLog]);
    }

    public function withLogLevel(LogLevel $logLevel): self {
        return clone($this, ['logLevel' => $logLevel]);
    }

    public function withErrorLog(string $errorLog): self {
        return clone($this, ['errorLog' => $errorLog]);
    }

    /**
     * Use regular pair of access and error logs, based on the server name
     */
    public function withStandarLogs(bool $nofail = false): self {
        $suffix = $nofail ? '-nofail' : '';

        return $this
            ->withLogLevel(LogLevel::Warning)
            ->withCustomLog(
                sprintf(
                    '%s combined',
                    $this->logDir->resolve("{$this->serverName}_access{$suffix}.log")->value,
                ),
            )
            ->withErrorLog(
                $this->logDir->resolve("{$this->serverName}_error{$suffix}.log")->value,
            );
    }

    /**
     * Downgrade TLS to v1.2 to allow for path-based client cert requirements
     * @throws SecureSiteException
     */
    public function withDowngradedTls(): self {
        SecureSiteException::ensureSiteSecured($this, 'TLS version');

        return clone($this, ['downgradeTls' => true]);
    }

    /**
     * Don’t use downgraded TLS version
     * @throws SecureSiteException
     */
    public function withUpgradedTls(): self {
        SecureSiteException::ensureSiteSecured($this, 'TLS version');

        return clone($this, ['downgradeTls' => false]);
    }

    /**
     * Adds an unconditional client certificate requirement to the entire vhost
     * $signerOrganization may be provided to limit which certificates are accepted
     * @throws SecureSiteException
     */
    public function withClientCertificateRequirement(
        ?bool $export = null,
        ?string $signerOrganization = null,
    ): self {
        SecureSiteException::ensureSiteSecured($this, 'client certificate requirement');

        $requirement = ClientCertificate::with(export: $export, signerOrganization: $signerOrganization);

        return clone($this, ['clientCertificateRequirement' => $requirement]);
    }

    /**
     * Use if the release path does not match the server name
     * @throws ConfigurationOrderException
     */
    public function withReleaseDir(string $path): self {
        if ($this->documentRoot) {
            throw ConfigurationOrderException::ofDocumentRootAlreadyDefined();
        }

        return clone($this, ['releaseDir' => $path]);
    }

    public function withOneLiveRelease(): self {
        if ($this->documentRoot) {
            throw ConfigurationOrderException::ofDocumentRootAlreadyDefined();
        }

        return clone($this, ['oneLiveRelease' => true]);
    }

    /**
     * @param null|Closure(Directory):Directory $fn
     */
    public function withDocumentRoot(?Closure $fn = null): self {
        $fn ??= static fn ($directory) => $directory;

        $documentRoot = $this->currentReleaseDir("docroot");
        $directory = $fn(Directory::from($documentRoot, $this)->withPublicAccess());

        return clone(
            $this,
            [
                'documentRoot' => $documentRoot,
                'directory' => $directory,
            ],
        );
    }

    /**
     * @param Closure(Directory):Directory $fn
     */
    public function withCustomDirectory(string $path, Closure $fn): self {
        return $this->with(
            $fn(
                Directory::from(
                    directory: $this->currentReleaseDir($path),
                    vhost: $this,
                ),
            ),
        );
    }

    /**
     * @param Closure(Location):Location $fn
     */
    public function withLocation(string $location, Closure $fn): self {
        return $this->with($fn(Location::from($location, $this)));
    }

    public function withAlias(string $from, string $to): self {
        return $this->with(Named::from("Alias $from", $this->currentReleaseDir($to)->value));
    }

    public function withComment(string $comment): self {
        return $this->with(Comment::from($comment));
    }

    public function withEnv(string $name, string $value, ?bool $quoted = null): self {
        return $this->with(
            $quoted
                ? Named::quoted("SetEnv $name", $value)
                : Named::from("SetEnv $name", $value),
        );
    }

    public function withUtf(): self {
        return $this->with(Named::from('AddDefaultCharset', 'utf-8'));
    }

    /**
     * Use a builder to add a Content-Security-Policy header
     * @param Closure(ContentSecurityPolicyBuilder):ContentSecurityPolicyBuilder $fn
     */
    public function withContentSecurityPolicy(Closure $fn): self {
        return $this->with(
            Named::from(
                'Header always set Content-Security-Policy',
                $fn(ContentSecurityPolicyBuilder::start())->toString(),
            ),
        );
    }

    /**
     * Serve an entire subpath (eg /blog) from a separate server
     */
    public function withProxyPass(string $path, string $endpoint): self {
        return $this->with(ProxyPass::from($path, $endpoint));
    }

    public function withPhpFpm(string $handler): self {
        return clone($this, ['phpFpm' => PhpFpm::fromHandler($handler)]);
    }

    public function withLongExpirationDatesForAssets(): self {
        return clone($this, ['expires' => new Expires()]);
    }

    public function withDeflateCompression(): self {
        return clone($this, ['deflate' => new DeflateCompression()]);
    }

    /**
     * @param Closure(self):self $fn
     */
    public function conditional(bool $condition, Closure $fn): self {
        return $condition ? $fn($this) : $this;
    }

    /**
     * Convert Virtual Host to HTTP one, removing all SSL configuration
     */
    public function toUnsecured(): self {
        return clone($this, ['endpoint' => Endpoint::Http]);
    }

    private function currentReleaseDir(string $resolvePath): Filesystem\Path {
        return $this->wwwDir
            ->resolve($this->releaseDir ?? $this->serverName)
            ->resolve($this->oneLiveRelease ? "" : "current")
            ->resolve($resolvePath);
    }
}
