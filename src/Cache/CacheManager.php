<?php
/**
 * TorrentPier – Bull-powered BitTorrent tracker engine
 *
 * @copyright Copyright (c) 2005-2025 TorrentPier (https://torrentpier.com)
 * @link      https://github.com/torrentpier/torrentpier for the canonical source repository
 * @license   https://github.com/torrentpier/torrentpier/blob/master/LICENSE MIT License
 */

namespace TorrentPier\Cache;

use Nette\Caching\Cache;
use Nette\Caching\Storage;
use Nette\Caching\Storages\FileStorage;
use Nette\Caching\Storages\MemcachedStorage;
use Nette\Caching\Storages\MemoryStorage;
use Nette\Caching\Storages\SQLiteStorage;
use TorrentPier\Dev;

/**
 * Unified Cache Manager using Nette Caching internally
 * Maintains backward compatibility with Legacy Cache and Datastore APIs
 *
 * @package TorrentPier\Cache
 */
class CacheManager
{
    /**
     * Singleton instances of cache managers
     * @var array
     */
    private static array $instances = [];

    /**
     * Nette Cache instance
     * @var Cache
     */
    private Cache $cache;

    /**
     * Storage instance
     * @var Storage
     */
    private Storage $storage;

    /**
     * Cache prefix
     * @var string
     */
    public string $prefix;

    /**
     * Engine type
     * @var string
     */
    public string $engine;

    /**
     * Currently in usage (for backward compatibility)
     * @var bool
     */
    public bool $used = true;

    /**
     * Debug properties for backward compatibility
     */
    public int $num_queries = 0;
    public float $sql_starttime = 0;
    public float $sql_inittime = 0;
    public float $sql_timetotal = 0;
    public float $cur_query_time = 0;
    public array $dbg = [];
    public int $dbg_id = 0;
    public bool $dbg_enabled = false;
    public ?string $cur_query = null;

    /**
     * Constructor
     *
     * @param string $namespace
     * @param Storage $storage Pre-built storage instance from UnifiedCacheSystem
     * @param array $config
     */
    private function __construct(string $namespace, Storage $storage, array $config)
    {
        $this->storage = $storage;
        $this->prefix = $config['prefix'] ?? 'tp_';
        $this->engine = $config['engine'] ?? 'Unknown';

        // Create Nette Cache instance with namespace
        $this->cache = new Cache($this->storage, $namespace);

        // Enable debug if allowed
        $this->dbg_enabled = dev()->checkSqlDebugAllowed();
    }

    /**
     * Get singleton instance (called by UnifiedCacheSystem)
     *
     * @param string $namespace
     * @param Storage $storage Pre-built storage instance
     * @param array $config
     * @return self
     */
    public static function getInstance(string $namespace, Storage $storage, array $config): self
    {
        $key = $namespace . '_' . md5(serialize($config));

        if (!isset(self::$instances[$key])) {
            self::$instances[$key] = new self($namespace, $storage, $config);
        }

        return self::$instances[$key];
    }


    /**
     * Cache get method (Legacy Cache API)
     *
     * @param string $name
     * @return mixed
     */
    public function get(string $name): mixed
    {
        $key = $this->prefix . $name;

        $this->cur_query = "cache->get('$key')";
        $this->debug('start');

        $result = $this->cache->load($key);

        $this->debug('stop');
        $this->cur_query = null;
        $this->num_queries++;

        // Convert null to false for backward compatibility with legacy cache system
        return $result ?? false;
    }

    /**
     * Cache set method (Legacy Cache API)
     *
     * @param string $name
     * @param mixed $value
     * @param int $ttl
     * @return bool
     */
    public function set(string $name, mixed $value, int $ttl = 604800): bool
    {
        $key = $this->prefix . $name;

        $this->cur_query = "cache->set('$key')";
        $this->debug('start');

        $dependencies = [];
        if ($ttl > 0) {
            $dependencies[Cache::Expire] = $ttl . ' seconds';
        }

        try {
            $this->cache->save($key, $value, $dependencies);
            $result = true;
        } catch (\Exception $e) {
            $result = false;
        }

        $this->debug('stop');
        $this->cur_query = null;
        $this->num_queries++;

        return $result;
    }

    /**
     * Cache remove method (Legacy Cache API)
     *
     * @param string|null $name
     * @return bool
     */
    public function rm(?string $name = null): bool
    {
        if ($name === null) {
            // Remove all items in this namespace
            $this->cur_query = "cache->clean(all)";
            $this->debug('start');

            $this->cache->clean([Cache::All => true]);

            $this->debug('stop');
            $this->cur_query = null;
            $this->num_queries++;

            return true;
        }

        $key = $this->prefix . $name;

        $this->cur_query = "cache->remove('$key')";
        $this->debug('start');

        $this->cache->remove($key);

        $this->debug('stop');
        $this->cur_query = null;
        $this->num_queries++;

        return true;
    }

    /**
     * Advanced Nette Caching methods
     */

    /**
     * Load with callback (Nette native method)
     *
     * @param string $key
     * @param callable|null $callback
     * @param array $dependencies
     * @return mixed
     */
    public function load(string $key, ?callable $callback = null, array $dependencies = []): mixed
    {
        $fullKey = $this->prefix . $key;

        $this->cur_query = "cache->load('$fullKey')";
        $this->debug('start');

        $result = $this->cache->load($fullKey, $callback, $dependencies);

        $this->debug('stop');
        $this->cur_query = null;
        $this->num_queries++;

        // Convert null to false for backward compatibility, but only if no callback was provided
        // When callback is provided, null indicates the callback was executed and returned null
        return ($result === null && $callback === null) ? false : $result;
    }

    /**
     * Save with dependencies
     *
     * @param string $key
     * @param mixed $value
     * @param array $dependencies
     * @return void
     */
    public function save(string $key, mixed $value, array $dependencies = []): void
    {
        $fullKey = $this->prefix . $key;

        $this->cur_query = "cache->save('$fullKey')";
        $this->debug('start');

        $this->cache->save($fullKey, $value, $dependencies);

        $this->debug('stop');
        $this->cur_query = null;
        $this->num_queries++;
    }

    /**
     * Clean cache by criteria
     *
     * @param array $conditions
     * @return void
     */
    public function clean(array $conditions = []): void
    {
        $this->cur_query = "cache->clean(" . json_encode($conditions) . ")";
        $this->debug('start');

        $this->cache->clean($conditions);

        $this->debug('stop');
        $this->cur_query = null;
        $this->num_queries++;
    }

    /**
     * Bulk load
     *
     * @param array $keys
     * @param callable|null $callback
     * @return array
     */
    public function bulkLoad(array $keys, ?callable $callback = null): array
    {
        $prefixedKeys = array_map(fn($key) => $this->prefix . $key, $keys);

        $this->cur_query = "cache->bulkLoad(" . count($keys) . " keys)";
        $this->debug('start');

        $result = $this->cache->bulkLoad($prefixedKeys, $callback);

        $this->debug('stop');
        $this->cur_query = null;
        $this->num_queries++;

        return $result;
    }

    /**
     * Memoize function call
     *
     * @param callable $function
     * @param mixed ...$args
     * @return mixed
     */
    public function call(callable $function, ...$args): mixed
    {
        $this->cur_query = "cache->call(" . (is_string($function) ? $function : 'callable') . ")";
        $this->debug('start');

        $result = $this->cache->call($function, ...$args);

        $this->debug('stop');
        $this->cur_query = null;
        $this->num_queries++;

        return $result;
    }

    /**
     * Wrap function for memoization
     *
     * @param callable $function
     * @return callable
     */
    public function wrap(callable $function): callable
    {
        return $this->cache->wrap($function);
    }

    /**
     * Capture output
     *
     * @param string $key
     * @return \Nette\Caching\OutputHelper|null
     */
    public function capture(string $key): ?\Nette\Caching\OutputHelper
    {
        $fullKey = $this->prefix . $key;
        return $this->cache->capture($fullKey);
    }

    /**
     * Remove specific key
     *
     * @param string $key
     * @return void
     */
    public function remove(string $key): void
    {
        $fullKey = $this->prefix . $key;

        $this->cur_query = "cache->remove('$fullKey')";
        $this->debug('start');

        $this->cache->remove($fullKey);

        $this->debug('stop');
        $this->cur_query = null;
        $this->num_queries++;
    }

    /**
     * Debug method (backward compatibility)
     *
     * @param string $mode
     * @param string|null $cur_query
     * @return void
     */
    public function debug(string $mode, ?string $cur_query = null): void
    {
        if (!$this->dbg_enabled) {
            return;
        }

        $id =& $this->dbg_id;
        $dbg =& $this->dbg[$id];

        switch ($mode) {
            case 'start':
                $this->sql_starttime = utime();
                $dbg['sql'] = dev()->formatShortQuery($cur_query ?? $this->cur_query);
                $dbg['src'] = $this->debug_find_source();
                $dbg['file'] = $this->debug_find_source('file');
                $dbg['line'] = $this->debug_find_source('line');
                $dbg['time'] = '';
                break;
            case 'stop':
                $this->cur_query_time = utime() - $this->sql_starttime;
                $this->sql_timetotal += $this->cur_query_time;
                $dbg['time'] = $this->cur_query_time;
                $id++;
                break;
            default:
                bb_simple_die('[Cache] Incorrect debug mode');
                break;
        }
    }

    /**
     * Find caller source (backward compatibility)
     *
     * @param string $mode
     * @return string
     */
    public function debug_find_source(string $mode = 'all'): string
    {
        if (!SQL_PREPEND_SRC) {
            return 'src disabled';
        }
        foreach (debug_backtrace() as $trace) {
            if (!empty($trace['file']) && $trace['file'] !== __FILE__) {
                switch ($mode) {
                    case 'file':
                        return $trace['file'];
                    case 'line':
                        return (string)$trace['line'];
                    case 'all':
                    default:
                        return hide_bb_path($trace['file']) . '(' . $trace['line'] . ')';
                }
            }
        }
        return 'src not found';
    }

    /**
     * Get storage instance (for advanced usage)
     *
     * @return Storage
     */
    public function getStorage(): Storage
    {
        return $this->storage;
    }

    /**
     * Get Nette Cache instance (for advanced usage)
     *
     * @return Cache
     */
    public function getCache(): Cache
    {
        return $this->cache;
    }

    /**
     * Magic property getter for backward compatibility
     *
     * @param string $name
     * @return mixed
     */
    public function __get(string $name): mixed
    {
        // Handle legacy properties that don't exist in unified system
        if ($name === 'db') {
            // Legacy cache systems sometimes had a 'db' property for database storage
            // Our unified system doesn't use separate database connections for cache
            // Return an object with empty debug arrays for compatibility
            return (object)[
                'dbg' => [],
                'engine' => $this->engine,
                'sql_timetotal' => 0
            ];
        }

        throw new \InvalidArgumentException("Property '$name' not found in CacheManager");
    }
}
