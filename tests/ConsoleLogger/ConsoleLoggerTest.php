<?php

declare(strict_types=1);

namespace WonderNetwork\SlimKernel\ConsoleLogger;

use Monolog\Logger;
use PHPUnit\Framework\TestCase;
use Psr\Log\LoggerInterface;
use Symfony\Component\Console\Application;
use Symfony\Component\Console\Input\ArrayInput;
use Symfony\Component\Console\Output\BufferedOutput;
use WonderNetwork\SlimKernel\Cli\Logging\ConsoleHandlerEventSubscriber;
use WonderNetwork\SlimKernel\Cli\Logging\ConsoleIoStack;
use WonderNetwork\SlimKernel\Cli\Logging\CurrentConsoleInput;
use WonderNetwork\SlimKernel\Cli\Logging\CurrentConsoleOutput;
use WonderNetwork\SlimKernel\Cli\Logging\Handler\ConsoleHandler;
use WonderNetwork\SlimKernel\EventDispatcher\EventSubscribersCollection;
use WonderNetwork\SlimKernel\KernelBuilder;
use WonderNetwork\SlimKernel\ServiceFactory\SymfonyConsoleServiceFactory;
use function DI\autowire;

final class ConsoleLoggerTest extends TestCase {
    public function testConsoleLogger(): void {
        $rootPath = realpath(__DIR__.'/../Resources/ConsoleLogger');

        if (false === $rootPath) {
            $this->fail('Root path does not exist');
        }

        $container = KernelBuilder::start($rootPath)
            ->add(
                [
                    ConsoleIoStack::class => autowire(),
                    ConsoleHandler::class => autowire(),
                    CurrentConsoleOutput::class => autowire(),
                    CurrentConsoleInput::class => autowire(),
                    LoggerInterface::class => function (ConsoleHandler $consoleHandler) {
                        $logger = new Logger('channel');
                        $logger->pushHandler($consoleHandler);

                        return $logger;
                    },
                    ConsoleHandlerEventSubscriber::class => autowire(),
                ],
            )
            ->register(
                EventSubscribersCollection::start()
                    ->add(ConsoleHandlerEventSubscriber::class),
            )
            ->register(
                new SymfonyConsoleServiceFactory(
                    path: '/src/*Command.php',
                ),
            )
            ->build();

        /** @var Application $app */
        $app = $container->get(Application::class);
        $output = new BufferedOutput();
        $app->run(input: new ArrayInput(['echo', '-vv', 'message' => 'Hello World']), output: $output);

        self::assertStringContainsString("channel info Received Hello World", $output->fetch());
    }
}
