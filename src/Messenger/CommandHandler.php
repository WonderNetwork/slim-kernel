<?php

declare(strict_types=1);

namespace WonderNetwork\SlimKernel\Messenger;

/**
 * @template T of Command
 */
interface CommandHandler {
    /**
     * @noinspection PhpUndefinedClassInspection
     * @param T $command
     * @return template-type<T, Command, 'T'>
     */
    public function __invoke(Command $command): mixed;
}
