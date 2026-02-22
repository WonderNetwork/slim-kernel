<?php

declare(strict_types=1);

namespace Acme;

use WonderNetwork\SlimKernel\Messenger\AsyncCommand;

final readonly class SideEffectsCommand implements AsyncCommand {
    public function __construct(
        public string $value,
    ) {
    }
}
