<?php

declare(strict_types=1);

namespace WonderNetwork\SlimKernel\Cli;

use DI\Definition\Definition;
use function DI\value;

final class AutoExit {
    private bool $value;

    public static function yes(): Definition {
        return value(new self(true));
    }

    public static function no(): Definition {
        return value(new self(false));
    }

    private function __construct(bool $value) {
        $this->value = $value;
    }

    public function value(): bool {
        return $this->value;
    }
}
