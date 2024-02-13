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
use function in_array;

/**
 * Class AbstractDatastore
 * @package TorrentPier\Legacy\Datastore
 */
class AbstractDatastore
{
    /**
     * Datastore directory
     *
     * @var string
     */
    public string $ds_dir = 'datastore';

    /**
     * @var array
     */
    public array $data = [];

    /**
     * Queued items
     *
     * @var array
     */
    public array $queued_items = [];

    /**
     * Known items
     *
     * @var array|string[]
     */
    public array $known_items = [
        'cat_forums' => 'build_cat_forums.php',
        'jumpbox' => 'build_cat_forums.php',
        'viewtopic_forum_select' => 'build_cat_forums.php',
        'latest_news' => 'build_cat_forums.php',
        'network_news' => 'build_cat_forums.php',
        'ads' => 'build_cat_forums.php',
        'moderators' => 'build_moderators.php',
        'stats' => 'build_stats.php',
        'ranks' => 'build_ranks.php',
        'ban_list' => 'build_bans.php',
        'attach_extensions' => 'build_attach_extensions.php',
        'smile_replacements' => 'build_smilies.php',
    ];

    /**
     * Enqueue items
     *
     * @param array $items
     * @return void
     */
    public function enqueue(array $items): void
    {
        foreach ($items as $item) {
            if (!in_array($item, $this->queued_items) && !isset($this->data[$item])) {
                $this->queued_items[] = $item;
            }
        }
    }

    /**
     * Get data
     *
     * @param string $title
     * @return mixed
     */
    public function &get(string $title): mixed
    {
        if (!isset($this->data[$title])) {
            $this->enqueue([$title]);
            $this->_fetch();
        }

        return $this->data[$title];
    }

    /**
     * Store
     *
     * @param string $item_name
     * @param mixed $item_data
     * @return void
     */
    public function store(string $item_name, mixed $item_data)
    {
    }

    /**
     * Removed item by name
     *
     * @param array|string $items
     * @return void
     */
    public function rm(array|string $items): void
    {
        foreach ((array)$items as $item) {
            unset($this->data[$item]);
        }
    }

    /**
     * Updated items
     *
     * @param array|string $items
     * @return void
     */
    public function update(array|string $items): void
    {
        if ($items == 'all') {
            $items = array_keys(array_unique($this->known_items));
        }

        foreach ((array)$items as $item) {
            $this->_build_item($item);
        }
    }

    /**
     * Fetch data
     *
     * @return void
     */
    public function _fetch(): void
    {
        $this->_fetch_from_store();

        foreach ($this->queued_items as $title) {
            if (!isset($this->data[$title]) || $this->data[$title] === false) {
                $this->_build_item($title);
            }
        }

        $this->queued_items = [];
    }

    /**
     * Fetch data from store
     *
     * @return void
     */
    public function _fetch_from_store()
    {
    }

    /**
     * Build item
     *
     * @param string $title
     * @return void
     */
    public function _build_item(string $title): void
    {
        $file = INC_DIR . '/' . $this->ds_dir . '/' . $this->known_items[$title];
        if (isset($this->known_items[$title]) && file_exists($file)) {
            require $file;
        } else {
            trigger_error("Unknown datastore item: $title", E_USER_ERROR);
        }
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
        if (!$this->dbg_enabled) {
            return;
        }

        $id =& $this->dbg_id;
        $dbg =& $this->dbg[$id];

        switch ($mode) {
            case 'start':
                $this->sql_starttime = utime();
                $dbg['sql'] = Dev::short_query($cur_query ?? $this->cur_query);
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
                bb_simple_die('[Datastore] Incorrect debug mode');
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
