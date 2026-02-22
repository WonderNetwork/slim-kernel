<?php

declare(strict_types=1);

namespace WonderNetwork\SlimKernel\Messenger;

use Symfony\Component\Messenger\Exception\HandlerFailedException;
use Symfony\Component\Messenger\HandleTrait;
use Symfony\Component\Messenger\MessageBusInterface;
use Throwable;

final class QueryBus {
    use HandleTrait;

    public function __construct(MessageBusInterface $messageBus) {
        $this->messageBus = $messageBus;
    }

    /**
     * @template T of mixed
     * @param Query<T> $query
     * @return T
     * @throws Throwable
     */
    public function query(Query $query): mixed {
        try {
            return $this->handle($query);
        } catch (HandlerFailedException $e) {
            [$e] = array_values($e->getWrappedExceptions());

            throw $e;
        }
    }
}
