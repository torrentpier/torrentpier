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
use TorrentPier\Feed\Exception\FeedGenerationException;
use TorrentPier\Feed\Model\FeedMetadata;

/**
 * Forum feed provider
 * Generates Atom feeds for forum topics
 */
class ForumFeedProvider implements FeedProviderInterface
{
    use FeedEntryMapperTrait;
    use TopicVisibilityFilterTrait;

    private ?string $forumName;
    private ?array $forumData;

    /**
     * @param int $forumId Forum ID (0 for global feed)
     * @param string|null $forumName Forum name (will be loaded if not provided)
     */
    public function __construct(
        private readonly int $forumId,
        ?string $forumName = null
    ) {
        $this->forumName = $forumName;
        $this->forumData = null;

        if ($forumName === null && $forumId > 0) {
            $this->loadForumData();
        }
    }

    /**
     * Load forum data from database
     *
     * @throws FeedGenerationException
     */
    private function loadForumData(): void
    {
        $sql = 'SELECT forum_name, allow_reg_tracker FROM ' . BB_FORUMS . ' WHERE forum_id = ' . $this->forumId . ' LIMIT 1';
        $result = DB()->fetch_row($sql);

        if (!$result) {
            throw new FeedGenerationException('Forum not found: ' . $this->forumId);
        }

        $this->forumName = $result['forum_name'];
        $this->forumData = $result;
    }

    /**
     * @inheritDoc
     */
    #[\Override]
    public function getCacheKey(): string
    {
        return 'atom:forum:' . $this->forumId;
    }

    /**
     * @inheritDoc
     */
    #[\Override]
    public function getMetadata(): FeedMetadata
    {
        $title = $this->forumId === 0
            ? (__('ATOM_GLOBAL_FEED') ?: config()->get('server_name'))
            : htmlCHR($this->forumName);

        return new FeedMetadata(
            title: $title,
            link: FULL_URL,
            lastModified: new DateTimeImmutable()
        );
    }

    /**
     * @inheritDoc
     * @throws Exception
     */
    #[\Override]
    public function getEntries(): array
    {
        $topics = $this->forumId === 0
            ? $this->getGlobalTopics()
            : $this->getForumTopics();

        // Filter topics from forbidden forums (for guests)
        $topics = $this->filterForbiddenTopics($topics);

        return $this->mapTopicsToEntries($topics);
    }

    /**
     * Get topics for global feed (all torrents)
     *
     * @return array
     */
    private function getGlobalTopics(): array
    {
        $sql = "
            SELECT
                t.topic_id, t.topic_title, t.topic_status, t.tracker_status, t.forum_id,
                u1.username AS first_username,
                p1.post_time AS topic_first_post_time,
                p1.post_edit_time AS topic_first_post_edit_time,
                p2.post_time AS topic_last_post_time,
                p2.post_edit_time AS topic_last_post_edit_time,
                tor.size AS tor_size, tor.tor_status,
                pt.post_html
            FROM " . BB_BT_TORRENTS . " tor
            LEFT JOIN " . BB_TOPICS . " t ON(tor.topic_id = t.topic_id)
            LEFT JOIN " . BB_USERS . " u1 ON(t.topic_poster = u1.user_id)
            LEFT JOIN " . BB_POSTS . " p1 ON(t.topic_first_post_id = p1.post_id)
            LEFT JOIN " . BB_POSTS . " p2 ON(t.topic_last_post_id = p2.post_id)
            LEFT JOIN " . BB_POSTS_HTML . " pt ON(p1.post_id = pt.post_id)
            ORDER BY t.topic_last_post_time DESC
            LIMIT 100
        ";

        return DB()->fetch_rowset($sql);
    }

    /**
     * Get topics for a specific forum
     *
     * @return array
     */
    private function getForumTopics(): array
    {
        // Check if the forum allows tracker registration (has torrents)
        $selectTorSql = '';
        $joinTorSql = '';

        if ($this->forumData && $this->forumData['allow_reg_tracker']) {
            $selectTorSql = ', tor.size AS tor_size, tor.tor_status';
            $joinTorSql = "LEFT JOIN " . BB_BT_TORRENTS . " tor ON(t.topic_id = tor.topic_id)";
        }

        $sql = "
            SELECT
                t.topic_id, t.topic_title, t.topic_status, t.tracker_status, t.forum_id,
                u1.username AS first_username,
                p1.post_time AS topic_first_post_time,
                p1.post_edit_time AS topic_first_post_edit_time,
                p2.post_time AS topic_last_post_time,
                p2.post_edit_time AS topic_last_post_edit_time,
                pt.post_html
                $selectTorSql
            FROM " . BB_TOPICS . " t
            LEFT JOIN " . BB_USERS . " u1 ON(t.topic_poster = u1.user_id)
            LEFT JOIN " . BB_POSTS . " p1 ON(t.topic_first_post_id = p1.post_id)
            LEFT JOIN " . BB_POSTS . " p2 ON(t.topic_last_post_id = p2.post_id)
            LEFT JOIN " . BB_POSTS_HTML . " pt ON(p1.post_id = pt.post_id)
            $joinTorSql
            WHERE t.forum_id = {$this->forumId}
            ORDER BY t.topic_last_post_time DESC
            LIMIT 50
        ";

        return DB()->fetch_rowset($sql);
    }
}
