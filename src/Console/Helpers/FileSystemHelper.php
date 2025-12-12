<?php
/**
 * TorrentPier – Bull-powered BitTorrent tracker engine
 *
 * @copyright Copyright (c) 2005-2025 TorrentPier (https://torrentpier.com)
 * @link      https://github.com/torrentpier/torrentpier for the canonical source repository
 * @license   https://github.com/torrentpier/torrentpier/blob/master/LICENSE MIT License
 */

namespace TorrentPier\Console\Helpers;

use FilesystemIterator;
use RecursiveDirectoryIterator;
use RecursiveIteratorIterator;

/**
 * Helper class for common filesystem operations in console commands
 */
class FileSystemHelper
{
    /**
     * Recursively remove a directory and all its contents
     *
     * @param string $dir Directory path to remove
     * @param bool $removeRoot Whether to remove the root directory itself (default: true)
     * @return bool True if the operation succeeded, false otherwise
     */
    public static function removeDirectory(string $dir, bool $removeRoot = true): bool
    {
        if (!is_dir($dir)) {
            return true;
        }

        try {
            $iterator = new RecursiveIteratorIterator(
                new RecursiveDirectoryIterator($dir, RecursiveDirectoryIterator::SKIP_DOTS),
                RecursiveIteratorIterator::CHILD_FIRST
            );

            foreach ($iterator as $item) {
                $path = $item->getRealPath();
                if ($item->isDir()) {
                    if (!@rmdir($path)) {
                        return false;
                    }
                } else {
                    if (!@unlink($path)) {
                        return false;
                    }
                }
            }

            if ($removeRoot) {
                return @rmdir($dir);
            }

            return true;
        } catch (\Throwable) {
            return false;
        }
    }

    /**
     * Clear directory contents without removing the directory itself
     *
     * @param string $dir Directory path to clear
     * @return bool True if the operation succeeded, false otherwise
     */
    public static function clearDirectory(string $dir): bool
    {
        return self::removeDirectory($dir, false);
    }

    /**
     * Get total size of directory and all contents recursively
     *
     * @param string $dir Directory path
     * @return int Size in bytes
     */
    public static function getDirectorySize(string $dir): int
    {
        if (!is_dir($dir)) {
            return 0;
        }

        $size = 0;

        try {
            $iterator = new RecursiveIteratorIterator(
                new RecursiveDirectoryIterator($dir, FilesystemIterator::SKIP_DOTS)
            );

            foreach ($iterator as $item) {
                if ($item->isFile()) {
                    $size += $item->getSize();
                }
            }
        } catch (\Throwable) {
            return 0;
        }

        return $size;
    }

    /**
     * Count files in the directory recursively
     *
     * @param string $dir Directory path
     * @return int Number of files
     */
    public static function countFiles(string $dir): int
    {
        if (!is_dir($dir)) {
            return 0;
        }

        $count = 0;

        try {
            $iterator = new RecursiveIteratorIterator(
                new RecursiveDirectoryIterator($dir, FilesystemIterator::SKIP_DOTS)
            );

            foreach ($iterator as $item) {
                if ($item->isFile()) {
                    $count++;
                }
            }
        } catch (\Throwable) {
            return 0;
        }

        return $count;
    }

    /**
     * Clear directory contents and return a count of deleted files
     *
     * @param string $dir Directory path to clear
     * @return int Number of files deleted
     */
    public static function clearDirectoryWithCount(string $dir): int
    {
        if (!is_dir($dir)) {
            return 0;
        }

        $count = 0;

        try {
            $iterator = new RecursiveIteratorIterator(
                new RecursiveDirectoryIterator($dir, FilesystemIterator::SKIP_DOTS),
                RecursiveIteratorIterator::CHILD_FIRST
            );

            foreach ($iterator as $item) {
                if ($item->isDir()) {
                    @rmdir($item->getRealPath());
                } else {
                    if (@unlink($item->getRealPath())) {
                        $count++;
                    }
                }
            }
        } catch (\Throwable) {
            // Return count of files deleted so far
        }

        return $count;
    }

    /**
     * Format bytes to human-readable string
     *
     * @param int $bytes Size in bytes
     * @param int $precision Decimal precision (default: 2)
     * @return string Formatted string (e.g., "1.5 MB")
     */
    public static function formatBytes(int $bytes, int $precision = 2): string
    {
        $units = ['B', 'KB', 'MB', 'GB', 'TB'];
        $i = 0;

        while ($bytes >= 1024 && $i < count($units) - 1) {
            $bytes /= 1024;
            $i++;
        }

        return round($bytes, $precision) . ' ' . $units[$i];
    }
}
