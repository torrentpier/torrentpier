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
use TorrentPier\Dev;

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
     * Unified cache manager instance
     * @var CacheManager
     */
    private CacheManager $cacheManager;

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
        'attach_extensions' => 'build_attach_extensions.php',
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
     * Constructor
     *
     * @param array $config
     */
    private function __construct(array $config)
    {
        // Create unified cache manager for datastore
        $this->cacheManager = CacheManager::getInstance('datastore', $config);
        $this->engine = $this->cacheManager->engine;
        $this->dbg_enabled = dev()->checkSqlDebugAllowed();
    }

    /**
     * Get singleton instance
     *
     * @param array $config
     * @return self
     */
    public static function getInstance(array $config): self
    {
        if (self::$instance === null) {
            self::$instance = new self($config);
        }

        return self::$instance;
    }

    /**
     * Enqueue items for batch loading
     *
     * @param array $items
     * @return void
     */
    public function enqueue(array $items): void
    {
        foreach ($items as $item) {
            if (!in_array($item, $this->queued_items) && !isset($this->data[$item])) {
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
        } catch (\Exception $e) {
            $this->_updateDebugCounters();
            return false;
        }
    }

    /**
     * Remove data from memory cache
     *
     * @param array|string $items
     * @return void
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
     * @return void
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
     *
     * @return void
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
     *
     * @return void
     */
    public function _fetch(): void
    {
        $this->_fetch_from_store();

        foreach ($this->queued_items as $title) {
            if (!isset($this->data[$title]) || $this->data[$title] === false) {
                $this->_build_item($title);
            }
        }

        $this->queued_items = [];
    }

    /**
     * Fetch items from cache store
     *
     * @return void
     */
    public function _fetch_from_store(): void
    {
        $item = null;
        if (!$items = $this->queued_items) {
            $src = $this->_debug_find_caller('enqueue');
            trigger_error("Datastore: item '$item' already enqueued [$src]", E_USER_ERROR);
        }

        // Use bulk loading for efficiency
        $keys = $items;
        $results = $this->cacheManager->bulkLoad($keys);

        foreach ($items as $item) {
            $this->data[$item] = $results[$this->cacheManager->prefix . $item] ?? false;
        }

        $this->_updateDebugCounters();
    }

    /**
     * Build item using builder script
     *
     * @param string $title
     * @return void
     */
    public function _build_item(string $title): void
    {
        $file = INC_DIR . '/' . $this->ds_dir . '/' . $this->known_items[$title];
        if (isset($this->known_items[$title]) && file_exists($file)) {
            require $file;
        } else {
            trigger_error("Unknown datastore item: $title", E_USER_ERROR);
        }
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
     * Update debug counters from cache manager
     *
     * @return void
     */
    private function _updateDebugCounters(): void
    {
        $this->num_queries = $this->cacheManager->num_queries;
        $this->sql_timetotal = $this->cacheManager->sql_timetotal;
        $this->dbg = $this->cacheManager->dbg;
        $this->dbg_id = $this->cacheManager->dbg_id;
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
     * @return void
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
     * @return void
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
     * @return void
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
     * Magic method to delegate unknown method calls to cache manager
     *
     * @param string $method
     * @param array $args
     * @return mixed
     */
    public function __call(string $method, array $args): mixed
    {
        if (method_exists($this->cacheManager, $method)) {
            $result = $this->cacheManager->$method(...$args);
            $this->_updateDebugCounters();
            return $result;
        }

        throw new \BadMethodCallException("Method '$method' not found in DatastoreManager or CacheManager");
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
            return $this->cacheManager->$name;
        }

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

        throw new \InvalidArgumentException("Property '$name' not found");
    }

    /**
     * Magic property setter to delegate to cache manager
     *
     * @param string $name
     * @param mixed $value
     * @return void
     */
    public function __set(string $name, mixed $value): void
    {
        if (property_exists($this->cacheManager, $name)) {
            $this->cacheManager->$name = $value;
        } else {
            throw new \InvalidArgumentException("Property '$name' not found");
        }
    }
}
