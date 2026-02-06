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

            // Get the default connection name
            $default = $config->get('database.default', 'mysql');
            $connection = $config->get("database.connections.{$default}");

            if (!$connection || !\is_array($connection)) {
                throw new RuntimeException("Database connection '{$default}' is not configured or invalid.");
            }

            // Convert Laravel-style config to legacy Database class format
            // Expected: [dbhost, dbport, dbname, dbuser, dbpasswd, charset, persist]
            $legacyConfig = [
                $connection['host'] ?? '127.0.0.1',
                $connection['port'] ?? '3306',
                $connection['database'] ?? 'torrentpier',
                $connection['username'] ?? 'root',
                $connection['password'] ?? '',
                $connection['charset'] ?? 'utf8mb4',
                false, // persist (persistent connections)
            ];

            return new Database($legacyConfig);
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
