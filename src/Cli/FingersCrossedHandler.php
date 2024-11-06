<?php
declare(strict_types=1);

namespace WonderNetwork\SlimKernel\Cli;

use Symfony\Component\Console\Command\Command;
use Symfony\Component\Console\Input\InputInterface;
use Symfony\Component\Console\Output\OutputInterface;
use Throwable;

final class FingersCrossedHandler {
    private InputInterface $input;
    private OutputInterface $output;

    public static function of(InputInterface $input, OutputInterface $output): self {
        return new self($input, $output);
    }

    private function __construct(InputInterface $input, OutputInterface $output) {
        $this->input = $input;
        $this->output = $output;
    }

    /**
     * @param callable(InputParams $input, FingersCrossedOutput $output):int $closure
     * @return int
     * @throws Throwable
     */
    public function run(callable $closure): int {
        $output = new FingersCrossedOutput($this->output);
        try {
            $result = $closure(InputParams::ofInput($this->input), $output);
            if (Command::SUCCESS !== $result) {
                $output->flush();
            }
        } catch (Throwable $e) {
            $output->flush();
            throw $e;
        }
        return $result;
    }
}
