<?php
/**
 * TorrentPier – Bull-powered BitTorrent tracker engine
 *
 * @copyright Copyright (c) 2005-2025 TorrentPier (https://torrentpier.com)
 * @link      https://github.com/torrentpier/torrentpier for the canonical source repository
 * @license   https://github.com/torrentpier/torrentpier/blob/master/LICENSE MIT License
 */

namespace TorrentPier\Console\Command\Cron;

use Symfony\Component\Console\Attribute\AsCommand;
use Symfony\Component\Console\Input\InputInterface;
use Symfony\Component\Console\Input\InputOption;
use Symfony\Component\Console\Output\OutputInterface;
use TorrentPier\Console\Command\Command;
use TorrentPier\Helpers\CronHelper;

/**
 * Run cron jobs manually
 */
#[AsCommand(
    name: 'cron:run',
    description: 'Run cron jobs'
)]
class CronRunCommand extends Command
{
    protected function configure(): void
    {
        $this
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

        $this->title('Cron Job Runner');

        if (!CronHelper::isEnabled() && !$force) {
            $this->warning('Cron is disabled. Use --force to run anyway.');
            return self::SUCCESS;
        }

        $this->info('Starting cron jobs...');
        $this->line('');

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
        } catch (\Throwable $e) {
            $this->error('Cron execution failed: ' . $e->getMessage());

            if ($this->isVerbose()) {
                $this->line('<error>' . $e->getTraceAsString() . '</error>');
            }

            return self::FAILURE;
        }
    }
}

