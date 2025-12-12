<?php

/**
 * TorrentPier – Bull-powered BitTorrent tracker engine
 *
 * @copyright Copyright (c) 2005-2025 TorrentPier (https://torrentpier.com)
 * @link      https://github.com/torrentpier/torrentpier for the canonical source repository
 * @license   https://github.com/torrentpier/torrentpier/blob/master/LICENSE MIT License
 */

namespace TorrentPier\Console\Commands\Cron;

use Symfony\Component\Console\Attribute\AsCommand;
use Symfony\Component\Console\Input\InputInterface;
use Symfony\Component\Console\Input\InputOption;
use Symfony\Component\Console\Output\OutputInterface;
use Throwable;
use TorrentPier\Console\Commands\Command;
use TorrentPier\Helpers\CronHelper;
use TorrentPier\Legacy\Admin\Cron;

/**
 * Run cron jobs manually
 */
#[AsCommand(
    name: 'cron:run',
    description: 'Run cron jobs (all or specific by ID)'
)]
class CronRunCommand extends Command
{
    protected function configure(): void
    {
        $this
            ->addOption(
                'job',
                'j',
                InputOption::VALUE_REQUIRED,
                'Run specific job(s) by ID (comma-separated, e.g., --job=1,2,3)'
            )
            ->addOption(
                'force',
                'f',
                InputOption::VALUE_NONE,
                'Force run regardless of schedule'
            );
    }

    protected function execute(InputInterface $input, OutputInterface $output): int
    {
        $force = $input->getOption('force');
        $jobIds = $input->getOption('job');

        $this->title('Cron Job Runner');

        // Run specific jobs by ID
        if ($jobIds !== null) {
            return $this->runSpecificJobs($jobIds);
        }

        // Run all scheduled jobs
        if (!CronHelper::isEnabled() && !$force) {
            $this->warning('Cron is disabled. Use --force to run anyway.');
            return self::SUCCESS;
        }

        $this->info('Starting cron jobs...');
        $this->line();

        $startTime = microtime(true);

        try {
            $executed = CronHelper::run(force: $force);

            $endTime = microtime(true);
            $duration = round($endTime - $startTime, 3);

            if ($executed) {
                $this->success("Cron jobs completed successfully in {$duration}s");
            } else {
                $this->comment('No cron jobs needed to run at this time.');
                $this->line("  Time taken: {$duration}s");
            }

            return self::SUCCESS;
        } catch (Throwable $e) {
            $this->error('Cron execution failed: ' . $e->getMessage());

            if ($this->isVerbose()) {
                $this->line('<error>' . $e->getTraceAsString() . '</error>');
            }

            return self::FAILURE;
        }
    }

    /**
     * Run specific cron jobs by ID
     */
    private function runSpecificJobs(string $jobIds): int
    {
        // Validate: only digits and commas
        if (!preg_match('/^[\d,]+$/', $jobIds)) {
            $this->error('Invalid job ID format. Use comma-separated numbers (e.g., 1,2,3)');
            return self::FAILURE;
        }

        $this->info("Running jobs: $jobIds");
        $this->line();

        $startTime = microtime(true);

        try {
            Cron::run_jobs($jobIds);

            $duration = round(microtime(true) - $startTime, 3);
            $this->success("Jobs completed in {$duration}s");

            return self::SUCCESS;
        } catch (Throwable $e) {
            $this->error('Job execution failed: ' . $e->getMessage());

            if ($this->isVerbose()) {
                $this->line('<error>' . $e->getTraceAsString() . '</error>');
            }

            return self::FAILURE;
        }
    }
}
