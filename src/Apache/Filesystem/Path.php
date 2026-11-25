<?php

declare(strict_types=1);

namespace WonderNetwork\SlimKernel\Apache\Filesystem;

final readonly class Path {
    public static function of(string $path): self {
        return new self(rtrim($path, '/'));
    }

    private function __construct(public string $value) {
    }

    public function resolve(string $path): self {
        if (false === str_starts_with($path, '/')) {
            $path = sprintf('%s/%s', $this->value, $path);
        }

        return self::of($path);
    }
}
