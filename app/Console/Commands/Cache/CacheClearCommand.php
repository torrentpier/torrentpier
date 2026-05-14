<?php

/**
 * TorrentPier – Bull-powered BitTorrent tracker engine
 *
 * @copyright Copyright (c) 2005-2026 TorrentPier (https://torrentpier.com)
 * @link      https://github.com/torrentpier/torrentpier for the canonical source repository
 * @license   https://github.com/torrentpier/torrentpier/blob/master/LICENSE MIT License
 */

namespace TorrentPier\Console\Commands\Cache;

use Illuminate\Contracts\Container\BindingResolutionException;
use Symfony\Component\Console\Attribute\AsCommand;
use Symfony\Component\Console\Input\InputInterface;
use Symfony\Component\Console\Input\InputOption;
use Symfony\Component\Console\Output\OutputInterface;
use Throwable;
use TorrentPier\Application;
use TorrentPier\Cache\UnifiedCacheSystem;
use TorrentPier\Config;
use TorrentPier\Console\Commands\Command;
use TorrentPier\Console\Helpers\FileSystemHelper;

/**
 * Clears the application cache
 */
#[AsCommand(
    name: 'cache:clear',
    description: 'Clear the application cache',
)]
class CacheClearCommand extends Command
{
    /**
     * Create a new cache clear command
     *
     * @param Config $config The configuration instance
     * @param UnifiedCacheSystem $cacheSystem The unified cache system
     * @param Application|null $app The application container (optional)
     * @throws BindingResolutionException
     */
    public function __construct(
        private readonly Config $config,
        private readonly UnifiedCacheSystem $cacheSystem,
        ?Application $app = null,
    ) {
        parent::__construct($app);
    }

    protected function configure(): void
    {
        $this
            ->addOption(
                'type',
                't',
                InputOption::VALUE_OPTIONAL,
                'Cache type to clear (all, system, templates)',
                'all',
            )
            ->addOption(
                'force',
                'f',
                InputOption::VALUE_NONE,
                'Force clear without confirmation',
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
                    $this->error("Unknown cache type: {$type}");

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
     * @throws BindingResolutionException
     */
    private function clearSystemCache(): void
    {
        // Legacy path (storage/framework/cache); active caches live under cache.db_dir.
        $legacyCacheDir = \defined('CACHE_DIR') ? CACHE_DIR : $this->config->get('cache_dir');
        if ($legacyCacheDir && files()->isDirectory($legacyCacheDir)) {
            FileSystemHelper::clearDirectory($legacyCacheDir);
        }

        $activeCacheDir = $this->cacheSystem->getCacheBaseDir();
        if ($activeCacheDir && files()->isDirectory($activeCacheDir)) {
            FileSystemHelper::clearDirectory($activeCacheDir);
        }

        try {
            $this->cacheSystem->clearAll();
        } catch (Throwable) {
        }
    }

    /**
     * Clear template cache
     */
    private function clearTemplateCache(): void
    {
        // Use injected config or fall back to constant
        $templateCacheDir = \defined('TEMPLATES_CACHE_DIR') ? TEMPLATES_CACHE_DIR : $this->config->get('templates_cache_dir');

        if (files()->isDirectory($templateCacheDir)) {
            FileSystemHelper::clearDirectory($templateCacheDir);
        }
    }
}
