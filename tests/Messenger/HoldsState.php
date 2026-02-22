<?php

declare(strict_types=1);

namespace WonderNetwork\SlimKernel\Messenger;

final class HoldsState {
    private ?string $value = null;

    public function getValue(): ?string {
        return $this->value;
    }

    public function setValue(?string $value): void {
        $this->value = $value;
    }
}
