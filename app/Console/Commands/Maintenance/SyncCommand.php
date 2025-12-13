<?php

/**
 * TorrentPier – Bull-powered BitTorrent tracker engine
 *
 * @copyright Copyright (c) 2005-2025 TorrentPier (https://torrentpier.com)
 * @link      https://github.com/torrentpier/torrentpier for the canonical source repository
 * @license   https://github.com/torrentpier/torrentpier/blob/master/LICENSE MIT License
 */

namespace TorrentPier\Console\Commands\Maintenance;

use Symfony\Component\Console\Attribute\AsCommand;
use Symfony\Component\Console\Input\InputInterface;
use Symfony\Component\Console\Output\OutputInterface;
use TorrentPier\Application;
use TorrentPier\Console\Helpers\FileSystemHelper;
use TorrentPier\Database\Database;
use TorrentPier\Legacy\Admin\Common;

/**
 * Synchronize all counters
 *
 * Runs full synchronization of topics, user posts, and forum counters.
 * This is a non-destructive operation that recalculates counts.
 */
#[AsCommand(
    name: 'maintenance:sync',
    description: 'Synchronize all counters (topics, users, forums)',
)]
class SyncCommand extends AbstractMaintenanceCommand
{
    public function __construct(
        private readonly Database $database,
        ?Application              $app = null,
    ) {
        parent::__construct($app);
    }

    protected function execute(InputInterface $input, OutputInterface $output): int
    {
        $this->title('Maintenance: Synchronize Counters');

        $isDryRun = $this->isDryRun();

        if ($isDryRun) {
            $this->displayDryRunNotice();
        }

        // Get current stats
        $stats = $this->getCurrentStats();

        $this->section('Current Statistics');
        $this->definitionList(
            ['Topics' => number_format($stats['topics'])],
            ['Posts' => number_format($stats['posts'])],
            ['Users' => number_format($stats['users'])],
            ['Forums' => number_format($stats['forums'])],
        );

        if ($isDryRun) {
            $this->line();
            $this->section('Operations');
            $this->listing([
                'Synchronize topic counters (topic_replies, first/last post IDs)',
                'Synchronize user post counts',
                'Synchronize forum counters (forum_posts, forum_topics)',
            ]);

            return self::SUCCESS;
        }

        $this->line();
        $this->section('Processing');

        $startTime = microtime(true);

        // Create a progress bar
        $progressBar = $this->createProgressBar(3);
        $progressBar->setFormat(' %current%/%max% [%bar%] %percent:3s%% | %message%');
        $progressBar->start();

        // Step 1: Sync topics
        $progressBar->setMessage('Synchronizing topics...');
        Common::sync('topic', 'all');
        $progressBar->advance();

        // Step 2: Sync user posts
        $progressBar->setMessage('Synchronizing user post counts...');
        Common::sync('user_posts', 'all');
        $progressBar->advance();

        // Step 3: Sync forums
        $progressBar->setMessage('Synchronizing forum counters...');
        Common::sync_all_forums();
        $progressBar->advance();

        $progressBar->setMessage('Done');
        $progressBar->finish();
        $this->line();

        $elapsed = round(microtime(true) - $startTime, 2);

        // Get updated stats
        $newStats = $this->getCurrentStats();

        $this->line();
        $this->section('Results');

        // Show changes
        $rows = [];
        $rows[] = ['Topics', number_format($stats['topics']), number_format($newStats['topics']), $this->formatDiff($stats['topics'], $newStats['topics'])];
        $rows[] = ['Posts', number_format($stats['posts']), number_format($newStats['posts']), $this->formatDiff($stats['posts'], $newStats['posts'])];
        $rows[] = ['Users', number_format($stats['users']), number_format($newStats['users']), $this->formatDiff($stats['users'], $newStats['users'])];
        $rows[] = ['Forums', number_format($stats['forums']), number_format($newStats['forums']), '-'];

        $this->table(['', 'Before', 'After', 'Diff'], $rows);

        $this->line();
        $this->definitionList(
            ['Time elapsed' => $elapsed . 's'],
            ['Memory used' => FileSystemHelper::formatBytes(memory_get_peak_usage(true))],
        );

        $this->success('Synchronization completed!');

        return self::SUCCESS;
    }

    /**
     * Get current database statistics
     */
    private function getCurrentStats(): array
    {
        return [
            'topics' => $this->database->table(BB_TOPICS)->where('topic_status != ?', TOPIC_MOVED)->count('*'),
            'posts' => $this->database->table(BB_POSTS)->count('*'),
            'users' => $this->database->table(BB_USERS)->where('user_id != ?', GUEST_UID)->count('*'),
            'forums' => $this->database->table(BB_FORUMS)->count('*'),
        ];
    }

    /**
     * Format difference between two values
     */
    private function formatDiff(int $before, int $after): string
    {
        $diff = $after - $before;
        if ($diff === 0) {
            return '-';
        }

        return ($diff > 0 ? '+' : '') . number_format($diff);
    }
}
