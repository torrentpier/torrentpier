<?php
/**
 * TorrentPier – Bull-powered BitTorrent tracker engine
 *
 * @copyright Copyright (c) 2005-2025 TorrentPier (https://torrentpier.com)
 * @link      https://github.com/torrentpier/torrentpier for the canonical source repository
 * @license   https://github.com/torrentpier/torrentpier/blob/master/LICENSE MIT License
 */

namespace TorrentPier\Cache;

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
        $this->stub = CacheManager::getInstance('__stub', [
            'storage_type' => 'memory',
            'prefix' => $this->cfg['prefix'] ?? 'tp_'
        ]);
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
                $config = $this->_buildCacheConfig($cache_type, $cache_name);

                if (!isset($this->managers[$cache_name])) {
                    $this->managers[$cache_name] = CacheManager::getInstance($cache_name, $config);
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
            $config = $this->_buildDatastoreConfig($datastore_type);
            $this->datastore = DatastoreManager::getInstance($config);
        }

        return $this->datastore;
    }

    /**
     * Build cache configuration
     *
     * @param string $cache_type
     * @param string $cache_name
     * @return array
     */
    private function _buildCacheConfig(string $cache_type, string $cache_name): array
    {
        $config = [
            'prefix' => $this->cfg['prefix'] ?? 'tp_',
        ];

        switch ($cache_type) {
            case 'file':
            case 'filecache':
            case 'apcu':
            case 'memcached':
            case 'redis':
                // Some deprecated cache types will fall back to file storage
                $config['storage_type'] = 'file';
                $config['db_dir'] = rtrim($this->cfg['db_dir'] ?? sys_get_temp_dir() . '/cache/', '/') . '/' . $cache_name . '/';
                break;

            case 'sqlite':
                $config['storage_type'] = 'sqlite';
                $config['sqlite_path'] = rtrim($this->cfg['db_dir'] ?? sys_get_temp_dir() . '/cache/', '/') . '/' . $cache_name . '.db';
                break;

            case 'memory':
                $config['storage_type'] = 'memory';
                break;

            default:
                $config['storage_type'] = 'file';
                $config['db_dir'] = rtrim($this->cfg['db_dir'] ?? sys_get_temp_dir() . '/cache/', '/') . '/' . $cache_name . '/';
                break;
        }

        return $config;
    }

    /**
     * Build datastore configuration
     *
     * @param string $datastore_type
     * @return array
     */
    private function _buildDatastoreConfig(string $datastore_type): array
    {
        $config = [
            'prefix' => $this->cfg['prefix'] ?? 'tp_',
        ];

        switch ($datastore_type) {
            case 'file':
            case 'filecache':
            case 'apcu':
            case 'memcached':
            case 'redis':
                // Some deprecated cache types will fall back to file storage
                $config['storage_type'] = 'file';
                $config['db_dir'] = rtrim($this->cfg['db_dir'] ?? sys_get_temp_dir() . '/cache/', '/') . '/datastore/';
                break;

            case 'sqlite':
                $config['storage_type'] = 'sqlite';
                $config['sqlite_path'] = rtrim($this->cfg['db_dir'] ?? sys_get_temp_dir() . '/cache/', '/') . '/datastore.db';
                break;

            case 'memory':
                $config['storage_type'] = 'memory';
                break;

            default:
                $config['storage_type'] = 'file';
                $config['db_dir'] = rtrim($this->cfg['db_dir'] ?? sys_get_temp_dir() . '/cache/', '/') . '/datastore/';
                break;
        }

        return $config;
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

        return CacheManager::getInstance($namespace, $fullConfig);
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
        $config = $this->cfg;
        $config['storage_type'] = 'sqlite'; // SQLite supports tags via journal

        return CacheManager::getInstance($namespace, $config);
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
