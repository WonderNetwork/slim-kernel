<?php

declare(strict_types=1);

namespace WonderNetwork\SlimKernel;

use JsonException;
use PHPUnit\Framework\Assert;
use RuntimeException;

final readonly class SnapshotAssertion {
    public static function assertSnapshot(
        string $snapshotPath,
        mixed $actual,
        string $message = '',
    ): void {
        $isJson = false;

        if (is_array($actual)) {
            $isJson = true;

            try {
                $actual = json_encode($actual, JSON_PRETTY_PRINT | JSON_THROW_ON_ERROR);
            } catch (JsonException $e) {
                throw new RuntimeException('Failed to decode JSON: '.$e->getMessage());
            }
        }

        Assert::assertIsString($actual);

        $dirname = dirname($snapshotPath);
        $filename = basename($snapshotPath);

        $actualPath = sprintf('%s/snapshot.actual.%s', $dirname, $filename);
        $expectedPath = sprintf('%s/snapshot.expected.%s', $dirname, $filename);

        file_put_contents($actualPath, $actual);

        if (false === file_exists($expectedPath)) {
            file_put_contents($expectedPath, $actual);
        }

        if ($isJson) {
            Assert::assertJsonStringEqualsJsonFile($expectedPath, $actual, $message);

            return;
        }

        Assert::assertStringEqualsFile($expectedPath, $actual, $message);
    }
}
