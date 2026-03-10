<?php

declare(strict_types=1);

namespace WonderNetwork\SlimKernel\Supervisor;

use PHPUnit\Framework\TestCase;
use Symfony\Component\Console\Input\ArgvInput;
use Symfony\Component\Console\Output\BufferedOutput;

final class GenerateSupervisorConfigCommandTest extends TestCase {
    public function testGeneratesConfig(): void {
        $programs = SupervisorConfiguration::start()
            ->withSimpleCommand('worker', 'bin/worker async')
            ->withPrograms(
                new SupervisorProgram(
                    name: 'jobs',
                    command: 'bin/worker jobs',
                    concurrency: 3,
                    startretries: 10,
                ),
            );
        $configDir = __DIR__.'/../Resources/Supervisor';
        $sut = new GenerateSupervisorConfigCommand(
            configDir: $configDir,
            configuration: $programs,
        );

        $output = new BufferedOutput();
        $argv = [(string) $sut->getName(), '--config-dir', $configDir, '/var/app/current'];
        $sut->run(new ArgvInput($argv), $output);

        self::assertSame(
            <<<EOF
            Writing worker configuration file to $configDir/worker.conf
            Writing jobs configuration file to $configDir/jobs.conf
            EOF,
            trim($output->fetch()),
        );

        $files = glob($configDir.'/*.conf') ?: [];
        sort($files);

        self::assertSame(["$configDir/jobs.conf", "$configDir/worker.conf"], $files);
        self::assertSame(
            <<<EOF
            [program:jobs]
            command=/var/app/current/bin/worker jobs
            process_name=%(program_name)s_%(process_num)02d
            numprocs=3
            startretries=10
            user=www-data
            autostart=true
            autorestart=true
            stderr_logfile=/var/log/supervisor/%(program_name)s_%(process_num)02d.err.log
            stderr_logfile_maxbytes=50MB
            stdout_logfile=/var/log/supervisor/%(program_name)s_%(process_num)02d.out.log
            stdout_logfile_maxbytes=50MB
            [program:worker]
            command=/var/app/current/bin/worker async
            process_name=worker
            numprocs=1
            startretries=15
            user=www-data
            autostart=true
            autorestart=true
            stderr_logfile=/var/log/supervisor/worker.err.log
            stderr_logfile_maxbytes=50MB
            stdout_logfile=/var/log/supervisor/worker.out.log
            stdout_logfile_maxbytes=50MB
            EOF,
            implode(
                "\n",
                array_map(
                    static fn (string $filename) => file_get_contents($filename),
                    $files,
                ),
            ),
        );
    }

    public function testGeneratesConfigWithStdio(): void {
        $programs = SupervisorConfiguration::start()
            ->withSimpleCommand('worker', 'bin/worker async')
            ->withPrograms(
                new SupervisorProgram(
                    name: 'jobs',
                    command: 'bin/worker jobs',
                    concurrency: 3,
                    startretries: 10,
                ),
            );
        $configDir = __DIR__.'/../Resources/Supervisor';
        $sut = new GenerateSupervisorConfigCommand(
            configDir: $configDir,
            configuration: $programs,
        );

        $output = new BufferedOutput();
        $argv = [(string) $sut->getName(), '--stdio', '--config-dir', $configDir, '/var/app/current'];
        $sut->run(new ArgvInput($argv), $output);

        $files = glob($configDir.'/*.conf') ?: [];
        sort($files);

        self::assertSame(
            <<<EOF
            [program:jobs]
            command=/var/app/current/bin/worker jobs
            process_name=%(program_name)s_%(process_num)02d
            numprocs=3
            startretries=10
            user=www-data
            autostart=true
            autorestart=true
            stderr_logfile=/dev/stderr
            stderr_logfile_maxbytes=0
            stdout_logfile=/dev/stdout
            stdout_logfile_maxbytes=0
            [program:worker]
            command=/var/app/current/bin/worker async
            process_name=worker
            numprocs=1
            startretries=15
            user=www-data
            autostart=true
            autorestart=true
            stderr_logfile=/dev/stderr
            stderr_logfile_maxbytes=0
            stdout_logfile=/dev/stdout
            stdout_logfile_maxbytes=0
            EOF,
            implode(
                "\n",
                array_map(
                    static fn (string $filename) => file_get_contents($filename),
                    $files,
                ),
            ),
        );
    }
}
