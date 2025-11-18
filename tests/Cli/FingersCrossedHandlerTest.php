<?php

declare(strict_types=1);

namespace WonderNetwork\SlimKernel\Cli;

use PHPUnit\Framework\TestCase;
use RuntimeException;
use Symfony\Component\Console\Command\Command;
use Symfony\Component\Console\Input\ArrayInput;
use Symfony\Component\Console\Output\BufferedOutput;
use Symfony\Component\Console\Output\OutputInterface;
use Throwable;

final class FingersCrossedHandlerTest extends TestCase {
    private BufferedOutput $spy;
    private FingersCrossedHandler $sut;

    protected function setUp(): void {
        $this->spy = new BufferedOutput();
        $this->sut = FingersCrossedHandler::of(new ArrayInput([]), $this->spy);
    }

    public function testHidesNormalOutputOnSuccess(): void {
        $result = $this->sut->run(
            function (InputParams $input, FingersCrossedOutput $output) {
                $output->writeln("Hello world");

                return Command::SUCCESS;
            },
        );
        self::assertSame(0, $result);
        self::assertSame("", $this->spy->fetch());
    }

    public function testDisplaysAllOutputOnFailure(): void {
        $result = $this->sut->run(
            function (InputParams $input, FingersCrossedOutput $output) {
                $output->writeln("Hello world");

                return Command::FAILURE;
            },
        );
        self::assertSame(1, $result);
        self::assertSame("Hello world\n", $this->spy->fetch());
    }

    public function testCorrectlyFormatsAllOutput(): void {
        $this->sut->run(
            function (InputParams $input, FingersCrossedOutput $output) {
                $output->writeln("Alpha");
                $output->writeln("Bravo");

                return Command::FAILURE;
            },
        );
        self::assertSame("Alpha\nBravo\n", $this->spy->fetch());
    }

    public function testDisplaysAllOutputWhenThrows(): void {
        $e = null;

        try {
            $this->sut->run(
                function (InputParams $input, FingersCrossedOutput $output): void {
                    $output->writeln("Hello world");

                    throw new RuntimeException();
                },
            );
        } catch (Throwable $e) {
        }
        self::assertSame("Hello world\n", $this->spy->fetch());
        self::assertInstanceOf(Throwable::class, $e);
    }

    public function testDisplaysAllOutputWhenVerbose(): void {
        $this->spy->setVerbosity(OutputInterface::VERBOSITY_VERBOSE);
        $this->sut->run(
            function (InputParams $input, FingersCrossedOutput $output) {
                $output->writeln("Hello world");

                return Command::SUCCESS;
            },
        );
        self::assertSame("Hello world\n", $this->spy->fetch());
    }

    public function testOutputIsInOrderWhenVerbositiIsIncreased(): void {
        $this->sut->run(
            function (InputParams $input, FingersCrossedOutput $output) {
                $output->writeln("Alpha");
                $this->spy->setVerbosity(OutputInterface::VERBOSITY_VERBOSE);
                $output->writeln("Bravo");

                return Command::SUCCESS;
            },
        );
        self::assertSame("Alpha\nBravo\n", $this->spy->fetch());
    }
}
