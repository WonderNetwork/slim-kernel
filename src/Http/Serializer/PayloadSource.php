<?php

declare(strict_types=1);

namespace WonderNetwork\SlimKernel\Http\Serializer;

enum PayloadSource {
    case Post;
    case Get;
}
