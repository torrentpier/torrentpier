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
use Symfony\Component\Console\Output\OutputInterface;
use TorrentPier\Console\Command\Command;

/**
 * Show migration status
 */
#[AsCommand(
    name: 'migrate:status',
    description: 'Show database migration status'
)]
class MigrateStatusCommand extends Command
{
    protected function execute(InputInterface $input, OutputInterface $output): int
    {
        $this->title('Migration Status');

        $phinxPath = BB_ROOT . 'vendor/bin/phinx';
        $configPath = BB_ROOT . 'phinx.php';

        if (!file_exists($phinxPath)) {
            $this->error('Phinx not found. Please run: composer install');
            return self::FAILURE;
        }

        $command = sprintf(
            '%s status --configuration=%s',
            escapeshellarg($phinxPath),
            escapeshellarg($configPath)
        );

        // Pass through to phinx
        passthru($command, $exitCode);

        return $exitCode === 0 ? self::SUCCESS : self::FAILURE;
    }
}

