<?php
/**
 * TorrentPier – Bull-powered BitTorrent tracker engine
 *
 * @copyright Copyright (c) 2005-2025 TorrentPier (https://torrentpier.com)
 * @link      https://github.com/torrentpier/torrentpier for the canonical source repository
 * @license   https://github.com/torrentpier/torrentpier/blob/master/LICENSE MIT License
 */

namespace TorrentPier\Topic;

/**
 * Topic access and authorization checks.
 */
class Guard
{
    /**
     * Check if the current user is the topic author.
     *
     * @param int $posterId Topic poster user ID
     * @return bool
     */
    public static function isAuthor(int $posterId): bool
    {
        if (IS_GUEST) {
            return false;
        }

        return userdata('user_id') == $posterId;
    }
}
