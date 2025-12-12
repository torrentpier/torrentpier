<?php
/**
 * TorrentPier – Bull-powered BitTorrent tracker engine
 *
 * @copyright Copyright (c) 2005-2025 TorrentPier (https://torrentpier.com)
 * @link      https://github.com/torrentpier/torrentpier for the canonical source repository
 * @license   https://github.com/torrentpier/torrentpier/blob/master/LICENSE MIT License
 */

namespace TorrentPier\Console\Command\Cache;

use Symfony\Component\Console\Attribute\AsCommand;
use Symfony\Component\Console\Input\InputInterface;
use Symfony\Component\Console\Output\OutputInterface;
use TorrentPier\Console\Command\Command;

/**
 * Displays cache status and statistics
 */
#[AsCommand(
    name: 'cache:status',
    description: 'Display cache status and statistics'
)]
class CacheStatusCommand extends Command
{
    protected function execute(InputInterface $input, OutputInterface $output): int
    {
        $this->title('Cache Status');

        $cacheDir = BB_ROOT . 'internal_data/cache';
        $templateCacheDir = BB_ROOT . 'internal_data/cache/twig';

        $stats = [];

        // System cache statistics
        if (is_dir($cacheDir)) {
            $systemCacheSize = $this->getDirectorySize($cacheDir);
            $systemCacheFiles = $this->countFiles($cacheDir);
            $stats[] = ['System Cache', $this->formatBytes($systemCacheSize), $systemCacheFiles];
        } else {
            $stats[] = ['System Cache', '<comment>Not initialized</comment>', 0];
        }

        // Template cache statistics
        if (is_dir($templateCacheDir)) {
            $templateCacheSize = $this->getDirectorySize($templateCacheDir);
            $templateCacheFiles = $this->countFiles($templateCacheDir);
            $stats[] = ['Template Cache', $this->formatBytes($templateCacheSize), $templateCacheFiles];
        } else {
            $stats[] = ['Template Cache', '<comment>Not initialized</comment>', 0];
        }

        $this->table(['Cache Type', 'Size', 'Files'], $stats);

        $this->section('Cache Directories');
        $this->definitionList(
            ['System Cache' => $cacheDir],
            ['Template Cache' => $templateCacheDir],
        );

        return self::SUCCESS;
    }

    /**
     * Get directory size recursively
     */
    private function getDirectorySize(string $dir): int
    {
        $size = 0;

        try {
            $items = new \RecursiveIteratorIterator(
                new \RecursiveDirectoryIterator($dir, \RecursiveDirectoryIterator::SKIP_DOTS)
            );

            foreach ($items as $item) {
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
     * Count files in directory recursively
     */
    private function countFiles(string $dir): int
    {
        $count = 0;

        try {
            $items = new \RecursiveIteratorIterator(
                new \RecursiveDirectoryIterator($dir, \RecursiveDirectoryIterator::SKIP_DOTS)
            );

            foreach ($items as $item) {
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
     * Format bytes to human readable string
     */
    private function formatBytes(int $bytes): string
    {
        $units = ['B', 'KB', 'MB', 'GB'];
        $i = 0;

        while ($bytes >= 1024 && $i < count($units) - 1) {
            $bytes /= 1024;
            $i++;
        }

        return round($bytes, 2) . ' ' . $units[$i];
    }
}

