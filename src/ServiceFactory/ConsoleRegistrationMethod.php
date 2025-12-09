<?php

declare(strict_types=1);

namespace WonderNetwork\SlimKernel\ServiceFactory;

enum ConsoleRegistrationMethod: string {
    case Deprecated = 'add';
    case Latest = 'addCommand';
}
