<?php

/**
 * TorrentPier – Bull-powered BitTorrent tracker engine
 *
 * @copyright Copyright (c) 2005-2025 TorrentPier (https://torrentpier.com)
 * @link      https://github.com/torrentpier/torrentpier for the canonical source repository
 * @license   https://github.com/torrentpier/torrentpier/blob/master/LICENSE MIT License
 */

namespace TorrentPier\Admin;

/**
 * Admin navigation tree builder with DB-backed storage and caching
 */
class AdminNavigation
{
    private const CACHE_KEY = 'admin_navigation';
    private const CACHE_TTL = 3600;

    /**
     * Get the admin navigation tree filtered by current user permissions
     */
    public static function getTree(string $currentScript = ''): array
    {
        $cacheKey = self::CACHE_KEY . '_' . (\defined('IS_SUPER_ADMIN') && IS_SUPER_ADMIN ? 'sa' : 'a');

        $tree = CACHE('bb_cache')->get($cacheKey);

        if (!$tree) {
            $tree = self::buildTree();
            CACHE('bb_cache')->set($cacheKey, $tree, self::CACHE_TTL);
        }

        // Mark current item
        if ($currentScript) {
            $tree = self::markCurrent($tree, $currentScript);
        }

        return $tree;
    }

    /**
     * Build hierarchical tree from flat DB rows
     */
    private static function buildTree(): array
    {
        $sql = 'SELECT * FROM ' . BB_NAVIGATION
            . " WHERE navigation_type = 'admin' AND is_active = 1"
            . ' ORDER BY display_order ASC';

        $result = DB()->sql_query($sql);
        $rows = DB()->sql_fetchrowset($result);

        if (!$rows) {
            return [];
        }

        // Filter by permissions
        $isSuperAdmin = \defined('IS_SUPER_ADMIN') && IS_SUPER_ADMIN;

        $filtered = [];
        foreach ($rows as $row) {
            if ($row['permission_check'] === 'super_admin' && !$isSuperAdmin) {
                continue;
            }
            $filtered[] = $row;
        }

        // Build parent-children map
        $roots = [];
        $children = [];

        foreach ($filtered as $row) {
            if ($row['parent_navigation_id'] === '') {
                $roots[] = $row;
            } else {
                $children[$row['parent_navigation_id']][] = $row;
            }
        }

        // Assemble tree
        $tree = [];
        foreach ($roots as $root) {
            $id = $root['navigation_id'];

            // Translate title (handle constants like APP_NAME)
            $titleKey = $root['title_key'];
            if (\defined($titleKey)) {
                $title = \constant($titleKey);
            } else {
                $title = __($titleKey);
                if (!$title || $title === $titleKey) {
                    $title = str_replace('_', ' ', $titleKey);
                }
            }

            $node = [
                'id' => $id,
                'title' => $title,
                'icon' => $root['icon'],
                'link' => $root['link'],
                'children' => [],
                'has_active_child' => false,
            ];

            if (isset($children[$id])) {
                foreach ($children[$id] as $child) {
                    $childTitleKey = $child['title_key'];
                    if (\defined($childTitleKey)) {
                        $childTitle = \constant($childTitleKey);
                    } else {
                        $childTitle = __($childTitleKey);
                        if (!$childTitle || $childTitle === $childTitleKey) {
                            $childTitle = str_replace('_', ' ', $childTitleKey);
                        }
                    }

                    $node['children'][] = [
                        'id' => $child['navigation_id'],
                        'title' => $childTitle,
                        'icon' => $child['icon'],
                        'link' => $child['link'],
                        'is_current' => false,
                    ];
                }
            }

            // Only include categories that have children (or are direct links like dashboard)
            if (!empty($node['children']) || !empty($node['link'])) {
                $tree[] = $node;
            }
        }

        return $tree;
    }

    /**
     * Mark the current page in the navigation tree
     */
    private static function markCurrent(array $tree, string $currentScript): array
    {
        foreach ($tree as &$category) {
            foreach ($category['children'] as &$child) {
                // Match by script name (e.g. "admin_board" matches "admin_board.php?mode=config")
                $linkFile = strtok($child['link'], '?');
                $linkScript = pathinfo($linkFile, PATHINFO_FILENAME);

                if ($linkScript === $currentScript || $linkFile === $currentScript) {
                    $child['is_current'] = true;
                    $category['has_active_child'] = true;
                }
            }
            unset($child);
        }
        unset($category);

        return $tree;
    }

    /**
     * Clear the navigation cache
     */
    public static function clearCache(): void
    {
        CACHE('bb_cache')->rm(self::CACHE_KEY . '_sa');
        CACHE('bb_cache')->rm(self::CACHE_KEY . '_a');
    }
}
