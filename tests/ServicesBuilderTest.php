<?php

declare(strict_types=1);

namespace WonderNetwork\SlimKernel;

use PHPUnit\Framework\TestCase;
use function WonderNetwork\SlimKernel\Collection\collection;
use function WonderNetwork\SlimKernel\Collection\map;

final class ServicesBuilderTest extends TestCase {
    public function testSimpleGlob(): void {
        $this->assertFiles('*.php', ['alpha.php', 'bravo.php']);
    }

    public function testRecursiveGlob(): void {
        $this->assertFiles(
            '**/*.php',
            [
                'alpha.php',
                'bravo.php',
                'charlie/delta.php',
                'charlie/echo/foxtrot.php',
            ],
        );
    }

    /**
     * @param string[] $expected
     */
    private function assertFiles(string $pattern, array $expected): void {
        $basePath = __DIR__.'/Resources/ServicesBuilder/';
        $sut = new ServicesBuilder($basePath);
        $actual = collection($sut->glob($pattern))->toArray();
        $expected = map($expected, static fn (string $path) => $basePath.$path);

        self::assertSame($expected, $actual);
    }
}
