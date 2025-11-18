<?php

declare(strict_types=1);

namespace WonderNetwork\SlimKernel\ServiceFactory;

use DI\Definition\AutowireDefinition;
use DI\Definition\Helper\AutowireDefinitionHelper;
use DI\Definition\ObjectDefinition\MethodInjection;
use DI\Definition\Reference;
use PHPUnit\Framework\TestCase;
use Symfony\Component\Console\Application;
use WonderNetwork\SlimKernel\ServicesBuilder;
use function WonderNetwork\SlimKernel\Collection\collection;
use function WonderNetwork\SlimKernel\Collection\map;

final class SymfonyConsoleServiceFactoryTest extends TestCase {
    public function test(): void {
        $servicesBuilder = new ServicesBuilder(__DIR__.'/../Resources/Commands');
        $sut = new SymfonyConsoleServiceFactory();
        $actual = collection($sut($servicesBuilder))->realize();

        $commands = $actual
            ->keys()
            ->reverse()
            ->drop(count(['Application', 'AutoExit']))
            ->reverse()
            ->toArray();

        /** @var AutowireDefinitionHelper $application */
        $application = $actual->get(Application::class);
        /** @var AutowireDefinition $definition */
        $definition = $application->getDefinition('');

        self::assertSame(
            [
                'Acme\\Foo\\Cli\\Alpha\\BravoCommand',
                'Acme\\Foo\\Cli\\Charlie\\DeltaCommand',
            ],
            $commands,
        );

        self::assertEquals(
            map(
                $commands,
                static fn (string $command) => new MethodInjection('add', [new Reference($command)]),
            ),
            collection($definition->getMethodInjections())
                ->filter(
                    static fn (MethodInjection $m) => $m->getMethodName() === 'add',
                )
                ->values()
                ->toArray(),
        );
    }
}
