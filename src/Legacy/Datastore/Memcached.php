<?php
/**
 * TorrentPier – Bull-powered BitTorrent tracker engine
 *
 * @copyright Copyright (c) 2005-2024 TorrentPier (https://torrentpier.com)
 * @link      https://github.com/torrentpier/torrentpier for the canonical source repository
 * @license   https://github.com/torrentpier/torrentpier/blob/master/LICENSE MIT License
 */

namespace TorrentPier\Legacy\Datastore;

use Memcached as MemcachedClient;
use MatthiasMullie\Scrapbook\Adapters\Memcached as MemcachedCache;

/**
 * Class Memcached
 * @package TorrentPier\Legacy\Datastore
 */
class Memcached extends Common
{
    /**
     * Cache driver name
     *
     * @var string
     */
    public string $engine = 'Memcached';

    /**
     * Connection status
     *
     * @var bool
     */
    public bool $connected = false;

    /**
     * Cache config
     *
     * @var array
     */
    private array $cfg;

    /**
     * Cache prefix
     *
     * @var string
     */
    private string $prefix;

    /**
     * Memcached class
     *
     * @var MemcachedClient
     */
    private MemcachedClient $client;

    /**
     * Adapters\Memcached class
     *
     * @var MemcachedCache
     */
    private MemcachedCache $memcached;

    /**
     * Memcached constructor
     *
     * @param array $cfg
     * @param string $prefix
     */
    public function __construct(array $cfg, string $prefix)
    {
        global $debug;

        $this->client = new MemcachedClient();
        $this->cfg = $cfg;
        $this->prefix = $prefix;
        $this->dbg_enabled = $debug->sqlDebugAllowed();
    }

    /**
     * Connect to cache
     *
     * @return void
     */
    private function connect(): void
    {
        $this->cur_query = 'connect ' . $this->cfg['host'] . ':' . $this->cfg['port'];
        $this->debug('start');

        if ($this->client->addServer($this->cfg['host'], $this->cfg['port'])) {
            $this->connected = true;
        }

        if (!$this->connected) {
            die("Could not connect to $this->engine server");
        }

        $this->memcached = new MemcachedCache($this->client);

        $this->debug('stop');
        $this->cur_query = null;
    }

    /**
     * Store data into cache
     *
     * @param string $item_name
     * @param mixed $item_data
     * @return bool
     */
    public function store(string $item_name, mixed $item_data): bool
    {
        if (!$this->connected) {
            $this->connect();
        }

        $this->data[$item_name] = $item_data;
        $item_name = $this->prefix . $item_name;

        $this->cur_query = "cache->" . __FUNCTION__ . "('$item_name')";
        $this->debug('start');

        $result = $this->memcached->set($item_name, $item_data);

        $this->debug('stop');
        $this->cur_query = null;
        $this->num_queries++;

        return $result;
    }

    /**
     * Removes data from cache
     *
     * @return void
     */
    public function clean(): void
    {
        if (!$this->connected) {
            $this->connect();
        }

        foreach ($this->known_items as $title => $script_name) {
            $title = $this->prefix . $title;
            $this->cur_query = "cache->rm('$title')";
            $this->debug('start');

            $this->memcached->delete($title);

            $this->debug('stop');
            $this->cur_query = null;
            $this->num_queries++;
        }
    }

    /**
     * Fetch cache from store
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

        if (!$this->connected) {
            $this->connect();
        }

        foreach ($items as $item) {
            $item_title = $this->prefix . $item;
            $this->cur_query = "cache->get('$item_title')";
            $this->debug('start');

            $this->data[$item] = $this->memcached->get($item_title);

            $this->debug('stop');
            $this->cur_query = null;
            $this->num_queries++;
        }
    }
}
