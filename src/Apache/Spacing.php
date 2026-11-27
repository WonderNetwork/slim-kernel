<?php

declare(strict_types=1);

namespace WonderNetwork\SlimKernel\Apache;

enum Spacing {
    case Compact;
    case Group;

    public function toNewlines(): string {
        return match ($this) {
            self::Compact => "\n",
            self::Group => "\n\n",
        };
    }
}
