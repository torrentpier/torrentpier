<?php

/**
 * TorrentPier – Bull-powered BitTorrent tracker engine
 *
 * @copyright Copyright (c) 2005-2025 TorrentPier (https://torrentpier.com)
 * @link      https://github.com/torrentpier/torrentpier for the canonical source repository
 * @license   https://github.com/torrentpier/torrentpier/blob/master/LICENSE MIT License
 */

declare(strict_types=1);

namespace TorrentPier\Router\SemanticUrl;

/**
 * Centralized configuration for semantic URL entity types
 */
class EntityConfig
{
    /**
     * Entity type configurations
     *
     * Each type defines:
     * - controller: PHP controller filename in app/Http/Controllers/
     * - script: BB_SCRIPT value for the controller
     * - param: Query parameter name (POST_*_URL constant equivalent)
     * - table: Database table name
     * - id_col: Primary key column name
     * - title_col: Title/name column for slug generation
     * - url_method: UrlBuilder static method name
     * - extra: Additional query parameters to set (optional)
     */
    private const array ENTITIES = [
        'threads' => [
            'controller' => 'viewtopic.php',
            'script' => 'topic',
            'param' => 't',
            'table' => 'bb_topics',
            'id_col' => 'topic_id',
            'title_col' => 'topic_title',
            'url_method' => 'topic',
        ],
        'forums' => [
            'controller' => 'viewforum.php',
            'script' => 'forum',
            'param' => 'f',
            'table' => 'bb_forums',
            'id_col' => 'forum_id',
            'title_col' => 'forum_name',
            'url_method' => 'forum',
        ],
        'members' => [
            'controller' => 'profile.php',
            'script' => 'profile',
            'param' => 'u',
            'table' => 'bb_users',
            'id_col' => 'user_id',
            'title_col' => 'username',
            'url_method' => 'member',
            'extra' => ['mode' => 'viewprofile'],
        ],
        'groups' => [
            'controller' => 'group.php',
            'script' => 'group',
            'param' => 'g',
            'table' => 'bb_groups',
            'id_col' => 'group_id',
            'title_col' => 'group_name',
            'url_method' => 'group',
        ],
        'groups_edit' => [
            'controller' => 'group_edit.php',
            'script' => 'group_edit',
            'param' => 'g',
            'table' => 'bb_groups',
            'id_col' => 'group_id',
            'title_col' => 'group_name',
            'url_method' => 'groupEdit',
        ],
        'categories' => [
            'controller' => 'index.php',
            'script' => 'index',
            'param' => 'c',
            'table' => 'bb_categories',
            'id_col' => 'cat_id',
            'title_col' => 'cat_title',
            'url_method' => 'category',
        ],
    ];

    /**
     * Get configuration for an entity type
     *
     * @param string $type Entity type (threads, forums, members, groups, groups_edit, categories)
     * @return array|null Configuration array or null if type doesn't exist
     */
    public static function get(string $type): ?array
    {
        return self::ENTITIES[$type] ?? null;
    }

    /**
     * Check if an entity type exists
     */
    public static function exists(string $type): bool
    {
        return isset(self::ENTITIES[$type]);
    }

    /**
     * Get all entity types
     *
     * @return string[]
     */
    public static function types(): array
    {
        return array_keys(self::ENTITIES);
    }

    /**
     * Fetch title/name from a database for an entity
     *
     * @param string $type Entity type
     * @param int $id Entity ID
     * @return string|null Title or null if the entity doesn't exist
     */
    public static function fetchTitle(string $type, int $id): ?string
    {
        $config = self::get($type);
        if ($config === null) {
            return null;
        }

        $row = DB()->table($config['table'])->get($id);

        return $row ? ($row->{$config['title_col']} ?? '') : null;
    }

    /**
     * Build semantic URL for an entity
     *
     * @param string $type Entity type
     * @param int $id Entity ID
     * @param string $title Title/name for slug generation
     * @param array $params Additional query parameters
     * @return string URL path
     */
    public static function buildUrl(string $type, int $id, string $title, array $params = []): string
    {
        $config = self::get($type);
        if ($config === null) {
            return '/';
        }

        $method = $config['url_method'];

        return UrlBuilder::$method($id, $title, $params);
    }

    /**
     * Get the controller path for an entity type
     *
     * @param string $type Entity type
     * @return string|null Full controller path or null if the type doesn't exist
     */
    public static function getControllerPath(string $type): ?string
    {
        $config = self::get($type);
        if ($config === null) {
            return null;
        }

        $basePath = dirname(__DIR__, 3);
        return $basePath . '/app/Http/Controllers/' . $config['controller'];
    }

    /**
     * Get extra parameters to set for an entity type (e.g., mode=viewprofile for members)
     *
     * @param string $type Entity type
     * @return array Extra parameters or empty array
     */
    public static function getExtraParams(string $type): array
    {
        $config = self::get($type);
        return $config['extra'] ?? [];
    }
}
