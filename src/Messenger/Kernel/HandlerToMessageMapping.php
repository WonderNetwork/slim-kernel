<?php

declare(strict_types=1);

namespace WonderNetwork\SlimKernel\Messenger\Kernel;

use ReflectionClass;
use ReflectionException;
use ReflectionIntersectionType;
use ReflectionNamedType;
use ReflectionUnionType;
use RuntimeException;

final readonly class HandlerToMessageMapping {
    /**
     * @throws ReflectionException
     */
    public function __invoke(string $handler): string {
        if (false === class_exists($handler)) {
            throw new RuntimeException(
                sprintf(
                    'Handler must be an object, %s given',
                    get_debug_type($handler),
                ),
            );
        }

        $reflectionObject = new ReflectionClass($handler);

        if (false === $reflectionObject->hasMethod('__invoke')) {
            throw new RuntimeException(
                sprintf(
                    'Handler %s does not have an __invoke method',
                    $handler,
                ),
            );
        }

        $reflectionMethod = $reflectionObject->getMethod('__invoke');

        if ($reflectionMethod->getNumberOfParameters() !== 1) {
            throw new RuntimeException(
                sprintf(
                    'Handler %s::__invoke() is expected to have exactly one parameter, actual: %d',
                    $handler,
                    $reflectionMethod->getNumberOfParameters(),
                ),
            );
        }

        $type = $reflectionMethod->getParameters()[0]->getType();

        return match (true) {
            $type instanceof ReflectionNamedType => $type->getName(),
            $type instanceof ReflectionUnionType => $this->handleUnionTypes($handler, ...$type->getTypes()),
            default => throw new RuntimeException(
                sprintf(
                    'Handler %s::__invoke($message) is not properly typehinted',
                    $handler,
                ),
            ),
        };
    }

    private function handleUnionTypes(string $class, ReflectionIntersectionType|ReflectionNamedType ...$types): string {
        if (count($types) > 2) {
            throw new RuntimeException(
                sprintf(
                    'Handler %s::invoke($message) has %d types in union. At most two are supported in the form: %s',
                    $class,
                    count($types),
                    'RealMessageImpl | MessageMarkerInterface',
                ),
            );
        }

        foreach ($types as $type) {
            if ($type instanceof ReflectionIntersectionType) {
                throw new RuntimeException(
                    sprintf(
                        'Handler %s::__invoke($message) cannot use an intersection typehint',
                        $class,
                    ),
                );
            }

            if ($type->isBuiltin()) {
                throw new RuntimeException(
                    sprintf(
                        'Handler %s::__invoke($message) needs to typehint a class name',
                        $class,
                    ),
                );
            }

            if (interface_exists($type->getName())) {
                continue;
            }

            return $type->getName();
        }

        throw new RuntimeException(
            sprintf(
                'Handler %s::__invoke($message) needs to typehint a class name',
                $class,
            ),
        );
    }
}
