<?php
/**
 * TorrentPier – Bull-powered BitTorrent tracker engine
 *
 * @copyright Copyright (c) 2005-2018 TorrentPier (https://torrentpier.com)
 * @link      https://github.com/torrentpier/torrentpier for the canonical source repository
 * @license   https://github.com/torrentpier/torrentpier/blob/master/LICENSE MIT License
 */

namespace TorrentPier\Legacy\Cache;

/**
 * Class Apc
 * @package TorrentPier\Legacy\Cache
 */
class Apc extends Common
{
    public $used = true;
    public $engine = 'APC';
    public $prefix;

    public function __construct($prefix = null)
    {
        if (!$this->is_installed()) {
            die('Error: APC extension not installed');
        }
        $this->dbg_enabled = sql_dbg_enabled();
        $this->prefix = $prefix;
    }

    public function get($name, $get_miss_key_callback = '', $ttl = 0)
    {
        $this->cur_query = "cache->get('$name')";
        $this->debug('start');
        $this->debug('stop');
        $this->cur_query = null;
        $this->num_queries++;

        return apc_fetch($this->prefix . $name);
    }

    public function set($name, $value, $ttl = 0)
    {
        $this->cur_query = "cache->set('$name')";
        $this->debug('start');
        $this->debug('stop');
        $this->cur_query = null;
        $this->num_queries++;

        return apc_store($this->prefix . $name, $value, $ttl);
    }

    public function rm($name = '')
    {
        if ($name) {
            $this->cur_query = "cache->rm('$name')";
            $this->debug('start');
            $this->debug('stop');
            $this->cur_query = null;
            $this->num_queries++;

            return apc_delete($this->prefix . $name);
        }

        return apc_clear_cache();
    }

    public function is_installed()
    {
        return \function_exists('apc_fetch');
    }
}
