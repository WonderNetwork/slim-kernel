<?php

declare(strict_types=1);

namespace WonderNetwork\SlimKernel\Apache\DirectiveCollection;

use Closure;
use WonderNetwork\SlimKernel\Apache\Block\ClientCertificate;
use WonderNetwork\SlimKernel\Apache\Block\PublicAccess;
use WonderNetwork\SlimKernel\Apache\Directive;
use WonderNetwork\SlimKernel\Apache\Directive\Comment;
use WonderNetwork\SlimKernel\Apache\Directive\Composite;
use WonderNetwork\SlimKernel\Apache\Directive\Named;
use WonderNetwork\SlimKernel\Apache\DirectiveCollection;
use WonderNetwork\SlimKernel\Apache\Exception\SecureSiteException;
use WonderNetwork\SlimKernel\Apache\Filesystem\Path;
use WonderNetwork\SlimKernel\Apache\Vhost;
use WonderNetwork\SlimKernel\Apache\WithDirectivesTrait;

final readonly class Directory implements DirectiveCollection {
    use WithDirectivesTrait;

    private Directive $access;
    private Directive $directoryIndex;
    private RewriteEngine $rewriteEngine;

    public static function from(Path $directory, Vhost $vhost): self {
        return new self($directory, $vhost);
    }

    private function __construct(
        private Path $directory,
        private Vhost $vhost,
    ) {
        $this->rewriteEngine = RewriteEngine::auto();
        $this->access = Composite::empty();
        $this->directoryIndex = Composite::empty();
        $this->customDirectives = $this->initCustomDirectives();
    }

    /**
     * @param Closure(self):self $fn
     */
    public function conditional(bool $condition, Closure $fn): self {
        return $condition ? $fn($this) : $this;
    }

    /**
     * Make sure directory is accessible
     */
    public function withPublicAccess(): self {
        return clone($this, ['access' => PublicAccess::asDirective()]);
    }

    public function directoryIndex(string $directoryIndex): self {
        return clone($this, ['directoryIndex' => Named::from('DirectoryIndex', $directoryIndex)]);
    }

    public function withRewriteCondition(string $condition): self {
        return clone($this, ['rewriteEngine' => $this->rewriteEngine->withCondition($condition)]);
    }

    public function withRewriteRule(string ...$rules): self {
        return clone($this, ['rewriteEngine' => $this->rewriteEngine->withRules(...$rules)]);
    }

    public function withRewriteComment(string $comment): self {
        return clone($this, ['rewriteEngine' => $this->rewriteEngine->withComment($comment)]);
    }

    public function withComment(string $comment): self {
        return $this->with(Comment::from($comment));
    }

    public function withPhpFallbackHandler(string $handler = 'index.php'): self {
        return $this
            ->withRewriteCondition('%{DOCUMENT_ROOT}%{REQUEST_URI} !-d')
            ->withRewriteCondition('%{DOCUMENT_ROOT}%{REQUEST_URI} !-f')
            ->withRewriteRule("^ $handler [L,QSA]");
    }

    /**
     * @throws SecureSiteException
     */
    public function withClientCertificateRequirement(
        ?bool $optional = null,
        ?bool $export = null,
    ): self {
        SecureSiteException::ensureSiteSecured($this->vhost, 'client certificate requirement');
        SecureSiteException::ensureTlsDowngraded($this->vhost);

        return $this->with(
            ClientCertificate::with(
                optional: $optional,
                export: $export,
            ),
        );
    }

    /**
     * @internal
     */
    public function toDirectives(): iterable {
        return ContainerCollection::fromDirective(
            name: 'Directory',
            value: $this->directory->value,
            directive: Composite::group(
                $this->access,
                $this->directoryIndex,
                Composite::fromCollection($this->rewriteEngine),
                $this->customDirectives,
            ),
        )->toDirectives();
    }
}
