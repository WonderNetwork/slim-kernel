<?php
declare(strict_types=1);

namespace WonderNetwork\SlimKernel\ServiceFactory;

use DI\Definition\Helper\CreateDefinitionHelper;
use Symfony\Component\Console;
use WonderNetwork\SlimKernel\ServiceFactory;
use WonderNetwork\SlimKernel\ServicesBuilder;
use function DI\autowire;
use function DI\get;
use function WonderNetwork\SlimKernel\Collection\collection;

final class SymfonyConsoleServiceFactory implements ServiceFactory {
    private string $path;
    private string $name;

    public function __construct(string $path = '/src/Cli/**/*Command.php', string $name = 'unknown') {
        $this->path = $path;
        $this->name = $name;
    }

    public function __invoke(ServicesBuilder $builder): iterable {
        yield from $commands = $builder->autowire()->glob($this->path);

        yield Console\Application::class => collection($commands)
            ->keys()
            ->reduce(
                static fn(CreateDefinitionHelper $def, string $command) => $def->method('add', get($command)),
                autowire()
                    ->constructor($this->name)
                    ->method('setAutoExit', false),
            );
    }
}
