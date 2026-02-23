<?php

declare(strict_types=1);

namespace WonderNetwork\SlimKernel\Supervisor;

final readonly class SupervisorConfiguration {
    public static function empty(): self {
        return new self([]);
    }

    public static function start(): self {
        return new self([]);
    }

    /**
     * @param list<SupervisorProgram> $programs
     */
    public function __construct(public array $programs) {
    }

    public function withPrograms(SupervisorProgram ...$programs): self {
        return new self([...$this->programs, ...array_values($programs)]);
    }

    public function withSimpleCommand(string $name, string $command): self {
        return new self([...$this->programs, SupervisorProgram::single($name, $command)]);
    }
}
