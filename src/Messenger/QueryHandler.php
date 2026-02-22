<?php

declare(strict_types=1);

namespace WonderNetwork\SlimKernel\Messenger;

/**
 * @template T of Query
 */
interface QueryHandler {
    /**
     * @noinspection PhpUndefinedClassInspection
     * @param T $query
     * @return template-type<T, Query, 'T'>
     */
    public function __invoke(Query $query): mixed;
}
