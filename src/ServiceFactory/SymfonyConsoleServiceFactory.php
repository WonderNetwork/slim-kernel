<?php

declare(strict_types=1);

namespace WonderNetwork\SlimKernel\ServiceFactory;

use DI\Definition\Helper\CreateDefinitionHelper;
use Symfony\Component\Console;
use WonderNetwork\SlimKernel\Cli\AutoExit;
use WonderNetwork\SlimKernel\ServiceFactory;
use WonderNetwork\SlimKernel\ServicesBuilder;
use function DI\autowire;
use function DI\get;
use function WonderNetwork\SlimKernel\Collection\collection;

final readonly class SymfonyConsoleServiceFactory implements ServiceFactory {
    public function __construct(
        private string $path = '/src/Cli/**/*Command.php',
        private string $name = 'unknown',
        private ConsoleRegistrationMethod $registrationMethod = ConsoleRegistrationMethod::Deprecated,
    ) {
    }

    public function __invoke(ServicesBuilder $builder): iterable {
        yield from $commands = $builder->autowire()->glob($this->path);
        yield AutoExit::class => AutoExit::no();

        yield Console\Application::class => collection($commands)
            ->keys()
            ->reduce(
                fn (CreateDefinitionHelper $def, string $command) => $def->method(
                    $this->registrationMethod->value,
                    get($command),
                ),
                autowire()
                    ->constructor($this->name)
                    ->method('setAutoExit', static fn (AutoExit $autoExit) => $autoExit->value()),
            );
    }
}
