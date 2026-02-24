<?php

declare(strict_types=1);

namespace WonderNetwork\SlimKernel\Messenger;

use Psr\Log\LoggerInterface;
use Symfony\Component\EventDispatcher\EventSubscriberInterface;
use Symfony\Component\Messenger\Event\WorkerRunningEvent;

final class WorkerMemoryUsageSubscriber implements EventSubscriberInterface {
    private int $memoryUsage;

    /**
     * @return iterable<string,string>
     */
    public static function getSubscribedEvents(): iterable {
        return [
            WorkerRunningEvent::class => 'onWorkerRunning',
        ];
    }

    public function __construct(
        private readonly LoggerInterface $logger,
        private readonly int $cutoff = 1024,
    ) {
        $this->memoryUsage = memory_get_usage();
    }

    public function onWorkerRunning(): void {
        $currentUsage = memory_get_usage();

        $difference = abs($this->memoryUsage - $currentUsage);

        if ($difference < $this->cutoff) {
            return;
        }

        $this->logger->debug(
            "Memory usage changed: {current} ({sign}{difference})",
            [
                'current' => $this->formatBytes($currentUsage),
                'sign' => ($this->memoryUsage > $currentUsage) ? "-" : "+",
                'difference' => $this->formatBytes($difference),
            ],
        );

        $this->memoryUsage = $currentUsage;
    }

    private function formatBytes(int $bytes): string {
        $units = ['B', 'KB', 'MB', 'GB', 'TB'];

        $bytes = max($bytes, 0);
        $pow = floor(($bytes ? log($bytes) : 0) / log(1024));
        $pow = (int) min($pow, count($units) - 1);

        $bytes /= 1024 ** $pow;

        return sprintf("%s %s", round($bytes, 2), $units[$pow]);
    }
}
