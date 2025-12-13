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

use Throwable;
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
            // Check if Config is already initialized (by common.php)
            // This allows both standalone container boot and common.php bootstrap
            try {
                return Config::getInstance();
            } catch (Throwable) {
                // Not initialized yet - do it now
                $bb_cfg = [];

                // Load main configuration
                $configPath = $app->configPath('config.php');
                if (file_exists($configPath)) {
                    require $configPath;
                }

                // Load local configuration overrides
                $localConfigPath = $app->configPath('config.local.php');
                if (file_exists($localConfigPath)) {
                    require $localConfigPath;
                }

                // Initialize the Config singleton with the loaded configuration
                return Config::init($bb_cfg);
            }
        });

        // Register alias for convenient access
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
