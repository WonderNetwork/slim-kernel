<?php

declare(strict_types=1);

namespace WonderNetwork\SlimKernel;

use PHPUnit\Framework\TestCase;
use RuntimeException;

final class AutowireTest extends TestCase {
    private string $root;

    protected function setUp(): void {
        $this->root = __DIR__.'/Resources/Autowire';
    }

    public function testResolvesClassNameUsingComposerAutoload(): void {
        $sut = Autowire::fromRootPath($this->root);

        $actual = array_keys($sut->glob('/Alpha/*.php'));

        self::assertEquals(['Acme\\Bravo'], $actual);
    }

    public function testThrowsWhenPsrRootNotFound(): void {
        $this->expectException(RuntimeException::class);
        Autowire::fromRootPath($this->root)->glob('/Echo/*.php');
    }
}
