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

use DateTimeImmutable;
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
        $entries = [];

        foreach ($topics as $topic) {
            // Skip moved topics
            if (isset($topic['topic_status']) && $topic['topic_status'] === TOPIC_MOVED) {
                continue;
            }

            // Skip frozen torrents
            if ($this->isFrozenTorrent($topic)) {
                continue;
            }

            $title = $this->getUpdatedPrefix($topic) . $this->buildTorrentTitle($topic);
            $lastTime = $topic['topic_last_post_edit_time'] ?: $topic['topic_last_post_time'];

            $entries[] = new FeedEntry(
                title: $title,
                link: $this->buildEntryLink($topic),
                lastModified: new DateTimeImmutable('@' . $lastTime),
                author: $topic['first_username'] ?: __('GUEST'),
                description: $this->buildEntryDescription($topic)
            );
        }

        return $entries;
    }

    /**
     * Check if the torrent is frozen and should be excluded
     *
     * @param array $topic
     * @return bool
     */
    private function isFrozenTorrent(array $topic): bool
    {
        if (!isset($topic['tor_status'])) {
            return false;
        }

        $torFrozen = config()->get('tor_frozen');
        return is_array($torFrozen) && isset($torFrozen[$topic['tor_status']]);
    }

    /**
     * Build torrent title with status and size info
     *
     * @param array $topic
     * @return string
     */
    private function buildTorrentTitle(array $topic): string
    {
        $title = censor()->censorString($topic['topic_title']);

        // Add torrent status if available
        if (isset($topic['tor_status'])) {
            $statusName = __('TOR_STATUS_NAME.' . $topic['tor_status']);
            // Only add status if translation exists (not just the key)
            if ($statusName !== 'TOR_STATUS_NAME.' . $topic['tor_status']) {
                $title .= ' (' . $statusName . ')';
            }
        }

        // Add torrent size if available
        if (isset($topic['tor_size'])) {
            $title .= ' [' . humn_size($topic['tor_size']) . ']';
        }

        return $title;
    }

    /**
     * Build entry link (direct download or topic view)
     *
     * @param array $topic
     * @return string
     */
    private function buildEntryLink(array $topic): string
    {
        // Direct download link if enabled and attachment exists
        if (config()->get('atom.direct_down') && isset($topic['attach_id'])) {
            return DL_URL . $topic['attach_id'];
        }

        // Default to topic view
        return TOPIC_URL . $topic['topic_id'];
    }

    /**
     * Build entry description if enabled
     *
     * @param array $topic
     * @return string|null
     */
    private function buildEntryDescription(array $topic): ?string
    {
        if (!config()->get('atom.direct_view') || !isset($topic['post_html'])) {
            return null;
        }

        return $topic['post_html'] . "\n\nTopic: " . FULL_URL . TOPIC_URL . $topic['topic_id'];
    }

    /**
     * Get [UPDATED] prefix for recently edited topics
     *
     * @param array $topic
     * @return string
     */
    private function getUpdatedPrefix(array $topic): string
    {
        if (!isset($topic['topic_first_post_edit_time'])) {
            return '';
        }

        $window = (int)(config()->get('atom.updated_window') ?? 604800); // default: 1 week

        if ($topic['topic_first_post_edit_time'] > TIMENOW - $window) {
            return '[' . __('ATOM_UPDATED') . '] ';
        }

        return '';
    }
}
