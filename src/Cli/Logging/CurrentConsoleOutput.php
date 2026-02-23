<?php

declare(strict_types=1);

namespace WonderNetwork\SlimKernel\Cli\Logging;

use Symfony\Component\Console\Output\OutputInterface;

final readonly class CurrentConsoleOutput {
    public function __construct(private ConsoleIoStack $stack) {
    }

    public function currentOutput(): ?OutputInterface {
        return $this->stack->current()?->output;
    }
}
