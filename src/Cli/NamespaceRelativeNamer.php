<?php
declare(strict_types=1);

namespace WonderNetwork\SlimKernel\Cli;

/**
 * @internal
 */
final class NamespaceRelativeNamer {
    private string $namespace;

    public static function ofBaseNamespace(string $namespace): self {
        return new self($namespace);
    }

    private function __construct(string $namespace) {
        $this->namespace = $namespace;
    }

    public function name(string $className): ?string {
        if (false === str_starts_with($className, $this->namespace)) {
            return null;
        }

        $relativeNamespace = substr($className, strlen($this->namespace) + 1);
        $commandSuffix = 'Command';
        if (str_ends_with($relativeNamespace, $commandSuffix)) {
            $relativeNamespace = substr($relativeNamespace, 0, -strlen($commandSuffix));
        }

        /** @var string $dashed */
        $dashed = preg_replace('/([a-z])([A-Z])/', '$1-$2', $relativeNamespace);
        $dotted = strtr($dashed, ['\\' => ':']);
        return strtolower($dotted);
    }

}
