<?php
declare(strict_types=1);

namespace WonderNetwork\SlimKernel;

use PHPUnit\Framework\TestCase;

final class StartupHookTest extends TestCase {
    public function test(): void {
        $spy = new StartupHookSpy();
        $container = KernelBuilder::start(__DIR__.'/..')
            ->onStartup($spy)
            ->build();
        self::assertSame($container, $spy->container);
    }
}
