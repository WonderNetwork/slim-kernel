<?php

declare(strict_types=1);

namespace Acme\ConsoleLogger;

use Psr\Log\LoggerInterface;
use Symfony\Component\Console\Command\Command;
use Symfony\Component\Console\Input\InputArgument;
use Symfony\Component\Console\Input\InputInterface;
use Symfony\Component\Console\Output\OutputInterface;

final class EchoCommand extends Command {
    public function __construct(private readonly LoggerInterface $logger) {
        parent::__construct('echo');
    }

    protected function configure(): void {
        $this->addArgument('message', InputArgument::REQUIRED);
    }

    protected function execute(InputInterface $input, OutputInterface $output): int {
        $this->logger->info("Received {message}", ['message' => $input->getArgument('message')]);

        return self::SUCCESS;
    }
}
