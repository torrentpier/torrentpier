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
 * Class Memcache
 * @package TorrentPier\Legacy\Datastore
 */
class Memcache extends Common
{
    public $cfg;
    public $memcache;
    public $connected = false;
    public $engine = 'Memcache';
    public $prefix;

    public function __construct($cfg, $prefix = null)
    {
        if (!$this->is_installed()) {
            die("Error: $this->engine extension not installed");
        }

        $this->cfg = $cfg;
        $this->prefix = $prefix;
        $this->memcache = new \Memcache();
        $this->dbg_enabled = Dev::sql_dbg_enabled();
    }

    public function connect()
    {
        $connect_type = ($this->cfg['pconnect']) ? 'pconnect' : 'connect';

        $this->cur_query = $connect_type . ' ' . $this->cfg['host'] . ':' . $this->cfg['port'];
        $this->debug('start');

        if (@$this->memcache->$connect_type($this->cfg['host'], $this->cfg['port'])) {
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

        return (bool)$this->memcache->set($this->prefix . $title, $var);
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

            $this->memcache->delete($this->prefix . $title, 0);
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

            $this->data[$item] = $this->memcache->get($this->prefix . $item);
        }
    }

    public function is_installed()
    {
        return class_exists('Memcache');
    }
}
