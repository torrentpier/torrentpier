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
use Throwable;
use TorrentPier\Console\Command\Command;
use TorrentPier\Console\Helpers\PhinxManager;

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

        try {
            $phinx = new PhinxManager($input, $output);
            $status = $phinx->getStatus();

            // Build table rows
            $rows = [];
            foreach ($status['migrations'] as $migration) {
                $statusLabel = match ($migration['status']) {
                    'up' => '<info>✓ Up</info>',
                    'down' => '<comment>○ Down</comment>',
                    'missing' => '<error>✗ Missing</error>',
                    default => $migration['status'],
                };

                $ranAt = $migration['ran_at'] ?? '-';

                $rows[] = [
                    $migration['version'],
                    $migration['name'],
                    $statusLabel,
                    $ranAt,
                ];
            }

            if (empty($rows)) {
                $this->warning('No migrations found.');
                return self::SUCCESS;
            }

            $this->table(
                ['Version', 'Migration Name', 'Status', 'Ran At'],
                $rows
            );

            // Summary
            $this->section('Summary');
            $this->definitionList(
                ['Environment' => $phinx->environment],
                ['Total Migrations' => count($status['migrations'])],
                ['Pending' => $status['pending'] > 0
                    ? '<comment>' . $status['pending'] . '</comment>'
                    : '<info>0</info>'],
                ['Ran' => $status['ran']],
                ['Missing' => $status['missing'] > 0
                    ? '<error>' . $status['missing'] . '</error>'
                    : '0'],
            );

            if ($status['pending'] > 0) {
                $this->line();
                $this->comment('Run "php bull migrate" to apply pending migrations.');
            }

            if ($status['missing'] > 0) {
                $this->line();
                $this->warning('Some migrations are missing their source files!');
            }

            return self::SUCCESS;
        } catch (Throwable $e) {
            $this->error('Failed to get migration status: ' . $e->getMessage());

            if ($this->isVerbose()) {
                $this->line('<error>' . $e->getTraceAsString() . '</error>');
            }

            return self::FAILURE;
        }
    }
}
