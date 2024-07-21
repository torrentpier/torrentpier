<?php
/**
 * TorrentPier – Bull-powered BitTorrent tracker engine
 *
 * @copyright Copyright (c) 2005-2024 TorrentPier (https://torrentpier.com)
 * @link      https://github.com/torrentpier/torrentpier for the canonical source repository
 * @license   https://github.com/torrentpier/torrentpier/blob/master/LICENSE MIT License
 */

namespace TorrentPier\Legacy\Cache;

use MatthiasMullie\Scrapbook\Adapters\Apc;

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
    public string $prefix;

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
        global $debug;

        $this->apcu = new Apc();
        $this->prefix = $prefix;
        $this->dbg_enabled = $debug->sqlDebugAllowed();
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

        $this->cur_query = "cache->get('$name')";
        $this->debug('start');

        if ($result = $this->apcu->get($name)) {
            $this->debug('stop');
            $this->cur_query = null;
            $this->num_queries++;
        }

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

        $this->cur_query = "cache->set('$name')";
        $this->debug('start');

        if ($result = $this->apcu->set($name, $value, $ttl)) {
            $this->debug('stop');
            $this->cur_query = null;
            $this->num_queries++;
        }

        return $result;
    }

    /**
     * Removes data from cache
     *
     * @param string|null $name
     * @return bool
     */
    public function rm(string $name = null): bool
    {
        if (is_string($name)) {
            $name = $this->prefix . $name;

            $this->cur_query = "cache->rm('$name')";
            $this->debug('start');

            if ($result = $this->apcu->delete($name)) {
                $this->debug('stop');
                $this->cur_query = null;
                $this->num_queries++;
            }

            return $result;
        }

        return $this->apcu->flush();
    }
}
