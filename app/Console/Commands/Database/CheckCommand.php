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

/**
 * Check database integrity
 *
 * Runs CHECK TABLE and detects orphan records.
 */
#[AsCommand(
    name: 'db:check',
    description: 'Check database integrity and find orphan records'
)]
class CheckCommand extends Command
{
    protected function configure(): void
    {
        $this
            ->addOption(
                'fix-orphans',
                null,
                InputOption::VALUE_NONE,
                'Delete orphan records (use with caution!)'
            )
            ->addOption(
                'dry-run',
                null,
                InputOption::VALUE_NONE,
                'Show what would be done without making changes'
            )
            ->setHelp(
                <<<'HELP'
The <info>%command.name%</info> command checks database integrity.

Checks performed:
  - <comment>CHECK TABLE</comment>: Verifies table structure integrity
  - <comment>Orphan detection</comment>: Finds records without valid parent references

<comment>Run integrity check:</comment>
  <info>php %command.full_name%</info>

<comment>Fix orphan records (delete them):</comment>
  <info>php %command.full_name% --fix-orphans</info>

<comment>Preview fixes without changes:</comment>
  <info>php %command.full_name% --fix-orphans --dry-run</info>

Orphan checks include:
  - Posts without topics
  - Topics without forums
  - Post text without posts
  - Torrent data without topics
HELP
            );
    }

    protected function execute(InputInterface $input, OutputInterface $output): int
    {
        $this->title('Database Integrity Check');

        $fixOrphans = $input->getOption('fix-orphans');
        $isDryRun = $input->getOption('dry-run');

        if ($isDryRun) {
            $this->warning(['DRY-RUN MODE', 'No changes will be made.']);
            $this->line();
        }

        $hasErrors = false;
        $hasOrphans = false;

        // Step 1: CHECK TABLE
        $this->section('Table Integrity Check');

        $tables = $this->getTorrentPierTables();
        $checkResults = [];
        $errorCount = 0;

        $progressBar = $this->createProgressBar(count($tables));
        $progressBar->setFormat(' %current%/%max% [%bar%] %percent:3s%% | %message%');
        $progressBar->start();

        foreach ($tables as $table) {
            $progressBar->setMessage("Checking: $table");

            $result = DB()->fetch_row("CHECK TABLE `$table`");
            $status = $result['Msg_type'] ?? 'unknown';
            $message = $result['Msg_text'] ?? '';

            if ($status !== 'status' || $message !== 'OK') {
                $checkResults[] = [
                    'table' => $table,
                    'status' => $status,
                    'message' => $message,
                ];

                if (in_array(strtolower($status), ['error', 'warning'])) {
                    $errorCount++;
                }
            }

            $progressBar->advance();
        }

        $progressBar->setMessage('Done');
        $progressBar->finish();
        $this->line();

        if (empty($checkResults)) {
            $this->info('All ' . count($tables) . ' tables passed integrity check.');
        } else {
            $rows = [];
            foreach ($checkResults as $result) {
                $statusDisplay = match (strtolower($result['status'])) {
                    'error' => '<error>' . $result['status'] . '</error>',
                    'warning' => '<comment>' . $result['status'] . '</comment>',
                    default => $result['status'],
                };
                $rows[] = [$result['table'], $statusDisplay, $result['message']];
            }
            $this->table(['Table', 'Status', 'Message'], $rows);

            if ($errorCount > 0) {
                $hasErrors = true;
                $this->error("Found $errorCount table(s) with issues!");
            }
        }

        // Step 2: Orphan detection
        $this->line();
        $this->section('Orphan Record Detection');

        $orphans = $this->detectOrphans();
        $totalOrphans = array_sum($orphans);

        $rows = [];
        foreach ($orphans as $type => $count) {
            $status = $count > 0 ? '<comment>' . number_format($count) . '</comment>' : '<info>0</info>';
            $rows[] = [$type, $status];
        }

        $this->table(['Check', 'Orphan Records'], $rows);

        if ($totalOrphans > 0) {
            $hasOrphans = true;
            $this->line();
            $this->warning("Found $totalOrphans total orphan record(s).");

            if ($fixOrphans) {
                if ($isDryRun) {
                    $this->comment("Would delete $totalOrphans orphan record(s).");
                } else {
                    $this->line();
                    $this->section('Fixing Orphans');
                    $deleted = $this->fixOrphans();
                    $this->info("Deleted $deleted orphan record(s).");
                }
            } else {
                $this->comment('Use --fix-orphans to delete orphan records.');
            }
        } else {
            $this->info('No orphan records found.');
        }

        // Summary
        $this->line();
        $this->section('Summary');
        $this->definitionList(
            ['Tables checked' => count($tables)],
            ['Table errors' => $errorCount > 0 ? "<error>$errorCount</error>" : '<info>0</info>'],
            ['Orphan records' => $totalOrphans > 0 ? "<comment>$totalOrphans</comment>" : '<info>0</info>'],
        );

        if ($hasErrors) {
            $this->error('Database has integrity issues that may require manual repair.');
            return self::FAILURE;
        }

        if ($hasOrphans && !$fixOrphans) {
            $this->warning('Orphan records found. Consider running with --fix-orphans.');
            return self::SUCCESS;
        }

        $this->success('Database integrity check passed!');
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
     * Detect orphan records
     */
    private function detectOrphans(): array
    {
        $orphans = [];

        // Posts without topics
        $row = DB()->fetch_row("
            SELECT COUNT(*) as cnt
            FROM " . BB_POSTS . " p
            LEFT JOIN " . BB_TOPICS . " t ON t.topic_id = p.topic_id
            WHERE t.topic_id IS NULL
        ");
        $orphans['Posts without topics'] = (int) ($row['cnt'] ?? 0);

        // Topics without forums
        $row = DB()->fetch_row("
            SELECT COUNT(*) as cnt
            FROM " . BB_TOPICS . " t
            LEFT JOIN " . BB_FORUMS . " f ON f.forum_id = t.forum_id
            WHERE f.forum_id IS NULL
        ");
        $orphans['Topics without forums'] = (int) ($row['cnt'] ?? 0);

        // Post text without posts
        $row = DB()->fetch_row("
            SELECT COUNT(*) as cnt
            FROM " . BB_POSTS_TEXT . " pt
            LEFT JOIN " . BB_POSTS . " p ON p.post_id = pt.post_id
            WHERE p.post_id IS NULL
        ");
        $orphans['Post text without posts'] = (int) ($row['cnt'] ?? 0);

        // Post search without posts
        $row = DB()->fetch_row("
            SELECT COUNT(*) as cnt
            FROM " . BB_POSTS_SEARCH . " ps
            LEFT JOIN " . BB_POSTS . " p ON p.post_id = ps.post_id
            WHERE p.post_id IS NULL
        ");
        $orphans['Post search without posts'] = (int) ($row['cnt'] ?? 0);

        // Post HTML cache without posts
        $row = DB()->fetch_row("
            SELECT COUNT(*) as cnt
            FROM " . BB_POSTS_HTML . " ph
            LEFT JOIN " . BB_POSTS . " p ON p.post_id = ph.post_id
            WHERE p.post_id IS NULL
        ");
        $orphans['Post HTML cache without posts'] = (int) ($row['cnt'] ?? 0);

        // Torrents without topics
        $row = DB()->fetch_row("
            SELECT COUNT(*) as cnt
            FROM " . BB_BT_TORRENTS . " tor
            LEFT JOIN " . BB_TOPICS . " t ON t.topic_id = tor.topic_id
            WHERE t.topic_id IS NULL
        ");
        $orphans['Torrents without topics'] = (int) ($row['cnt'] ?? 0);

        // Poll votes without topics
        $row = DB()->fetch_row("
            SELECT COUNT(*) as cnt
            FROM " . BB_POLL_VOTES . " pv
            LEFT JOIN " . BB_TOPICS . " t ON t.topic_id = pv.topic_id
            WHERE t.topic_id IS NULL
        ");
        $orphans['Poll votes without topics'] = (int) ($row['cnt'] ?? 0);

        // Poll users without topics
        $row = DB()->fetch_row("
            SELECT COUNT(*) as cnt
            FROM " . BB_POLL_USERS . " pu
            LEFT JOIN " . BB_TOPICS . " t ON t.topic_id = pu.topic_id
            WHERE t.topic_id IS NULL
        ");
        $orphans['Poll users without topics'] = (int) ($row['cnt'] ?? 0);

        return $orphans;
    }

    /**
     * Fix orphan records by deleting them
     */
    private function fixOrphans(): int
    {
        $totalDeleted = 0;

        // Delete posts without topics
        DB()->query("
            DELETE p FROM " . BB_POSTS . " p
            LEFT JOIN " . BB_TOPICS . " t ON t.topic_id = p.topic_id
            WHERE t.topic_id IS NULL
        ");
        $totalDeleted += DB()->affected_rows();

        // Delete topics without forums
        DB()->query("
            DELETE t FROM " . BB_TOPICS . " t
            LEFT JOIN " . BB_FORUMS . " f ON f.forum_id = t.forum_id
            WHERE f.forum_id IS NULL
        ");
        $totalDeleted += DB()->affected_rows();

        // Delete orphan post text
        DB()->query("
            DELETE pt FROM " . BB_POSTS_TEXT . " pt
            LEFT JOIN " . BB_POSTS . " p ON p.post_id = pt.post_id
            WHERE p.post_id IS NULL
        ");
        $totalDeleted += DB()->affected_rows();

        // Delete orphan post search
        DB()->query("
            DELETE ps FROM " . BB_POSTS_SEARCH . " ps
            LEFT JOIN " . BB_POSTS . " p ON p.post_id = ps.post_id
            WHERE p.post_id IS NULL
        ");
        $totalDeleted += DB()->affected_rows();

        // Delete orphan post HTML cache
        DB()->query("
            DELETE ph FROM " . BB_POSTS_HTML . " ph
            LEFT JOIN " . BB_POSTS . " p ON p.post_id = ph.post_id
            WHERE p.post_id IS NULL
        ");
        $totalDeleted += DB()->affected_rows();

        // Delete orphan torrents
        DB()->query("
            DELETE tor FROM " . BB_BT_TORRENTS . " tor
            LEFT JOIN " . BB_TOPICS . " t ON t.topic_id = tor.topic_id
            WHERE t.topic_id IS NULL
        ");
        $totalDeleted += DB()->affected_rows();

        // Delete orphan poll votes
        DB()->query("
            DELETE pv FROM " . BB_POLL_VOTES . " pv
            LEFT JOIN " . BB_TOPICS . " t ON t.topic_id = pv.topic_id
            WHERE t.topic_id IS NULL
        ");
        $totalDeleted += DB()->affected_rows();

        // Delete orphan poll users
        DB()->query("
            DELETE pu FROM " . BB_POLL_USERS . " pu
            LEFT JOIN " . BB_TOPICS . " t ON t.topic_id = pu.topic_id
            WHERE t.topic_id IS NULL
        ");
        $totalDeleted += DB()->affected_rows();

        return $totalDeleted;
    }
}
