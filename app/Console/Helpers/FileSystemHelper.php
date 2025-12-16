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
use Illuminate\Contracts\Container\BindingResolutionException;
use RecursiveDirectoryIterator;
use RecursiveIteratorIterator;
use Throwable;

/**
 * Helper class for common filesystem operations in console commands
 */
class FileSystemHelper
{
    /**
     * Files to exclude from deletion by default (e.g., git placeholders)
     */
    private const array DEFAULT_EXCLUDE = ['.keep', '.gitkeep'];

    /**
     * Recursively remove a directory and all its contents
     *
     * @param string $dir Directory path to remove
     * @param bool $removeRoot Whether to remove the root directory itself (default: true)
     * @param array $exclude Filenames to exclude from deletion (default: ['.keep', '.gitkeep'])
     * @throws BindingResolutionException
     * @return bool True if the operation succeeded, false otherwise
     */
    public static function removeDirectory(string $dir, bool $removeRoot = true, array $exclude = self::DEFAULT_EXCLUDE): bool
    {
        if (!files()->isDirectory($dir)) {
            return true;
        }

        try {
            $iterator = new RecursiveIteratorIterator(
                new RecursiveDirectoryIterator($dir, FilesystemIterator::SKIP_DOTS),
                RecursiveIteratorIterator::CHILD_FIRST,
            );

            foreach ($iterator as $item) {
                $path = $item->getRealPath();
                $filename = $item->getFilename();

                if ($item->isDir()) {
                    // Only remove empty directories (may fail if not empty - that's ok)
                    if (files()->isEmptyDirectory($path)) {
                        files()->deleteDirectory($path);
                    }
                } elseif (!\in_array($filename, $exclude, true)) {
                    if (files()->isFile($path) && !files()->delete($path)) {
                        return false;
                    }
                }
            }

            if ($removeRoot && files()->isEmptyDirectory($dir)) {
                return files()->deleteDirectory($dir);
            }

            return true;
        } catch (Throwable) {
            return false;
        }
    }

    /**
     * Clear directory contents without removing the directory itself
     *
     * @param string $dir Directory path to clear
     * @param array $exclude Filenames to exclude from deletion (default: ['.keep', '.gitkeep'])
     * @return bool True if the operation succeeded, false otherwise
     */
    public static function clearDirectory(string $dir, array $exclude = self::DEFAULT_EXCLUDE): bool
    {
        return self::removeDirectory($dir, false, $exclude);
    }

    /**
     * Get total size of directory and all contents recursively
     *
     * @param string $dir Directory path
     * @throws BindingResolutionException
     * @return int Size in bytes
     */
    public static function getDirectorySize(string $dir): int
    {
        if (!files()->isDirectory($dir)) {
            return 0;
        }

        $size = 0;

        try {
            $iterator = new RecursiveIteratorIterator(
                new RecursiveDirectoryIterator($dir, FilesystemIterator::SKIP_DOTS),
            );

            foreach ($iterator as $item) {
                if ($item->isFile()) {
                    $size += $item->getSize();
                }
            }
        } catch (Throwable) {
            return 0;
        }

        return $size;
    }

    /**
     * Count files in the directory recursively
     *
     * @param string $dir Directory path
     * @throws BindingResolutionException
     * @return int Number of files
     */
    public static function countFiles(string $dir): int
    {
        if (!files()->isDirectory($dir)) {
            return 0;
        }

        $count = 0;

        try {
            $iterator = new RecursiveIteratorIterator(
                new RecursiveDirectoryIterator($dir, FilesystemIterator::SKIP_DOTS),
            );

            foreach ($iterator as $item) {
                if ($item->isFile()) {
                    $count++;
                }
            }
        } catch (Throwable) {
            return 0;
        }

        return $count;
    }

    /**
     * Clear directory contents and return a count of deleted files
     *
     * @param string $dir Directory path to clear
     * @param array $exclude Filenames to exclude from deletion (default: ['.keep', '.gitkeep'])
     * @throws BindingResolutionException
     * @return int Number of files deleted
     */
    public static function clearDirectoryWithCount(string $dir, array $exclude = self::DEFAULT_EXCLUDE): int
    {
        if (!files()->isDirectory($dir)) {
            return 0;
        }

        $count = 0;

        try {
            $iterator = new RecursiveIteratorIterator(
                new RecursiveDirectoryIterator($dir, FilesystemIterator::SKIP_DOTS),
                RecursiveIteratorIterator::CHILD_FIRST,
            );

            foreach ($iterator as $item) {
                $path = $item->getRealPath();

                if ($item->isDir()) {
                    // Only remove empty directories
                    if (files()->isEmptyDirectory($path)) {
                        files()->deleteDirectory($path);
                    }
                } elseif (!\in_array($item->getFilename(), $exclude, true)) {
                    if (files()->isFile($path) && files()->delete($path)) {
                        $count++;
                    }
                }
            }
        } catch (Throwable) {
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

        while ($bytes >= 1024 && $i < \count($units) - 1) {
            $bytes /= 1024;
            $i++;
        }

        return round($bytes, $precision) . ' ' . $units[$i];
    }
}
