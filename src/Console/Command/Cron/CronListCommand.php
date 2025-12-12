<?php
/**
 * TorrentPier – Bull-powered BitTorrent tracker engine
 *
 * @copyright Copyright (c) 2005-2025 TorrentPier (https://torrentpier.com)
 * @link      https://github.com/torrentpier/torrentpier for the canonical source repository
 * @license   https://github.com/torrentpier/torrentpier/blob/master/LICENSE MIT License
 */

namespace TorrentPier\Console\Command\Cron;

use DateTimeInterface;
use Symfony\Component\Console\Attribute\AsCommand;
use Symfony\Component\Console\Input\InputInterface;
use Symfony\Component\Console\Output\OutputInterface;
use Throwable;
use TorrentPier\Console\Command\Command;
use TorrentPier\Helpers\CronHelper;

/**
 * List all registered cron jobs
 */
#[AsCommand(
    name: 'cron:list',
    description: 'List all registered cron jobs and their status'
)]
class CronListCommand extends Command
{
    protected function execute(InputInterface $input, OutputInterface $output): int
    {
        $this->title('Cron Jobs');

        // Get cron jobs from a database
        try {
            $jobs = DB()->fetch_rowset("SELECT * FROM " . BB_CRON . " ORDER BY cron_active DESC, run_order");

            if (empty($jobs)) {
                $this->warning('No cron jobs found in the database.');
                return self::SUCCESS;
            }

            $rows = [];
            foreach ($jobs as $job) {
                $status = $job['cron_active'] ? '<info>Active</info>' : '<comment>Inactive</comment>';
                $lastRun = $job['last_run']
                    ? ($job['last_run'] instanceof DateTimeInterface ? $job['last_run']->format('Y-m-d H:i:s') : date('Y-m-d H:i:s', $job['last_run']))
                    : 'Never';
                $nextRun = $job['next_run']
                    ? ($job['next_run'] instanceof DateTimeInterface ? $job['next_run']->format('Y-m-d H:i:s') : date('Y-m-d H:i:s', $job['next_run']))
                    : 'N/A';
                $execTime = isset($job['execution_time']) ? round($job['execution_time'], 3) . 's' : 'N/A';

                $rows[] = [
                    $job['cron_id'],
                    $job['cron_title'],
                    $status,
                    $lastRun,
                    $nextRun,
                    $execTime,
                    $job['run_order'],
                ];
            }

            $this->table(
                ['ID', 'Title', 'Status', 'Last Run', 'Next Run', 'Exec Time', 'Order'],
                $rows
            );

            $this->section('Status');
            $this->definitionList(
                ['Cron Enabled' => CronHelper::isEnabled() ? '<info>Yes</info>' : '<comment>No</comment>'],
                ['Lock File' => is_file(CRON_RUNNING) ? '<comment>Locked</comment>' : '<info>Free</info>'],
            );

        } catch (Throwable $e) {
            $this->error('Failed to fetch cron jobs: ' . $e->getMessage());

            if ($this->isVerbose()) {
                $this->line('<error>' . $e->getTraceAsString() . '</error>');
            }

            return self::FAILURE;
        }

        return self::SUCCESS;
    }
}
