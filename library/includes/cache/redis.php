<?php

if (!defined('BB_ROOT')) die(basename(__FILE__));

class cache_redis extends cache_common
{
	var $used      = true;
	var $engine    = 'Redis';
	var $cfg       = null;
	var $redis     = null;
	var $prefix    = null;
	var $connected = false;

	function cache_redis ($cfg, $prefix = null)
	{
		if (!$this->is_installed())
		{
			die('Error: Redis extension not installed');
		}

		$this->cfg    = $cfg;
		$this->prefix = $prefix;
		$this->redis  = new Redis();
		$this->dbg_enabled = sql_dbg_enabled();
	}

	function connect ()
	{
		$this->cur_query = 'connect '. $this->cfg['host'] .':'. $this->cfg['port'];
		$this->debug('start');

		if (@$this->redis->connect($this->cfg['host'], $this->cfg['port']))
		{
			$this->connected = true;
		}

		if (!$this->connected && $this->cfg['con_required'])
		{
			die('Could not connect to redis server');
		}

		$this->debug('stop');
		$this->cur_query = null;
	}

	function get ($name, $get_miss_key_callback = '', $ttl = 0)
	{
		if (!$this->connected) $this->connect();

		$this->cur_query = "cache->get('$name')";
		$this->debug('start');
		$this->debug('stop');
		$this->cur_query = null;
		$this->num_queries++;

		return ($this->connected) ? unserialize($this->redis->get($this->prefix . $name)) : false;
	}

	function set ($name, $value, $ttl = 0)
	{
		if (!$this->connected) $this->connect();

		$this->cur_query = "cache->set('$name')";
		$this->debug('start');

		if ($this->redis->set($this->prefix . $name, serialize($value)))
		{
			if ($ttl > 0)
			{
				$this->redis->expire($this->prefix . $name, $ttl);
			}

			$this->debug('stop');
			$this->cur_query = null;
			$this->num_queries++;

			return true;
		}
		else
		{
			return false;
		}
	}

	function rm ($name = '')
	{
		if (!$this->connected) $this->connect();

		if ($name)
		{
			$this->cur_query = "cache->rm('$name')";
			$this->debug('start');
			$this->debug('stop');
			$this->cur_query = null;
			$this->num_queries++;

			return ($this->connected) ? $this->redis->del($this->prefix . $name) : false;
		}
		else
		{
			return ($this->connected) ? $this->redis->flushdb() : false;
		}
	}

	function is_installed ()
	{
		return class_exists('Redis');
	}
}