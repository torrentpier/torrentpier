<?php

if (!defined('BB_ROOT')) die(basename(__FILE__));

class cache_apc extends cache_common
{
	var $used   = true;
	var $engine = 'APC';
	var $prefix = null;

	function cache_apc ($prefix = null)
	{
		if (!$this->is_installed())
		{
			die('Error: APC extension not installed');
		}
		$this->dbg_enabled = sql_dbg_enabled();
		$this->prefix = $prefix;
	}

	function get ($name, $get_miss_key_callback = '', $ttl = 0)
	{
		$this->cur_query = "cache->get('$name')";
		$this->debug('start');
		$this->debug('stop');
		$this->cur_query = null;
		$this->num_queries++;

		return apc_fetch($this->prefix . $name);
	}

	function set ($name, $value, $ttl = 0)
	{
		$this->cur_query = "cache->set('$name')";
		$this->debug('start');
		$this->debug('stop');
		$this->cur_query = null;
		$this->num_queries++;

		return apc_store($this->prefix . $name, $value, $ttl);
	}

	function rm ($name = '')
	{
		if ($name)
		{
			$this->cur_query = "cache->rm('$name')";
			$this->debug('start');
			$this->debug('stop');
			$this->cur_query = null;
			$this->num_queries++;

			return apc_delete($this->prefix . $name);
		}
		else
		{
			return apc_clear_cache();
		}
	}

	function is_installed ()
	{
		return function_exists('apc_fetch');
	}
}