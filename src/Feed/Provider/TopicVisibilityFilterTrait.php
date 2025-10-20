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

/**
 * Common logic for filtering topics by forum visibility permissions
 */
trait TopicVisibilityFilterTrait
{
    /**
     * Filter topics from forbidden forums (for guests)
     *
     * @param array $topics
     * @return array
     */
    private function filterForbiddenTopics(array $topics): array
    {
        global $datastore;

        // Get forbidden forums for guests
        if (!$forums = $datastore->get('cat_forums')) {
            $datastore->update('cat_forums');
            $forums = $datastore->get('cat_forums');
        }
        $notForumsId = explode(',', $forums['not_auth_forums']['guest_view'] ?? '');

        // Filter out topics from forbidden forums using forum_id
        return array_filter($topics, function ($topic) use ($notForumsId) {
            return !in_array($topic['forum_id'], $notForumsId, true);
        });
    }
}
