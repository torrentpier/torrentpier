<?php
/**
 * TorrentPier – Bull-powered BitTorrent tracker engine
 *
 * @copyright Copyright (c) 2005-2024 TorrentPier (https://torrentpier.com)
 * @link      https://github.com/torrentpier/torrentpier for the canonical source repository
 * @license   https://github.com/torrentpier/torrentpier/blob/master/LICENSE MIT License
 */

namespace TorrentPier\Legacy\Cache;

use TorrentPier\Dev;

/**
 * Class APCu
 * @package TorrentPier\Legacy\Cache
 */
class APCu extends Common
{
    public $used = true;
    public $engine = 'APCu';
    public $prefix;

    public function __construct($prefix = null)
    {
        global $debug;

        if (!$this->is_installed()) {
            die("Error: $this->engine extension not installed");
        }

        $this->prefix = $prefix;
        $this->dbg_enabled = $debug->sqlDebugAllowed();
    }

    public function get($name, $get_miss_key_callback = '', $ttl = 0)
    {
        $this->cur_query = "cache->get('$name')";
        $this->debug('start');
        $this->debug('stop');
        $this->cur_query = null;
        $this->num_queries++;

        return apcu_fetch($this->prefix . $name);
    }

    public function set($name, $value, $ttl = 0)
    {
        $this->cur_query = "cache->set('$name')";
        $this->debug('start');

        if (apcu_store($this->prefix . $name, $value, $ttl)) {
            $this->debug('stop');
            $this->cur_query = null;
            $this->num_queries++;

            return true;
        }

        return false;
    }

    public function rm($name = '')
    {
        if ($name) {
            $this->cur_query = "cache->rm('$name')";
            $this->debug('start');
            $this->debug('stop');
            $this->cur_query = null;
            $this->num_queries++;

            return apcu_delete($this->prefix . $name);
        }

        return apcu_clear_cache();
    }

    public function is_installed(): bool
    {
        return extension_loaded('apcu') && apcu_enabled();
    }
}
