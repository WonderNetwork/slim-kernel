<?php

declare(strict_types=1);

namespace WonderNetwork\SlimKernel;

use Closure;
use RuntimeException;
use function WonderNetwork\SlimKernel\Collection\findFiles;

final class ConfigurationFiles {
    private string $root;

    public function __construct(string $root) {
        $this->root = $root;
    }

    /**
     * @return Closure[]
     */
    public function glob(string $pattern): iterable {
        foreach (findFiles($this->root, $pattern) as $file) {
            $closure = require $file;

            if (false === $closure instanceof Closure) {
                throw new RuntimeException(
                    sprintf("The return value from '%s' needs to be a closure", $file),
                );
            }

            yield $closure;
        }
    }
}
