<?php

declare(strict_types=1);

namespace Acme\Requeue;

use WonderNetwork\SlimKernel\Messenger\AsyncCommand;
use WonderNetwork\SlimKernel\Messenger\AsyncCommandHandler;
use WonderNetwork\SlimKernel\Messenger\CommandBus;
use WonderNetwork\SlimKernel\Messenger\Delay;

/**
 * @implements AsyncCommandHandler<RetryCommand>
 */
final readonly class RetryCommandHandler implements AsyncCommandHandler {
    public function __construct(private CommandBus $commandBus) {
    }

    public function __invoke(RetryCommand|AsyncCommand $command): void {
        $this->commandBus->delay(
            command: $command,
            transport: 'some',
            delay: Delay::ofHours(1),
        );
    }
}
