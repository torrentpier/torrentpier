<?php

if (!defined('BB_ROOT')) die(basename(__FILE__));

class cache_common
{
	var $used = false;
	/**
	 * Returns value of variable
	 */
	function get ($name, $get_miss_key_callback = '', $ttl = 604800)
	{
		if ($get_miss_key_callback) return $get_miss_key_callback($name);
		return is_array($name) ? array() : false;
	}
	/**
	 * Store value of variable
	 */
	function set ($name, $value, $ttl = 604800)
	{
		return false;
	}
	/**
	 * Remove variable
	 */
	function rm ($name = '')
	{
		return false;
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