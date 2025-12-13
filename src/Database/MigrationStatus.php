<?php

/**
 * TorrentPier – Bull-powered BitTorrent tracker engine
 *
 * @copyright Copyright (c) 2005-2025 TorrentPier (https://torrentpier.com)
 * @link      https://github.com/torrentpier/torrentpier for the canonical source repository
 * @license   https://github.com/torrentpier/torrentpier/blob/master/LICENSE MIT License
 */

namespace TorrentPier\Database;

use Exception;

/**
 * Migration Status Manager
 *
 * Provides read-only access to database migration status information
 * for the admin panel. Uses TorrentPier's database singleton and config system.
 */
class MigrationStatus
{
    private string $migrationTable;

    private string $migrationPath;

    private array $initialMigrations = [
        '20250619000001',
        '20250619000002',
    ];

    public function __construct()
    {
        $this->migrationTable = BB_MIGRATIONS;
        $this->migrationPath = BB_ROOT . 'database/migrations';
    }

    /**
     * Get complete migration status information
     *
     * @return array Migration status data including applied/pending migrations
     */
    public function getMigrationStatus(): array
    {
        try {
            // Check if migration table exists using Nette Database Explorer
            $tableExists = $this->checkMigrationTableExists();
            $setupStatus = $this->getSetupStatus();

            if (!$tableExists) {
                return [
                    'table_exists' => false,
                    'current_version' => null,
                    'applied_migrations' => [],
                    'pending_migrations' => $this->getAvailableMigrations(),
                    'setup_status' => $setupStatus,
                    'requires_setup' => $setupStatus['needs_setup'],
                ];
            }

            // Get applied migrations using Nette Database Explorer
            $appliedMigrations = DB()->query("
                SELECT version, migration_name, start_time, end_time
                FROM {$this->migrationTable}
                ORDER BY version ASC
            ")->fetchAll();

            // Convert Nette Result objects to arrays
            $appliedMigrationsArray = [];
            foreach ($appliedMigrations as $migration) {
                $appliedMigrationsArray[] = [
                    'version' => $migration->version,
                    'migration_name' => $migration->migration_name,
                    'start_time' => $migration->start_time,
                    'end_time' => $migration->end_time,
                ];
            }

            // Get current version (latest applied)
            $currentVersion = null;
            if (!empty($appliedMigrationsArray)) {
                $currentVersion = end($appliedMigrationsArray)['version'];
            }

            // Get pending migrations
            $availableMigrations = $this->getAvailableMigrations();
            $appliedVersions = array_column($appliedMigrationsArray, 'version');
            $pendingMigrations = array_filter($availableMigrations, function ($migration) use ($appliedVersions) {
                return !\in_array($migration['version'], $appliedVersions);
            });

            return [
                'table_exists' => true,
                'current_version' => $currentVersion,
                'applied_migrations' => $appliedMigrationsArray,
                'pending_migrations' => array_values($pendingMigrations),
                'setup_status' => $setupStatus,
                'requires_setup' => $setupStatus['needs_setup'],
            ];
        } catch (Exception $e) {
            bb_die('Error checking migration status: ' . $e->getMessage());
        }
    }

    /**
     * Get database schema information
     *
     * @return array Database statistics and information
     */
    public function getSchemaInfo(): array
    {
        try {
            // Get database name using Nette Database Explorer
            $dbInfo = DB()->query('SELECT DATABASE() as db_name')->fetch();

            // Get table count using Nette Database Explorer
            $tableInfo = DB()->query('
                SELECT COUNT(*) as table_count
                FROM information_schema.tables
                WHERE table_schema = DATABASE()
            ')->fetch();

            // Get database size using Nette Database Explorer
            $sizeInfo = DB()->query('
                SELECT
                    ROUND(SUM(data_length + index_length) / 1024 / 1024, 2) as size_mb
                FROM information_schema.tables
                WHERE table_schema = DATABASE()
            ')->fetch();

            return [
                'database_name' => $dbInfo->db_name ?? 'Unknown',
                'table_count' => $tableInfo->table_count ?? 0,
                'size_mb' => $sizeInfo->size_mb ?? 0,
            ];
        } catch (Exception $e) {
            return [
                'database_name' => 'Unknown',
                'table_count' => 0,
                'size_mb' => 0,
                'error' => $e->getMessage(),
            ];
        }
    }

    /**
     * Determine setup status for existing installations
     *
     * @return array Setup status information
     */
    private function getSetupStatus(): array
    {
        try {
            // Check if core TorrentPier tables exist (indicates existing installation)
            $coreTablesExist = $this->checkCoreTablesExist();
            $migrationTableExists = $this->checkMigrationTableExists();

            if (!$coreTablesExist) {
                // Fresh installation
                return [
                    'type' => 'fresh',
                    'needs_setup' => false,
                    'message' => 'Fresh installation - migrations will run normally',
                    'action_required' => false,
                ];
            }

            if (!$migrationTableExists) {
                // Existing installation without migration system
                return [
                    'type' => 'existing_needs_setup',
                    'needs_setup' => true,
                    'message' => 'Existing installation detected - migration setup required',
                    'action_required' => true,
                    'instructions' => 'Mark initial migrations as applied using --fake flag',
                ];
            }

            // Check if initial migrations are marked as applied
            $initialMigrationsApplied = $this->checkInitialMigrationsApplied();

            if (!$initialMigrationsApplied) {
                return [
                    'type' => 'existing_partial_setup',
                    'needs_setup' => true,
                    'message' => 'Migration table exists but initial migrations not marked as applied',
                    'action_required' => true,
                    'instructions' => 'Run: php vendor/bin/phinx migrate --fake --target=20250619000002',
                ];
            }

            // Fully set up
            return [
                'type' => 'fully_setup',
                'needs_setup' => false,
                'message' => 'Migration system fully configured',
                'action_required' => false,
            ];
        } catch (Exception $e) {
            return [
                'type' => 'error',
                'needs_setup' => false,
                'message' => 'Error detecting setup status: ' . $e->getMessage(),
                'action_required' => false,
            ];
        }
    }

    /**
     * Check if core TorrentPier tables exist
     *
     * @return bool True if core tables exist
     */
    private function checkCoreTablesExist(): bool
    {
        try {
            $coreTable = 'bb_users'; // Key table that should exist in any TorrentPier installation
            $escapedTable = DB()->escape($coreTable);
            $result = DB()->query("
                SELECT COUNT(*) as table_count
                FROM information_schema.tables
                WHERE table_schema = DATABASE()
                AND table_name = '{$escapedTable}'
            ")->fetch();

            return $result && $result->table_count > 0;
        } catch (Exception $e) {
            return false;
        }
    }

    /**
     * Check if initial migrations are marked as applied
     *
     * @return bool True if initial migrations are applied
     */
    private function checkInitialMigrationsApplied(): bool
    {
        try {
            $initialMigrationsCSV = implode(',', $this->initialMigrations);
            $result = DB()->query("
                SELECT COUNT(*) as migration_count
                FROM {$this->migrationTable}
                WHERE version IN ({$initialMigrationsCSV})
            ")->fetch();

            return $result && $result->migration_count >= \count($this->initialMigrations);
        } catch (Exception $e) {
            return false;
        }
    }

    /**
     * Check if migration table exists
     *
     * @return bool True if table exists, false otherwise
     */
    private function checkMigrationTableExists(): bool
    {
        try {
            // Using simple query without parameters to avoid issues
            $escapedTable = DB()->escape($this->migrationTable);
            $result = DB()->query("
                SELECT COUNT(*) as table_count
                FROM information_schema.tables
                WHERE table_schema = DATABASE()
                AND table_name = '{$escapedTable}'
            ")->fetch();

            return $result && $result->table_count > 0;
        } catch (Exception $e) {
            return false;
        }
    }

    /**
     * Get available migrations from filesystem
     *
     * @return array List of available migration files
     */
    private function getAvailableMigrations(): array
    {
        $migrations = [];

        if (is_dir($this->migrationPath)) {
            $files = glob($this->migrationPath . '/*.php');
            foreach ($files as $file) {
                $filename = basename($file);
                if (preg_match('/^(\d+)_(.+)\.php$/', $filename, $matches)) {
                    $migrations[] = [
                        'version' => $matches[1],
                        'name' => $matches[2],
                        'filename' => $filename,
                        'file_path' => $file,
                    ];
                }
            }
        }

        // Sort by version
        usort($migrations, function ($a, $b) {
            return strcmp($a['version'], $b['version']);
        });

        return $migrations;
    }
}
