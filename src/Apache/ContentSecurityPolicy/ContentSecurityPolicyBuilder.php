<?php

declare(strict_types=1);

namespace WonderNetwork\SlimKernel\Apache\ContentSecurityPolicy;

use Closure;
use RuntimeException;

final readonly class ContentSecurityPolicyBuilder {
    public const string None = "'none'";
    public const string Self = "'self'";
    public const string Data = 'data:';
    public const string Blob = 'blob:';
    public const string WasmUnsafeEval = "'wasm-unsafe-eval'";
    public const string UnsafeInline = "'unsafe-inline'";
    public const string UnsafeEval = "'unsafe-eval'";

    public static function start(): self {
        return new self();
    }

    private function __construct(
        private ?ContentSecurityPolicyRule $rule = null,
    ) {
    }

    /**
     * @param non-empty-string $name
     * @param Closure(ContentSecurityPolicyRule,string):ContentSecurityPolicyRule $fn
     */
    public function rule(string $name, Closure $fn): self {
        $rule = $fn(ContentSecurityPolicyRule::empty(), $name);

        return new self($this->rule?->merge($rule) ?? $rule);
    }

    /**
     * @param non-empty-string $name
     * @param Closure(ContentSecurityPolicyRule,string):ContentSecurityPolicyRule $fn
     */
    public function conditionalRule(string $name, bool $condition, Closure $fn): self {
        return $condition ? $this->rule($name, $fn) : $this;
    }

    public function toString(): string {
        return $this->rule?->toString() ?? throw new RuntimeException('No rules defined');
    }
}
