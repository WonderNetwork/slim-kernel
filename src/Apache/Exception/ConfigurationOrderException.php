<?php

declare(strict_types=1);

namespace WonderNetwork\SlimKernel\Apache\Exception;

use Exception;

final class ConfigurationOrderException extends Exception implements ApacheConfigurationException {
    public static function ofDocumentRootAlreadyDefined(): self {
        return new self("Changing the document root path has no effect after the <Directory> block has already been configured");
    }

    public static function ofLoggingAlreadyDefined(): self {
        return new self("Changin the log directory has no effect after the logging has already been configured");
    }
}
