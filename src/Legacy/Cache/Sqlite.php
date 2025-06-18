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

use MatthiasMullie\Scrapbook\Adapters\SQLite as SQLiteCache;
use PDO;

use Exception;

/**
 * Class Sqlite
 * @package TorrentPier\Legacy\Cache
 */
class Sqlite extends Common
{
    /**
     * Currently in usage
     *
     * @var bool
     */
    public bool $used = true;

    /**
     * Cache driver name
     *
     * @var string
     */
    public string $engine = 'SQLite';

    /**
     * SQLite DB file extension
     *
     * @var string
     */
    public string $dbExtension = '.db';

    /**
     * Cache prefix
     *
     * @var string
     */
    private string $prefix;

    /**
     * Adapters\SQLite class
     *
     * @var SQLiteCache
     */
    private SQLiteCache $sqlite;

    /**
     * Sqlite constructor
     *
     * @param string $dir
     * @param string $prefix
     */
    public function __construct(string $dir, string $prefix)
    {
        if (!$this->isInstalled()) {
            throw new Exception('ext-pdo_sqlite not installed. Check out php.ini file');
        }
        $client = new PDO('sqlite:' . $dir . $this->dbExtension);
        $this->sqlite = new SQLiteCache($client);
        $this->prefix = $prefix;
        $this->dbg_enabled = dev()->checkSqlDebugAllowed();
    }

    /**
     * Fetch data from cache
     *
     * @param string $name
     * @return mixed
     */
    public function get(string $name): mixed
    {
        $name = $this->prefix . $name;

        $this->cur_query = "cache->" . __FUNCTION__ . "('$name')";
        $this->debug('start');

        $result = $this->sqlite->get($name);

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
        $name = $this->prefix . $name;

        $this->cur_query = "cache->" . __FUNCTION__ . "('$name')";
        $this->debug('start');

        $result = $this->sqlite->set($name, $value, $ttl);

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
        $targetMethod = is_string($name) ? 'delete' : 'flush';
        $name = is_string($name) ? $this->prefix . $name : null;

        $this->cur_query = "cache->$targetMethod('$name')";
        $this->debug('start');

        $result = $this->sqlite->$targetMethod($name);

        $this->debug('stop');
        $this->cur_query = null;
        $this->num_queries++;

        return $result;
    }

    /**
     * Checking if PDO SQLite is installed
     *
     * @return bool
     */
    private function isInstalled(): bool
    {
        return extension_loaded('pdo_sqlite') && class_exists('PDO');
    }
}
