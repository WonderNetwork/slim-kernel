<?php

declare(strict_types=1);

namespace Acme\Sample;

use WonderNetwork\SlimKernel\Messenger\AsyncCommand;

final readonly class SideEffectsCommand implements AsyncCommand {
    public function __construct(
        public string $value,
    ) {
    }
}
