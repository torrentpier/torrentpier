<?php

/**
 * TorrentPier – Bull-powered BitTorrent tracker engine
 *
 * @copyright Copyright (c) 2005-2025 TorrentPier (https://torrentpier.com)
 * @link      https://github.com/torrentpier/torrentpier for the canonical source repository
 * @license   https://github.com/torrentpier/torrentpier/blob/master/LICENSE MIT License
 */

declare(strict_types=1);

namespace App\Providers;

use ReflectionException;
use TorrentPier\Cache\CacheManager;
use TorrentPier\Cache\DatastoreManager;
use TorrentPier\Cache\UnifiedCacheSystem;
use TorrentPier\Config;
use TorrentPier\ServiceProvider;

/**
 * Cache Service Provider
 *
 * Registers caching services including the unified cache system,
 * cache managers, and datastore manager.
 */
class CacheServiceProvider extends ServiceProvider
{
    /**
     * Register cache services
     * @throws ReflectionException
     */
    public function register(): void
    {
        // Main unified cache system
        $this->app->singleton(UnifiedCacheSystem::class, function ($app) {
            /** @var Config $config */
            $config = $app->make(Config::class);
            return UnifiedCacheSystem::getInstance($config->all());
        });

        // Datastore manager for persistent data
        $this->app->singleton(DatastoreManager::class, function ($app) {
            /** @var Config $config */
            $config = $app->make(Config::class);

            /** @var UnifiedCacheSystem $cacheSystem */
            $cacheSystem = $app->make(UnifiedCacheSystem::class);

            return $cacheSystem->getDatastore($config->get('datastore_type', 'file'));
        });

        // Factory for getting cache managers by name
        $this->app->bind('cache.manager', function ($app, array $params) {
            $cacheName = $params['name'] ?? 'bb_cache';

            /** @var UnifiedCacheSystem $cacheSystem */
            $cacheSystem = $app->make(UnifiedCacheSystem::class);

            return $cacheSystem->get_cache_obj($cacheName);
        });

        // Register aliases
        $this->app->alias(UnifiedCacheSystem::class, 'cache');
        $this->app->alias(DatastoreManager::class, 'datastore');
    }

    /**
     * Get the services provided by this provider
     *
     * @return string[]
     */
    public function provides(): array
    {
        return [
            UnifiedCacheSystem::class,
            DatastoreManager::class,
            CacheManager::class,
            'cache',
            'datastore',
            'cache.manager',
        ];
    }
}
