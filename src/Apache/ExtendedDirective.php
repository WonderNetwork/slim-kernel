<?php

declare(strict_types=1);

namespace WonderNetwork\SlimKernel\Apache;

interface ExtendedDirective extends Directive {
    public function isGlued(): bool;
}
