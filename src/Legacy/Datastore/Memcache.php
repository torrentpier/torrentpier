<?php
/**
 * MIT License
 *
 * Copyright (c) 2005-2017 TorrentPier
 *
 * Permission is hereby granted, free of charge, to any person obtaining a copy
 * of this software and associated documentation files (the "Software"), to deal
 * in the Software without restriction, including without limitation the rights
 * to use, copy, modify, merge, publish, distribute, sublicense, and/or sell
 * copies of the Software, and to permit persons to whom the Software is
 * furnished to do so, subject to the following conditions:
 *
 * The above copyright notice and this permission notice shall be included in all
 * copies or substantial portions of the Software.
 *
 * THE SOFTWARE IS PROVIDED "AS IS", WITHOUT WARRANTY OF ANY KIND, EXPRESS OR
 * IMPLIED, INCLUDING BUT NOT LIMITED TO THE WARRANTIES OF MERCHANTABILITY,
 * FITNESS FOR A PARTICULAR PURPOSE AND NONINFRINGEMENT. IN NO EVENT SHALL THE
 * AUTHORS OR COPYRIGHT HOLDERS BE LIABLE FOR ANY CLAIM, DAMAGES OR OTHER
 * LIABILITY, WHETHER IN AN ACTION OF CONTRACT, TORT OR OTHERWISE, ARISING FROM,
 * OUT OF OR IN CONNECTION WITH THE SOFTWARE OR THE USE OR OTHER DEALINGS IN THE
 * SOFTWARE.
 */

namespace TorrentPier\Legacy\Datastore;

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
            die('Error: Memcached extension not installed');
        }

        $this->cfg = $cfg;
        $this->prefix = $prefix;
        $this->memcache = new \Memcache();
        $this->dbg_enabled = sql_dbg_enabled();
    }

    public function connect()
    {
        $connect_type = ($this->cfg['pconnect']) ? 'pconnect' : 'connect';

        $this->cur_query = $connect_type . ' ' . $this->cfg['host'] . ':' . $this->cfg['port'];
        $this->debug('start');

        if (@$this->memcache->$connect_type($this->cfg['host'], $this->cfg['port'])) {
            $this->connected = true;
        }

        if (DBG_LOG) {
            dbg_log(' ', 'CACHE-connect' . ($this->connected ? '' : '-FAIL'));
        }

        if (!$this->connected && $this->cfg['con_required']) {
            die('Could not connect to memcached server');
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
