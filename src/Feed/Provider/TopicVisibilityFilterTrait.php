<?php

/**
 * TorrentPier – Bull-powered BitTorrent tracker engine
 *
 * @copyright Copyright (c) 2005-2026 TorrentPier (https://torrentpier.com)
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
     * Filter topics from forums the current viewer cannot see
     */
    private function filterForbiddenTopics(array $topics): array
    {
        $notForumsId = user()->get_excluded_forums(AUTH_VIEW, 'array');

        if (empty($notForumsId)) {
            return $topics;
        }

        return array_filter($topics, function ($topic) use ($notForumsId) {
            $forumId = isset($topic['forum_id']) ? (int)$topic['forum_id'] : 0;

            return !\in_array($forumId, $notForumsId);
        });
    }
}
