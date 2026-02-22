<?php

declare(strict_types=1);

namespace WonderNetwork\SlimKernel\Messenger;

use Acme\SideEffectsCommand;
use Acme\StateQuery;
use PHPUnit\Framework\TestCase;
use RuntimeException;
use Symfony\Component\Console\Input\ArrayInput;
use Symfony\Component\Console\Output\BufferedOutput;
use Symfony\Component\Messenger\Command\ConsumeMessagesCommand;
use Symfony\Component\Messenger\Transport\InMemory\InMemoryTransport;
use WonderNetwork\SlimKernel\KernelBuilder;
use WonderNetwork\SlimKernel\Messenger\Kernel\MessengerServiceFactory;
use WonderNetwork\SlimKernel\Messenger\Kernel\TransportLocatorBuilder;

final class MessengerTest extends TestCase {
    protected function setUp(): void {
        if (file_exists($filename = __DIR__.'/../../.cache/CompiledContainer.php')) {
            unlink($filename);
        }
    }

    public function testMessenger(): void {
        $transportName = 'in-memory';

        $root = realpath(__DIR__.'/../Resources/Messenger')
            ?: throw new RuntimeException('Oops');
        $container = KernelBuilder::start($root)
            ->useCache(__DIR__.'/../../.cache/')
            ->register(
                new MessengerServiceFactory(
                    commandPath: 'src/*AsyncHandler.php',
                    queryPath: 'src/*QueryHandler.php',
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
}
