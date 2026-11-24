<?php

declare(strict_types=1);

namespace WonderNetwork\SlimKernel\Http;

enum HttpEnvironment {
    case Untrusted;
    case BehindTrustedLoadBalancer;

    public function trustsHeaders(): bool {
        return match ($this) {
            self::Untrusted => false,
            self::BehindTrustedLoadBalancer => true,
        };
    }
}
