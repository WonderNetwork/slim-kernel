<?php
declare(strict_types=1);

namespace WonderNetwork\SlimKernel\Cli;

use PHPUnit\Framework\TestCase;

class NamespaceRelativeNamerTest extends TestCase {
    public function testRelativeName(): void {
        $namespace = 'Acme\\Foo\\Cli';
        $className = 'Acme\\Foo\\Cli\\Category\\AlphaBravoCommand';
        $sut = NamespaceRelativeNamer::ofBaseNamespace($namespace);
        self::assertSame("category:alpha-bravo", $sut->name($className));
    }

    public function testBailsIfRootNamespaceDoesNotMAtch(): void {
        $namespace = 'Acme\\Foo\\Cli';
        $className = 'Acme\\Bar\\Cli\\Category\\AlphaBravoCommand';
        $sut = NamespaceRelativeNamer::ofBaseNamespace($namespace);
        self::assertNull($sut->name($className));
    }
}
