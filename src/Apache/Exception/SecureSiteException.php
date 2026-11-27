<?php

declare(strict_types=1);

namespace WonderNetwork\SlimKernel\Apache\Exception;

use Exception;
use WonderNetwork\SlimKernel\Apache\Vhost;

final class SecureSiteException extends Exception implements ApacheConfigurationException {
    /**
     * @throws SecureSiteException
     */
    public static function ensureSiteSecured(Vhost $vhost, string $name): void {
        if ($vhost->endpoint->isSecure()) {
            return;
        }

        throw new self("Setting $name has no effect in a non-https vhost");
    }

    public static function ensureTlsDowngraded(Vhost $vhost): void {
        if ($vhost->downgradeTls) {
            return;
        }

        throw new self("Adding path-based client certificate settings requires downgraded TLS version");
    }
}
