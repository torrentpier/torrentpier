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
                'Target migration version to rollback to'
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

        if (!$force) {
            $this->warning('This will rollback database migrations. Data may be lost!');
            if (!$this->confirm('Are you sure you want to continue?', false)) {
                $this->comment('Operation cancelled.');
                return self::SUCCESS;
            }
        }

        $phinxPath = BB_ROOT . 'vendor/bin/phinx';
        $configPath = BB_ROOT . 'phinx.php';

        if (!file_exists($phinxPath)) {
            $this->error('Phinx not found. Please run: composer install');
            return self::FAILURE;
        }

        $command = sprintf(
            '%s rollback --configuration=%s',
            escapeshellarg($phinxPath),
            escapeshellarg($configPath)
        );

        if ($target) {
            $command .= sprintf(' --target=%s', escapeshellarg($target));
        }

        $this->info('Rolling back migrations...');
        $this->line('');

        // Pass through to phinx
        passthru($command, $exitCode);

        $this->line('');

        if ($exitCode === 0) {
            $this->success('Rollback completed successfully!');
        } else {
            $this->error('Rollback failed with exit code: ' . $exitCode);
        }

        return $exitCode === 0 ? self::SUCCESS : self::FAILURE;
    }
}

