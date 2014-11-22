<?php

if (!defined('BB_ROOT')) die(basename(__FILE__));

class datastore_common
{
	/**
	 * Директория с builder-скриптами (внутри INC_DIR)
	 */
	var $ds_dir = 'datastore/';
	/**
	 * Готовая к употреблению data
	 * array('title' => data)
	 */
	var $data = array();
	/**
	 * Список элементов, которые будут извлечены из хранилища при первом же запросе get()
	 * до этого момента они ставятся в очередь $queued_items для дальнейшего извлечения _fetch()'ем
	 * всех элементов одним запросом
	 * array('title1', 'title2'...)
	 */
	var $queued_items = array();

	/**
	 * 'title' => 'builder script name' inside "includes/datastore" dir
	 */
	var $known_items = array(
		'cat_forums'             => 'build_cat_forums.php',
		'jumpbox'                => 'build_cat_forums.php',
		'viewtopic_forum_select' => 'build_cat_forums.php',
		'latest_news'            => 'build_cat_forums.php',
		'network_news'           => 'build_cat_forums.php',
		'ads'                    => 'build_cat_forums.php',
		'moderators'             => 'build_moderators.php',
		'stats'                  => 'build_stats.php',
		'ranks'                  => 'build_ranks.php',
		'attach_extensions'      => 'build_attach_extensions.php',
		'smile_replacements'     => 'build_smilies.php',
	);

	/**
	 * Constructor
	 */
	function datastore_common () {}

	/**
	 * @param  array(item1_title, item2_title...) or single item's title
	 */
	function enqueue ($items)
	{
		foreach ((array) $items as $item)
		{
			// игнор уже поставленного в очередь либо уже извлеченного
			if (!in_array($item, $this->queued_items) && !isset($this->data[$item]))
			{
				$this->queued_items[] = $item;
			}
		}
	}

	function &get ($title)
	{
		if (!isset($this->data[$title]))
		{
			$this->enqueue($title);
			$this->_fetch();
		}
		return $this->data[$title];
	}

	function store ($item_name, $item_data) {}

	function rm ($items)
	{
		foreach ((array) $items as $item)
		{
			unset($this->data[$item]);
		}
	}

	function update ($items)
	{
		if ($items == 'all')
		{
			$items = array_keys(array_unique($this->known_items));
		}
		foreach ((array) $items as $item)
		{
			$this->_build_item($item);
		}
	}

	function _fetch ()
	{
		$this->_fetch_from_store();

		foreach ($this->queued_items as $title)
		{
			if (!isset($this->data[$title]) || $this->data[$title] === false)
			{
				$this->_build_item($title);
			}
		}

		$this->queued_items = array();
	}

	function _fetch_from_store () {}

	function _build_item ($title)
	{
		if (!empty($this->known_items[$title]))
		{
			require(INC_DIR . $this->ds_dir . $this->known_items[$title]);
		}
		else
		{
			trigger_error("Unknown datastore item: $title", E_USER_ERROR);
		}
	}

	var $num_queries    = 0;
	var $sql_starttime  = 0;
	var $sql_inittime   = 0;
	var $sql_timetotal  = 0;
	var $cur_query_time = 0;

	var $dbg            = array();
	var $dbg_id         = 0;
	var $dbg_enabled    = false;
	var $cur_query      = null;

	function debug ($mode, $cur_query = null)
	{
		if (!$this->dbg_enabled) return;

		$id  =& $this->dbg_id;
		$dbg =& $this->dbg[$id];

		if ($mode == 'start')
		{
			$this->sql_starttime = utime();

			$dbg['sql']  = isset($cur_query) ? short_query($cur_query) : short_query($this->cur_query);
			$dbg['src']  = $this->debug_find_source();
			$dbg['file'] = $this->debug_find_source('file');
			$dbg['line'] = $this->debug_find_source('line');
			$dbg['time'] = '';
		}
		else if ($mode == 'stop')
		{
			$this->cur_query_time = utime() - $this->sql_starttime;
			$this->sql_timetotal += $this->cur_query_time;
			$dbg['time'] = $this->cur_query_time;
			$id++;
		}
	}

	function debug_find_source ($mode = '')
	{
		foreach (debug_backtrace() as $trace)
		{
			if ($trace['file'] !== __FILE__)
			{
				switch ($mode)
				{
					case 'file': return $trace['file'];
					case 'line': return $trace['line'];
					default: return hide_bb_path($trace['file']) .'('. $trace['line'] .')';
				}
			}
		}
		return 'src not found';
	}
}