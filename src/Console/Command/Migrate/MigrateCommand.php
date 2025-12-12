<?php
/**
 * TorrentPier – Bull-powered BitTorrent tracker engine
 *
 * @copyright Copyright (c) 2005-2025 TorrentPier (https://torrentpier.com)
 * @link      https://github.com/torrentpier/torrentpier for the canonical source repository
 * @license   https://github.com/torrentpier/torrentpier/blob/master/LICENSE MIT License
 */

namespace TorrentPier\Console\Command\Migrate;

use Symfony\Component\Console\Attribute\AsCommand;
use Symfony\Component\Console\Input\InputInterface;
use Symfony\Component\Console\Input\InputOption;
use Symfony\Component\Console\Output\OutputInterface;
use TorrentPier\Console\Command\Command;
use TorrentPier\Console\Helpers\PhinxManager;

/**
 * Run database migrations
 */
#[AsCommand(
    name: 'migrate',
    description: 'Run database migrations'
)]
class MigrateCommand extends Command
{
    protected function configure(): void
    {
        $this
            ->addOption(
                'target',
                't',
                InputOption::VALUE_OPTIONAL,
                'Target migration version'
            )
            ->addOption(
                'fake',
                null,
                InputOption::VALUE_NONE,
                'Mark migrations as run without actually running them'
            )
            ->addOption(
                'force',
                'f',
                InputOption::VALUE_NONE,
                'Force run without confirmation (for automated scripts)'
            );
    }

    protected function execute(InputInterface $input, OutputInterface $output): int
    {
        $this->title('Database Migrations');

        $target = $input->getOption('target');
        $fake = $input->getOption('fake');

        try {
            $phinx = new PhinxManager($input, $output);

            // Show current status first
            $status = $phinx->getStatus();

            if ($status['pending'] === 0) {
                $this->success('Database is up to date. No migrations to run.');
                return self::SUCCESS;
            }

            $this->info(sprintf('Found %d pending migration(s)', $status['pending']));
            $this->line('');

            if ($fake) {
                $this->warning('Running in FAKE mode - migrations will be marked as run without executing');
                $this->line('');
            }

            $this->section('Running Migrations');

            $targetVersion = $target !== null ? (int)$target : null;
            $phinx->migrate($targetVersion, $fake);

            $this->line('');
            $this->success('Migrations completed successfully!');

            return self::SUCCESS;
        } catch (\Throwable $e) {
            $this->error('Migration failed: ' . $e->getMessage());

            if ($this->isVerbose()) {
                $this->line('');
                $this->line('<error>' . $e->getTraceAsString() . '</error>');
            }

            return self::FAILURE;
        }
    }
}
