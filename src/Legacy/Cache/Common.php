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
 * Class Common
 * @package TorrentPier\Legacy\Cache
 */
class Common
{
    public $used = false;

    /**
     * Returns value of variable
     */
    public function get($name, $get_miss_key_callback = '', $ttl = 604800)
    {
        if ($get_miss_key_callback) {
            return $get_miss_key_callback($name);
        }
        return is_array($name) ? array() : false;
    }

    /**
     * Store value of variable
     */
    public function set($name, $value, $ttl = 604800)
    {
        return false;
    }

    /**
     * Remove variable
     */
    public function rm($name = '')
    {
        return false;
    }

    public $num_queries = 0;
    public $sql_starttime = 0;
    public $sql_inittime = 0;
    public $sql_timetotal = 0;
    public $cur_query_time = 0;

    public $dbg = array();
    public $dbg_id = 0;
    public $dbg_enabled = false;
    public $cur_query;

    public function debug($mode, $cur_query = null)
    {
        if (!$this->dbg_enabled) {
            return;
        }

        $id =& $this->dbg_id;
        $dbg =& $this->dbg[$id];

        if ($mode == 'start') {
            $this->sql_starttime = utime();

            $dbg['sql'] = isset($cur_query) ? short_query($cur_query) : short_query($this->cur_query);
            $dbg['src'] = $this->debug_find_source();
            $dbg['file'] = $this->debug_find_source('file');
            $dbg['line'] = $this->debug_find_source('line');
            $dbg['time'] = '';
        } elseif ($mode == 'stop') {
            $this->cur_query_time = utime() - $this->sql_starttime;
            $this->sql_timetotal += $this->cur_query_time;
            $dbg['time'] = $this->cur_query_time;
            $id++;
        }
    }

    public function debug_find_source($mode = '')
    {
        foreach (debug_backtrace() as $trace) {
            if ($trace['file'] !== __FILE__) {
                switch ($mode) {
                    case 'file':
                        return $trace['file'];
                    case 'line':
                        return $trace['line'];
                    default:
                        return hide_bb_path($trace['file']) . '(' . $trace['line'] . ')';
                }
            }
        }
        return 'src not found';
    }
}
