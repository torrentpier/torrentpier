<?php

/**
 * TorrentPier – Bull-powered BitTorrent tracker engine
 *
 * @copyright Copyright (c) 2005-2025 TorrentPier (https://torrentpier.com)
 * @link      https://github.com/torrentpier/torrentpier for the canonical source repository
 * @license   https://github.com/torrentpier/torrentpier/blob/master/LICENSE MIT License
 */

declare(strict_types=1);

namespace TorrentPier\Router\SemanticUrl\Traits;

/**
 * URL generators for entities with slug.id pattern
 *
 * Handles: topics, forums, members, groups
 */
trait EntityUrls
{
    /**
     * Generate a topic URL
     *
     * @param int|null $id Topic ID
     * @param string $title Topic title (will be slugified)
     * @param array $params Additional query parameters (e.g., ['start' => 20])
     * @return string Full URL path
     */
    public static function topic(?int $id, string $title = '', array $params = []): string
    {
        if ($id === null || $id <= 0) {
            return '#';
        }
        return self::buildUrl('threads', $id, $title, $params);
    }

    /**
     * Generate a topic URL with anchor to a specific post
     *
     * @param int $topicId Topic ID
     * @param string $title Topic title (will be slugified)
     * @param int $postId Post ID to anchor to
     * @return string Full URL path with #post_id anchor
     */
    public static function topicPost(int $topicId, string $title, int $postId): string
    {
        return self::topic($topicId, $title, ['_fragment' => (string) $postId]);
    }

    /**
     * Generate a topic URL for viewing the newest posts
     *
     * @param int $topicId Topic ID
     * @param string $title Topic title (will be slugified)
     * @return string Full URL path with view=newest#newest
     */
    public static function topicNewest(int $topicId, string $title): string
    {
        return self::topic($topicId, $title, ['view' => 'newest', '_fragment' => 'newest']);
    }

    /**
     * Generate a forum URL
     *
     * @param int|null $id Forum ID
     * @param string $name Forum name (will be slugified)
     * @param array $params Additional query parameters
     * @return string Full URL path
     */
    public static function forum(?int $id, string $name = '', array $params = []): string
    {
        if ($id === null || $id <= 0) {
            return '#';
        }
        return self::buildUrl('forum', $id, $name, $params);
    }

    /**
     * Generate a member profile URL
     *
     * @param int|null $id User ID
     * @param string $username Username (will be slugified)
     * @param array $params Additional query parameters
     * @return string Full URL path
     */
    public static function member(?int $id, string $username = '', array $params = []): string
    {
        if ($id === null || $id <= 0) {
            return '#';
        }
        return self::buildUrl('members', $id, $username, $params);
    }

    /**
     * Generate a member email URL (/members/slug.id/email/)
     */
    public static function memberEmail(int $id, string $username = ''): string
    {
        return rtrim(self::member($id, $username), '/') . '/email/';
    }

    /**
     * Generate a group URL
     *
     * @param int|null $id Group ID
     * @param string $name Group name (will be slugified)
     * @param array $params Additional query parameters
     * @return string Full URL path
     */
    public static function group(?int $id, string $name = '', array $params = []): string
    {
        if ($id === null || $id <= 0) {
            return '#';
        }
        return self::buildUrl('groups', $id, $name, $params);
    }

    /**
     * Generate a group edit URL (/groups/slug.id/edit/)
     */
    public static function groupEdit(int $id, string $name = '', array $params = []): string
    {
        $url = rtrim(self::group($id, $name), '/') . '/edit/';
        return self::appendParams($url, $params);
    }

    /**
     * Generate a category URL
     *
     * @param int|null $id Category ID
     * @param string $name Category name (will be slugified)
     * @param array $params Additional query parameters
     * @return string Full URL path
     */
    public static function category(?int $id, string $name = '', array $params = []): string
    {
        if ($id === null || $id <= 0) {
            return '/';
        }
        return self::buildUrl('category', $id, $name, $params);
    }
}
