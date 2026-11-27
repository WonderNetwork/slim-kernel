<?php

declare(strict_types=1);

namespace WonderNetwork\SlimKernel\Apache;

enum Endpoint: int {
    case Http = 80;
    case Secure = 443;

    public function isSecure(): bool {
        return match ($this) {
            self::Http => false,
            self::Secure => true,
        };
    }
}
