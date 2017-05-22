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
 * Class Xcache
 * @package TorrentPier\Legacy\Cache
 */
class Xcache extends Common
{
    public $used = true;
    public $engine = 'XCache';
    public $prefix;

    public function __construct($prefix = null)
    {
        if (!$this->is_installed()) {
            die('Error: XCache extension not installed');
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

        return xcache_get($this->prefix . $name);
    }

    public function set($name, $value, $ttl = 0)
    {
        $this->cur_query = "cache->set('$name')";
        $this->debug('start');
        $this->debug('stop');
        $this->cur_query = null;
        $this->num_queries++;

        return xcache_set($this->prefix . $name, $value, $ttl);
    }

    public function rm($name = '')
    {
        if ($name) {
            $this->cur_query = "cache->rm('$name')";
            $this->debug('start');
            $this->debug('stop');
            $this->cur_query = null;
            $this->num_queries++;

            return xcache_unset($this->prefix . $name);
        } else {
            xcache_clear_cache(XC_TYPE_PHP, 0);
            xcache_clear_cache(XC_TYPE_VAR, 0);
            return;
        }
    }

    public function is_installed()
    {
        return function_exists('xcache_get');
    }
}
