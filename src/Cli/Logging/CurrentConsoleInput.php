<?php

declare(strict_types=1);

namespace WonderNetwork\SlimKernel\Cli\Logging;

use Symfony\Component\Console\Input\InputInterface;

final readonly class CurrentConsoleInput {
    public function __construct(private ConsoleIoStack $stack) {
    }

    public function currentInput(): ?InputInterface {
        return $this->stack->current()?->input;
    }
}
