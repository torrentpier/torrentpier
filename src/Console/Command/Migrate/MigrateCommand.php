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
            );
    }

    protected function execute(InputInterface $input, OutputInterface $output): int
    {
        $this->title('Database Migrations');

        $target = $input->getOption('target');
        $fake = $input->getOption('fake');

        $phinxPath = BB_ROOT . 'vendor/bin/phinx';
        $configPath = BB_ROOT . 'phinx.php';

        if (!file_exists($phinxPath)) {
            $this->error('Phinx not found. Please run: composer install');
            return self::FAILURE;
        }

        $command = sprintf(
            '%s migrate --configuration=%s',
            escapeshellarg($phinxPath),
            escapeshellarg($configPath)
        );

        if ($target) {
            $command .= sprintf(' --target=%s', escapeshellarg($target));
        }

        if ($fake) {
            $command .= ' --fake';
        }

        $this->info('Running migrations...');
        $this->line('');

        // Pass through to phinx
        passthru($command, $exitCode);

        $this->line('');

        if ($exitCode === 0) {
            $this->success('Migrations completed successfully!');
        } else {
            $this->error('Migration failed with exit code: ' . $exitCode);
        }

        return $exitCode === 0 ? self::SUCCESS : self::FAILURE;
    }
}

