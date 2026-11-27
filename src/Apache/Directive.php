<?php

declare(strict_types=1);

namespace WonderNetwork\SlimKernel\Apache;

use WonderNetwork\SlimKernel\Apache\Exception\ApacheConfigurationException;

interface Directive {
    /**
     * @throws ApacheConfigurationException
     */
    public function toString(): string;
}
