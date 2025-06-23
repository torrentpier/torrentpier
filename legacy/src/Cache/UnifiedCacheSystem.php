<?php
/**
 * TorrentPier – Bull-powered BitTorrent tracker engine
 *
 * @copyright Copyright (c) 2005-2025 TorrentPier (https://torrentpier.com)
 * @link      https://github.com/torrentpier/torrentpier for the canonical source repository
 * @license   https://github.com/torrentpier/torrentpier/blob/master/LICENSE MIT License
 */

namespace TorrentPier\Cache;

use Nette\Caching\Storage;
use Nette\Caching\Storages\FileStorage;
use Nette\Caching\Storages\MemcachedStorage;
use Nette\Caching\Storages\MemoryStorage;
use Nette\Caching\Storages\SQLiteStorage;

/**
 * Unified Cache System using Nette Caching
 * Replaces Legacy Caches class and provides both cache and datastore functionality
 *
 * @package TorrentPier\Cache
 */
class UnifiedCacheSystem
{
    /**
     * Singleton instance
     * @var self|null
     */
    private static ?self $instance = null;

    /**
     * Configuration
     * @var array
     */
    private array $cfg;

    /**
     * Cache manager instances
     * @var array
     */
    private array $managers = [];

    /**
     * References to cache managers (for backward compatibility)
     * @var array
     */
    private array $ref = [];

    /**
     * Datastore manager instance
     * @var DatastoreManager|null
     */
    private ?DatastoreManager $datastore = null;

    /**
     * Stub cache manager for non-configured caches
     * @var CacheManager|null
     */
    private ?CacheManager $stub = null;

    /**
     * Get singleton instance
     *
     * @param array|null $cfg
     * @return self
     */
    public static function getInstance(?array $cfg = null): self
    {
        if (self::$instance === null) {
            if ($cfg === null) {
                throw new \InvalidArgumentException('Configuration must be provided on first initialization');
            }
            self::$instance = new self($cfg);
        }

        return self::$instance;
    }

    /**
     * Constructor
     *
     * @param array $cfg
     */
    private function __construct(array $cfg)
    {
        $this->cfg = $cfg['cache'] ?? [];

        // Create stub cache manager
        $stubStorage = new MemoryStorage();
        $stubConfig = [
            'engine' => 'Memory',
            'prefix' => $this->cfg['prefix'] ?? 'tp_'
        ];
        $this->stub = CacheManager::getInstance('__stub', $stubStorage, $stubConfig);
    }

    /**
     * Get cache manager instance (backward compatible with CACHE() function)
     *
     * @param string $cache_name
     * @return CacheManager
     */
    public function get_cache_obj(string $cache_name): CacheManager
    {
        if (!isset($this->ref[$cache_name])) {
            if (!$engine_cfg = $this->cfg['engines'][$cache_name] ?? null) {
                // Return stub for non-configured caches
                $this->ref[$cache_name] = $this->stub;
            } else {
                $cache_type = $engine_cfg[0] ?? 'file';

                if (!isset($this->managers[$cache_name])) {
                    // Build storage and config directly
                    $storage = $this->_buildStorage($cache_type, $cache_name);
                    $config = [
                        'engine' => $this->_getEngineType($cache_type),
                        'prefix' => $this->cfg['prefix'] ?? 'tp_'
                    ];

                    $this->managers[$cache_name] = CacheManager::getInstance($cache_name, $storage, $config);
                }
                $this->ref[$cache_name] = $this->managers[$cache_name];
            }
        }

        return $this->ref[$cache_name];
    }

    /**
     * Get datastore manager instance
     *
     * @param string $datastore_type
     * @return DatastoreManager
     */
    public function getDatastore(string $datastore_type = 'file'): DatastoreManager
    {
        if ($this->datastore === null) {
            // Build storage and config for datastore
            $storage = $this->_buildDatastoreStorage($datastore_type);
            $config = [
                'engine' => $this->_getEngineType($datastore_type),
                'prefix' => $this->cfg['prefix'] ?? 'tp_'
            ];

            $this->datastore = DatastoreManager::getInstance($storage, $config);
        }

        return $this->datastore;
    }

    /**
     * Build storage instance directly (eliminates redundancy with CacheManager)
     *
     * @param string $cache_type
     * @param string $cache_name
     * @return Storage
     */
    private function _buildStorage(string $cache_type, string $cache_name): Storage
    {
        switch ($cache_type) {
            case 'file':
            case 'filecache':
            case 'apcu':
            case 'redis':
                // Some deprecated cache types will fall back to file storage
                $dir = rtrim($this->cfg['db_dir'] ?? sys_get_temp_dir() . '/cache/', '/') . '/' . $cache_name . '/';

                // Create directory automatically using TorrentPier's bb_mkdir function
                if (!is_dir($dir) && !bb_mkdir($dir)) {
                    throw new \RuntimeException("Failed to create cache directory: $dir");
                }

                return new FileStorage($dir);

            case 'sqlite':
                $dbFile = rtrim($this->cfg['db_dir'] ?? sys_get_temp_dir() . '/cache/', '/') . '/' . $cache_name . '.db';

                // Create parent directory for SQLite file
                $dbDir = dirname($dbFile);
                if (!is_dir($dbDir) && !bb_mkdir($dbDir)) {
                    throw new \RuntimeException("Failed to create cache directory for SQLite: $dbDir");
                }

                return new SQLiteStorage($dbFile);

            case 'memory':
                return new MemoryStorage();

            case 'memcached':
                $memcachedConfig = $this->cfg['memcached'] ?? ['host' => '127.0.0.1', 'port' => 11211];
                $host = $memcachedConfig['host'] ?? '127.0.0.1';
                $port = $memcachedConfig['port'] ?? 11211;
                return new MemcachedStorage("{$host}:{$port}");

            default:
                // Fallback to file storage
                $dir = rtrim($this->cfg['db_dir'] ?? sys_get_temp_dir() . '/cache/', '/') . '/' . $cache_name . '/';

                // Create directory automatically using TorrentPier's bb_mkdir function
                if (!is_dir($dir) && !bb_mkdir($dir)) {
                    throw new \RuntimeException("Failed to create cache directory: $dir");
                }

                return new FileStorage($dir);
        }
    }

    /**
     * Get engine type name for debugging
     *
     * @param string $cache_type
     * @return string
     */
    private function _getEngineType(string $cache_type): string
    {
        return match ($cache_type) {
            'sqlite' => 'SQLite',
            'memory' => 'Memory',
            'memcached' => 'Memcached',
            default => 'File',
        };
    }

    /**
     * Build datastore storage instance
     *
     * @param string $datastore_type
     * @return Storage
     */
    private function _buildDatastoreStorage(string $datastore_type): Storage
    {
        switch ($datastore_type) {
            case 'file':
            case 'filecache':
            case 'apcu':
            case 'redis':
                // Some deprecated cache types will fall back to file storage
                $dir = rtrim($this->cfg['db_dir'] ?? sys_get_temp_dir() . '/cache/', '/') . '/datastore/';

                // Create directory automatically using TorrentPier's bb_mkdir function
                if (!is_dir($dir) && !bb_mkdir($dir)) {
                    throw new \RuntimeException("Failed to create datastore directory: $dir");
                }

                return new FileStorage($dir);

            case 'sqlite':
                $dbFile = rtrim($this->cfg['db_dir'] ?? sys_get_temp_dir() . '/cache/', '/') . '/datastore.db';

                // Create parent directory for SQLite file
                $dbDir = dirname($dbFile);
                if (!is_dir($dbDir) && !bb_mkdir($dbDir)) {
                    throw new \RuntimeException("Failed to create datastore directory for SQLite: $dbDir");
                }

                return new SQLiteStorage($dbFile);

            case 'memory':
                return new MemoryStorage();

            case 'memcached':
                $memcachedConfig = $this->cfg['memcached'] ?? ['host' => '127.0.0.1', 'port' => 11211];
                $host = $memcachedConfig['host'] ?? '127.0.0.1';
                $port = $memcachedConfig['port'] ?? 11211;
                return new MemcachedStorage("{$host}:{$port}");

            default:
                // Fallback to file storage
                $dir = rtrim($this->cfg['db_dir'] ?? sys_get_temp_dir() . '/cache/', '/') . '/datastore/';

                // Create directory automatically using TorrentPier's bb_mkdir function
                if (!is_dir($dir) && !bb_mkdir($dir)) {
                    throw new \RuntimeException("Failed to create datastore directory: $dir");
                }

                return new FileStorage($dir);
        }
    }

    /**
     * Get all cache managers (for debugging)
     *
     * @return array
     */
    public function getAllCacheManagers(): array
    {
        return $this->managers;
    }

    /**
     * Get configuration
     *
     * @return array
     */
    public function getConfig(): array
    {
        return $this->cfg;
    }

    /**
     * Clear all caches
     *
     * @return void
     */
    public function clearAll(): void
    {
        foreach ($this->managers as $manager) {
            $manager->rm(); // Clear all items in namespace
        }

        if ($this->datastore) {
            $this->datastore->clean();
        }
    }

    /**
     * Get cache statistics
     *
     * @return array
     */
    public function getStatistics(): array
    {
        $stats = [
            'total_managers' => count($this->managers),
            'managers' => []
        ];

        foreach ($this->managers as $name => $manager) {
            $stats['managers'][$name] = [
                'engine' => $manager->engine,
                'num_queries' => $manager->num_queries,
                'total_time' => $manager->sql_timetotal,
                'debug_enabled' => $manager->dbg_enabled
            ];
        }

        if ($this->datastore) {
            $stats['datastore'] = [
                'engine' => $this->datastore->engine,
                'num_queries' => $this->datastore->num_queries,
                'total_time' => $this->datastore->sql_timetotal,
                'queued_items' => count($this->datastore->queued_items),
                'loaded_items' => count($this->datastore->data)
            ];
        }

        return $stats;
    }

    /**
     * Magic method for backward compatibility
     * Allows access to legacy properties like ->obj
     *
     * @param string $name
     * @return mixed
     */
    public function __get(string $name): mixed
    {
        switch ($name) {
            case 'obj':
                // Return array of cache objects for backward compatibility
                $obj = ['__stub' => $this->stub];
                foreach ($this->managers as $cache_name => $manager) {
                    $obj[$cache_name] = $manager;
                }
                return $obj;

            case 'cfg':
                return $this->cfg;

            case 'ref':
                return $this->ref;

            default:
                throw new \InvalidArgumentException("Property '$name' not found");
        }
    }

    /**
     * Create cache manager with advanced Nette features
     *
     * @param string $namespace
     * @param array $config
     * @return CacheManager
     */
    public function createAdvancedCache(string $namespace, array $config = []): CacheManager
    {
        $fullConfig = array_merge($this->cfg, $config);
        $fullConfig['prefix'] = $fullConfig['prefix'] ?? 'tp_';

        // Build storage for the advanced cache
        $storageType = $config['storage_type'] ?? 'file';
        $storage = $this->_buildStorage($storageType, $namespace);
        $managerConfig = [
            'engine' => $this->_getEngineType($storageType),
            'prefix' => $fullConfig['prefix']
        ];

        return CacheManager::getInstance($namespace, $storage, $managerConfig);
    }

    /**
     * Create cache with file dependencies
     *
     * @param string $namespace
     * @param array $files
     * @return CacheManager
     */
    public function createFileBasedCache(string $namespace, array $files = []): CacheManager
    {
        $cache = $this->createAdvancedCache($namespace);

        // Example usage:
        // $value = $cache->load('key', function() use ($files) {
        //     return expensive_computation();
        // }, [Cache::Files => $files]);

        return $cache;
    }

    /**
     * Create cache with tags support
     *
     * @param string $namespace
     * @return CacheManager
     */
    public function createTaggedCache(string $namespace): CacheManager
    {
        // Use SQLite storage which supports tags via journal
        $storage = $this->_buildStorage('sqlite', $namespace);
        $config = [
            'engine' => 'SQLite',
            'prefix' => $this->cfg['prefix'] ?? 'tp_'
        ];

        return CacheManager::getInstance($namespace, $storage, $config);
    }

    /**
     * Prevent cloning of the singleton instance
     */
    private function __clone()
    {
    }

    /**
     * Prevent unserialization of the singleton instance
     */
    public function __wakeup()
    {
        throw new \Exception("Cannot unserialize a singleton.");
    }
}
