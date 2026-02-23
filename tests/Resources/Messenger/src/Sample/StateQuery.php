<?php

declare(strict_types=1);

namespace Acme\Sample;

use WonderNetwork\SlimKernel\Messenger\Query;

/**
 * @implements Query<string|null>
 */
final readonly class StateQuery implements Query {
}
