<?php

declare(strict_types=1);

namespace WonderNetwork\SlimKernel\Cli\Logging;

use Symfony\Component\Console\ConsoleEvents;
use Symfony\Component\Console\Event\ConsoleCommandEvent;
use Symfony\Component\Console\Output\ConsoleOutputInterface;
use Symfony\Component\EventDispatcher\EventSubscriberInterface;

final readonly class ConsoleHandlerEventSubscriber implements EventSubscriberInterface {
    public static function getSubscribedEvents(): array {
        return [
            ConsoleEvents::COMMAND => 'onCommand',
            ConsoleEvents::TERMINATE => 'onTerminate',
        ];
    }

    public function __construct(private ConsoleIoStack $consoleIoStack) {
    }

    public function onCommand(ConsoleCommandEvent $event): void {
        $input = $event->getInput();
        $output = $event->getOutput();

        if ($output instanceof ConsoleOutputInterface) {
            $output = $output->getErrorOutput();
        }

        $this->consoleIoStack->push(new ConsoleIo($input, $output));
    }

    public function onTerminate(): void {
        $this->consoleIoStack->pop();
    }
}
