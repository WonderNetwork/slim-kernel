<?php

declare(strict_types=1);

namespace Acme;

use WonderNetwork\SlimKernel\Messenger\HoldsState;
use WonderNetwork\SlimKernel\Messenger\Query;
use WonderNetwork\SlimKernel\Messenger\QueryHandler;

/**
 * @implements QueryHandler<StateQuery>
 */
final readonly class StateQueryHandler implements QueryHandler {
    public function __construct(private HoldsState $state) {
    }

    public function __invoke(StateQuery|Query $query): mixed {
        return $this->state->getValue();
    }
}
