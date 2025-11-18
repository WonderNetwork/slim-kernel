<?php

declare(strict_types=1);

namespace WonderNetwork\SlimKernel\Cli;

use RuntimeException;
use Symfony\Component\Console\Input\InputInterface;
use WonderNetwork\SlimKernel\Accessor\ArrayAccessor;

/**
 * @property ArrayAccessor $arguments
 * @property ArrayAccessor $options
 */
final class InputParams {
    private ArrayAccessor $arguments;
    private ArrayAccessor $options;

    public static function ofInput(InputInterface $input): self {
        return new self(
            ArrayAccessor::of($input->getArguments()),
            ArrayAccessor::of($input->getOptions()),
        );
    }

    public function __construct(ArrayAccessor $arguments, ArrayAccessor $options) {
        $this->arguments = $arguments;
        $this->options = $options;
    }

    public function __get(string $name): ArrayAccessor {
        switch ($name) {
            case 'arguments':
                return $this->arguments;
            case 'options':
                return $this->options;
        }

        throw new RuntimeException("Unknown collection: $name");
    }
}
