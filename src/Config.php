<?php
/**
 * TorrentPier – Bull-powered BitTorrent tracker engine
 *
 * @copyright Copyright (c) 2005-2025 TorrentPier (https://torrentpier.com)
 * @link      https://github.com/torrentpier/torrentpier for the canonical source repository
 * @license   https://github.com/torrentpier/torrentpier/blob/master/LICENSE MIT License
 */

namespace TorrentPier;

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
     * Get the singleton instance of Config
     */
    public static function getInstance(array $config = []): Config
    {
        if (self::$instance === null) {
            self::$instance = new self($config);
        }
        return self::$instance;
    }

    /**
     * Initialize the config with the global $bb_cfg array
     */
    public static function init(array $bb_cfg): Config
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

        return array_key_exists($key, $this->config);
    }

    /**
     * Get all configuration values
     */
    public function all(): array
    {
        return $this->config;
    }

    /**
     * Get a nested value using dot notation
     */
    private function getNestedValue(string $key, mixed $default = null): mixed
    {
        $keys = explode('.', $key);
        $value = $this->config;

        foreach ($keys as $k) {
            if (!is_array($value) || !array_key_exists($k, $value)) {
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
            if (!isset($target[$k]) || !is_array($target[$k])) {
                $target[$k] = [];
            }
            $target = &$target[$k];
        }

        $target = $value;
    }

    /**
     * Merge additional configuration values
     */
    public function merge(array $config): void
    {
        $this->config = array_merge_recursive($this->config, $config);
    }

    /**
     * Get a section of the configuration
     */
    public function getSection(string $section): array
    {
        return $this->config[$section] ?? [];
    }

    // ========================================================================
    // MOD CONFIGURATION NAMESPACE ISOLATION
    // ========================================================================

    /**
     * Get mod-specific configuration value
     *
     * Reads from 'mods.{mod_id}.{key}' namespace
     *
     * @param string $modId Mod identifier
     * @param string $key Config key
     * @param mixed $default Default value if not found
     * @return mixed Config value
     */
    public function getModConfig(string $modId, string $key, mixed $default = null): mixed
    {
        return $this->get("mods.{$modId}.{$key}", $default);
    }

    /**
     * Set mod-specific configuration value
     *
     * Writes to 'mods.{mod_id}.{key}' namespace
     *
     * @param string $modId Mod identifier
     * @param string $key Config key
     * @param mixed $value Config value
     * @return void
     */
    public function setModConfig(string $modId, string $key, mixed $value): void
    {
        $this->set("mods.{$modId}.{$key}", $value);
    }

    /**
     * Get all configuration for a specific mod
     *
     * Returns entire 'mods.{mod_id}' namespace
     *
     * @param string $modId Mod identifier
     * @return array Mod configuration array
     */
    public function getAllModConfig(string $modId): array
    {
        return $this->get("mods.{$modId}", []);
    }

    /**
     * Check if mod-specific configuration key exists
     *
     * @param string $modId Mod identifier
     * @param string $key Config key
     * @return bool
     */
    public function hasModConfig(string $modId, string $key): bool
    {
        return $this->has("mods.{$modId}.{$key}");
    }

    /**
     * Delete mod-specific configuration key
     *
     * @param string $modId Mod identifier
     * @param string $key Config key
     * @return void
     */
    public function deleteModConfig(string $modId, string $key): void
    {
        $allModConfig = $this->getAllModConfig($modId);

        if (str_contains($key, '.')) {
            // Handle nested keys
            $keys = explode('.', $key);
            $target = &$allModConfig;

            foreach ($keys as $i => $k) {
                if ($i === count($keys) - 1) {
                    unset($target[$k]);
                } else {
                    if (!isset($target[$k]) || !is_array($target[$k])) {
                        return; // Key path doesn't exist
                    }
                    $target = &$target[$k];
                }
            }
        } else {
            unset($allModConfig[$key]);
        }

        // Update the entire mod config
        $this->set("mods.{$modId}", $allModConfig);
    }

    /**
     * Clear all configuration for a specific mod
     *
     * Removes entire 'mods.{mod_id}' namespace
     *
     * @param string $modId Mod identifier
     * @return void
     */
    public function clearModConfig(string $modId): void
    {
        if (isset($this->config['mods'][$modId])) {
            unset($this->config['mods'][$modId]);
        }
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
        throw new \LogicException("Cannot serialize a singleton.");
    }

    /**
     * Prevent unserialization of the singleton instance
     */
    public function __unserialize(array $data): void
    {
        throw new \LogicException("Cannot unserialize a singleton.");
    }
}
