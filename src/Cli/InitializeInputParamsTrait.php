<?php
declare(strict_types=1);

namespace WonderNetwork\SlimKernel\Cli;

use Symfony\Component\Console\Input\InputInterface;
use Symfony\Component\Console\Output\OutputInterface;

trait InitializeInputParamsTrait {
    protected InputParams $params;

    protected function initialize(InputInterface $input, OutputInterface $output): void {
        $this->params = InputParams::ofInput($input);
    }
}
