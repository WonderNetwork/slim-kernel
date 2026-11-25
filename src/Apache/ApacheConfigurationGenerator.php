<?php

declare(strict_types=1);

namespace WonderNetwork\SlimKernel\Apache;

use WonderNetwork\SlimKernel\Apache\Directive\Composite;
use WonderNetwork\SlimKernel\Apache\DirectiveCollection\VirtualHost;
use function WonderNetwork\SlimKernel\Collection\collection;

final readonly class ApacheConfigurationGenerator {
    public static function generate(Vhost ...$vhosts): string {
        return collection($vhosts)
            ->map(VirtualHost::fromVhost(...))
            ->map(Composite::fromCollection(...))
            ->map(static fn (Composite $group) => $group->toString())
            ->interpose("\n\n")
            ->append("\n")
            ->toString();
    }
}
