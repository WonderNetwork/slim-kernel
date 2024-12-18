<?php
declare(strict_types=1);

namespace WonderNetwork\SlimKernel\Collection;

use DusanKasan\Knapsack\Collection;
use RuntimeException;
use SplFileInfo;
use Symfony\Component\Finder\Finder;

/** @return string[] */
function findFiles(string $root, string ...$patterns): iterable {
    $globstar = '**/';

    $root = rtrim($root, '/');
    foreach ($patterns as $pattern) {
        // php glob does not support globstar to match any directory depth. Let’s hack around it
        if (str_contains($pattern, $globstar)) {
            [$directory, $name] = explode($globstar, $pattern, 2);
            $finder = Finder::create()
                ->in($root.'/'.ltrim($directory, '/'))
                ->name($name)
                ->files()
                ->sortByName();
            yield from map(
                array_values(iterator_to_array($finder)),
                static fn (SplFileInfo $file) => $file->getRealPath(),
            );
            continue;
        }

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
