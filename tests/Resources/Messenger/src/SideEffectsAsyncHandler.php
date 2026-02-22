<?php

declare(strict_types=1);

namespace Acme;

use WonderNetwork\SlimKernel\Messenger\AsyncCommand;
use WonderNetwork\SlimKernel\Messenger\AsyncCommandHandler;
use WonderNetwork\SlimKernel\Messenger\HoldsState;

/**
 * @implements AsyncCommandHandler<SideEffectsCommand>
 */
final readonly class SideEffectsAsyncHandler implements AsyncCommandHandler {
    public function __construct(private HoldsState $state) {
    }

    public function __invoke(SideEffectsCommand|AsyncCommand $command): void {
        $this->state->setValue($command->value);
    }
}
