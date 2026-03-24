<?php

declare(strict_types=1);

namespace WonderNetwork\SlimKernel\Messenger;

use Acme\Sample\SideEffectsCommand;
use Acme\Sample\StateQuery;
use DI\Container;
use PHPUnit\Framework\TestCase;
use RuntimeException;
use Symfony\Component\Console\Input\ArrayInput;
use Symfony\Component\Console\Output\BufferedOutput;
use Symfony\Component\Messenger\Command\ConsumeMessagesCommand;
use Symfony\Component\Messenger\Transport\InMemory\InMemoryTransport;
use WonderNetwork\SlimKernel\KernelBuilder;
use WonderNetwork\SlimKernel\Messenger\Kernel\CommandBusDependencies;
use WonderNetwork\SlimKernel\Messenger\Kernel\MessengerServiceFactory;
use WonderNetwork\SlimKernel\Messenger\Kernel\TransportLocatorBuilder;

final class MessengerTest extends TestCase {
    private KernelBuilder $kernelBuilder;

    protected function setUp(): void {
        if (file_exists($filename = __DIR__.'/../../.cache/CompiledContainer.php')) {
            unlink($filename);
        }

        $this->kernelBuilder = KernelBuilder::start(
            realpath(__DIR__.'/../Resources/Messenger')
                ?: throw new RuntimeException('Oops'),
        );
    }

    public function testMessenger(): void {
        $transportName = 'in-memory';

        $container = $this->kernelBuilder
            ->useCache(__DIR__.'/../../.cache/')
            ->register(
                new MessengerServiceFactory(
                    commandPath: 'src/Sample/*AsyncHandler.php',
                    queryPath: 'src/Sample/*QueryHandler.php',
                    transports: TransportLocatorBuilder::start()
                        ->withTransport(
                            name: $transportName,
                            sender: InMemoryTransport::class,
                            receiver: InMemoryTransport::class,
                        ),
                ),
            )
            ->add(
                [
                    InMemoryTransport::class => new InMemoryTransport(),
                    HoldsState::class => new HoldsState(),
                ],
            )
            ->build();

        /** @var CommandBus $commandBus */
        $commandBus = $container->get(CommandBus::class);
        /** @var QueryBus $queryBus */
        $queryBus = $container->get(QueryBus::class);
        /** @var ConsumeMessagesCommand $consumeMessagesCommand */
        $consumeMessagesCommand = $container->get(ConsumeMessagesCommand::class);

        $some = bin2hex(random_bytes(16));
        $commandBus->queue(new SideEffectsCommand($some), $transportName);

        self::assertNull($queryBus->query(new StateQuery()));

        $consumeMessagesCommand->run(new ArrayInput(['--limit' => 1]), new BufferedOutput());

        self::assertSame($some, $queryBus->query(new StateQuery()));
    }

    public function testHandlersCanDependOnCommandBus(): void {
        $container = $this->kernelBuilder
            ->register(
                new MessengerServiceFactory(
                    commandPath: 'src/Requeue/*Handler.php',
                    queryPath: 'src/Requeue/*QueryHandler.php',
                ),
            )
            ->build();

        $this->expectNotToPerformAssertions();
        $container->get(CommandBus::class);
    }

    public function testCustomTransports(): void {
        $transportName = 'default';

        $defaultTransport = new InMemoryTransport();
        $customTransport = new InMemoryTransport();

        $container = $this->kernelBuilder
            ->register(
                new MessengerServiceFactory(
                    commandPath: 'src/Sample/*AsyncHandler.php',
                    queryPath: 'src/Sample/*QueryHandler.php',
                    transports: TransportLocatorBuilder::start()
                        ->withTransport(
                            name: $transportName,
                            sender: InMemoryTransport::class,
                            receiver: InMemoryTransport::class,
                        ),
                ),
            )
            ->add(
                [
                    InMemoryTransport::class => $defaultTransport,
                    CommandBusDependencies::SendersLocator->value => fn () => new Container(
                        [
                            $transportName => $customTransport,
                        ],
                    ),
                ],
            )
            ->build();

        /** @var CommandBus $commandBus */
        $commandBus = $container->get(CommandBus::class);

        $some = bin2hex(random_bytes(16));
        $commandBus->queue(new SideEffectsCommand($some), $transportName);

        self::assertCount(0, $defaultTransport->get());
        self::assertCount(1, $customTransport->get());
    }
}
