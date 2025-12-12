<?php

/**
 * TorrentPier – Bull-powered BitTorrent tracker engine
 *
 * @copyright Copyright (c) 2005-2025 TorrentPier (https://torrentpier.com)
 * @link      https://github.com/torrentpier/torrentpier for the canonical source repository
 * @license   https://github.com/torrentpier/torrentpier/blob/master/LICENSE MIT License
 */

namespace TorrentPier\Console\Commands\Rebuild;

use Symfony\Component\Console\Attribute\AsCommand;
use Symfony\Component\Console\Input\InputInterface;
use Symfony\Component\Console\Output\OutputInterface;
use Throwable;
use TorrentPier\Console\Commands\Command;
use TorrentPier\Console\Helpers\FileSystemHelper;
use TorrentPier\Sitemap;

/**
 * Rebuild the sitemap
 *
 * Generates XML sitemaps for search engines.
 */
#[AsCommand(
    name: 'rebuild:sitemap',
    description: 'Regenerate XML sitemap files'
)]
class SitemapCommand extends Command
{
    protected function execute(InputInterface $input, OutputInterface $output): int
    {
        $this->title('Rebuild Sitemap');

        // Check if sitemap directory exists
        if (!is_dir(SITEMAP_DIR)) {
            $this->error('Sitemap directory does not exist: ' . SITEMAP_DIR);
            $this->comment('Create the directory first or run: php bull storage:link');
            return self::FAILURE;
        }

        // Check if writable
        if (!is_writable(SITEMAP_DIR)) {
            $this->error('Sitemap directory is not writable: ' . SITEMAP_DIR);
            return self::FAILURE;
        }

        // Get current stats
        $forumCount = $this->getForumCount();
        $topicCount = $this->getTopicCount();

        $this->section('Configuration');
        $this->definitionList(
            ['Forums' => number_format($forumCount)],
            ['Topics' => number_format($topicCount)],
            ['Output directory' => SITEMAP_DIR],
        );

        $this->line();
        $this->section('Processing');
        $this->line('  Generating sitemap files...');

        $startTime = microtime(true);

        try {
            $sitemap = new Sitemap();
            $sitemap->createSitemap();
        } catch (Throwable $e) {
            $this->error('Failed to generate sitemap: ' . $e->getMessage());
            if ($this->isVerbose()) {
                $this->line();
                $this->line("<error>{$e->getTraceAsString()}</error>");
            }
            return self::FAILURE;
        }

        $elapsed = round(microtime(true) - $startTime, 2);

        // List generated files
        $this->line();
        $this->section('Generated Files');

        $files = [
            'sitemap.xml' => SITEMAP_DIR . '/sitemap.xml',
            'sitemap_dynamic.xml' => SITEMAP_DIR . '/sitemap_dynamic.xml',
            'sitemap_static.xml' => SITEMAP_DIR . '/sitemap_static.xml',
        ];

        $rows = [];
        foreach ($files as $name => $path) {
            if (file_exists($path)) {
                $size = filesize($path);
                $rows[] = [$name, FileSystemHelper::formatBytes($size), date('Y-m-d H:i:s', filemtime($path))];
            }
        }

        if (!empty($rows)) {
            $this->table(['File', 'Size', 'Modified'], $rows);
        }

        // Display URLs
        $this->line();
        $this->section('Sitemap URLs');
        $this->line('  Main index: <comment>' . make_url('/sitemap.xml') . '</comment>');

        $this->line();
        $this->section('Statistics');
        $this->definitionList(
            ['Time elapsed' => $elapsed . 's'],
            ['Memory used' => FileSystemHelper::formatBytes(memory_get_peak_usage(true))],
        );

        $this->success('Sitemap generated successfully!');
        return self::SUCCESS;
    }

    /**
     * Get forum count for sitemap
     */
    private function getForumCount(): int
    {
        return DB()->table(BB_FORUMS)->count('*');
    }

    /**
     * Get topic count for sitemap
     */
    private function getTopicCount(): int
    {
        return DB()->table(BB_TOPICS)
            ->where('topic_status != ?', TOPIC_MOVED)
            ->count('*');
    }
}
