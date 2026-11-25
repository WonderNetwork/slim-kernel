<?php

declare(strict_types=1);

namespace WonderNetwork\SlimKernel\Apache;

enum LogLevel: string {
    case Emergency = 'emerg';
    case Alert = 'alert';
    case Critical = 'crit';
    case Error = 'error';
    case Warning = 'warn';
    case Notice = 'notice';
    case Info = 'info';
    case Debug = 'debug';
}
