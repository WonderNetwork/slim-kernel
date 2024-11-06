<?php
declare(strict_types=1);

namespace WonderNetwork\SlimKernel\Cli;

use Symfony\Component\Console\Output\OutputInterface;

final class FingersCrossedOutput {
    private OutputInterface $output;
    /** @var string[] */
    private array $messages = [];

    public function __construct(OutputInterface $output) {
        $this->output = $output;
    }

    public function writeln(string $message): void {
        if ($this->isBuffering()) {
            $this->messages[] = $message;
            return;
        }
        $this->flush();
        $this->output->writeln($message);
    }

    public function flush(): void {
        $this->output->writeln(array_slice($this->messages, 0));
    }

    public function isBuffering(): bool {
        return false === $this->output->isVerbose();
    }
}
