<?php
/**
 * TorrentPier – Bull-powered BitTorrent tracker engine
 *
 * @copyright Copyright (c) 2005-2025 TorrentPier (https://torrentpier.com)
 * @link      https://github.com/torrentpier/torrentpier for the canonical source repository
 * @license   https://github.com/torrentpier/torrentpier/blob/master/LICENSE MIT License
 */

namespace TorrentPier\Legacy\Datastore;

use TorrentPier\Dev;

use Redis as RedisClient;
use MatthiasMullie\Scrapbook\Adapters\Redis as RedisCache;

use Exception;

/**
 * Class Redis
 * @package TorrentPier\Legacy\Datastore
 */
class Redis extends Common
{
    /**
     * Cache driver name
     *
     * @var string
     */
    public string $engine = 'Redis';

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
     * Redis class
     *
     * @var RedisClient
     */
    private RedisClient $client;

    /**
     * Adapters\Redis class
     *
     * @var RedisCache
     */
    private RedisCache $redis;

    /**
     * Redis constructor
     *
     * @param array $cfg
     * @param string $prefix
     */
    public function __construct(array $cfg, string $prefix)
    {
        if (!$this->isInstalled()) {
            throw new Exception('ext-redis not installed. Check out php.ini file');
        }
        $this->client = new RedisClient();
        $this->cfg = $cfg;
        $this->prefix = $prefix;
        $this->dbg_enabled = dev()->checkSqlDebugAllowed();
    }

    /**
     * Connect to cache
     *
     * @return void
     */
    private function connect(): void
    {
        $connectType = $this->cfg['pconnect'] ? 'pconnect' : 'connect';

        $this->cur_query = $connectType . ' ' . $this->cfg['host'] . ':' . $this->cfg['port'];
        $this->debug('start');

        if ($this->client->$connectType($this->cfg['host'], $this->cfg['port'])) {
            $this->connected = true;
        }

        if (!$this->connected) {
            throw new Exception("Could not connect to $this->engine server");
        }

        $this->redis = new RedisCache($this->client);

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

        $result = $this->redis->set($item_name, $item_data);

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

            $this->redis->delete($title);

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

            $this->data[$item] = $this->redis->get($item_title);

            $this->debug('stop');
            $this->cur_query = null;
            $this->num_queries++;
        }
    }

    /**
     * Checking if Redis is installed
     *
     * @return bool
     */
    private function isInstalled(): bool
    {
        return extension_loaded('redis') && class_exists('Redis');
    }
}
