<?php

/**
 * TorrentPier – Bull-powered BitTorrent tracker engine
 *
 * @copyright Copyright (c) 2005-2025 TorrentPier (https://torrentpier.com)
 * @link      https://github.com/torrentpier/torrentpier for the canonical source repository
 * @license   https://github.com/torrentpier/torrentpier/blob/master/LICENSE MIT License
 */

namespace TorrentPier;

use RuntimeException;

/**
 * Tracks read status of topics and forums via cookies.
 * Replaces global $tracking_topics and $tracking_forums.
 */
class ReadTracker
{
    private ?array $topics = null;
    private ?array $forums = null;

    public function __construct() {}

    /**
     * Get topic tracking data (lazy-loaded)
     */
    public function &getTopics(): array
    {
        if ($this->topics === null) {
            $this->topics = $this->loadTracks('topic');
        }

        return $this->topics;
    }

    /**
     * Get forum tracking data (lazy-loaded)
     */
    public function &getForums(): array
    {
        if ($this->forums === null) {
            $this->forums = $this->loadTracks('forum');
        }

        return $this->forums;
    }

    /**
     * Set track for a topic or forum
     *
     * @param string $type 'topic' or 'forum'
     * @param int|array|null $tracks ID or array of IDs to track
     * @param int|null $val Timestamp value
     */
    public function setTrack(string $type, int|array|null $tracks = null, ?int $val = null): void
    {
        if (IS_GUEST) {
            return;
        }

        $val ??= TIMENOW;
        $cookieName = $type === 'topic' ? COOKIE_TOPIC : COOKIE_FORUM;
        if ($type === 'topic') {
            $trackingAry = &$this->getTopics();
        } else {
            $trackingAry = &$this->getForums();
        }

        if ($tracks !== null) {
            if (!\is_array($tracks)) {
                $tracks = [$tracks => $val];
            }
            foreach ($tracks as $key => $value) {
                if ($value > user()->data['user_lastvisit']) {
                    $trackingAry[$key] = $value;
                } elseif (isset($trackingAry[$key])) {
                    unset($trackingAry[$key]);
                }
            }
        }

        $this->handleOverflow($trackingAry);

        if ($trackingAry) {
            bb_setcookie($cookieName, json_encode($trackingAry));
        }
    }

    /**
     * Get last read time for a topic/forum
     */
    public function getLastRead(int $topicId = 0, int $forumId = 0): int
    {
        $topics = $this->getTopics();
        $forums = $this->getForums();

        $t = $topics[$topicId] ?? 0;
        $f = $forums[$forumId] ?? 0;

        return max($t, $f, user()->data['user_lastvisit']);
    }

    /**
     * Check if content is unread
     */
    public function isUnread(int $ref, int $topicId = 0, int $forumId = 0): bool
    {
        return !IS_GUEST && $ref > $this->getLastRead($topicId, $forumId);
    }

    /**
     * Get tracking data by type (uses cached methods for topic/forum)
     */
    public function getTracks(string $type): array
    {
        return match ($type) {
            'topic' => $this->getTopics(),
            'forum' => $this->getForums(),
            default => $this->loadTracks($type),
        };
    }

    /**
     * Handle overflow when too many tracks are stored
     */
    private function handleOverflow(array &$trackingAry): void
    {
        $topics = $this->getTopics();
        $forums = $this->getForums();

        $overflow = \count($topics) + \count($forums) - COOKIE_MAX_TRACKS;

        if ($overflow > 0) {
            arsort($trackingAry);
            for ($i = 0; $i < $overflow; $i++) {
                array_pop($trackingAry);
            }
        }
    }

    /**
     * Load tracking data from a cookie
     */
    private function loadTracks(string $type): array
    {
        $cookieName = match ($type) {
            'topic' => COOKIE_TOPIC,
            'forum' => COOKIE_FORUM,
            'pm' => COOKIE_PM,
            default => throw new RuntimeException("ReadTracker::loadTracks(): invalid type '{$type}'"),
        };

        $tracks = !empty($_COOKIE[$cookieName]) ? json_decode($_COOKIE[$cookieName], true) : false;

        return $tracks ?: [];
    }
}
