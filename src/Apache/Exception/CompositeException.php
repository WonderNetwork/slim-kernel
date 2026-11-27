<?php

declare(strict_types=1);

namespace WonderNetwork\SlimKernel\Apache\Exception;

use Exception;

final class CompositeException extends Exception implements ApacheConfigurationException {
    public static function ofLastLineGlued(): self {
        return new self('The last line refers to a line after it. It cannot be last');
    }
}
