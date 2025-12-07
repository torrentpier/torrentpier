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
 * Torrent status moderation.
 */
class Moderation
{
    use HelperTrait;

    /**
     * Change torrent status.
     *
     * @param int $topicId Topic ID
     * @param int $newStatus New torrent status
     */
    public static function changeStatus(int $topicId, int $newStatus): void
    {
        $torrent = self::getTorrentInfo($topicId);
        self::checkAuth($torrent['forum_id'], $torrent['topic_poster']);

        DB()->table(BB_BT_TORRENTS)
            ->where('topic_id', $topicId)
            ->update([
                'tor_status' => $newStatus,
                'checked_user_id' => userdata('user_id'),
                'checked_time' => TIMENOW,
            ]);
    }

    /**
     * Change torrent type (freeleech/gold/silver).
     *
     * @param int $topicId Topic ID
     * @param int $torType New torrent type
     */
    public static function changeType(int $topicId, int $torType): void
    {
        self::getTorrentInfo($topicId); // validates topic exists

        if (!IS_AM) {
            bb_die(__('ONLY_FOR_MOD'));
        }

        DB()->table(BB_BT_TORRENTS)
            ->where('topic_id', $topicId)
            ->update(['tor_type' => $torType]);
    }
}
