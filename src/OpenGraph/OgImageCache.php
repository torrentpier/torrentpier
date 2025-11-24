<?php
/**
 * TorrentPier – Bull-powered BitTorrent tracker engine
 *
 * @copyright Copyright (c) 2005-2025 TorrentPier (https://torrentpier.com)
 * @link      https://github.com/torrentpier/torrentpier for the canonical source repository
 * @license   https://github.com/torrentpier/torrentpier/blob/master/LICENSE MIT License
 */

declare(strict_types=1);

namespace TorrentPier\OpenGraph;

/**
 * Manages caching for generated OG images with smart invalidation
 */
class OgImageCache
{
    private string $cachePath;
    private int $ttl;

    public function __construct(?string $cachePath = null, int $ttl = 604800)
    {
        $this->cachePath = $cachePath ?? BB_PATH . '/internal_data/og_cache/';
        $this->ttl = $ttl; // 7 days default

        if (!is_dir($this->cachePath)) {
            mkdir($this->cachePath, 0755, true);
        }
    }

    /**
     * Get cached image file path if valid
     *
     * @param string $type Type of resource (topic, forum, user)
     * @param int $id Resource ID
     * @param int $updatedAt Timestamp when content was last updated
     * @return string|null Path to cached file or null if cache miss
     */
    public function get(string $type, int $id, int $updatedAt = 0): ?string
    {
        $file = $this->getFilePath($type, $id);

        if (!file_exists($file)) {
            return null;
        }

        $fileMtime = filemtime($file);

        // Cache is stale if content was updated after cache creation
        if ($updatedAt > 0 && $fileMtime < $updatedAt) {
            return null;
        }

        // Check TTL expiration
        if ($fileMtime < (time() - $this->ttl)) {
            return null;
        }

        return $file;
    }

    /**
     * Save image data to cache
     *
     * @param string $type Type of resource
     * @param int $id Resource ID
     * @param string $imageData Binary image data
     * @return string Path to saved file
     */
    public function set(string $type, int $id, string $imageData): string
    {
        $file = $this->getFilePath($type, $id);
        file_put_contents($file, $imageData);
        return $file;
    }

    /**
     * Invalidate specific cache entry
     *
     * @param string $type Type of resource
     * @param int $id Resource ID
     */
    public function invalidate(string $type, int $id): void
    {
        $file = $this->getFilePath($type, $id);
        if (file_exists($file)) {
            unlink($file);
        }
    }

    /**
     * Clear all cache or by type
     *
     * @param string|null $type Optional type to clear
     */
    public function clear(?string $type = null): void
    {
        if ($type !== null) {
            $dir = $this->cachePath . $type;
            if (is_dir($dir)) {
                $this->removeDirectory($dir);
            }
        } else {
            foreach (['topic', 'forum', 'user'] as $t) {
                $dir = $this->cachePath . $t;
                if (is_dir($dir)) {
                    $this->removeDirectory($dir);
                }
            }
        }
    }

    /**
     * Get file path with subdirectory distribution
     */
    private function getFilePath(string $type, int $id): string
    {
        // Use modulo to distribute files across subdirectories
        $subdir = $type . '/' . ($id % 100);
        $dir = $this->cachePath . $subdir;

        if (!is_dir($dir)) {
            mkdir($dir, 0755, true);
        }

        return $dir . '/' . $id . '.png';
    }

    /**
     * Recursively remove directory
     */
    private function removeDirectory(string $dir): void
    {
        if (!is_dir($dir)) {
            return;
        }

        $items = new \RecursiveIteratorIterator(
            new \RecursiveDirectoryIterator($dir, \RecursiveDirectoryIterator::SKIP_DOTS),
            \RecursiveIteratorIterator::CHILD_FIRST
        );

        foreach ($items as $item) {
            if ($item->isDir()) {
                rmdir($item->getPathname());
            } else {
                unlink($item->getPathname());
            }
        }

        rmdir($dir);
    }
}
