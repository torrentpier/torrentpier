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

use Illuminate\Contracts\Config\Repository as RepositoryContract;
use TorrentPier\Config;
use TorrentPier\ServiceProvider;

/**
 * Configuration Service Provider
 *
 * Config is already instantiated by LoadConfiguration bootstrapper.
 * This provider just ensures aliases are set up correctly.
 */
class ConfigServiceProvider extends ServiceProvider
{
    /**
     * Register config aliases
     */
    public function register(): void
    {
        if (!$this->app->bound(Config::class)) {
            return;
        }

        $this->app->alias(Config::class, 'config');
        $this->app->alias(Config::class, RepositoryContract::class);
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
            RepositoryContract::class,
            'config',
        ];
    }
}
