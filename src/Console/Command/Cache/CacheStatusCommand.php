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
use TorrentPier\Console\Helpers\FileSystemHelper;

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

        $cacheDir = CACHE_DIR;
        $templateCacheDir = TEMPLATES_CACHE_DIR;

        $stats = [];

        // System cache statistics
        if (is_dir($cacheDir)) {
            $systemCacheSize = FileSystemHelper::getDirectorySize($cacheDir);
            $systemCacheFiles = FileSystemHelper::countFiles($cacheDir);
            $stats[] = ['System Cache', FileSystemHelper::formatBytes($systemCacheSize), $systemCacheFiles];
        } else {
            $stats[] = ['System Cache', '<comment>Not initialized</comment>', 0];
        }

        // Template cache statistics
        if (is_dir($templateCacheDir)) {
            $templateCacheSize = FileSystemHelper::getDirectorySize($templateCacheDir);
            $templateCacheFiles = FileSystemHelper::countFiles($templateCacheDir);
            $stats[] = ['Template Cache', FileSystemHelper::formatBytes($templateCacheSize), $templateCacheFiles];
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
}
