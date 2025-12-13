<?php

/**
 * TorrentPier – Bull-powered BitTorrent tracker engine
 *
 * @copyright Copyright (c) 2005-2025 TorrentPier (https://torrentpier.com)
 * @link      https://github.com/torrentpier/torrentpier for the canonical source repository
 * @license   https://github.com/torrentpier/torrentpier/blob/master/LICENSE MIT License
 */

namespace TorrentPier\Database;

use RuntimeException;

/**
 * Database Factory - maintains compatibility with existing DB() function calls
 *
 * This factory completely replaces the legacy SqlDb/Dbs system with the new
 * Nette Database implementation while maintaining full backward compatibility.
 */
class DatabaseFactory
{
    private static array $instances = [];
    private static array $server_configs = [];
    private static array $server_aliases = [];

    /**
     * Initialize the factory with database configuration
     */
    public static function init(array $db_config, array $db_aliases = []): void
    {
        self::$server_configs = $db_config;
        self::$server_aliases = $db_aliases;
    }

    /**
     * Get database instance (maintains compatibility with existing DB() calls)
     */
    public static function getInstance(string $srv_name_or_alias = 'db'): Database
    {
        $srv_name = self::resolveSrvName($srv_name_or_alias);

        if (!isset(self::$instances[$srv_name])) {
            // Get configuration for this server
            $cfg_values = self::$server_configs[$srv_name] ?? null;
            if (!$cfg_values) {
                throw new RuntimeException("Database configuration not found for server: {$srv_name}");
            }

            self::$instances[$srv_name] = Database::getInstance($cfg_values, $srv_name);
        }

        return self::$instances[$srv_name];
    }

    /**
     * Check if a specific database server is configured
     */
    public static function hasServer(string $srv_name): bool
    {
        return isset(self::$server_configs[$srv_name]);
    }

    /**
     * Get all configured server names
     */
    public static function getServerNames(): array
    {
        return array_keys(self::$server_configs);
    }

    /**
     * Clear all cached instances (useful for testing)
     */
    public static function clearInstances(): void
    {
        foreach (self::$instances as $instance) {
            if (method_exists($instance, 'close')) {
                $instance->close();
            }
        }
        self::$instances = [];
        Database::destroyInstances();
    }

    /**
     * Resolve server name using alias system
     */
    private static function resolveSrvName(string $name): string
    {
        // Check if it's an alias
        if (isset(self::$server_aliases[$name])) {
            return self::$server_aliases[$name];
        }

        // Check if it's a direct server name
        if (isset(self::$server_configs[$name])) {
            return $name;
        }

        // Default to 'db'
        return 'db';
    }
}
