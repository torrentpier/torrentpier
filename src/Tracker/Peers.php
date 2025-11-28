<?php
/**
 * TorrentPier – Bull-powered BitTorrent tracker engine
 *
 * @copyright Copyright (c) 2005-2025 TorrentPier (https://torrentpier.com)
 * @link      https://github.com/torrentpier/torrentpier for the canonical source repository
 * @license   https://github.com/torrentpier/torrentpier/blob/master/LICENSE MIT License
 */

namespace TorrentPier\Tracker;

/**
 * Tracker peer records management.
 */
class Peers
{
    /**
     * Remove all peer records for a user from the tracker.
     *
     * @param int $userId User ID
     * @return int Number of deleted rows
     */
    public static function removeByUser(int $userId): int
    {
        return DB()->table(BB_BT_TRACKER)->where('user_id', $userId)->delete();
    }
}
