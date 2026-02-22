<?php

declare(strict_types=1);

namespace WonderNetwork\SlimKernel\Messenger;

final readonly class Delay {
    public static function ofMilliseconds(int $milliseconds): self {
        return new self($milliseconds);
    }

    public static function ofSeconds(int $seconds): self {
        return self::ofMilliseconds($seconds * 1_000);
    }

    public static function ofMinutes(int $minutes): self {
        return self::ofSeconds($minutes * 60);
    }

    public static function ofHours(int $hours): self {
        return self::ofMinutes($hours * 60);
    }

    public function __construct(public int $milliseconds) {
    }
}
