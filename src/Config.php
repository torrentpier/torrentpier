<?php

/**
 * TorrentPier – Bull-powered BitTorrent tracker engine
 *
 * @copyright Copyright (c) 2005-2025 TorrentPier (https://torrentpier.com)
 * @link      https://github.com/torrentpier/torrentpier for the canonical source repository
 * @license   https://github.com/torrentpier/torrentpier/blob/master/LICENSE MIT License
 */

namespace TorrentPier;

use InvalidArgumentException;
use LogicException;

/**
 * Configuration management class
 *
 * Encapsulates the global $bb_cfg array and provides methods to access configuration values
 */
class Config
{
    private static ?Config $instance = null;
    private array $config = [];

    private function __construct(array $config = [])
    {
        $this->config = $config;
    }

    /**
     * Magic method to allow property access
     */
    public function __get(string $key): mixed
    {
        return $this->get($key);
    }

    /**
     * Magic method to allow property setting
     */
    public function __set(string $key, mixed $value): void
    {
        $this->set($key, $value);
    }

    /**
     * Magic method to check if property exists
     */
    public function __isset(string $key): bool
    {
        return $this->has($key);
    }

    /**
     * Prevent cloning of the singleton instance
     */
    private function __clone() {}

    /**
     * Prevent serialization of the singleton instance
     */
    public function __serialize(): array
    {
        throw new LogicException('Cannot serialize a singleton.');
    }

    /**
     * Prevent unserialization of the singleton instance
     */
    public function __unserialize(array $data): void
    {
        throw new LogicException('Cannot unserialize a singleton.');
    }

    /**
     * Get the singleton instance of Config
     */
    public static function getInstance(array $config = []): self
    {
        if (self::$instance === null) {
            self::$instance = new self($config);
        }

        return self::$instance;
    }

    /**
     * Initialize the config with the global $bb_cfg array
     */
    public static function init(array $bb_cfg): self
    {
        self::$instance = new self($bb_cfg);

        return self::$instance;
    }

    /**
     * Get a configuration value by key
     * Supports dot notation for nested arrays (e.g., 'db.host')
     */
    public function get(string $key, mixed $default = null): mixed
    {
        if (str_contains($key, '.')) {
            return $this->getNestedValue($key, $default);
        }

        return $this->config[$key] ?? $default;
    }

    /**
     * Set a configuration value by key
     * Supports dot notation for nested arrays
     */
    public function set(string $key, mixed $value): void
    {
        if (str_contains($key, '.')) {
            $this->setNestedValue($key, $value);
        } else {
            $this->config[$key] = $value;
        }
    }

    /**
     * Check if a configuration key exists
     * Supports dot notation for nested arrays
     */
    public function has(string $key): bool
    {
        if (str_contains($key, '.')) {
            return $this->getNestedValue($key) !== null;
        }

        return \array_key_exists($key, $this->config);
    }

    /**
     * Get all configuration values
     */
    public function all(): array
    {
        return $this->config;
    }

    /**
     * Merge additional configuration values
     */
    public function merge(array $config): void
    {
        $this->config = array_merge_recursive($this->config, $config);
    }

    /**
     * Load configuration from the database table
     *
     * @param string $table Database table name
     * @param bool $fromDb Force load from database (skip cache)
     * @param bool $updateCache Update cache after loading
     * @return array Configuration array
     */
    public function loadFromDatabase(string $table, bool $fromDb = false, bool $updateCache = true): array
    {
        $this->validateTableName($table);

        if (!$fromDb) {
            $cached = CACHE('bb_config')->get("config_{$table}");
            if ($cached) {
                return $cached;
            }
        }

        $cfg = [];
        foreach (DB()->fetch_rowset("SELECT * FROM {$table}") as $row) {
            $cfg[$row['config_name']] = $row['config_value'];
        }

        if ($updateCache) {
            CACHE('bb_config')->set("config_{$table}", $cfg);
        }

        return $cfg;
    }

    /**
     * Update configuration in database table
     *
     * @param array $params Key-value pairs to update
     * @param string $table Database table name
     */
    public function updateDatabase(array $params, string $table): void
    {
        $this->validateTableName($table);

        $updates = [];
        foreach ($params as $name => $val) {
            $updates[] = [
                'config_name' => $name,
                'config_value' => $val,
            ];
        }
        $updates = DB()->build_array('MULTI_INSERT', $updates);

        DB()->query("REPLACE INTO {$table} {$updates}");

        // Update cache
        $this->loadFromDatabase($table, true, true);
    }

    /**
     * Get a section of the configuration
     */
    public function getSection(string $section): array
    {
        return $this->config[$section] ?? [];
    }

    /**
     * Get a nested value using dot notation
     */
    private function getNestedValue(string $key, mixed $default = null): mixed
    {
        $keys = explode('.', $key);
        $value = $this->config;

        foreach ($keys as $k) {
            if (!\is_array($value) || !\array_key_exists($k, $value)) {
                return $default;
            }
            $value = $value[$k];
        }

        return $value;
    }

    /**
     * Set a nested value using dot notation
     */
    private function setNestedValue(string $key, mixed $value): void
    {
        $keys = explode('.', $key);
        $target = &$this->config;

        foreach ($keys as $k) {
            if (!isset($target[$k]) || !\is_array($target[$k])) {
                $target[$k] = [];
            }
            $target = &$target[$k];
        }

        $target = $value;
    }

    /**
     * Validate table name to prevent SQL injection
     */
    private function validateTableName(string $table): void
    {
        if (!preg_match('/^[a-zA-Z_][a-zA-Z0-9_]*$/', $table)) {
            throw new InvalidArgumentException("Invalid table name: {$table}");
        }
    }
}
