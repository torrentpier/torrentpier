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
use Symfony\Component\Console\Input\InputOption;
use Symfony\Component\Console\Output\OutputInterface;
use TorrentPier\Console\Helpers\FileSystemHelper;

/**
 * Cleanup stale data
 *
 * Removes old poll votes, expired password reset requests, and cached post HTML.
 */
#[AsCommand(
    name: 'maintenance:cleanup',
    description: 'Clean up stale data (poll votes, password requests, post cache)',
)]
class CleanupCommand extends AbstractMaintenanceCommand
{
    protected function configure(): void
    {
        parent::configure();

        $this->addOption(
            'all-posts-cache',
            null,
            InputOption::VALUE_NONE,
            'Clear ALL post HTML cache (bb_posts_html), not just stale entries',
        );
    }

    protected function execute(InputInterface $input, OutputInterface $output): int
    {
        $this->title('Maintenance: Cleanup');

        $isDryRun = $this->isDryRun();

        if ($isDryRun) {
            $this->displayDryRunNotice();
        }

        $clearAllPostsCache = (bool)$input->getOption('all-posts-cache');

        // Gather stats before cleanup
        $stats = $this->gatherStats($clearAllPostsCache);

        $this->section('Data to Clean');

        $postsCacheConfig = $clearAllPostsCache
            ? 'ALL (--all-posts-cache)'
            : 'posts_cache_days_keep: ' . (config()->get('posts_cache_days_keep') ?: 'disabled');

        $this->table(
            ['Type', 'Records', 'Configuration'],
            [
                ['Old poll votes', number_format($stats['poll_users']), 'poll_max_days: ' . (config()->get('poll_max_days') ?: 'disabled')],
                ['Expired password requests', number_format($stats['password_requests']), '7 days old'],
                ['Post HTML cache', number_format($stats['posts_cache']), $postsCacheConfig],
            ],
        );

        $totalRecords = $stats['poll_users'] + $stats['password_requests'] + $stats['posts_cache'];

        if ($totalRecords === 0) {
            $this->info('Nothing to clean up.');

            return self::SUCCESS;
        }

        if ($isDryRun) {
            $this->line();
            $this->comment('Would delete ' . number_format($totalRecords) . ' total record(s)');

            return self::SUCCESS;
        }

        $this->line();
        $this->section('Processing');

        $startTime = microtime(true);
        $deleted = [
            'poll_users' => 0,
            'password_requests' => 0,
            'posts_cache' => 0,
        ];

        // Clean poll users
        if ($stats['poll_users'] > 0) {
            $this->line('  Cleaning old poll votes...');
            $deleted['poll_users'] = $this->cleanPollUsers();
            $this->line("    <info>✓</info> Deleted {$deleted['poll_users']} record(s)");
        }

        // Clean password requests
        if ($stats['password_requests'] > 0) {
            $this->line('  Cleaning expired password requests...');
            $deleted['password_requests'] = $this->cleanPasswordRequests();
            $this->line("    <info>✓</info> Cleared {$deleted['password_requests']} request(s)");
        }

        // Clean post cache
        if ($stats['posts_cache'] > 0) {
            $label = $clearAllPostsCache ? 'Clearing ALL post HTML cache...' : 'Cleaning stale post HTML cache...';
            $this->line('  ' . $label);
            $deleted['posts_cache'] = $this->cleanPostsCache($clearAllPostsCache);
            $this->line("    <info>✓</info> Deleted {$deleted['posts_cache']} record(s)");
        }

        $elapsed = round(microtime(true) - $startTime, 2);
        $totalDeleted = array_sum($deleted);

        $this->line();
        $this->section('Results');
        $this->definitionList(
            ['Total records cleaned' => number_format($totalDeleted)],
            ['Time elapsed' => $elapsed . 's'],
            ['Memory used' => FileSystemHelper::formatBytes(memory_get_peak_usage(true))],
        );

        $this->success('Cleanup completed!');

        return self::SUCCESS;
    }

    /**
     * Gather statistics for cleanup targets
     */
    private function gatherStats(bool $clearAllPostsCache = false): array
    {
        $stats = [
            'poll_users' => 0,
            'password_requests' => 0,
            'posts_cache' => 0,
        ];

        // Poll users older than poll_max_days
        $pollMaxDays = (int)config()->get('poll_max_days');
        if ($pollMaxDays > 0) {
            $cutoff = TIMENOW - 86400 * $pollMaxDays;
            $stats['poll_users'] = DB()->table(BB_POLL_USERS)
                ->where('vote_dt < ?', $cutoff)
                ->count('*');
        }

        // Password requests older than 7 days
        $passwordCutoff = TIMENOW - 7 * 86400;
        $stats['password_requests'] = DB()->table(BB_USERS)
            ->where('user_newpasswd <> ?', '')
            ->where('user_lastvisit < ?', $passwordCutoff)
            ->count('*');

        // Post HTML cache
        if ($clearAllPostsCache) {
            // Count ALL records
            $stats['posts_cache'] = DB()->table(BB_POSTS_HTML)->count('*');
        } else {
            // Only older than posts_cache_days_keep
            $postsCacheDays = (int)config()->get('posts_cache_days_keep');
            if ($postsCacheDays > 0) {
                $row = DB()->fetch_row('SELECT COUNT(*) as cnt FROM ' . BB_POSTS_HTML . " WHERE post_html_time < DATE_SUB(NOW(), INTERVAL {$postsCacheDays} DAY)");
                $stats['posts_cache'] = (int)($row['cnt'] ?? 0);
            }
        }

        return $stats;
    }

    /**
     * Clean old poll user votes
     */
    private function cleanPollUsers(): int
    {
        $pollMaxDays = (int)config()->get('poll_max_days');
        if ($pollMaxDays <= 0) {
            return 0;
        }

        $cutoff = TIMENOW - 86400 * $pollMaxDays;
        $perCycle = 20000;

        $row = DB()->fetch_row('SELECT MIN(topic_id) AS start_id, MAX(topic_id) AS finish_id FROM ' . BB_POLL_USERS);
        $startId = (int)($row['start_id'] ?? 0);
        $finishId = (int)($row['finish_id'] ?? 0);

        if ($startId === 0) {
            return 0;
        }

        $totalDeleted = 0;

        while (true) {
            $endId = $startId + $perCycle - 1;

            DB()->query('
                DELETE FROM ' . BB_POLL_USERS . "
                WHERE topic_id BETWEEN {$startId} AND {$endId}
                    AND vote_dt < {$cutoff}
            ");

            $totalDeleted += DB()->affected_rows();

            if ($endId >= $finishId) {
                break;
            }

            $startId += $perCycle;
        }

        return $totalDeleted;
    }

    /**
     * Clean expired password reset requests
     */
    private function cleanPasswordRequests(): int
    {
        $cutoff = TIMENOW - 7 * 86400;

        DB()->query('UPDATE ' . BB_USERS . " SET user_newpasswd = '' WHERE user_newpasswd <> '' AND user_lastvisit < {$cutoff}");

        return DB()->affected_rows();
    }

    /**
     * Clean post HTML cache
     */
    private function cleanPostsCache(bool $clearAll = false): int
    {
        if ($clearAll) {
            // Get count before truncating (TRUNCATE doesn't return affected_rows)
            $count = DB()->table(BB_POSTS_HTML)->count('*');
            DB()->query('TRUNCATE TABLE ' . BB_POSTS_HTML);

            return $count;
        }

        $postsCacheDays = (int)config()->get('posts_cache_days_keep');
        if ($postsCacheDays <= 0) {
            return 0;
        }

        DB()->query('DELETE FROM ' . BB_POSTS_HTML . " WHERE post_html_time < DATE_SUB(NOW(), INTERVAL {$postsCacheDays} DAY)");

        return DB()->affected_rows();
    }
}
