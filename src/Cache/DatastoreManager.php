<?php

/**
 * TorrentPier – Bull-powered BitTorrent tracker engine
 *
 * @copyright Copyright (c) 2005-2025 TorrentPier (https://torrentpier.com)
 * @link      https://github.com/torrentpier/torrentpier for the canonical source repository
 * @license   https://github.com/torrentpier/torrentpier/blob/master/LICENSE MIT License
 */

namespace TorrentPier\Cache;

use BadMethodCallException;
use Exception;
use InvalidArgumentException;
use Nette\Caching\Cache;
use Nette\Caching\Storage;

/**
 * Datastore Manager using unified CacheManager internally
 * Maintains backward compatibility with Legacy Datastore API
 *
 * @package TorrentPier\Cache
 */
class DatastoreManager
{
    /**
     * Singleton instance
     * @var self|null
     */
    private static ?self $instance = null;

    /**
     * Директория с builder-скриптами (внутри INC_DIR)
     */
    public string $ds_dir = 'datastore';

    /**
     * Готовая к употреблению data
     * array('title' => data)
     */
    public array $data = [];

    /**
     * Список элементов, которые будут извлечены из хранилища при первом же запросе get()
     * до этого момента они ставятся в очередь $queued_items для дальнейшего извлечения _fetch()'ем
     * всех элементов одним запросом
     * array('title1', 'title2'...)
     */
    public array $queued_items = [];

    /**
     * 'title' => 'builder script name' inside "includes/datastore" dir
     */
    public array $known_items = [
        'cat_forums' => 'build_cat_forums.php',
        'censor' => 'build_censor.php',
        'check_updates' => 'build_check_updates.php',
        'jumpbox' => 'build_cat_forums.php',
        'viewtopic_forum_select' => 'build_cat_forums.php',
        'latest_news' => 'build_cat_forums.php',
        'network_news' => 'build_cat_forums.php',
        'ads' => 'build_cat_forums.php',
        'moderators' => 'build_moderators.php',
        'stats' => 'build_stats.php',
        'ranks' => 'build_ranks.php',
        'ban_list' => 'build_bans.php',
        'smile_replacements' => 'build_smilies.php',
    ];

    /**
     * Engine type (for backward compatibility)
     * @var string
     */
    public string $engine;

    /**
     * Debug properties (delegated to CacheManager)
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
     * Unified cache manager instance
     * @var CacheManager
     */
    private CacheManager $cacheManager;

    /**
     * Constructor
     *
     * @param Storage $storage Pre-built storage instance from UnifiedCacheSystem
     * @param array $config
     */
    private function __construct(Storage $storage, array $config)
    {
        // Create unified cache manager for datastore with pre-built storage
        $this->cacheManager = CacheManager::getInstance('datastore', $storage, $config);
        $this->engine = $this->cacheManager->engine;
        $this->dbg_enabled = tracy()->isDebugAllowed();
    }

    /**
     * Magic method to delegate unknown method calls to cache manager
     *
     * @param string $method
     * @param array $args
     * @return mixed
     */
    public function __call(string $method, array $args): mixed
    {
        if (method_exists($this->cacheManager, $method)) {
            $result = $this->cacheManager->{$method}(...$args);
            $this->_updateDebugCounters();

            return $result;
        }

        throw new BadMethodCallException("Method '{$method}' not found in DatastoreManager or CacheManager");
    }

    /**
     * Magic property getter to delegate to cache manager
     *
     * @param string $name
     * @return mixed
     */
    public function __get(string $name): mixed
    {
        if (property_exists($this->cacheManager, $name)) {
            return $this->cacheManager->{$name};
        }

        // Handle legacy properties that don't exist in unified system
        if ($name === 'db') {
            // Legacy cache systems sometimes had a 'db' property for database storage
            // Our unified system doesn't use separate database connections for cache
            // Return an object with empty debug arrays for compatibility
            return (object)[
                'dbg' => [],
                'engine' => $this->engine,
                'sql_timetotal' => 0,
            ];
        }

        throw new InvalidArgumentException("Property '{$name}' not found");
    }

    /**
     * Magic property setter to delegate to cache manager
     *
     * @param string $name
     * @param mixed $value
     */
    public function __set(string $name, mixed $value): void
    {
        if (property_exists($this->cacheManager, $name)) {
            $this->cacheManager->{$name} = $value;
        } else {
            throw new InvalidArgumentException("Property '{$name}' not found");
        }
    }

    /**
     * Get singleton instance
     *
     * @param Storage $storage Pre-built storage instance
     * @param array $config
     * @return self
     */
    public static function getInstance(Storage $storage, array $config): self
    {
        if (self::$instance === null) {
            self::$instance = new self($storage, $config);
        }

        return self::$instance;
    }

    /**
     * Enqueue items for batch loading
     *
     * @param array $items
     */
    public function enqueue(array $items): void
    {
        foreach ($items as $item) {
            if (!\in_array($item, $this->queued_items) && !isset($this->data[$item])) {
                $this->queued_items[] = $item;
            }
        }
    }

    /**
     * Get datastore item
     *
     * @param string $title
     * @return mixed
     */
    public function &get(string $title): mixed
    {
        if (!isset($this->data[$title])) {
            $this->enqueue([$title]);
            $this->_fetch();
        }

        return $this->data[$title];
    }

    /**
     * Store data into datastore
     *
     * @param string $item_name
     * @param mixed $item_data
     * @return bool
     */
    public function store(string $item_name, mixed $item_data): bool
    {
        $this->data[$item_name] = $item_data;

        // Use cache manager with permanent storage (no TTL)
        $dependencies = [
            // No time expiration for datastore items - they persist until manually updated
        ];

        try {
            $this->cacheManager->save($item_name, $item_data, $dependencies);
            $this->_updateDebugCounters();

            return true;
        } catch (Exception $e) {
            $this->_updateDebugCounters();

            return false;
        }
    }

    /**
     * Remove data from memory cache
     *
     * @param array|string $items
     */
    public function rm(array|string $items): void
    {
        foreach ((array)$items as $item) {
            unset($this->data[$item]);
        }
    }

    /**
     * Update datastore items
     *
     * @param array|string $items
     */
    public function update(array|string $items): void
    {
        if ($items == 'all') {
            $items = array_keys(array_unique($this->known_items));
        }
        foreach ((array)$items as $item) {
            $this->_build_item($item);
        }
    }

    /**
     * Clean datastore cache (for admin purposes)
     */
    public function clean(): void
    {
        foreach ($this->known_items as $title => $script_name) {
            $this->cacheManager->remove($title);
        }
        $this->_updateDebugCounters();
    }

    /**
     * Fetch items from store
     */
    public function _fetch(): void
    {
        $this->_fetch_from_store();

        foreach ($this->queued_items as $title) {
            // Only rebuild items that had true cache misses, not cached false/null values
            if (!isset($this->data[$title]) || $this->data[$title] === '__CACHE_MISS__') {
                $this->_build_item($title);
            }
        }

        $this->queued_items = [];
    }

    /**
     * Fetch items from cache store
     *
     * @throws Exception
     */
    public function _fetch_from_store(): void
    {
        if (!$items = $this->queued_items) {
            $src = $this->_debug_find_caller('enqueue');

            throw new Exception("Datastore: no items queued for fetching [{$src}]");
        }

        // Use bulk loading for efficiency
        $keys = $items;
        $results = $this->cacheManager->bulkLoad($keys);

        foreach ($items as $item) {
            $fullKey = $this->cacheManager->prefix . $item;

            // Distinguish between cache miss (null) and cached false value
            if (\array_key_exists($fullKey, $results)) {
                // Item exists in cache (even if the value is null/false)
                $this->data[$item] = $results[$fullKey];
            } else {
                // True cache miss - item not found in cache at all
                // Use a special sentinel value to mark as "needs building"
                $this->data[$item] = '__CACHE_MISS__';
            }
        }

        $this->_updateDebugCounters();
    }

    /**
     * Build item using builder script
     *
     * @param string $title
     * @throws Exception
     */
    public function _build_item(string $title): void
    {
        if (!isset($this->known_items[$title])) {
            throw new Exception("Unknown datastore item: {$title}");
        }

        $file = INC_DIR . '/' . $this->ds_dir . '/' . $this->known_items[$title];
        if (!file_exists($file)) {
            throw new Exception("Datastore builder script not found: {$file}");
        }

        require $file;
    }

    /**
     * Find debug caller (backward compatibility)
     *
     * @param string $function_name
     * @return string
     */
    public function _debug_find_caller(string $function_name): string
    {
        foreach (debug_backtrace() as $trace) {
            if (isset($trace['function']) && $trace['function'] === $function_name) {
                return hide_bb_path($trace['file']) . '(' . $trace['line'] . ')';
            }
        }

        return 'caller not found';
    }

    /**
     * Advanced Nette caching methods (extended functionality)
     */

    /**
     * Load with dependencies
     *
     * @param string $key
     * @param callable|null $callback
     * @param array $dependencies
     * @return mixed
     */
    public function load(string $key, ?callable $callback = null, array $dependencies = []): mixed
    {
        return $this->cacheManager->load($key, $callback, $dependencies);
    }

    /**
     * Save with dependencies
     *
     * @param string $key
     * @param mixed $value
     * @param array $dependencies
     */
    public function save(string $key, mixed $value, array $dependencies = []): void
    {
        $this->cacheManager->save($key, $value, $dependencies);
        $this->_updateDebugCounters();
    }

    /**
     * Clean by criteria
     *
     * @param array $conditions
     */
    public function cleanByCriteria(array $conditions = []): void
    {
        $this->cacheManager->clean($conditions);
        $this->_updateDebugCounters();
    }

    /**
     * Clean by tags
     *
     * @param array $tags
     */
    public function cleanByTags(array $tags): void
    {
        $this->cacheManager->clean([Cache::Tags => $tags]);
        $this->_updateDebugCounters();
    }

    /**
     * Get cache manager instance (for advanced usage)
     *
     * @return CacheManager
     */
    public function getCacheManager(): CacheManager
    {
        return $this->cacheManager;
    }

    /**
     * Get engine name
     *
     * @return string
     */
    public function getEngine(): string
    {
        return $this->engine;
    }

    /**
     * Check if storage supports tags
     *
     * @return bool
     */
    public function supportsTags(): bool
    {
        return $this->cacheManager->getStorage() instanceof \Nette\Caching\Storages\IJournal;
    }

    /**
     * Update debug counters from cache manager
     */
    private function _updateDebugCounters(): void
    {
        $this->num_queries = $this->cacheManager->num_queries;
        $this->sql_timetotal = $this->cacheManager->sql_timetotal;
        $this->dbg = $this->cacheManager->dbg;
        $this->dbg_id = $this->cacheManager->dbg_id;
    }
}
