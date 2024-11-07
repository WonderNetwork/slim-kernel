<?php
declare(strict_types=1);

namespace WonderNetwork\SlimKernel\Cli;

trait DefaultNameTrait {
    protected static function getNameRelativeToNamespace(string $namespace): ?string {
        return NamespaceRelativeNamer::ofBaseNamespace($namespace)->name(static::class);
    }
}
