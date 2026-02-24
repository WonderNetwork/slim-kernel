<?php

declare(strict_types=1);

namespace WonderNetwork\SlimKernel\Messenger;

use Monolog\Handler\TestHandler;
use Monolog\Logger;
use PHPUnit\Framework\TestCase;

final class WorkerMemoryUsageSubscriberTest extends TestCase {
    public function testReportsAboveCutoff(): void {
        $buffer = new TestHandler();
        $logger = new Logger('some');
        $logger->pushHandler($buffer);
        $memoryLeak = [];

        $sut = new WorkerMemoryUsageSubscriber($logger, cutoff: 10 * 1024);
        $sut->onWorkerRunning();
        self::assertEmpty($buffer->getRecords());

        $memoryLeak[] = str_repeat(' ', 10 * 1024);
        // triggers the first log
        $sut->onWorkerRunning();
        self::assertCount(1, $buffer->getRecords());
        // the logs themselves take memory, so:
        $sut->onWorkerRunning();
        self::assertCount(2, $buffer->getRecords());
        // no more logs:
        $sut->onWorkerRunning();
        $sut->onWorkerRunning();
        $sut->onWorkerRunning();
        self::assertCount(2, $buffer->getRecords());

        unset($memoryLeak);
    }
}
