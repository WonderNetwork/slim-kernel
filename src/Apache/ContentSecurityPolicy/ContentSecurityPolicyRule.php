<?php

declare(strict_types=1);

namespace WonderNetwork\SlimKernel\Apache\ContentSecurityPolicy;

final readonly class ContentSecurityPolicyRule {
    public static function empty(): self {
        return new self();
    }

    /**
     * @param array<string,string[]> $directives
     */
    private function __construct(
        private array $directives = [],
    ) {
    }

    public function merge(self $other): self {
        return new self(array_merge_recursive($this->directives, $other->directives));
    }

    public function defaultSrc(string ...$tokens): self {
        return new self(array_replace($this->directives, ['default-src' => $tokens]));
    }

    public function connectSrc(string ...$tokens): self {
        return new self(array_replace($this->directives, ['connect-src' => $tokens]));
    }

    public function fontSrc(string ...$tokens): self {
        return new self(array_replace($this->directives, ['font-src' => $tokens]));
    }

    public function imgSrc(string ...$tokens): self {
        return new self(array_replace($this->directives, ['img-src' => $tokens]));
    }

    public function objectSrc(string ...$tokens): self {
        return new self(array_replace($this->directives, ['object-src' => $tokens]));
    }

    public function scriptSrc(string ...$tokens): self {
        return new self(array_replace($this->directives, ['script-src' => $tokens]));
    }

    public function styleSrc(string ...$tokens): self {
        return new self(array_replace($this->directives, ['style-src' => $tokens]));
    }

    public function frameAncestors(string ...$tokens): self {
        return new self(array_replace($this->directives, ['frame-ancestors' => $tokens]));
    }

    public function formAction(string ...$tokens): self {
        return new self(array_replace($this->directives, ['form-action' => $tokens]));
    }

    public function baseUri(string ...$tokens): self {
        return new self(array_replace($this->directives, ['base-uri' => $tokens]));
    }

    public function manifestSrc(string ...$tokens): self {
        return new self(array_replace($this->directives, ['manifest-src' => $tokens]));
    }

    public function childSrc(string ...$tokens): self {
        return new self(array_replace($this->directives, ['child-src' => $tokens]));
    }

    public function frameSrc(string ...$tokens): self {
        return new self(array_replace($this->directives, ['frame-src' => $tokens]));
    }

    public function toString(): string {
        $directives = [];

        foreach ($this->directives as $key => $tokens) {
            $name = strtolower((string) preg_replace('/[A-Z]/', '-$0', $key));
            $firstToken = array_shift($tokens);

            if (null === $firstToken) {
                continue;
            }

            $result = sprintf('%s %s', $name, $firstToken);

            foreach ($tokens as $token) {
                $result .= sprintf(" \\\n    %s", $token);
            }

            $result .= ";";
            $directives[] = $result;
        }

        $value = implode(" \\\n", $directives);

        $prefix = str_repeat('  ', 2);

        $indented = strtr($value, ["\n" => "\n$prefix"]);

        return "\\\n$prefix\"$indented\"";
    }
}
