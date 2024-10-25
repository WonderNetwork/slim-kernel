<?php
declare(strict_types=1);

namespace WonderNetwork\SlimKernel\Collection;

use DusanKasan\Knapsack\Collection;
use RuntimeException;

/** @return string[] */
function findFiles(string $root, string ...$patterns): iterable {
    $root = rtrim($root, '/');
    foreach ($patterns as $pattern) {
        $result = glob($root.'/'.ltrim($pattern, '/'));
        if (false === $result) {
            throw new RuntimeException('Invalid pattern: '.$pattern);
        }
        yield from $result;
    }
}

/**
 * @template T of mixed
 * @param iterable<T> $input
 * @return Collection<T>
 */
function collection($input): Collection {
    return Collection::from($input);
}


/**
 * @template T of mixed
 * @param iterable<T> $input
 * @return T[]
 */
function toArray($input): array {
    return collection($input)->toArray();
}

/**
 * @template T of mixed
 * @template F of mixed
 * @param iterable<T> $input
 * @param callable(T,?(int|string)):F ...$fn
 * @return F[]
 */
function map($input, callable ...$fn): array {
    // @phpstan-ignore method.nonObject
    return collection($fn)
        ->reduce(
            fn (Collection $collection, callable $fn) => $collection->map($fn),
            collection($input),
        )
        ->toArray();
}

/**
 * @template T of mixed
 * @param iterable<T> $input
 * @param callable(T):bool $fn
 * @return T[]
 */
function filter($input, callable $fn): array {
    return collection($input)->filter($fn)->toArray();
}
