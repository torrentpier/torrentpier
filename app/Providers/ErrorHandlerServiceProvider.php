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

use Illuminate\Contracts\Container\BindingResolutionException;
use TorrentPier\ServiceProvider;
use TorrentPier\Tracy\TracyBarManager;
use TorrentPier\Whoops\WhoopsManager;

/**
 * Error Handler Service Provider
 *
 * Registers error handling services (Whoops, Tracy).
 * Should be registered early to catch errors during boot.
 */
class ErrorHandlerServiceProvider extends ServiceProvider
{
    /**
     * Register error handling services
     */
    public function register(): void
    {
        // Whoops error handler
        $this->app->singleton(WhoopsManager::class, function () {
            return new WhoopsManager;
        });

        // Tracy debug bar
        $this->app->singleton(TracyBarManager::class, function () {
            return new TracyBarManager;
        });

        // Register aliases
        $this->app->alias(WhoopsManager::class, 'whoops');
        $this->app->alias(TracyBarManager::class, 'tracy');
    }

    /**
     * Bootstrap the error handlers
     * @throws BindingResolutionException
     */
    public function boot(): void
    {
        // Initialize error handlers
        // Note: These may already be initialized by common.php,
        // but calling init() again is safe
        $this->app->make(WhoopsManager::class)->init();
        $this->app->make(TracyBarManager::class)->init();
    }

    /**
     * Get the services provided by this provider
     *
     * @return string[]
     */
    public function provides(): array
    {
        return [
            WhoopsManager::class,
            TracyBarManager::class,
            'whoops',
            'tracy',
        ];
    }
}
