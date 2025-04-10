<?php
/**
 * TorrentPier – Bull-powered BitTorrent tracker engine
 *
 * @copyright Copyright (c) 2005-2025 TorrentPier (https://torrentpier.com)
 * @link      https://github.com/torrentpier/torrentpier for the canonical source repository
 * @license   https://github.com/torrentpier/torrentpier/blob/master/LICENSE MIT License
 */

namespace TorrentPier\Legacy\Cache;

use TorrentPier\Dev;

use Redis as RedisClient;
use MatthiasMullie\Scrapbook\Adapters\Redis as RedisCache;

use Exception;

/**
 * Class Redis
 * @package TorrentPier\Legacy\Cache
 */
class Redis extends Common
{
    /**
     * Currently in usage
     *
     * @var bool
     */
    public bool $used = true;

    /**
     * Connection status
     *
     * @var bool
     */
    public bool $connected = false;

    /**
     * Cache driver name
     *
     * @var string
     */
    public string $engine = 'Redis';

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
        $this->dbg_enabled = Dev::sqlDebugAllowed();
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
     * Fetch data from cache
     *
     * @param string $name
     * @return mixed
     */
    public function get(string $name): mixed
    {
        if (!$this->connected) {
            $this->connect();
        }

        $name = $this->prefix . $name;

        $this->cur_query = "cache->" . __FUNCTION__ . "('$name')";
        $this->debug('start');

        $result = $this->redis->get($name);

        $this->debug('stop');
        $this->cur_query = null;
        $this->num_queries++;

        return $result;
    }

    /**
     * Store data into cache
     *
     * @param string $name
     * @param mixed $value
     * @param int $ttl
     * @return bool
     */
    public function set(string $name, mixed $value, int $ttl = 0): bool
    {
        if (!$this->connected) {
            $this->connect();
        }

        $name = $this->prefix . $name;

        $this->cur_query = "cache->" . __FUNCTION__ . "('$name')";
        $this->debug('start');

        $result = $this->redis->set($name, $value, $ttl);

        $this->debug('stop');
        $this->cur_query = null;
        $this->num_queries++;

        return $result;
    }

    /**
     * Removes data from cache
     *
     * @param string|null $name
     * @return bool
     */
    public function rm(?string $name = null): bool
    {
        if (!$this->connected) {
            $this->connect();
        }

        $targetMethod = is_string($name) ? 'delete' : 'flush';
        $name = is_string($name) ? $this->prefix . $name : null;

        $this->cur_query = "cache->$targetMethod('$name')";
        $this->debug('start');

        $result = $this->redis->$targetMethod($name);

        $this->debug('stop');
        $this->cur_query = null;
        $this->num_queries++;

        return $result;
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
