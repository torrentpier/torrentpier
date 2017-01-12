<?php

if (!defined('BB_ROOT')) die(basename(__FILE__));

class datastore_xcache extends datastore_common
{
	public $prefix = null;
	public $engine = 'XCache';

	public function __construct ($prefix = null)
	{
		if (!$this->is_installed())
		{
			die('Error: XCache extension not installed');
		}

		$this->dbg_enabled = sql_dbg_enabled();
		$this->prefix = $prefix;
	}

	public function store ($title, $var)
	{
		$this->data[$title] = $var;

		$this->cur_query = "cache->set('$title')";
		$this->debug('start');
		$this->debug('stop');
		$this->cur_query = null;
		$this->num_queries++;

		return (bool) xcache_set($this->prefix . $title, $var);
	}

	public function clean ()
	{
		foreach ($this->known_items as $title => $script_name)
		{
			$this->cur_query = "cache->rm('$title')";
			$this->debug('start');
			$this->debug('stop');
			$this->cur_query = null;
			$this->num_queries++;

			xcache_unset($this->prefix . $title);
		}
	}

	public function _fetch_from_store ()
	{
		if (!$items = $this->queued_items)
		{
			$src = $this->_debug_find_caller('enqueue');
			trigger_error("Datastore: item '$item' already enqueued [$src]", E_USER_ERROR);
		}

		foreach ($items as $item)
		{
			$this->cur_query = "cache->set('$item')";
			$this->debug('start');
			$this->debug('stop');
			$this->cur_query = null;
			$this->num_queries++;

			$this->data[$item] = xcache_get($this->prefix . $item);
		}
	}

	public function is_installed ()
	{
		return function_exists('xcache_get');
	}
}
