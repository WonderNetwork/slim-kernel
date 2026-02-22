<?php

declare(strict_types=1);

namespace WonderNetwork\SlimKernel\Messenger;

/**
 * @template T of AsyncCommand
 */
interface AsyncCommandHandler {
    /**
     * @param T $command
     */
    public function __invoke(AsyncCommand $command): void;
}
