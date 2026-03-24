<?php

declare(strict_types=1);

namespace WonderNetwork\SlimKernel\Messenger\Kernel;

enum CommandBusDependencies: string {
    case CachePool = self::class.'::CachePool';
    case Logger = self::class.'::Logger';
    case EventDispatcher = self::class.'::EventDispatcher';
    case Serializer = self::class.'::Serializer';
    case Worker = self::class.'::Worker';
    case SupervisorConfigDir = self::class.'::SupervisorConfigDir';
    case SendersLocator = self::class.'::SendersLocator';
    case ReceiversLocator = self::class.'::ReceiversLocator';
}
