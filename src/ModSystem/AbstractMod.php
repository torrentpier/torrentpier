<?php

declare(strict_types=1);

namespace TorrentPier\ModSystem;

use TorrentPier\Database\Database;
use TorrentPier\Cache\CacheManager;

/**
 * AbstractMod - Base class for all mods
 *
 * Provides common functionality and lifecycle hooks for mods
 *
 * @package TorrentPier\ModSystem
 * @since 3.0.0
 */
abstract class AbstractMod
{
    /**
     * Mod manifest data
     */
    protected array $manifest;

    /**
     * Mod directory path
     */
    protected string $path;

    /**
     * Mod ID (from manifest)
     */
    protected string $id;

    /**
     * Mod version (from manifest)
     */
    protected string $version;

    /**
     * Database instance
     */
    private Database $db;

    /**
     * Cache instance
     */
    private CacheManager $cache;

    /**
     * Constructor
     *
     * @param array $manifest Mod manifest data
     * @param string $path Mod directory path
     */
    public function __construct(array $manifest, string $path)
    {
        $this->manifest = $manifest;
        $this->path = $path;
        $this->id = $manifest['id'];
        $this->version = $manifest['version'];

        $this->db = DB();
        $this->cache = CACHE("mod_{$this->id}");
    }

    // ========================================================================
    // LIFECYCLE HOOKS (Override in child class)
    // ========================================================================

    /**
     * Called on every request when mod is loaded
     *
     * Use this to:
     * - Register hooks and filters
     * - Initialize runtime components
     * - Set up event listeners
     *
     * This is called EVERY time the mod is loaded (on each request),
     * unlike activate() which is called only once during installation.
     *
     * @return void
     */
    abstract public function boot(): void;

    /**
     * Called when mod is activated
     *
     * Use this to:
     * - Run database migrations
     * - Register permissions
     * - Set up initial data
     *
     * @return void
     * @throws ModException
     */
    public function activate(): void
    {
        // Override in child class
    }

    /**
     * Called when mod is deactivated
     *
     * Use this to:
     * - Unregister hooks
     * - Clear caches
     * - Disable features
     *
     * Note: Should NOT delete data or drop tables (use uninstall for that)
     *
     * @return void
     * @throws ModException
     */
    public function deactivate(): void
    {
        // Override in child class
    }

    /**
     * Called when mod is completely uninstalled
     *
     * Use this to:
     * - Drop database tables
     * - Delete mod data
     * - Remove configuration
     *
     * @return void
     * @throws ModException
     */
    public function uninstall(): void
    {
        // Override in child class
    }

    /**
     * Called when mod is upgraded to a new version
     *
     * @param string $oldVersion Previous version
     * @return void
     * @throws ModException
     */
    public function upgrade(string $oldVersion): void
    {
        // Override in child class
    }

    // ========================================================================
    // HELPER METHODS (Use in child class)
    // ========================================================================

    /**
     * Get mod configuration value
     *
     * Reads from config('mods.{mod_id}.{key}') with fallback to default
     *
     * @param string $key Config key
     * @param mixed $default Default value if not found
     * @return mixed Config value
     */
    protected function config(string $key, mixed $default = null): mixed
    {
        return config()->get("mods.{$this->id}.{$key}", $default);
    }

    /**
     * Set mod configuration value
     *
     * Writes to config('mods.{mod_id}.{key}')
     *
     * @param string $key Config key
     * @param mixed $value Config value
     * @return void
     */
    protected function setConfig(string $key, mixed $value): void
    {
        config()->set("mods.{$this->id}.{$key}", $value);
    }

    /**
     * Run database migrations from directory
     *
     * @param string $migrationsPath Path to migrations directory (relative to mod root)
     * @return void
     * @throws ModException
     */
    protected function runMigrations(string $migrationsPath): void
    {
        $fullPath = $this->path . '/' . ltrim($migrationsPath, '/');

        if (!is_dir($fullPath)) {
            throw new ModException(
                "Migrations directory not found: {$fullPath}",
                ModException::FILE_OPERATION_ERROR,
                ['mod_id' => $this->id, 'path' => $fullPath]
            );
        }

        $files = glob($fullPath . '/*.sql');

        if (empty($files)) {
            return; // No migrations to run
        }

        sort($files); // Run in alphabetical order

        foreach ($files as $file) {
            try {
                $sql = file_get_contents($file);

                // Execute SQL (split by semicolons for multiple statements)
                $statements = array_filter(
                    array_map('trim', explode(';', $sql)),
                    fn($s) => !empty($s)
                );

                foreach ($statements as $statement) {
                    $this->db->query($statement);
                }

                $this->log('migration', "Executed migration: " . basename($file));
            } catch (\Exception $e) {
                throw new ModException(
                    "Migration failed: " . basename($file) . " - " . $e->getMessage(),
                    ModException::DATABASE_ERROR,
                    ['mod_id' => $this->id, 'file' => $file]
                );
            }
        }
    }

    /**
     * Register mod permissions
     *
     * @param array $permissions Associative array of permission_key => description
     * @return void
     */
    protected function registerPermissions(array $permissions): void
    {
        // Store permissions in mod_permissions table (if implemented)
        // For now, log them
        foreach ($permissions as $key => $description) {
            $this->log('permission_register', "Registered permission: {$key} - {$description}");
        }

        // TODO: Integrate with TorrentPier's permission system
    }

    /**
     * Log mod activity
     *
     * @param string $action Action being performed
     * @param string $message Log message
     * @param array|null $details Additional details (will be JSON encoded)
     * @return void
     */
    protected function log(string $action, string $message, ?array $details = null): void
    {
        try {
            $stmt = $this->db->prepare("
                INSERT INTO bb_mod_logs (mod_id, action, message, details, created_at)
                VALUES (?, ?, ?, ?, NOW())
            ");

            $stmt->execute([
                $this->id,
                $action,
                $message,
                $details ? json_encode($details) : null
            ]);
        } catch (\Exception $e) {
            // Silent fail to prevent breaking mod
            error_log("AbstractMod [{$this->id}]: Failed to log - " . $e->getMessage());
        }
    }

    /**
     * Get database instance
     *
     * @return Database
     */
    protected function getDb(): Database
    {
        return $this->db;
    }

    /**
     * Get cache instance
     *
     * @return CacheManager
     */
    protected function getCache(): CacheManager
    {
        return $this->cache;
    }

    /**
     * Get mod-specific cache key
     *
     * Prefixes key with mod ID to prevent conflicts
     *
     * @param string $key Cache key
     * @return string Prefixed cache key
     */
    protected function getCacheKey(string $key): string
    {
        return "mod.{$this->id}.{$key}";
    }

    /**
     * Add action hook
     *
     * Convenience wrapper for hooks()->add_action()
     *
     * @param string $hook Hook name
     * @param callable $callback Callback function
     * @param int $priority Priority (default: 10)
     * @param int $accepted_args Number of arguments (default: 1)
     * @return void
     */
    protected function addAction(string $hook, callable $callback, int $priority = 10, int $accepted_args = 1): void
    {
        \hooks()->add_action($hook, $callback, $priority, $accepted_args);
    }

    /**
     * Add filter hook
     *
     * Convenience wrapper for hooks()->add_filter()
     *
     * @param string $hook Hook name
     * @param callable $callback Callback function
     * @param int $priority Priority (default: 10)
     * @param int $accepted_args Number of arguments (default: 1)
     * @return void
     */
    protected function addFilter(string $hook, callable $callback, int $priority = 10, int $accepted_args = 1): void
    {
        \hooks()->add_filter($hook, $callback, $priority, $accepted_args);
    }

    /**
     * Include mod file
     *
     * @param string $file File path relative to mod root
     * @return void
     * @throws ModException
     */
    protected function includeFile(string $file): void
    {
        $fullPath = $this->path . '/' . ltrim($file, '/');

        if (!file_exists($fullPath)) {
            throw new ModException(
                "File not found: {$fullPath}",
                ModException::FILE_OPERATION_ERROR,
                ['mod_id' => $this->id, 'file' => $file]
            );
        }

        require_once $fullPath;
    }

    /**
     * Check if table exists
     *
     * @param string $tableName Table name (without prefix)
     * @return bool
     */
    protected function tableExists(string $tableName): bool
    {
        try {
            $result = $this->db->query("SHOW TABLES LIKE '{$tableName}'");
            return $result->rowCount() > 0;
        } catch (\Exception $e) {
            return false;
        }
    }

    // ========================================================================
    // STATE QUERY METHODS
    // ========================================================================

    /**
     * Check if mod is active
     *
     * @return bool
     */
    public function isActive(): bool
    {
        $stmt = $this->db->prepare("SELECT is_active FROM bb_mods WHERE mod_id = ?");
        $stmt->execute([$this->id]);

        $result = $stmt->fetch(\PDO::FETCH_ASSOC);

        return $result !== false && $result['is_active'] == 1;
    }

    /**
     * Get mod version
     *
     * @return string
     */
    public function getVersion(): string
    {
        return $this->version;
    }

    /**
     * Get mod ID
     *
     * @return string
     */
    public function getId(): string
    {
        return $this->id;
    }

    /**
     * Get mod name
     *
     * @return string
     */
    public function getName(): string
    {
        return $this->manifest['name'] ?? $this->id;
    }

    /**
     * Get mod description
     *
     * @return string
     */
    public function getDescription(): string
    {
        return $this->manifest['description'] ?? '';
    }

    /**
     * Get mod author
     *
     * @return string
     */
    public function getAuthor(): string
    {
        return $this->manifest['author'] ?? '';
    }

    /**
     * Get full manifest data
     *
     * @return array
     */
    public function getManifest(): array
    {
        return $this->manifest;
    }

    /**
     * Get mod path
     *
     * @return string
     */
    public function getPath(): string
    {
        return $this->path;
    }
}
