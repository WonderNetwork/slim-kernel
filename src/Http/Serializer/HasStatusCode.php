<?php

declare(strict_types=1);

namespace WonderNetwork\SlimKernel\Http\Serializer;

interface HasStatusCode {
    public function getStatusCode(): int;
}
