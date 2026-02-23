<?php

namespace WonderNetwork\SlimKernel\Cli\Logging\Formatter;

use Monolog\Formatter\FormatterInterface;
use Monolog\Level;
use Monolog\LogRecord;
use Stringable;
use Symfony\Component\Console\Formatter\OutputFormatter;

/**
 * @see https://raw.githubusercontent.com/symfony/symfony/refs/heads/8.0/src/Symfony/Bridge/Monolog/Formatter/ConsoleFormatter.php
 *
 * Formats incoming records for console output by coloring them depending on log level.
 */
final class ConsoleFormatter implements FormatterInterface {
    private const array LEVEL_COLOR_MAP = [
        Level::Debug->value => 'fg=white',
        Level::Info->value => 'fg=green',
        Level::Notice->value => 'fg=blue',
        Level::Warning->value => 'fg=cyan',
        Level::Error->value => 'fg=yellow',
        Level::Critical->value => 'fg=red',
        Level::Alert->value => 'fg=red',
        Level::Emergency->value => 'fg=white;bg=red',
    ];

    public function formatBatch(array $records): mixed {
        foreach ($records as $key => $record) {
            $records[$key] = $this->format($record);
        }

        return $records;
    }

    public function format(LogRecord $record): string {
        $record = $this->replacePlaceHolder($record);

        $levelColor = \sprintf('<%s>', self::LEVEL_COLOR_MAP[$record->level->value]);

        return \strtr(
            "<fg=gray>%datetime% %channel%</> %start_tag%%level_name%%end_tag% %message%\n",
            [
                '%datetime%' => $record->datetime->format('H:i:s'),
                '%start_tag%' => $levelColor,
                '%level_name%' => strtolower($record->level->getName()),
                '%end_tag%' => '</>',
                '%channel%' => $record->channel,
                '%message%' => $this->replacePlaceHolder($record)->message,
            ],
        );
    }

    private function replacePlaceHolder(LogRecord $record): LogRecord {
        $message = $record->message;

        if (false === \str_contains($message, '{')) {
            return $record;
        }

        $context = $record->context;

        $replacements = [];

        foreach ($context as $k => $v) {
            $v = OutputFormatter::escape($this->dumpData($v));
            $replacements['{'.$k.'}'] = \sprintf('<comment>%s</>', $v);
        }

        return $record->with(message: \strtr($message, $replacements));
    }

    private function dumpData(mixed $data): string {
        if (\is_string($data) || \is_int($data) || \is_float($data) || $data instanceof Stringable) {
            return (string) $data;
        }

        if (null === $data) {
            return 'N/A';
        }

        if (\is_bool($data)) {
            return $data ? 'true' : 'false';
        }

        if (\is_array($data)) {
            return \sprintf('array(%d)', \count($data));
        }

        if (\is_resource($data)) {
            return \sprintf('resource(%d)', \get_resource_type($data));
        }

        if (\is_object($data)) {
            return \sprintf('object(%d)', $data::class);
        }

        return \sprintf('unknown(%s)', \get_debug_type($data));
    }
}
