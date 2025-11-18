<?php

declare(strict_types=1);

namespace WonderNetwork\SlimKernel;

use RuntimeException;
use function DI\autowire;
use function WonderNetwork\SlimKernel\Collection\collection;
use function WonderNetwork\SlimKernel\Collection\findFiles;
use function WonderNetwork\SlimKernel\Collection\map;

/**
 * @phpstan-type ComposerJson array{autoload:array{"psr-4"?:array<string,string|string[]>}}
 */
final class Autowire {
    /** @var array<string, string[]> */
    private array $autoload;
    private string $root;

    public static function fromRootPath(string $root): self {
        $composerJson = file_get_contents($root.'/composer.json');

        if (false === $composerJson) {
            throw new RuntimeException('Error reading composer.json file');
        }

        /** @var ?ComposerJson $composer */
        $composer = json_decode($composerJson, true);

        if (false === is_array($composer)) {
            throw new RuntimeException('Error parsing composer.json file');
        }

        return new self($root, $composer);
    }

    /**
     * @param ComposerJson $composer
     */
    private function __construct(string $root, array $composer) {
        $this->root = rtrim($root, '/');

        $this->autoload = map(
            $composer['autoload']['psr-4'] ?? [],
            static fn ($directories) => map(
                (array) $directories,
                static fn (string $directory) => realpath($root.'/'.$directory).'/',
            ),
        );
    }

    /**
     * @return string[]
     */
    public function glob(string $pattern): array {
        $files = findFiles($this->root, $pattern);

        return collection($files)
            ->map(
                fn (string $fileName) => $this->getClassName($fileName),
            )
            ->indexBy(static fn (string $className) => $className)
            ->map(static fn () => autowire())
            ->toArray();
    }

    private function getClassName(string $fileName): string {
        foreach ($this->autoload as $namespace => $directories) {
            foreach ($directories as $directory) {
                if (str_starts_with($fileName, $directory)) {
                    $relativePath = substr($fileName, strlen($directory), -strlen('.php'));

                    return $namespace.strtr($relativePath, ['/' => '\\']);
                }
            }
        }

        throw new RuntimeException("Failed to deduce class name for path $fileName");
    }
}
