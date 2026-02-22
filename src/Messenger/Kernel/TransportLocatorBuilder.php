<?php

declare(strict_types=1);

namespace WonderNetwork\SlimKernel\Messenger\Kernel;

use DI\Container;
use Psr\Container\ContainerInterface;
use Symfony\Component\Messenger\Transport\Receiver\ReceiverInterface;
use Symfony\Component\Messenger\Transport\Sender\SenderInterface;
use function WonderNetwork\SlimKernel\Collection\map;

final readonly class TransportLocatorBuilder {
    public static function empty(): self {
        return new self([], []);
    }

    public static function start(): self {
        return new self([], []);
    }

    /**
     * @param array<string,string> $senders
     * @param array<string,string> $receivers
     */
    private function __construct(public array $senders, public array $receivers) {
    }

    /**
     * @param class-string<SenderInterface> $sender
     * @param class-string<ReceiverInterface> $receiver
     */
    public function withTransport(string $name, string $sender, string $receiver): self {
        return new self([$name => $sender] + $this->senders, [$name => $receiver] + $this->receivers);
    }

    public function sendersLocator(ContainerInterface $container): Container {
        return $this->createLocator($container, $this->senders);
    }

    public function receiversLocator(ContainerInterface $container): Container {
        return $this->createLocator($container, $this->receivers);
    }

    /**
     * @param array<string,string> $map
     */
    private function createLocator(ContainerInterface $container, array $map): Container {
        return new Container(
            // lazy copy the definitions into the service locator
            map(
                $map,
                static fn (string $className) => fn () => $container->get($className),
            ),
        );
    }
}
