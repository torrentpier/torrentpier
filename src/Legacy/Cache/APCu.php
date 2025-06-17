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

use MatthiasMullie\Scrapbook\Adapters\Apc;

use Exception;

/**
 * Class APCu
 * @package TorrentPier\Legacy\Cache
 */
class APCu extends Common
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
    public string $engine = 'APCu';

    /**
     * Cache prefix
     *
     * @var string
     */
    private string $prefix;

    /**
     * Adapters\Apc class
     *
     * @var Apc
     */
    private Apc $apcu;

    /**
     * APCu constructor
     *
     * @param string $prefix
     */
    public function __construct(string $prefix)
    {
        if (!$this->isInstalled()) {
            throw new Exception('ext-apcu not installed. Check out php.ini file');
        }
        $this->apcu = new Apc();
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

        $result = $this->apcu->get($name);

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

        $result = $this->apcu->set($name, $value, $ttl);

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

        $result = $this->apcu->$targetMethod($name);

        $this->debug('stop');
        $this->cur_query = null;
        $this->num_queries++;

        return $result;
    }

    /**
     * Checking if APCu is installed
     *
     * @return bool
     */
    private function isInstalled(): bool
    {
        return extension_loaded('apcu') && function_exists('apcu_fetch');
    }
}
