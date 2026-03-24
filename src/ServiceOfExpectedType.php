<?php

declare(strict_types=1);

namespace WonderNetwork\SlimKernel;

use DI\DependencyException;
use Psr\Container\ContainerExceptionInterface;
use Psr\Container\ContainerInterface;
use Psr\Container\NotFoundExceptionInterface;

final readonly class ServiceOfExpectedType {
    /**
     * @template T of object
     * @param class-string<T> $expectedType
     * @return T
     * @throws ContainerExceptionInterface
     * @throws NotFoundExceptionInterface
     */
    public static function getFromContainer(ContainerInterface $container, string $key, string $expectedType): mixed {
        $actual = $container->get($key);

        if (false === $actual instanceof $expectedType) {
            throw new DependencyException(
                sprintf(
                    'Service "%s" is expected to be of type "%s", %s given',
                    $key,
                    $expectedType,
                    get_debug_type($actual),
                ),
            );
        }

        return $actual;
    }
}
