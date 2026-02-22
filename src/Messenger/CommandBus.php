<?php

declare(strict_types=1);

namespace WonderNetwork\SlimKernel\Messenger;

use Symfony\Component\Messenger\Exception\HandlerFailedException;
use Symfony\Component\Messenger\HandleTrait;
use Symfony\Component\Messenger\MessageBusInterface;
use Symfony\Component\Messenger\Stamp\DelayStamp;
use Symfony\Component\Messenger\Stamp\TransportNamesStamp;
use Throwable;

final class CommandBus {
    use HandleTrait {
        handle as handleAndReturn;
    }

    public function __construct(MessageBusInterface $messageBus) {
        $this->messageBus = $messageBus;
    }

    /**
     * @template T
     * @param Command<T> $command
     * @phpstan-return T
     * @throws Throwable
     * @noinspection PhpParameterNameChangedDuringInheritanceInspection
     */
    public function handle(Command $command): mixed {
        try {
            return $this->handleAndReturn($command);
        } catch (HandlerFailedException $e) {
            [$e] = array_values($e->getWrappedExceptions());

            throw $e;
        }
    }

    public function queue(AsyncCommand $command, string $transport): void {
        $transportNamesStamp = new TransportNamesStamp($transport);
        $this->messageBus->dispatch($command, [$transportNamesStamp]);
    }

    public function delay(AsyncCommand $command, string $transport, Delay $delay): void {
        $transportNamesStamp = new TransportNamesStamp($transport);
        $delayStamp = new DelayStamp(delay: $delay->milliseconds);
        $this->messageBus->dispatch($command, [$transportNamesStamp, $delayStamp]);
    }
}
