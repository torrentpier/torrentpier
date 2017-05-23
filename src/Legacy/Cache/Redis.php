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

namespace TorrentPier\Legacy\Cache;

/**
 * Class Redis
 * @package TorrentPier\Legacy\Cache
 */
class Redis extends Common
{
    public $used = true;
    public $engine = 'Redis';
    public $cfg;
    public $redis;
    public $prefix;
    public $connected = false;

    public function __construct($cfg, $prefix = null)
    {
        if (!$this->is_installed()) {
            die('Error: Redis extension not installed');
        }

        $this->cfg = $cfg;
        $this->prefix = $prefix;
        $this->redis = new \Redis();
        $this->dbg_enabled = sql_dbg_enabled();
    }

    public function connect()
    {
        $this->cur_query = 'connect ' . $this->cfg['host'] . ':' . $this->cfg['port'];
        $this->debug('start');

        if (@$this->redis->connect($this->cfg['host'], $this->cfg['port'])) {
            $this->connected = true;
        }

        if (!$this->connected && $this->cfg['con_required']) {
            die('Could not connect to redis server');
        }

        $this->debug('stop');
        $this->cur_query = null;
    }

    public function get($name, $get_miss_key_callback = '', $ttl = 0)
    {
        if (!$this->connected) {
            $this->connect();
        }

        $this->cur_query = "cache->get('$name')";
        $this->debug('start');
        $this->debug('stop');
        $this->cur_query = null;
        $this->num_queries++;

        return ($this->connected) ? unserialize($this->redis->get($this->prefix . $name)) : false;
    }

    public function set($name, $value, $ttl = 0)
    {
        if (!$this->connected) {
            $this->connect();
        }

        $this->cur_query = "cache->set('$name')";
        $this->debug('start');

        if ($this->redis->set($this->prefix . $name, serialize($value))) {
            if ($ttl > 0) {
                $this->redis->expire($this->prefix . $name, $ttl);
            }

            $this->debug('stop');
            $this->cur_query = null;
            $this->num_queries++;

            return true;
        } else {
            return false;
        }
    }

    public function rm($name = '')
    {
        if (!$this->connected) {
            $this->connect();
        }

        if ($name) {
            $this->cur_query = "cache->rm('$name')";
            $this->debug('start');
            $this->debug('stop');
            $this->cur_query = null;
            $this->num_queries++;

            return ($this->connected) ? $this->redis->del($this->prefix . $name) : false;
        } else {
            return ($this->connected) ? $this->redis->flushDB() : false;
        }
    }

    public function is_installed()
    {
        return class_exists('Redis');
    }
}
