<?php

/**
 * TorrentPier – Bull-powered BitTorrent tracker engine
 *
 * @copyright Copyright (c) 2005-2025 TorrentPier (https://torrentpier.com)
 * @link      https://github.com/torrentpier/torrentpier for the canonical source repository
 * @license   https://github.com/torrentpier/torrentpier/blob/master/LICENSE MIT License
 */

namespace TorrentPier\Console\Commands\Database;

use Symfony\Component\Console\Attribute\AsCommand;
use Symfony\Component\Console\Input\InputInterface;
use Symfony\Component\Console\Input\InputOption;
use Symfony\Component\Console\Output\OutputInterface;
use TorrentPier\Console\Commands\Command;
use TorrentPier\Console\Helpers\FileSystemHelper;

/**
 * Optimize database tables
 *
 * Runs OPTIMIZE and ANALYZE on database tables to defragment
 * and update index statistics.
 */
#[AsCommand(
    name: 'db:optimize',
    description: 'Optimize and analyze database tables'
)]
class OptimizeCommand extends Command
{
    protected function configure(): void
    {
        $this
            ->addOption(
                'tables',
                't',
                InputOption::VALUE_REQUIRED,
                'Specific tables to optimize (comma-separated)'
            )
            ->addOption(
                'analyze-only',
                'a',
                InputOption::VALUE_NONE,
                'Only run ANALYZE (skip OPTIMIZE)'
            )
            ->addOption(
                'dry-run',
                null,
                InputOption::VALUE_NONE,
                'Show what would be done without executing'
            )
            ->setHelp(
                <<<'HELP'
The <info>%command.name%</info> command optimizes database tables.

Operations performed:
  - <comment>ANALYZE TABLE</comment>: Updates index statistics for query optimizer
  - <comment>OPTIMIZE TABLE</comment>: Defragments tables, reclaims space

<comment>Optimize all TorrentPier tables:</comment>
  <info>php %command.full_name%</info>

<comment>Optimize specific tables:</comment>
  <info>php %command.full_name% --tables=bb_posts,bb_topics</info>

<comment>Only analyze (faster, updates statistics):</comment>
  <info>php %command.full_name% --analyze-only</info>

<comment>Preview what would be done:</comment>
  <info>php %command.full_name% --dry-run</info>

Note: OPTIMIZE may take a long time on large tables and briefly locks them.
Consider running during low-traffic periods.
HELP
            );
    }

    protected function execute(InputInterface $input, OutputInterface $output): int
    {
        $this->title('Database Optimization');

        $specificTables = $input->getOption('tables');
        $analyzeOnly = $input->getOption('analyze-only');
        $isDryRun = $input->getOption('dry-run');

        if ($isDryRun) {
            $this->warning(['DRY-RUN MODE', 'No changes will be made.']);
            $this->line();
        }

        // Get tables to optimize
        if ($specificTables !== null) {
            $tables = array_map('trim', explode(',', $specificTables));
        } else {
            $tables = $this->getTorrentPierTables();
        }

        if (empty($tables)) {
            $this->error('No tables found to optimize.');
            return self::FAILURE;
        }

        // Get table sizes before
        $sizesBefore = $this->getTableSizes($tables);
        $totalSizeBefore = array_sum($sizesBefore);

        $this->section('Configuration');
        $this->definitionList(
            ['Tables to process' => count($tables)],
            ['Total size' => FileSystemHelper::formatBytes($totalSizeBefore)],
            ['Operations' => $analyzeOnly ? 'ANALYZE only' : 'ANALYZE + OPTIMIZE'],
        );

        if ($this->isVerbose()) {
            $this->line();
            $this->section('Tables');
            $rows = [];
            foreach ($tables as $table) {
                $rows[] = [$table, FileSystemHelper::formatBytes($sizesBefore[$table] ?? 0)];
            }
            $this->table(['Table', 'Size'], $rows);
        }

        $this->line();

        if ($isDryRun) {
            $this->comment('Would process ' . count($tables) . ' table(s)');
            return self::SUCCESS;
        }
        $this->section('Processing');

        $startTime = microtime(true);
        $results = [];

        // Create a progress bar
        $progressBar = $this->createProgressBar(count($tables));
        $progressBar->setFormat(' %current%/%max% [%bar%] %percent:3s%% | %message%');
        $progressBar->start();

        foreach ($tables as $table) {
            $progressBar->setMessage("Processing: $table");

            // ANALYZE
            $analyzeResult = DB()->fetch_row("ANALYZE TABLE `$table`");
            $analyzeStatus = $analyzeResult['Msg_type'] ?? 'unknown';

            // OPTIMIZE (unless analyze-only)
            $optimizeStatus = null;
            if (!$analyzeOnly) {
                $optimizeResult = DB()->fetch_row("OPTIMIZE TABLE `$table`");
                $optimizeStatus = $optimizeResult['Msg_type'] ?? 'unknown';
            }

            $results[] = [
                'table' => $table,
                'analyze' => $analyzeStatus,
                'optimize' => $optimizeStatus,
            ];
            $progressBar->advance();
        }

        $progressBar->setMessage('Done');
        $progressBar->finish();
        $this->line();

        $elapsed = round(microtime(true) - $startTime, 2);

        // Get sizes after
        $sizesAfter = $this->getTableSizes($tables);
        $totalSizeAfter = array_sum($sizesAfter);

        // Display results
        $this->line();
        $this->section('Results');

        if ($this->isVerbose()) {
            $rows = [];
            foreach ($results as $result) {
                $sizeBefore = $sizesBefore[$result['table']] ?? 0;
                $sizeAfter = $sizesAfter[$result['table']] ?? 0;
                $saved = $sizeBefore - $sizeAfter;

                $rows[] = [
                    $result['table'],
                    $result['analyze'],
                    $result['optimize'] ?? '-',
                    $saved > 0 ? FileSystemHelper::formatBytes($saved) : '-',
                ];
            }
            $this->table(['Table', 'Analyze', 'Optimize', 'Space Saved'], $rows);
            $this->line();
        }

        $totalSaved = $totalSizeBefore - $totalSizeAfter;

        $this->definitionList(
            ['Tables processed' => count($tables)],
            ['Size before' => FileSystemHelper::formatBytes($totalSizeBefore)],
            ['Size after' => FileSystemHelper::formatBytes($totalSizeAfter)],
            ['Space saved' => $totalSaved > 0 ? FileSystemHelper::formatBytes($totalSaved) : 'None'],
            ['Time elapsed' => $elapsed . 's'],
        );

        // Note when size increased (common for InnoDB)
        if ($totalSaved < 0) {
            $increased = FileSystemHelper::formatBytes(abs($totalSaved));
            $this->line();
            $this->comment("Note: Size increased by $increased. This is normal for InnoDB tables that weren't fragmented.");
            $this->comment('ANALYZE TABLE still updated index statistics for the query optimizer.');
        }

        $this->success('Database optimization completed!');
        return self::SUCCESS;
    }

    /**
     * Get all TorrentPier tables
     */
    private function getTorrentPierTables(): array
    {
        $prefix = 'bb_';
        $tables = [];

        $result = DB()->fetch_rowset("SHOW TABLES");
        foreach ($result as $row) {
            $table = array_values($row)[0];
            if (str_starts_with($table, $prefix)) {
                $tables[] = $table;
            }
        }

        return $tables;
    }

    /**
     * Get table sizes
     */
    private function getTableSizes(array $tables): array
    {
        $sizes = [];
        $database = DB()->selected_db;

        foreach ($tables as $table) {
            $row = DB()->fetch_row("
                SELECT DATA_LENGTH + INDEX_LENGTH as size
                FROM information_schema.TABLES
                WHERE TABLE_SCHEMA = '$database' AND TABLE_NAME = '$table'
            ");
            $sizes[$table] = (int) ($row['size'] ?? 0);
        }

        return $sizes;
    }
}
