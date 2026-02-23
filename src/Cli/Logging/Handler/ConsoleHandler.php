<?php

namespace WonderNetwork\SlimKernel\Cli\Logging\Handler;

use Monolog\Formatter\FormatterInterface;
use Monolog\Handler\AbstractProcessingHandler;
use Monolog\Level;
use Monolog\LogRecord;
use Stringable;
use Symfony\Component\Console\Output\OutputInterface;
use WonderNetwork\SlimKernel\Cli\Logging\CurrentConsoleOutput;
use WonderNetwork\SlimKernel\Cli\Logging\Formatter\ConsoleFormatter;

/**
 * @see https://github.com/symfony/symfony/raw/refs/heads/8.0/src/Symfony/Bridge/Monolog/Handler/ConsoleHandler.php
 */
final class ConsoleHandler extends AbstractProcessingHandler {
    private const array VERBOSITY_LEVEL_MAP = [
        OutputInterface::VERBOSITY_QUIET => Level::Error,
        OutputInterface::VERBOSITY_NORMAL => Level::Warning,
        OutputInterface::VERBOSITY_VERBOSE => Level::Notice,
        OutputInterface::VERBOSITY_VERY_VERBOSE => Level::Info,
        OutputInterface::VERBOSITY_DEBUG => Level::Debug,
    ];

    public function __construct(private readonly CurrentConsoleOutput $output) {
        parent::__construct();
    }

    public function isHandling(LogRecord $record): bool {
        return $this->updateLevel() && parent::isHandling($record);
    }

    public function handle(LogRecord $record): bool {
        // we have to update the logging level each time because the verbosity of the
        // console output might have changed in the meantime (it is not immutable)
        return $this->updateLevel() && parent::handle($record);
    }

    protected function write(LogRecord $record): void {
        if (false === is_string($record->formatted) && false === $record->formatted instanceof Stringable) {
            return;
        }

        $output = $this->output->currentOutput();
        assert(null !== $output);

        $output->write(
            (string) $record->formatted,
            false,
            $output->getVerbosity(),
        );
    }

    protected function getDefaultFormatter(): FormatterInterface {
        return new ConsoleFormatter();
    }

    /**
     * Updates the logging level based on the verbosity setting of the console output.
     *
     * @return bool Whether the handler is enabled and verbosity is not set to quiet
     */
    private function updateLevel(): bool {
        $output = $this->output->currentOutput();

        if (null === $output) {
            return false;
        }

        $verbosity = $output->getVerbosity();
        $this->setLevel(self::VERBOSITY_LEVEL_MAP[$verbosity]);

        return true;
    }
}
