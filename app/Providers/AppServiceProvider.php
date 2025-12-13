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

use TorrentPier\ServiceProvider;

/**
 * Application Service Provider
 *
 * This provider is for application-specific bindings and bootstrapping.
 * It is registered last, after all core providers, so it has access
 * to all other services.
 */
class AppServiceProvider extends ServiceProvider
{
    /**
     * Register any application services
     */
    public function register(): void
    {
        // Register application-specific bindings here
        //
        // Example:
        // $this->app->singleton(SomeService::class, function ($app) {
        //     return new SomeService($app->make(Config::class));
        // });
    }

    /**
     * Bootstrap any application services
     */
    public function boot(): void
    {
        // Perform any application bootstrapping here
        //
        // This method is called after all service providers have been
        // registered, so you have access to all services.
        //
        // Example:
        // - Register event listeners
        // - Configure third-party packages
        // - Set up application-wide middleware
    }
}
