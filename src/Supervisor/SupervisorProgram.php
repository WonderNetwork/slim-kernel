<?php

declare(strict_types=1);

namespace WonderNetwork\SlimKernel\Supervisor;

final readonly class SupervisorProgram {
    public static function single(string $name, string $command): self {
        return new self(
            name: $name,
            command: $command,
            concurrency: 1,
            startretries: 15,
        );
    }

    /**
     * @param positive-int $concurrency
     * @param positive-int $startretries
     */
    public function __construct(
        public string $name,
        public string $command,
        public int $concurrency,
        public int $startretries,
    ) {
    }
}
