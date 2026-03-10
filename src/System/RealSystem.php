<?php

declare(strict_types=1);

namespace WonderNetwork\SlimKernel\System;

final readonly class RealSystem implements System {
    public function memoryUsage(): int {
        return memory_get_usage();
    }
}
