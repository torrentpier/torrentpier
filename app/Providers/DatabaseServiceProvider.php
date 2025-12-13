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

use TorrentPier\Config;
use TorrentPier\Database\Database;
use TorrentPier\ServiceProvider;

/**
 * Database Service Provider
 *
 * Registers the database service.
 */
class DatabaseServiceProvider extends ServiceProvider
{
    /**
     * Register database services
     */
    public function register(): void
    {
        $this->app->singleton(Database::class, function ($app) {
            /** @var Config $config */
            $config = $app->make(Config::class);
            $dbConfig = $config->get('db');

            // Get the default 'db' server configuration
            return new Database($dbConfig['db']);
        });

        $this->app->alias(Database::class, 'db');
    }

    /**
     * Get the services provided by this provider
     *
     * @return string[]
     */
    public function provides(): array
    {
        return [
            Database::class,
            'db',
        ];
    }
}
