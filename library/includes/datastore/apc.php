<?php

if (!defined('BB_ROOT')) die(basename(__FILE__));

class datastore_apc extends datastore_common
{
	var $engine = 'APC';
	var $prefix = null;

	function datastore_apc ($prefix = null)
	{
		if (!$this->is_installed())
		{
			die('Error: APC extension not installed');
		}
		$this->dbg_enabled = sql_dbg_enabled();
		$this->prefix = $prefix;
	}

	function store ($title, $var)
	{
		$this->data[$title] = $var;

		$this->cur_query = "cache->set('$title')";
		$this->debug('start');
		$this->debug('stop');
		$this->cur_query = null;
		$this->num_queries++;

		return (bool) apc_store($this->prefix . $title, $var);
	}

	function clean ()
	{
		foreach ($this->known_items as $title => $script_name)
		{
			$this->cur_query = "cache->rm('$title')";
			$this->debug('start');
			$this->debug('stop');
			$this->cur_query = null;
			$this->num_queries++;

			apc_delete($this->prefix . $title);
		}
	}

	function _fetch_from_store ()
	{
		if (!$items = $this->queued_items)
		{
			$src = $this->_debug_find_caller('enqueue');
			trigger_error("Datastore: item '$item' already enqueued [$src]", E_USER_ERROR);
		}

		foreach ($items as $item)
		{
			$this->cur_query = "cache->get('$item')";
			$this->debug('start');
			$this->debug('stop');
			$this->cur_query = null;
			$this->num_queries++;

			$this->data[$item] = apc_fetch($this->prefix . $item);
		}
	}

	function is_installed ()
	{
		return function_exists('apc_fetch');
	}
}