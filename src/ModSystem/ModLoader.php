<?php

declare(strict_types=1);

namespace TorrentPier\ModSystem;

use Exception;
use TorrentPier\Database\Database;
use TorrentPier\Cache\CacheManager;

/**
 * ModLoader - Discovers, validates, and loads mods
 *
 * @package TorrentPier\ModSystem
 * @since 3.0.0
 */
class ModLoader
{
    /**
     * Singleton instance
     */
    private static ?ModLoader $instance = null;

    /**
     * Path to mods directory
     */
    private string $modsPath;

    /**
     * Cache instance
     */
    private CacheManager $cache;

    /**
     * Database instance
     */
    private Database $db;

    /**
     * Discovered mods (cached)
     *
     * @var array<string, array>
     */
    private array $discoveredMods = [];

    /**
     * Active mod instances
     *
     * @var array<string, AbstractMod>
     */
    private array $activeMods = [];

    /**
     * Cache TTL for discovered mods (24 hours)
     */
    private const CACHE_TTL = 86400;

    /**
     * Cache key for discovered mods
     */
    private const CACHE_KEY = 'mod_system.discovered_mods';

    /**
     * Required manifest fields
     */
    private const REQUIRED_FIELDS = ['id', 'name', 'version', 'entrypoint'];

    /**
     * Constructor
     *
     * @param string|null $modsPath Custom mods path (for testing)
     */
    public function __construct(?string $modsPath = null)
    {
        $this->modsPath = $modsPath ?? BB_ROOT . '/mods';
        $this->cache = CACHE('mod_system');
        $this->db = DB();

        // Ensure the mods directory exists
        if (!is_dir($this->modsPath)) {
            @mkdir($this->modsPath, 0755, true);
        }
    }

    /**
     * Get ModLoader singleton instance
     *
     * @param string|null $modsPath Custom mods path (for testing)
     * @return ModLoader
     */
    public static function getInstance(?string $modsPath = null): ModLoader
    {
        if (self::$instance === null) {
            self::$instance = new self($modsPath);
        }
        return self::$instance;
    }

    /**
     * Safe dev logging wrapper for test environment compatibility
     *
     * @param string $category Log category
     * @param string $message Log message
     * @param array|null $context Optional context data
     * @return void
     */
    private function devLog(string $category, string $message, ?array $context = null): void
    {
        if (function_exists('dev') && ($dev = dev()) && method_exists($dev, 'log')) {
            $dev->log($category, $message, $context ?? []);
        }
    }

    /**
     * Reset singleton instance (for testing)
     *
     * @return void
     */
    public static function resetInstance(): void
    {
        self::$instance = null;
    }

    /**
     * Discover all mods in the mods directory
     *
     * Scans /mods/ directory for subdirectories containing manifest.json
     * Results are cached for 24 hours
     *
     * @param bool $forceRefresh Force cache refresh
     * @return array<string, array> Associative array of mod_id => manifest data
     */
    public function discoverMods(bool $forceRefresh = false): array
    {
        $this->devLog('mod_system', 'discoverMods() called', ['force_refresh' => $forceRefresh]);

        // Return the cached result if available
        if (!$forceRefresh && !empty($this->discoveredMods)) {
            $this->devLog('mod_system', 'Returning cached discovered mods: ' . count($this->discoveredMods));
            return $this->discoveredMods;
        }

        // Try to get from the cache
        if (!$forceRefresh) {
            $cached = $this->cache->get(self::CACHE_KEY);
            if ($cached !== false) {
                $this->devLog('mod_system', 'Loaded from cache: ' . count($cached) . ' mods');
                $this->discoveredMods = $cached;
                return $this->discoveredMods;
            }
        }

        $mods = [];

        // Scan mods directory
        if (!is_dir($this->modsPath)) {
            $this->devLog('mod_system', 'Mods directory not found: ' . $this->modsPath);
            $this->discoveredMods = $mods;
            return $mods;
        }

        $directories = scandir($this->modsPath);
        $this->devLog('mod_system', 'Scanning directory: ' . $this->modsPath, ['directories' => implode(', ', $directories)]);

        foreach ($directories as $dir) {
            // Skip special directories and files
            if ($dir === '.' || $dir === '..' || !is_dir($this->modsPath . '/' . $dir)) {
                continue;
            }

            $this->devLog('mod_system', "Checking directory: {$dir}");

            $manifestPath = $this->modsPath . '/' . $dir . '/manifest.json';

            // Skip if no manifest.json
            if (!file_exists($manifestPath)) {
                $this->devLog('mod_system', "  ✗ No manifest.json in {$dir}");
                continue;
            }

            $this->devLog('mod_system', "  ✓ Found manifest: {$manifestPath}");

            try {
                $manifest = $this->loadManifest($manifestPath);
                $this->devLog('mod_system', "  ✓ Manifest loaded, ID: " . ($manifest['id'] ?? 'MISSING'));

                // Validate manifest
                if (!$this->validateManifest($manifest, $manifestPath)) {
                    $this->devLog('mod_system', "  ✗ Manifest validation failed for {$dir}");
                    continue;
                }

                // Store with mod ID as a key
                $modId = $manifest['id'];
                $manifest['_path'] = $this->modsPath . '/' . $dir;
                $manifest['_manifest_path'] = $manifestPath;

                $mods[$modId] = $manifest;
                $this->devLog('mod_system', "  ✓ Mod added to discovered list: {$modId}");
            } catch (ModException $e) {
                $this->devLog('mod_system', "  ✗ ModException in {$dir}: " . $e->getMessage());
                // Log error but continue discovering other mods
                $this->logError($dir, 'discover', $e->getMessage(), $e->getContext());
            }
        }

        $this->devLog('mod_system', 'Discovery complete. Total mods found: ' . count($mods), ['mod_ids' => array_keys($mods)]);

        $this->discoveredMods = $mods;

        // Cache for 24 hours
        $this->cache->set(self::CACHE_KEY, $mods, self::CACHE_TTL);

        return $mods;
    }

    /**
     * Load active mods from a database and instantiate them
     *
     * @return void
     * @throws ModException
     */
    public function loadActiveMods(): void
    {
        // Query active mods
        $activeMods = $this->db->fetch_rowset("
            SELECT mod_id, name, version, manifest_path
            FROM bb_mods
            WHERE is_active = 1
            ORDER BY installed_at ASC
        ");

        $this->devLog('mod_system', 'Found ' . count($activeMods) . ' active mods in database', $activeMods);

        foreach ($activeMods as $modData) {
            try {
                $this->devLog('mod_system', "Attempting to load mod: {$modData['mod_id']}");

                $mod = $this->loadMod($modData['mod_id']);

                if ($mod !== null) {
                    $this->activeMods[$modData['mod_id']] = $mod;
                    $this->devLog('mod_system', "✓ Successfully loaded mod: {$modData['mod_id']}");
                } else {
                    $this->devLog('mod_system', "✗ Failed to load mod (returned null): {$modData['mod_id']}");
                }
            } catch (ModException $e) {
                $this->devLog('mod_system', "✗ ModException loading {$modData['mod_id']}: " . $e->getMessage());

                // Log error but continue loading other mods
                $this->logError($modData['mod_id'], 'load', $e->getMessage(), $e->getContext());

                // Optionally deactivate broken mod
                if (config()->get('mods.auto_deactivate_broken', true)) {
                    $this->deactivateModInDb($modData['mod_id']);
                }
            }
        }
    }

    /**
     * Load a specific mod by ID
     *
     * @param string $modId Mod identifier
     * @return AbstractMod|null Mod instance or null if not found
     * @throws ModException
     */
    public function loadMod(string $modId): ?AbstractMod
    {
        // Check if already loaded
        if (isset($this->activeMods[$modId])) {
            return $this->activeMods[$modId];
        }

        // Discover mods if not done yet
        if (empty($this->discoveredMods)) {
            $this->discoverMods();
        }

        // Return a cached instance if already loaded
        if (isset($this->activeMods[$modId])) {
            return $this->activeMods[$modId];
        }

        // Check if mod exists
        if (!isset($this->discoveredMods[$modId])) {
            throw new ModException(
                "Mod not found: {$modId}",
                ModException::MOD_NOT_FOUND,
                ['mod_id' => $modId]
            );
        }

        $manifest = $this->discoveredMods[$modId];
        $modPath = $manifest['_path'];
        $entrypoint = $modPath . '/' . $manifest['entrypoint'];

        // Check if the entrypoint exists
        if (!file_exists($entrypoint)) {
            throw new ModException(
                "Mod entrypoint not found: {$entrypoint}",
                ModException::FILE_OPERATION_ERROR,
                ['mod_id' => $modId, 'entrypoint' => $entrypoint]
            );
        }

        // Load entrypoint file
        require_once $entrypoint;

        // Register autoloader for mod's src/ directory if it exists
        $srcPath = $modPath . '/src';
        if (is_dir($srcPath)) {
            spl_autoload_register(function ($class) use ($srcPath, $modId) {
                // Convert mod ID to namespace prefix
                // karma-system -> TorrentPier\Mod\KarmaSystem
                $parts = preg_split('/[-_]/', $modId);
                $namespace = 'TorrentPier\\Mod\\' . implode('', array_map('ucfirst', $parts)) . '\\';

                // Check if this class belongs to this mod
                if (!str_starts_with($class, $namespace)) {
                    return;
                }

                // Get the relative class name
                $relativeClass = substr($class, strlen($namespace));

                // Convert namespace separators to directory separators
                $file = $srcPath . '/' . str_replace('\\', '/', $relativeClass) . '.php';

                if (file_exists($file)) {
                    require_once $file;
                }
            });
        }

        // Determine class name (convention: ID in PascalCase + "Mod")
        // e.g., karma → KarmaMod, auto-mod → AutoModMod
        $className = $this->getModClassName($modId);

        // Check if a class exists
        if (!class_exists($className)) {
            throw new ModException(
                "Mod class not found: {$className}",
                ModException::FILE_OPERATION_ERROR,
                ['mod_id' => $modId, 'class' => $className]
            );
        }

        // Instantiate mod
        $mod = new $className($manifest, $modPath);

        // Verify it extends AbstractMod
        if (!$mod instanceof AbstractMod) {
            throw new ModException(
                "Mod class must extend AbstractMod: {$className}",
                ModException::MANIFEST_INVALID_SCHEMA,
                ['mod_id' => $modId, 'class' => $className]
            );
        }

        // Boot the mod (register hooks, initialize runtime components)
        $this->devLog('mod_system', "Calling boot() on mod: {$modId}");
        try {
            $mod->boot();
            $this->devLog('mod_system', "✓ boot() completed for: {$modId}");
        } catch (Exception $e) {
            $this->devLog('mod_system', "✗ boot() failed for {$modId}: " . $e->getMessage());
            throw new ModException(
                "Mod boot failed: {$modId} - " . $e->getMessage(),
                ModException::BOOT_ERROR,
                ['mod_id' => $modId, 'error' => $e->getMessage()]
            );
        }

        // Cache the instance
        $this->activeMods[$modId] = $mod;

        return $mod;
    }

    /**
     * Get installed mods from database
     *
     * @return array<int, array> Array of mod data
     */
    public function getInstalledMods(): array
    {
        return $this->db->fetch_rowset("
            SELECT *
            FROM bb_mods
            ORDER BY installed_at DESC
        ");
    }

    /**
     * Get active mods instances
     *
     * @return array<string, AbstractMod>
     */
    public function getActiveMods(): array
    {
        return $this->activeMods;
    }

    /**
     * Get mod directory path
     *
     * @return string
     */
    public function getModsPath(): string
    {
        return $this->modsPath;
    }

    /**
     * Check if a mod is installed
     *
     * @param string $modId Mod ID
     * @return bool
     */
    public function isInstalled(string $modId): bool
    {
        $result = $this->db->fetch_row("
            SELECT COUNT(*) as count
            FROM bb_mods
            WHERE mod_id = '" . $this->db->escape($modId) . "'
        ", 'count');

        return (int)$result > 0;
    }

    /**
     * Check if a mod is active
     *
     * @param string $modId Mod ID
     * @return bool
     */
    public function isActive(string $modId): bool
    {
        $result = $this->db->fetch_row("
            SELECT is_active
            FROM bb_mods
            WHERE mod_id = '" . $this->db->escape($modId) . "'
        ", 'is_active');

        return $result !== false && (int)$result === 1;
    }

    /**
     * Clear mods cache
     *
     * @return void
     */
    public function clearCache(): void
    {
        $this->cache->rm(self::CACHE_KEY);
        $this->discoveredMods = [];
    }

    /**
     * Load the manifest.json file
     *
     * @param string $path Path to manifest.json
     * @return array Manifest data
     * @throws ModException
     */
    private function loadManifest(string $path): array
    {
        if (!file_exists($path)) {
            throw new ModException(
                "Manifest file not found: {$path}",
                ModException::MANIFEST_NOT_FOUND,
                ['path' => $path]
            );
        }

        $content = file_get_contents($path);
        $manifest = json_decode($content, true);

        if (json_last_error() !== JSON_ERROR_NONE) {
            throw new ModException(
                "Invalid JSON in manifest: " . json_last_error_msg(),
                ModException::MANIFEST_INVALID_JSON,
                ['path' => $path, 'json_error' => json_last_error_msg()]
            );
        }

        return $manifest;
    }

    /**
     * Validate manifest structure
     *
     * @param array $manifest Manifest data
     * @param string $path Manifest path (for error messages)
     * @return bool
     * @throws ModException
     */
    public function validateManifest(array $manifest, string $path = ''): bool
    {
        // Check required fields
        foreach (self::REQUIRED_FIELDS as $field) {
            if (empty($manifest[$field])) {
                throw new ModException(
                    "Missing required field in manifest: {$field}",
                    ModException::MANIFEST_MISSING_FIELD,
                    ['field' => $field, 'path' => $path]
                );
            }
        }

        // Validate mod ID format (alphanumeric, hyphens, underscores only)
        if (!preg_match('/^[a-z0-9_-]+$/', $manifest['id'])) {
            throw new ModException(
                "Invalid mod ID format: {$manifest['id']}",
                ModException::MANIFEST_INVALID_SCHEMA,
                ['mod_id' => $manifest['id'], 'path' => $path]
            );
        }

        // Validate version format (semantic versioning)
        if (!preg_match('/^\d+\.\d+\.\d+/', $manifest['version'])) {
            throw new ModException(
                "Invalid version format: {$manifest['version']}",
                ModException::MANIFEST_INVALID_SCHEMA,
                ['version' => $manifest['version'], 'path' => $path]
            );
        }

        // Check compatibility
        if (isset($manifest['requires'])) {
            $this->checkCompatibility($manifest);
        }

        return true;
    }

    /**
     * Check mod compatibility with TorrentPier and PHP versions
     *
     * @param array $manifest Manifest data
     * @return bool
     * @throws ModException
     */
    public function checkCompatibility(array $manifest): bool
    {
        $requires = $manifest['requires'] ?? [];

        // Check TorrentPier version
        if (isset($requires['torrentpier'])) {
            $requiredVersion = $requires['torrentpier'];
            $currentVersion = config()->get('tp_version') ?? 'v0.0.0';

            if (!$this->versionSatisfies($currentVersion, $requiredVersion)) {
                throw new ModException(
                    "Mod requires TorrentPier {$requiredVersion}, current version: {$currentVersion}",
                    ModException::COMPATIBILITY_TP_VERSION,
                    [
                        'mod_id' => $manifest['id'],
                        'required' => $requiredVersion,
                        'current' => $currentVersion
                    ]
                );
            }
        }

        // Check PHP version
        if (isset($requires['php'])) {
            $requiredVersion = $requires['php'];
            $currentVersion = PHP_VERSION;

            if (!$this->versionSatisfies($currentVersion, $requiredVersion)) {
                throw new ModException(
                    "Mod requires PHP {$requiredVersion}, current version: {$currentVersion}",
                    ModException::COMPATIBILITY_PHP_VERSION,
                    [
                        'mod_id' => $manifest['id'],
                        'required' => $requiredVersion,
                        'current' => $currentVersion
                    ]
                );
            }
        }

        // Check mod dependencies
        if (isset($requires['mods']) && is_array($requires['mods'])) {
            foreach ($requires['mods'] as $dependencyId) {
                if (!$this->isActive($dependencyId)) {
                    throw new ModException(
                        "Mod requires active dependency: {$dependencyId}",
                        ModException::COMPATIBILITY_MISSING_DEPENDENCY,
                        [
                            'mod_id' => $manifest['id'],
                            'dependency' => $dependencyId
                        ]
                    );
                }
            }
        }

        return true;
    }

    /**
     * Check if version satisfies requirement
     *
     * Supports: >=1.0.0, >1.0.0, <2.0.0, <=2.0.0, ^1.0.0, ~1.2.0
     *
     * @param string $version Current version
     * @param string $requirement Required version string
     * @return bool
     */
    private function versionSatisfies(string $version, string $requirement): bool
    {
        // Normalize a version by removing 'v' or 'V' prefix
        $version = ltrim($version, 'vV');

        // Parse operators
        if (preg_match('/^([><=~^]+)\s*(.+)$/', $requirement, $matches)) {
            $operator = $matches[1];
            $requiredVersion = ltrim($matches[2], 'vV'); // Normalize a required version too

            switch ($operator) {
                case '>=':
                    return version_compare($version, $requiredVersion, '>=');
                case '>':
                    return version_compare($version, $requiredVersion, '>');
                case '<=':
                    return version_compare($version, $requiredVersion, '<=');
                case '<':
                    return version_compare($version, $requiredVersion, '<');
                case '^': // Compatible with (the same major version)
                    $vParts = explode('.', $version);
                    $rParts = explode('.', $requiredVersion);
                    return $vParts[0] === $rParts[0] && version_compare($version, $requiredVersion, '>=');
                case '~': // Compatible with (the same major.minor version)
                    $vParts = explode('.', $version);
                    $rParts = explode('.', $requiredVersion);
                    return $vParts[0] === $rParts[0] && $vParts[1] === $rParts[1] && version_compare($version, $requiredVersion, '>=');
                default:
                    return version_compare($version, $requiredVersion, '=');
            }
        }

        // Exact version match - normalize requirement too
        $requirement = ltrim($requirement, 'vV');
        return version_compare($version, $requirement, '=');
    }

    /**
     * Get mod class name from mod ID
     *
     * Convention: karma-system → TorrentPier\Mod\KarmaSystem\Mod
     *
     * @param string $modId Mod ID
     * @return string Fully qualified class name
     */
    private function getModClassName(string $modId): string
    {
        // Convert kebab-case or snake_case to PascalCase for namespace
        $parts = preg_split('/[-_]/', $modId);
        $namespace = implode('', array_map('ucfirst', $parts));

        // Return a fully qualified class name
        return 'TorrentPier\\Mod\\' . $namespace . '\\Mod';
    }

    /**
     * Deactivate mod in the database (emergency fallback)
     *
     * @param string $modId Mod ID
     * @return void
     */
    private function deactivateModInDb(string $modId): void
    {
        try {
            $this->db->query("
                UPDATE bb_mods
                SET is_active = 0
                WHERE mod_id = '" . $this->db->escape($modId) . "'
            ");

            $this->logError($modId, 'auto_deactivate', 'Mod automatically deactivated due to errors');
        } catch (Exception) {
            // Silent fail - we're already in error handling
        }
    }

    /**
     * Log mod error to database
     *
     * @param string $modId Mod ID
     * @param string $action Action being performed
     * @param string $message Error message
     * @param array $context Additional context
     * @return void
     */
    private function logError(string $modId, string $action, string $message, array $context = []): void
    {
        try {
            $sql_ary = [
                'mod_id' => $modId,
                'action' => 'error_' . $action,
                'message' => $message,
                'details' => !empty($context) ? json_encode($context) : null,
            ];

            $sql = $this->db->build_array('INSERT', $sql_ary);
            $this->db->query("INSERT INTO bb_mod_logs {$sql}");
        } catch (Exception $e) {
            // Silent fail - prevent recursive errors
            error_log("ModLoader: Failed to log error for {$modId}: " . $e->getMessage());
        }

        // Also log to PHP error log for debugging
        error_log("ModLoader [{$modId}]: {$message}");
    }
}
