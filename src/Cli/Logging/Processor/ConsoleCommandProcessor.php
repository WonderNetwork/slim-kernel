<?php

declare(strict_types=1);

namespace WonderNetwork\SlimKernel\Cli\Logging\Processor;

use Monolog\LogRecord;
use Monolog\Processor\ProcessorInterface;
use WonderNetwork\SlimKernel\Cli\Logging\CurrentConsoleInput;

final readonly class ConsoleCommandProcessor implements ProcessorInterface {
    public function __construct(
        private CurrentConsoleInput $input,
        private string $nameKey = 'command.name',
        private string $optionsKey = 'command.options',
        private string $argumentsKey = 'command.arguments',
    ) {
    }

    public function __invoke(LogRecord $record): LogRecord {
        $input = $this->input->currentInput();

        if (null !== $input) {
            $record->extra[$this->nameKey] = $input->getFirstArgument();
            $record->extra[$this->optionsKey] = $input->getOptions();
            $record->extra[$this->argumentsKey] = $input->getArguments();
        }

        return $record;
    }
}
