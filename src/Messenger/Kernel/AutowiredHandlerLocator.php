<?php

declare(strict_types=1);

namespace WonderNetwork\SlimKernel\Messenger\Kernel;

use Psr\Container\ContainerInterface;
use Symfony\Component\Messenger\Envelope;
use Symfony\Component\Messenger\Handler\HandlerDescriptor;
use Symfony\Component\Messenger\Handler\HandlersLocatorInterface;
use function WonderNetwork\SlimKernel\Collection\collection;

final readonly class AutowiredHandlerLocator implements HandlersLocatorInterface {
    /**
     * @var array<class-string, class-string>
     */
    private array $handlers;

    /**
     * @param array<callable> $handlers
     */
    public function __construct(private ContainerInterface $container, array $handlers) {
        $this->handlers = collection($handlers)->indexBy(new HandlerToMessageMapping())->toArray();
    }

    public function getHandlers(Envelope $envelope): iterable {
        $class = $envelope->getMessage()::class;
        $handlerClass = $this->handlers[$class] ?? null;

        if ($handlerClass) {
            $handler = $this->container->get($handlerClass);

            if (is_callable($handler)) {
                yield new HandlerDescriptor($handler);
            }
        }
    }
}
