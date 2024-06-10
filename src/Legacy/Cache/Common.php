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
        return \is_array($name) ? [] : false;
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

    public $dbg = [];
    public $dbg_id = 0;
    public $dbg_enabled = false;
    public $cur_query;

    public function debug($mode, $cur_query = null)
    {
        global $debug;

        if (!$this->dbg_enabled) {
            return;
        }

        $id =& $this->dbg_id;
        $dbg =& $this->dbg[$id];

        switch ($mode) {
            case 'start':
                $this->sql_starttime = utime();
                $dbg['sql'] = $debug->shortQuery($cur_query ?? $this->cur_query);
                $dbg['src'] = $this->debug_find_source();
                $dbg['file'] = $this->debug_find_source('file');
                $dbg['line'] = $this->debug_find_source('line');
                $dbg['time'] = '';
                break;
            case 'stop':
                $this->cur_query_time = utime() - $this->sql_starttime;
                $this->sql_timetotal += $this->cur_query_time;
                $dbg['time'] = $this->cur_query_time;
                $id++;
                break;
            default:
                bb_simple_die('[Cache] Incorrect debug mode');
                break;
        }
    }

    /**
     * Find caller source
     *
     * @param string $mode
     * @return string
     */
    public function debug_find_source(string $mode = 'all'): string
    {
        if (!SQL_PREPEND_SRC) {
            return 'src disabled';
        }
        foreach (debug_backtrace() as $trace) {
            if (!empty($trace['file']) && $trace['file'] !== __FILE__) {
                switch ($mode) {
                    case 'file':
                        return $trace['file'];
                    case 'line':
                        return $trace['line'];
                    case 'all':
                    default:
                        return hide_bb_path($trace['file']) . '(' . $trace['line'] . ')';
                }
            }
        }
        return 'src not found';
    }
}
