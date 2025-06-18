<?php
/**
 * TorrentPier – Bull-powered BitTorrent tracker engine
 *
 * @copyright Copyright (c) 2005-2025 TorrentPier (https://torrentpier.com)
 * @link      https://github.com/torrentpier/torrentpier for the canonical source repository
 * @license   https://github.com/torrentpier/torrentpier/blob/master/LICENSE MIT License
 */

namespace TorrentPier\Legacy\Datastore;

use TorrentPier\Dev;

/**
 * Class Common
 * @package TorrentPier\Legacy\Datastore
 */
class Common
{
    /**
     * Директория с builder-скриптами (внутри INC_DIR)
     */
    public string $ds_dir = 'datastore';

    /**
     * Готовая к употреблению data
     * array('title' => data)
     */
    public array $data = [];

    /**
     * Список элементов, которые будут извлечены из хранилища при первом же запросе get()
     * до этого момента они ставятся в очередь $queued_items для дальнейшего извлечения _fetch()'ем
     * всех элементов одним запросом
     * array('title1', 'title2'...)
     */
    public array $queued_items = [];

    /**
     * 'title' => 'builder script name' inside "includes/datastore" dir
     */
    public array $known_items = [
        'cat_forums' => 'build_cat_forums.php',
        'censor' => 'build_censor.php',
        'check_updates' => 'build_check_updates.php',
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

    public function &get($title)
    {
        if (!isset($this->data[$title])) {
            $this->enqueue([$title]);
            $this->_fetch();
        }
        return $this->data[$title];
    }

    /**
     * Store data into cache
     *
     * @param string $item_name
     * @param mixed $item_data
     * @return bool
     */
    public function store(string $item_name, mixed $item_data): bool
    {
        return false;
    }

    public function rm($items)
    {
        foreach ((array)$items as $item) {
            unset($this->data[$item]);
        }
    }

    public function update($items)
    {
        if ($items == 'all') {
            $items = array_keys(array_unique($this->known_items));
        }
        foreach ((array)$items as $item) {
            $this->_build_item($item);
        }
    }

    public function _fetch()
    {
        $this->_fetch_from_store();

        foreach ($this->queued_items as $title) {
            if (!isset($this->data[$title]) || $this->data[$title] === false) {
                $this->_build_item($title);
            }
        }

        $this->queued_items = [];
    }

    public function _fetch_from_store()
    {
    }

    public function _build_item($title)
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
                $dbg['sql'] = dev()->formatShortQuery($cur_query ?? $this->cur_query);
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
