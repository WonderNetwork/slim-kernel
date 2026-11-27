<?php

declare(strict_types=1);

namespace WonderNetwork\SlimKernel\Apache;

use WonderNetwork\SlimKernel\Apache\Exception\ApacheConfigurationException;

interface DirectiveCollection {
    /**
     * @return iterable<?Directive>
     * @throws ApacheConfigurationException
     */
    public function toDirectives(): iterable;
}
