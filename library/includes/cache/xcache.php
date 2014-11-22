<?php

if (!defined('BB_ROOT')) die(basename(__FILE__));

class cache_xcache extends cache_common
{
	var $used   = true;
	var $engine = 'XCache';
	var $prefix = null;

	function cache_xcache ($prefix = null)
	{
		if (!$this->is_installed())
		{
			die('Error: XCache extension not installed');
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

		return xcache_get($this->prefix . $name);
	}

	function set ($name, $value, $ttl = 0)
	{
		$this->cur_query = "cache->set('$name')";
		$this->debug('start');
		$this->debug('stop');
		$this->cur_query = null;
		$this->num_queries++;

		return xcache_set($this->prefix . $name, $value, $ttl);
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

			return xcache_unset($this->prefix . $name);
		}
		else
		{
			xcache_clear_cache(XC_TYPE_PHP, 0);
			xcache_clear_cache(XC_TYPE_VAR, 0);
			return;
		}
	}

	function is_installed ()
	{
		return function_exists('xcache_get');
	}
}