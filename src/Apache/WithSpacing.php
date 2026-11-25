<?php

declare(strict_types=1);

namespace WonderNetwork\SlimKernel\Apache;

trait WithSpacing {
    private readonly Spacing $spacing;

    public function compact(): static {
        return clone($this, ['spacing' => Spacing::Compact]);
    }

    public function group(): static {
        return clone($this, ['spacing' => Spacing::Group]);
    }

    private function initSpacing(): Spacing {
        return Spacing::Group;
    }
}
