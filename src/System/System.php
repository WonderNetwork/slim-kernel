<?php

declare(strict_types=1);

namespace WonderNetwork\SlimKernel\System;

interface System {
    public function memoryUsage(): int;
}
