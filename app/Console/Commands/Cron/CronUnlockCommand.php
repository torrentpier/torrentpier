<?php

/**
 * TorrentPier – Bull-powered BitTorrent tracker engine
 *
 * @copyright Copyright (c) 2005-2025 TorrentPier (https://torrentpier.com)
 * @link      https://github.com/torrentpier/torrentpier for the canonical source repository
 * @license   https://github.com/torrentpier/torrentpier/blob/master/LICENSE MIT License
 */

namespace TorrentPier\Console\Commands\Cron;

use Illuminate\Contracts\Container\BindingResolutionException;
use Symfony\Component\Console\Attribute\AsCommand;
use Symfony\Component\Console\Input\InputInterface;
use Symfony\Component\Console\Input\InputOption;
use Symfony\Component\Console\Output\OutputInterface;
use TorrentPier\Console\Commands\Command;
use TorrentPier\Console\Helpers\OutputHelper;

/**
 * Remove the cron lock file
 */
#[AsCommand(
    name: 'cron:unlock',
    description: 'Remove the cron lock file to allow cron jobs to run',
)]
class CronUnlockCommand extends Command
{
    protected function configure(): void
    {
        $this->addOption(
            'force',
            'f',
            InputOption::VALUE_NONE,
            'Force unlock without confirmation',
        );
    }

    /**
     * @throws BindingResolutionException
     */
    protected function execute(InputInterface $input, OutputInterface $output): int
    {
        $this->title('Cron Unlock');

        if (!files()->isFile(CRON_RUNNING)) {
            $this->info('Cron is not locked.');

            return self::SUCCESS;
        }

        // Show lock file info
        $lockTime = files()->lastModified(CRON_RUNNING);
        $lockedFor = time() - $lockTime;

        $this->section('Lock Information');
        $this->definitionList(
            ['Lock File' => CRON_RUNNING],
            ['Locked Since' => date('Y-m-d H:i:s', $lockTime)],
            ['Locked For' => OutputHelper::formatDuration($lockedFor)],
        );

        if (!$input->getOption('force') && !$this->confirm('Remove the lock file?')) {
            $this->comment('Operation cancelled.');

            return self::SUCCESS;
        }

        if (!files()->delete(CRON_RUNNING)) {
            $this->error('Failed to remove lock file. Check permissions.');

            return self::FAILURE;
        }

        $this->success('Cron lock removed successfully.');

        return self::SUCCESS;
    }
}
