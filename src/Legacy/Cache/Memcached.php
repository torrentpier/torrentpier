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

use Memcached as MemcachedClient;
use MatthiasMullie\Scrapbook\Adapters\Memcached as MemcachedCache;

use Exception;

/**
 * Class Memcached
 * @package TorrentPier\Legacy\Cache
 */
class Memcached extends Common
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
    public string $engine = 'Memcached';

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
        if (!$this->isInstalled()) {
            throw new Exception('ext-memcached not installed. Check out php.ini file');
        }
        $this->client = new MemcachedClient();
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
        $this->cur_query = 'connect ' . $this->cfg['host'] . ':' . $this->cfg['port'];
        $this->debug('start');

        if ($this->client->addServer($this->cfg['host'], $this->cfg['port'])) {
            $this->connected = true;
        }

        if (!$this->connected) {
            throw new Exception("Could not connect to $this->engine server");
        }

        $this->memcached = new MemcachedCache($this->client);

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

        $result = $this->memcached->get($name);

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

        $result = $this->memcached->set($name, $value, $ttl);

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

        $result = $this->memcached->$targetMethod($name);

        $this->debug('stop');
        $this->cur_query = null;
        $this->num_queries++;

        return $result;
    }

    /**
     * Checking if Memcached is installed
     *
     * @return bool
     */
    private function isInstalled(): bool
    {
        return extension_loaded('memcached') && class_exists('Memcached');
    }
}
