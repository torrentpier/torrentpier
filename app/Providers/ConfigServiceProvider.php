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

use RuntimeException;
use TorrentPier\Config;
use TorrentPier\ServiceProvider;

/**
 * Configuration Service Provider
 *
 * Registers the configuration service. This provider must be registered
 * first as other providers depend on the configuration being available.
 */
class ConfigServiceProvider extends ServiceProvider
{
    /**
     * Register the configuration service
     */
    public function register(): void
    {
        $this->app->singleton(Config::class, function ($app) {
            if (!$app->bound('config.data')) {
                throw new RuntimeException(
                    'Config data not loaded. Ensure LoadConfiguration bootstrapper runs before service providers.',
                );
            }

            return new Config($app->make('config.data'));
        });

        $this->app->alias(Config::class, 'config');
    }

    /**
     * Get the services provided by this provider
     *
     * @return string[]
     */
    public function provides(): array
    {
        return [
            Config::class,
            'config',
        ];
    }
}
