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
use Symfony\Component\Console\Input\InputOption;
use Symfony\Component\Console\Output\OutputInterface;
use Throwable;
use TorrentPier\Console\Command\Command;
use TorrentPier\Console\Helpers\FileSystemHelper;

/**
 * Clears the application cache
 */
#[AsCommand(
    name: 'cache:clear',
    description: 'Clear the application cache'
)]
class CacheClearCommand extends Command
{
    protected function configure(): void
    {
        $this
            ->addOption(
                'type',
                't',
                InputOption::VALUE_OPTIONAL,
                'Cache type to clear (all, system, templates)',
                'all'
            )
            ->addOption(
                'force',
                'f',
                InputOption::VALUE_NONE,
                'Force clear without confirmation'
            );
    }

    protected function execute(InputInterface $input, OutputInterface $output): int
    {
        $type = $input->getOption('type');
        $force = $input->getOption('force');

        $this->title('Cache Clear');

        if (!$force && !$this->confirm('Are you sure you want to clear the cache?')) {
            $this->comment('Operation cancelled.');
            return self::SUCCESS;
        }

        $cleared = [];

        try {
            switch ($type) {
                case 'all':
                    $this->clearSystemCache();
                    $cleared[] = 'System cache';
                    $this->clearTemplateCache();
                    $cleared[] = 'Template cache';
                    break;

                case 'system':
                    $this->clearSystemCache();
                    $cleared[] = 'System cache';
                    break;

                case 'templates':
                    $this->clearTemplateCache();
                    $cleared[] = 'Template cache';
                    break;

                default:
                    $this->error("Unknown cache type: $type");
                    return self::FAILURE;
            }

            $this->success('Cache cleared successfully!');
            $this->listing($cleared);

            return self::SUCCESS;
        } catch (Throwable $e) {
            $this->error('Failed to clear cache: ' . $e->getMessage());

            if ($this->isVerbose()) {
                $this->line('<error>' . $e->getTraceAsString() . '</error>');
            }

            return self::FAILURE;
        }
    }

    /**
     * Clear system cache
     */
    private function clearSystemCache(): void
    {
        $cacheDir = CACHE_DIR;

        if (is_dir($cacheDir)) {
            FileSystemHelper::clearDirectory($cacheDir);
        }

        // Clear runtime cache if available
        if (function_exists('CACHE')) {
            try {
                CACHE('bb_cache')->clean();
            } catch (Throwable) {
                // Ignore if the cache is not available
            }
        }
    }

    /**
     * Clear template cache
     */
    private function clearTemplateCache(): void
    {
        $templateCacheDir = TEMPLATES_CACHE_DIR;

        if (is_dir($templateCacheDir)) {
            FileSystemHelper::clearDirectory($templateCacheDir);
        }
    }
}
