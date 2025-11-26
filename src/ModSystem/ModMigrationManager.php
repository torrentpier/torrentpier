<?php
/**
 * TorrentPier Mod Migration Manager
 *
 * Handles database migrations for mods using simple SQL files.
 * All mod migrations are tracked in a single bb_mod_migrations table.
 *
 * @package TorrentPier\ModSystem
 * @author TorrentPier Team
 * @license MIT
 */

declare(strict_types=1);

namespace TorrentPier\ModSystem;

use Exception;

/**
 * Mod Migration Manager
 *
 * Simple migration system for mods:
 * - Each mod has migrations in mods/{mod_id}/migrations/
 * - Migrations are simple SQL files (001_create_tables.sql, etc.)
 * - All mods tracked in a single bb_mod_migrations table
 * - Supports rollback via .rollback.sql files
 */
class ModMigrationManager
{
    /**
     * Run pending migrations for a mod
     *
     * @param string $modId Mod identifier (e.g., 'karma-system')
     * @param string $migrationsPath Path to migrations directory
     * @return void
     * @throws Exception If migration fails
     */
    public function run(string $modId, string $migrationsPath): void
    {
        if (!is_dir($migrationsPath)) {
            return; // No migrations directory
        }

        // Get already executed migrations
        $executed = $this->getExecutedVersions($modId);

        // Get all migration files
        $files = glob($migrationsPath . '/*.sql');
        if (empty($files)) {
            return;
        }

        sort($files); // Execute in order

        foreach ($files as $file) {
            $filename = basename($file);

            // Skip rollback files
            if (str_ends_with($filename, '.rollback.sql')) {
                continue;
            }

            $version = $this->extractVersion($filename);

            if (in_array($version, $executed)) {
                continue; // Already executed
            }

            $this->executeMigration($modId, $version, $filename, $file);
        }
    }

    /**
     * Rollback migrations for a mod
     *
     * @param string $modId Mod identifier
     * @param int|null $targetVersion Version to rollback to (null = rollback all)
     * @return void
     * @throws Exception If rollback fails
     */
    public function rollback(string $modId, ?int $targetVersion = null): void
    {
        $executed = $this->getExecuted($modId);

        if (empty($executed)) {
            return; // Nothing to rollback
        }

        // Rollback in reverse order
        $executed = array_reverse($executed);

        foreach ($executed as $migration) {
            if ($targetVersion !== null && $migration['version'] <= $targetVersion) {
                break; // Stop at target version
            }

            $this->rollbackMigration($modId, $migration);
        }
    }

    /**
     * Get list of executed migrations for a mod
     *
     * @param string $modId Mod identifier
     * @return array
     */
    public function getExecuted(string $modId): array
    {
        $modId = DB()->escape($modId);
        return DB()->fetch_rowset("
            SELECT mod_id, version, migration_name, start_time, end_time
            FROM bb_mod_migrations
            WHERE mod_id = '$modId'
            ORDER BY version ASC
        ") ?: [];
    }

    /**
     * Get list of pending migrations
     *
     * @param string $modId Mod identifier
     * @param string $migrationsPath Path to migrations directory
     * @return array
     */
    public function getPending(string $modId, string $migrationsPath): array
    {
        if (!is_dir($migrationsPath)) {
            return [];
        }

        $executed = $this->getExecutedVersions($modId);
        $files = glob($migrationsPath . '/*.sql') ?: [];
        $pending = [];

        foreach ($files as $file) {
            $filename = basename($file);

            // Skip rollback files
            if (str_ends_with($filename, '.rollback.sql')) {
                continue;
            }

            $version = $this->extractVersion($filename);

            if (!in_array($version, $executed)) {
                $pending[] = $filename;
            }
        }

        sort($pending);
        return $pending;
    }

    /**
     * Get only version numbers of executed migrations
     *
     * @param string $modId Mod identifier
     * @return array
     */
    private function getExecutedVersions(string $modId): array
    {
        $modId = DB()->escape($modId);
        $rows = DB()->fetch_rowset("
            SELECT version FROM bb_mod_migrations WHERE mod_id = '$modId'
        ");

        return array_column($rows ?: [], 'version');
    }

    /**
     * Execute a single migration
     *
     * @param string $modId Mod identifier
     * @param int $version Migration version
     * @param string $filename Migration filename
     * @param string $filepath Full path to migration file
     * @return void
     * @throws Exception If migration fails
     */
    private function executeMigration(string $modId, int $version, string $filename, string $filepath): void
    {
        $sql = file_get_contents($filepath);

        if (empty($sql)) {
            throw new Exception("Migration file is empty: {$filename}");
        }

        $startTime = time();

        // Split SQL into individual statements
        // Remove comments and split on semicolons
        $statements = array_filter(
            array_map('trim', explode(';', $sql)),
            function ($stmt) {
                // Remove comments
                $stmt = preg_replace('/--[^\n]*\n/', '', $stmt);
                $stmt = trim($stmt);
                return !empty($stmt);
            }
        );

        // Execute each statement separately
        foreach ($statements as $statement) {
            DB()->sql_query($statement);
        }

        // Record migration
        $modId_escaped = DB()->escape($modId);
        $version_escaped = (int)$version;
        $filename_escaped = DB()->escape($filename);
        $startTime_escaped = (int)$startTime;
        $endTime_escaped = time();

        // Record migration in tracking table
        $insertSQL = "
            INSERT INTO bb_mod_migrations (mod_id, version, migration_name, start_time, end_time)
            VALUES ('$modId_escaped', $version_escaped, '$filename_escaped', NOW(), NOW())
        ";

        DB()->sql_query($insertSQL);

        if (function_exists('dev')) {
            dev()->log('mod_migrations', "Executed migration: {$modId}/{$filename}");
        }
    }

    /**
     * Rollback a single migration
     *
     * @param string $modId Mod identifier
     * @param array $migration Migration record
     * @return void
     * @throws Exception If rollback fails
     */
    private function rollbackMigration(string $modId, array $migration): void
    {
        $version = $migration['version'];
        $rollbackFile = str_replace('.sql', '.rollback.sql', $migration['migration_name']);

        // Look for rollback file in mods/{mod_id}/migrations/
        $modsPath = BB_ROOT . 'mods/' . $modId . '/migrations/';
        $rollbackPath = $modsPath . $rollbackFile;

        if (!file_exists($rollbackPath)) {
            throw new Exception("Rollback file not found: {$rollbackFile}");
        }

        $sql = file_get_contents($rollbackPath);

        // Split SQL into individual statements
        $statements = array_filter(
            array_map('trim', explode(';', $sql)),
            function ($stmt) {
                $stmt = preg_replace('/--[^\n]*\n/', '', $stmt);
                $stmt = trim($stmt);
                return !empty($stmt);
            }
        );

        // Execute rollback statements
        foreach ($statements as $statement) {
            DB()->sql_query($statement);
        }

        // Remove migration record
        $modId_escaped = DB()->escape($modId);
        $version_escaped = (int)$version;

        DB()->sql_query("
            DELETE FROM bb_mod_migrations
            WHERE mod_id = '$modId_escaped' AND version = $version_escaped
        ");

        if (function_exists('dev')) {
            dev()->log('mod_migrations', "Rolled back migration: {$modId}/{$rollbackFile}");
        }
    }

    /**
     * Extract version number from migration filename
     *
     * @param string $filename Migration filename (e.g., '001_create_tables.sql')
     * @return int Version number
     */
    private function extractVersion(string $filename): int
    {
        // Extract leading number from filename
        if (preg_match('/^(\d+)_/', $filename, $matches)) {
            return (int)$matches[1];
        }

        return 0;
    }
}
