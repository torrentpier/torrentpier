<?php
/**
 * TorrentPier – Bull-powered BitTorrent tracker engine
 *
 * @copyright Copyright (c) 2005-2023 TorrentPier (https://torrentpier.com)
 * @link      https://github.com/torrentpier/torrentpier for the canonical source repository
 * @license   https://github.com/torrentpier/torrentpier/blob/master/LICENSE MIT License
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
        return \is_array($name) ? array() : false;
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
