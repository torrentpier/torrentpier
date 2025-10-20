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
use TorrentPier\Feed\Model\FeedMetadata;

/**
 * User feed provider
 * Generates Atom feeds for user's topics
 */
class UserFeedProvider implements FeedProviderInterface
{
    use FeedEntryMapperTrait;

    private int $userId;
    private string $username;

    /**
     * @param int $userId User ID
     * @param string $username Username
     */
    public function __construct(int $userId, string $username)
    {
        $this->userId = $userId;
        $this->username = $username;
    }

    /**
     * @inheritDoc
     */
    public function getCacheKey(): string
    {
        return 'atom:user:' . $this->userId;
    }

    /**
     * @inheritDoc
     */
    public function getMetadata(): FeedMetadata
    {
        return new FeedMetadata(
            title: $this->username,
            link: FULL_URL,
            lastModified: new DateTime()
        );
    }

    /**
     * @inheritDoc
     * @throws Exception
     */
    public function getEntries(): array
    {
        $topics = $this->getUserTopics();
        return $this->mapTopicsToEntries($topics);
    }

    /**
     * Get topics created by a user
     *
     * @return array
     */
    private function getUserTopics(): array
    {
        $sql = "
            SELECT
                t.topic_id, t.topic_title, t.topic_status,
                u1.username AS first_username,
                p1.post_time AS topic_first_post_time,
                p1.post_edit_time AS topic_first_post_edit_time,
                p2.post_time AS topic_last_post_time,
                p2.post_edit_time AS topic_last_post_edit_time,
                tor.size AS tor_size, tor.tor_status, tor.attach_id,
                pt.post_html
            FROM " . BB_TOPICS . " t
            LEFT JOIN " . BB_USERS . " u1 ON(t.topic_poster = u1.user_id)
            LEFT JOIN " . BB_POSTS . " p1 ON(t.topic_first_post_id = p1.post_id)
            LEFT JOIN " . BB_POSTS . " p2 ON(t.topic_last_post_id = p2.post_id)
            LEFT JOIN " . BB_POSTS_HTML . " pt ON(p1.post_id = pt.post_id)
            LEFT JOIN " . BB_BT_TORRENTS . " tor ON(t.topic_id = tor.topic_id)
            WHERE t.topic_poster = {$this->userId}
            ORDER BY t.topic_last_post_time DESC
            LIMIT 50
        ";

        return DB()->fetch_rowset($sql);
    }
}
