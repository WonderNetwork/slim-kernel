<?php

declare(strict_types=1);

namespace WonderNetwork\SlimKernel\Cli\Logging;

use Symfony\Component\Console\Input\InputInterface;
use Symfony\Component\Console\Output\OutputInterface;

final readonly class ConsoleIo {
    public function __construct(
        public InputInterface $input,
        public OutputInterface $output,
    ) {
    }
}
