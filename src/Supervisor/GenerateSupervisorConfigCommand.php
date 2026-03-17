<?php

declare(strict_types=1);

namespace WonderNetwork\SlimKernel\Supervisor;

use Symfony\Component\Console\Command\Command;
use Symfony\Component\Console\Input\InputArgument;
use Symfony\Component\Console\Input\InputInterface;
use Symfony\Component\Console\Input\InputOption;
use Symfony\Component\Console\Output\OutputInterface;
use Symfony\Component\Console\Style\SymfonyStyle;
use WonderNetwork\SlimKernel\Cli\InitializeInputParamsTrait;

final class GenerateSupervisorConfigCommand extends Command {
    use InitializeInputParamsTrait;

    public function __construct(
        private readonly string $configDir,
        private readonly SupervisorConfiguration $configuration,
    ) {
        parent::__construct('supervisor:generate-config');
    }

    protected function configure(): void {
        $this->addArgument(
            name: 'current-directory',
            mode: InputArgument::REQUIRED,
            description: 'The absolute directory to the script',
        );

        $this->addOption(
            name: 'stdio',
            mode: InputOption::VALUE_NONE,
            description: "Log to stdout/stderr instead of files",
        );

        $this->addOption(
            name: 'purge',
            mode: InputOption::VALUE_NONE | InputOption::VALUE_NEGATABLE,
            description: 'Remove all files before generating new ones',
        );

        $this->addOption(
            name: 'logfile',
            mode: InputOption::VALUE_REQUIRED,
            default: '/var/log/supervisor/{processName}.{suffix}.log',
        );

        $this->addOption(
            name: 'logfile-maxbytes',
            mode: InputOption::VALUE_REQUIRED,
            default: "50MB",
        );

        $this->addOption(
            name: 'config-dir',
            mode: InputOption::VALUE_REQUIRED,
            default: $this->configDir,
        );
    }

    protected function execute(InputInterface $input, OutputInterface $output): int {
        $io = new SymfonyStyle($input, $output);

        $currentDirectory = $this->params->arguments->string('current-directory');
        $stdio = $this->params->options->bool('stdio');
        $noPurge = $this->params->options->bool('no-purge');
        $logfile = $this->params->options->string('logfile');
        $maxBytes = $this->params->options->string('logfile-maxbytes');
        $configDir = $this->params->options->string('config-dir');

        if ("" === $configDir) {
            $io->error("Config directory can't be empty");

            return self::FAILURE;
        }

        if (false === $noPurge) {
            foreach (glob(sprintf('%s/*.conf', $configDir)) ?: [] as $preExistingConfig) {
                unlink($preExistingConfig);
            }
        }

        if ($stdio) {
            $maxBytes = "0";
            $logfile = '/dev/std{suffix}';
        }

        foreach ($this->configuration->programs as $program) {
            $concurrency = $program->concurrency;
            $processName = $program->name;

            if ($concurrency > 1) {
                $processName = '%(program_name)s_%(process_num)02d';
            }

            $errorLog = strtr($logfile, ['{processName}' => $processName, '{suffix}' => 'err']);
            $standardLog = strtr($logfile, ['{processName}' => $processName, '{suffix}' => 'out']);

            $fullCommand = sprintf('%s/%s', rtrim($currentDirectory, '/'), $program->command);
            $supervisorConfigPath = sprintf('%s/%s.conf', $configDir, $program->name);

            $supervisorConfig = <<<EOF
            [program:$program->name]
            command=$fullCommand
            process_name=$processName
            numprocs=$concurrency
            startretries=$program->startretries
            user=www-data
            autostart=true
            autorestart=true
            stderr_logfile=$errorLog
            stderr_logfile_maxbytes=$maxBytes
            stdout_logfile=$standardLog
            stdout_logfile_maxbytes=$maxBytes
            EOF;

            file_put_contents($supervisorConfigPath, $supervisorConfig);
            $io->writeln(
                sprintf(
                    'Writing <comment>%s</comment> configuration file to <info>%s</info>',
                    $program->name,
                    $supervisorConfigPath,
                ),
            );
        }

        return self::SUCCESS;
    }
}
