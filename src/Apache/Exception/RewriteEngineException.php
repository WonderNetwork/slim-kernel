<?php

declare(strict_types=1);

namespace WonderNetwork\SlimKernel\Apache\Exception;

use Exception;

final class RewriteEngineException extends Exception implements ApacheConfigurationException {
    public static function ofUnusedCondition(): self {
        return new self("RewriteCond was left dangling without a following RewriteRule");
    }
}
