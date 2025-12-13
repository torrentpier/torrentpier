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
     */
    private function filterForbiddenTopics(array $topics): array
    {
        // Get forbidden forums for guests
        $forums = forum_tree();

        // Get guest_view string, default to empty
        $guestView = $forums['not_auth_forums']['guest_view'] ?? '';

        // Explode and cast to an int array (empty string produces an empty array)
        $notForumsId = $guestView !== ''
            ? array_map('intval', explode(',', $guestView))
            : [];

        // If no forbidden forums, return all topics
        if (empty($notForumsId)) {
            return $topics;
        }

        // Filter out topics from forbidden forums
        return array_filter($topics, function ($topic) use ($notForumsId) {
            // Get forum_id as int, default to 0 if missing
            $forumId = isset($topic['forum_id']) ? (int)$topic['forum_id'] : 0;

            // Keep a topic if its forum is NOT in a forbidden list (strict comparison)
            return !\in_array($forumId, $notForumsId, true);
        });
    }
}
