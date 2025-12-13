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
use TorrentPier\Config;
use TorrentPier\Database\Database;
use TorrentPier\Database\DatabaseFactory;
use TorrentPier\ServiceProvider;

/**
 * Database Service Provider
 *
 * Registers database services and manages database connections.
 * Supports multiple database connections via aliases.
 */
class DatabaseServiceProvider extends ServiceProvider
{
    /**
     * Indicates if the provider has been initialized
     */
    private static bool $initialized = false;

    /**
     * Register database services
     */
    public function register(): void
    {
        // Register a factory wrapper for getting connections by alias
        $this->app->singleton('db.factory', function ($app) {
            // Initialize the factory if not already done
            if (!self::$initialized) {
                /** @var Config $config */
                $config = $app->make(Config::class);
                DatabaseFactory::init(
                    $config->get('db'),
                    $config->get('db_alias', []),
                );
                self::$initialized = true;
            }

            return new class {
                /**
                 * Get a database connection by alias
                 */
                public function connection(string $alias = 'db'): Database
                {
                    return DatabaseFactory::getInstance($alias);
                }

                /**
                 * Check if a server/alias exists
                 */
                public function hasConnection(string $alias): bool
                {
                    return DatabaseFactory::hasServer($alias);
                }

                /**
                 * Get all available server names
                 *
                 * @return string[]
                 */
                public function getConnectionNames(): array
                {
                    return DatabaseFactory::getServerNames();
                }
            };
        });

        // Register the default database connection
        $this->app->singleton(Database::class, function ($app) {
            return $app->make('db.factory')->connection('db');
        });

        // Register alias
        $this->app->alias(Database::class, 'db');
    }

    /**
     * Bootstrap the database services
     * @throws BindingResolutionException
     */
    public function boot(): void
    {
        // Ensure the factory is initialized on boot
        $this->app->make('db.factory');
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
            'db.factory',
        ];
    }
}
