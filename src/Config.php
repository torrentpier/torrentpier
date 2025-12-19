<?php

/**
 * TorrentPier – Bull-powered BitTorrent tracker engine
 *
 * @copyright Copyright (c) 2005-2025 TorrentPier (https://torrentpier.com)
 * @link      https://github.com/torrentpier/torrentpier for the canonical source repository
 * @license   https://github.com/torrentpier/torrentpier/blob/master/LICENSE MIT License
 */

namespace TorrentPier;

use Illuminate\Config\Repository;
use InvalidArgumentException;

/**
 * Configuration management class
 *
 * Extends Illuminate\Config\Repository for Laravel 12 compatibility
 * while providing TorrentPier-specific database configuration methods.
 */
class Config extends Repository
{
    /**
     * Load configuration from the database table
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
        return $this->get($section, []);
    }

    /**
     * Merge additional configuration values
     */
    public function merge(array $config): void
    {
        foreach ($config as $key => $value) {
            if (\is_array($value) && \is_array($existing = $this->get($key))) {
                $value = array_replace_recursive($existing, $value);
            }
            $this->set($key, $value);
        }
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
