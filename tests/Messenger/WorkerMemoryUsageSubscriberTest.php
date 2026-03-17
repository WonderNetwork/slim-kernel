<?php

declare(strict_types=1);

namespace WonderNetwork\SlimKernel\Messenger;

use Monolog\Handler\TestHandler;
use Monolog\Logger;
use PHPUnit\Framework\TestCase;
use WonderNetwork\SlimKernel\System\FakeSystem;

final class WorkerMemoryUsageSubscriberTest extends TestCase {
    public function testReportsAboveCutoff(): void {
        $buffer = new TestHandler();
        $logger = new Logger('some');
        $logger->pushHandler($buffer);

        $system = new FakeSystem();

        $sut = new WorkerMemoryUsageSubscriber(
            logger: $logger,
            cutoff: 10 * 1024,
            system: $system,
        );
        $sut->onWorkerRunning();
        self::assertEmpty($buffer->getRecords());

        $system->setMemoryUsage(10 * 1024 - 1);
        $sut->onWorkerRunning();
        self::assertCount(0, $buffer->getRecords());

        $system->setMemoryUsage(10 * 1024);
        $sut->onWorkerRunning();
        self::assertCount(1, $buffer->getRecords());
        self::assertSame([
            'current' => '10 KB',
            'sign' => '+',
            'difference' => '10 KB',
        ], $buffer->getRecords()[0]->context);

        $sut->onWorkerRunning();
        $system->setMemoryUsage(12 * 1024);
        $sut->onWorkerRunning();
        self::assertCount(1, $buffer->getRecords());
    }
}
