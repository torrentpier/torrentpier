<?php
/**
 * TorrentPier – Bull-powered BitTorrent tracker engine
 *
 * @copyright Copyright (c) 2005-2024 TorrentPier (https://torrentpier.com)
 * @link      https://github.com/torrentpier/torrentpier for the canonical source repository
 * @license   https://github.com/torrentpier/torrentpier/blob/master/LICENSE MIT License
 */

namespace TorrentPier\Legacy\Datastore;

use TorrentPier\Dev;

/**
 * Class Redis
 * @package TorrentPier\Legacy\Datastore
 */
class Redis extends Common
{
    public $cfg;
    public $redis;
    public $prefix;
    public $connected = false;
    public $engine = 'Redis';

    public function __construct($cfg, $prefix = null)
    {
        if (!$this->is_installed()) {
            die("Error: $this->engine extension not installed");
        }

        $this->cfg = $cfg;
        $this->redis = new \Redis();
        $this->dbg_enabled = Dev::sql_dbg_enabled();
        $this->prefix = $prefix;
    }

    public function connect()
    {
        $connect_type = ($this->cfg['pconnect']) ? 'pconnect' : 'connect';

        $this->cur_query = $connect_type . ' ' . $this->cfg['host'] . ':' . $this->cfg['port'];
        $this->debug('start');

        if (@$this->redis->$connect_type($this->cfg['host'], $this->cfg['port'])) {
            $this->connected = true;
        }

        if (!$this->connected && $this->cfg['con_required']) {
            die("Could not connect to $this->engine server");
        }

        $this->debug('stop');
        $this->cur_query = null;
    }

    public function store($title, $var)
    {
        if (!$this->connected) {
            $this->connect();
        }
        $this->data[$title] = $var;

        $this->cur_query = "cache->set('$title')";
        $this->debug('start');
        $this->debug('stop');
        $this->cur_query = null;
        $this->num_queries++;

        return (bool)$this->redis->set($this->prefix . $title, serialize($var));
    }

    public function clean()
    {
        if (!$this->connected) {
            $this->connect();
        }
        foreach ($this->known_items as $title => $script_name) {
            $this->cur_query = "cache->rm('$title')";
            $this->debug('start');
            $this->debug('stop');
            $this->cur_query = null;
            $this->num_queries++;

            $this->redis->del($this->prefix . $title);
        }
    }

    public function _fetch_from_store()
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
            $this->cur_query = "cache->get('$item')";
            $this->debug('start');
            $this->debug('stop');
            $this->cur_query = null;
            $this->num_queries++;

            $this->data[$item] = unserialize($this->redis->get($this->prefix . $item));
        }
    }

    public function is_installed()
    {
        return class_exists('Redis');
    }
}
