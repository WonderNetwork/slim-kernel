<?php

declare(strict_types=1);

namespace WonderNetwork\SlimKernel\Apache;

use PHPUnit\Framework\Attributes\DataProvider;
use PHPUnit\Framework\TestCase;
use WonderNetwork\SlimKernel\SnapshotAssertion;
use function WonderNetwork\SlimKernel\Collection\collection;

final class ApacheConfigurationGeneratorTest extends TestCase {
    #[DataProvider('dataSnapshot')]
    public function testSnapshot(string $file, string $snapshot): void {
        $vhosts = require $file;

        if ($vhosts instanceof Vhost) {
            $vhosts = [$vhosts];
        }

        $actual = ApacheConfigurationGenerator::generate(...$vhosts);

        SnapshotAssertion::assertSnapshot($snapshot, $actual);
    }

    /**
     * @return iterable<mixed>
     */
    public static function dataSnapshot(): iterable {
        yield from collection(glob(__DIR__.'/Resources/*.php') ?: [])
            ->indexBy(static fn (string $filename) => basename($filename, '.php'))
            ->map(
                static fn (string $filename) => [
                    $filename,
                    strtr($filename, ['.php' => '.conf']),
                ],
            );
    }
}
