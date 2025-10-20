<?php
/**
 * TorrentPier – Bull-powered BitTorrent tracker engine
 *
 * @copyright Copyright (c) 2005-2025 TorrentPier (https://torrentpier.com)
 * @link      https://github.com/torrentpier/torrentpier for the canonical source repository
 * @license   https://github.com/torrentpier/torrentpier/blob/master/LICENSE MIT License
 */

declare(strict_types=1);

namespace TorrentPier\Feed\Provider;

use DateTime;
use Exception;
use TorrentPier\Feed\Model\FeedEntry;

/**
 * Common logic for mapping database topics to feed entries
 */
trait FeedEntryMapperTrait
{
    /**
     * Map database topics to feed entries
     *
     * @param array $topics
     * @return FeedEntry[]
     * @throws Exception
     */
    private function mapTopicsToEntries(array $topics): array
    {
        global $lang;

        $entries = [];

        foreach ($topics as $topic) {
            // Skip moved topics
            if (isset($topic['topic_status']) && $topic['topic_status'] === TOPIC_MOVED) {
                continue;
            }

            // Skip frozen torrents
            if (isset($topic['tor_status']) && isset(config()->get('tor_frozen')[$topic['tor_status']])) {
                continue;
            }

            // Build title with torrent info
            $torSize = '';
            if (isset($topic['tor_size'])) {
                $torSize = ' [' . humn_size($topic['tor_size']) . ']';
            }

            $torStatus = '';
            if (isset($topic['tor_status'], $lang['TOR_STATUS_NAME'][$topic['tor_status']])) {
                $torStatus = " ({$lang['TOR_STATUS_NAME'][$topic['tor_status']]})";
            }

            $title = censor()->censorString($topic['topic_title']) . $torStatus . $torSize;

            // Determine last modification time
            $lastTime = $topic['topic_last_post_edit_time'] ?: $topic['topic_last_post_time'];

            // Determine a link (direct download or topic view)
            $link = config()->get('atom.direct_down') && isset($topic['attach_id'])
                ? DL_URL . $topic['attach_id']
                : TOPIC_URL . $topic['topic_id'];

            // Prepare description if enabled
            $description = null;
            if (config()->get('atom.direct_view') && isset($topic['post_html'])) {
                $description = $topic['post_html'] . "\n\nTopic: " . FULL_URL . TOPIC_URL . $topic['topic_id'];
            }

            // Check for updated marker
            $updated = '';
            $checktime = TIMENOW - 604800; // 1 week
            if (isset($topic['topic_first_post_edit_time']) && $topic['topic_first_post_edit_time'] > $checktime) {
                $updated = '[' . $lang['ATOM_UPDATED'] . '] ';
            }

            $entries[] = new FeedEntry(
                title: $updated . $title,
                link: $link,
                lastModified: new DateTime('@' . $lastTime),
                author: $topic['first_username'] ?: $lang['GUEST'],
                description: $description
            );
        }

        return $entries;
    }
}
