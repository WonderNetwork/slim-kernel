<?php

declare(strict_types=1);

namespace WonderNetwork\SlimKernel\Cli;

use Symfony\Component\Console\Input\InputInterface;
use Symfony\Component\Console\Output\OutputInterface;
use Throwable;

trait FingersCrossedTrait {
    /**
     * @throws Throwable
     */
    abstract public function fingersCrossed(InputParams $inputParams, FingersCrossedOutput $output): int;

    protected function execute(InputInterface $input, OutputInterface $output): int {
        return FingersCrossedHandler::of($input, $output)->run([$this, 'fingersCrossed']);
    }
}
