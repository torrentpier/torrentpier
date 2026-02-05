<?php

/**
 * TorrentPier – Bull-powered BitTorrent tracker engine
 *
 * @copyright Copyright (c) 2005-2025 TorrentPier (https://torrentpier.com)
 * @link      https://github.com/torrentpier/torrentpier for the canonical source repository
 * @license   https://github.com/torrentpier/torrentpier/blob/master/LICENSE MIT License
 */

namespace TorrentPier\Torrent;

/**
 * Counts unique torrent file downloads with daily limits.
 *
 * Tracks unique downloads per user and enforces daily download limits.
 * Data is aggregated daily by cron job and stored in the topic table.
 */
class DownloadCounter
{
    /**
     * Daily download limit for regular users
     */
    private int $dailyLimit;

    /**
     * Daily download limit for premium users
     */
    private int $dailyLimitPremium;

    public function __construct()
    {
        $config = config()->get('tracker.torrent_dl');
        $this->dailyLimit = $config['daily_limit'] ?? 50;
        $this->dailyLimitPremium = $config['daily_limit_premium'] ?? 100;
    }

    /**
     * Record a download attempt and check if allowed.
     *
     * @param int $topicId Topic ID of the torrent
     * @param int $userId User ID attempting download
     * @param bool $isPremium Whether a user has premium status
     * @return bool True if download is allowed, false if limit exceeded
     */
    public function recordDownload(int $topicId, int $userId, bool $isPremium = false): bool
    {
        // Guests always allowed (no tracking)
        if ($userId <= 0) {
            return true;
        }

        $limit = $isPremium ? $this->dailyLimitPremium : $this->dailyLimit;
        $dailyCount = $this->getDailyCount($userId);

        // Check if limit exceeded
        if ($dailyCount >= $limit) {
            // Allow re-download of previously downloaded torrents
            return (bool)($this->hasDownloaded($topicId, $userId));
        }

        // Record download (INSERT IGNORE for unique constraint)
        $sql = 'INSERT IGNORE INTO ' . BB_TORRENT_DL . " (topic_id, user_id) VALUES ({$topicId}, {$userId})";
        DB()->query($sql);

        // If a new record was inserted, increment daily counter
        if (DB()->affected_rows() > 0) {
            $this->incrementDailyCount($userId);
        }

        return true;
    }

    /**
     * Get a user's download count for today.
     *
     * @param int $userId User ID
     * @return int Number of downloads today
     */
    public function getDailyCount(int $userId): int
    {
        $row = DB()->table(BB_USER_DL_DAY)
            ->where('user_id', $userId)
            ->fetch();

        return (int)($row?->cnt ?? 0);
    }

    /**
     * Check if a user has already downloaded this torrent.
     *
     * @param int $topicId Topic ID
     * @param int $userId User ID
     * @return bool True if already downloaded
     */
    public function hasDownloaded(int $topicId, int $userId): bool
    {
        return (bool)DB()->table(BB_TORRENT_DL)
            ->where('topic_id', $topicId)
            ->where('user_id', $userId)
            ->fetch();
    }

    /**
     * Increment user's daily download counter.
     *
     * @param int $userId User ID
     */
    private function incrementDailyCount(int $userId): void
    {
        $sql = 'INSERT INTO ' . BB_USER_DL_DAY . " (user_id, cnt) VALUES ({$userId}, 1)
                ON DUPLICATE KEY UPDATE cnt = cnt + 1";
        DB()->query($sql);
    }
}
