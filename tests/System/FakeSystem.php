<?php

declare(strict_types=1);

namespace WonderNetwork\SlimKernel\System;

final class FakeSystem implements System {
    private int $memoryUsage = 0;

    public function setMemoryUsage(int $memoryUsage): void {
        $this->memoryUsage = $memoryUsage;
    }

    public function memoryUsage(): int {
        return $this->memoryUsage;
    }
}
