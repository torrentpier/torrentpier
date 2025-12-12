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
 * Rollback database migrations
 */
#[AsCommand(
    name: 'migrate:rollback',
    description: 'Rollback the last database migration'
)]
class MigrateRollbackCommand extends Command
{
    protected function configure(): void
    {
        $this
            ->addOption(
                'target',
                't',
                InputOption::VALUE_OPTIONAL,
                'Target migration version to rollback to (0 = rollback all)'
            )
            ->addOption(
                'force',
                'f',
                InputOption::VALUE_NONE,
                'Force rollback without confirmation'
            );
    }

    protected function execute(InputInterface $input, OutputInterface $output): int
    {
        $this->title('Migration Rollback');

        $target = $input->getOption('target');
        $force = $input->getOption('force');

        try {
            $phinx = new PhinxManager($input, $output);
            $status = $phinx->getStatus();

            if ($status['ran'] === 0) {
                $this->warning('No migrations to rollback.');
                return self::SUCCESS;
            }

            // Show what will be rolled back
            $ranMigrations = array_filter(
                $status['migrations'],
                fn($m) => $m['status'] === 'up'
            );

            if (empty($ranMigrations)) {
                $this->warning('No migrations to rollback.');
                return self::SUCCESS;
            }

            if ($target === '0') {
                $this->warning('This will rollback ALL migrations!');
                $affectedCount = count($ranMigrations);
            } elseif ($target !== null) {
                $targetVersion = (int)$target;
                $affectedCount = count(array_filter(
                    $ranMigrations,
                    fn($m) => (int)$m['version'] > $targetVersion
                ));
                $this->info(sprintf('Rolling back to version %d (%d migration(s))', $targetVersion, $affectedCount));
            } else {
                $affectedCount = 1;
                $lastMigration = end($ranMigrations);
                $this->info(sprintf('Rolling back: %s', $lastMigration['name']));
            }

            if (!$force) {
                $this->line('');
                $this->warning('⚠ This operation may cause data loss!');
                $this->line('');

                if (!$this->confirm('Are you sure you want to rollback?', false)) {
                    $this->comment('Operation cancelled.');
                    return self::SUCCESS;
                }
            }

            $this->section('Rolling Back');

            $targetVersion = $target !== null ? (int)$target : null;
            $phinx->rollback($targetVersion);

            $this->line('');
            $this->success('Rollback completed successfully!');

            return self::SUCCESS;
        } catch (\Throwable $e) {
            $this->error('Rollback failed: ' . $e->getMessage());

            if ($this->isVerbose()) {
                $this->line('<error>' . $e->getTraceAsString() . '</error>');
            }

            return self::FAILURE;
        }
    }
}
