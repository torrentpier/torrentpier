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
use Override;
use TorrentPier\Feed\Model\FeedMetadata;

/**
 * User feed provider
 * Generates Atom feeds for user's topics
 */
class UserFeedProvider implements FeedProviderInterface
{
    use FeedEntryMapperTrait;
    use TopicVisibilityFilterTrait;

    /**
     * @param int $userId User ID
     * @param string $username Username
     */
    public function __construct(
        private readonly int $userId,
        private readonly string $username,
    ) {}

    /**
     * @inheritDoc
     */
    #[Override]
    public function getCacheKey(): string
    {
        return 'atom:user:' . $this->userId;
    }

    /**
     * @inheritDoc
     */
    #[Override]
    public function getMetadata(): FeedMetadata
    {
        return new FeedMetadata(
            title: $this->username,
            link: make_url(url()->member($this->userId, $this->username)),
            lastModified: new DateTimeImmutable,
        );
    }

    /**
     * @inheritDoc
     * @throws Exception
     */
    #[Override]
    public function getEntries(): array
    {
        $topics = $this->getUserTopics();

        // Filter topics from forbidden forums (for guests)
        $topics = $this->filterForbiddenTopics($topics);

        return $this->mapTopicsToEntries($topics);
    }

    /**
     * Get topics created by a user
     */
    private function getUserTopics(): array
    {
        $sql = '
            SELECT
                t.topic_id, t.topic_title, t.topic_status, t.tracker_status, t.forum_id,
                u1.username AS first_username,
                p1.post_time AS topic_first_post_time,
                p1.post_edit_time AS topic_first_post_edit_time,
                p1.post_anonymous AS first_post_anonymous,
                p2.post_time AS topic_last_post_time,
                p2.post_edit_time AS topic_last_post_edit_time,
                tor.size AS tor_size, tor.tor_status,
                pt.post_html
            FROM ' . BB_TOPICS . ' t
            LEFT JOIN ' . BB_USERS . ' u1 ON(t.topic_poster = u1.user_id)
            LEFT JOIN ' . BB_POSTS . ' p1 ON(t.topic_first_post_id = p1.post_id)
            LEFT JOIN ' . BB_POSTS . ' p2 ON(t.topic_last_post_id = p2.post_id)
            LEFT JOIN ' . BB_POSTS_HTML . ' pt ON(p1.post_id = pt.post_id)
            LEFT JOIN ' . BB_BT_TORRENTS . " tor ON(t.topic_id = tor.topic_id)
            WHERE t.topic_poster = {$this->userId}
            ORDER BY t.topic_last_post_time DESC
            LIMIT 50
        ";

        return DB()->fetch_rowset($sql);
    }
}
