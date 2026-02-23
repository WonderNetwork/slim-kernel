<?php

declare(strict_types=1);

namespace WonderNetwork\SlimKernel\Cli\Logging;

use SplStack;

final class ConsoleIoStack {
    /**
     * @var SplStack<ConsoleIo>
     */
    private SplStack $stack;

    public function __construct() {
        $this->stack = new SplStack();
    }

    public function push(ConsoleIo $consoleIo): void {
        $this->stack->push($consoleIo);
    }

    public function pop(): void {
        if (false === $this->stack->isEmpty()) {
            $this->stack->pop();
        }
    }

    public function current(): ?ConsoleIo {
        if ($this->stack->isEmpty()) {
            return null;
        }

        return $this->stack->top();
    }
}
