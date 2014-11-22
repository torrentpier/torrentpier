<?php

if (!defined('BB_ROOT')) die(basename(__FILE__));

class datastore_memcache extends datastore_common
{
	var $cfg       = null;
	var $memcache  = null;
	var $connected = false;
	var $engine    = 'Memcache';
	var $prefix    = null;

	function datastore_memcache ($cfg, $prefix = null)
	{
		if (!$this->is_installed())
		{
			die('Error: Memcached extension not installed');
		}

		$this->cfg         = $cfg;
		$this->prefix      = $prefix;
		$this->memcache    = new Memcache;
		$this->dbg_enabled = sql_dbg_enabled();
	}

	function connect ()
	{
		$connect_type = ($this->cfg['pconnect']) ? 'pconnect' : 'connect';

		$this->cur_query = $connect_type .' '. $this->cfg['host'] .':'. $this->cfg['port'];
		$this->debug('start');

		if (@$this->memcache->$connect_type($this->cfg['host'], $this->cfg['port']))
		{
			$this->connected = true;
		}

		if (DBG_LOG) dbg_log(' ', 'CACHE-connect'. ($this->connected ? '' : '-FAIL'));

		if (!$this->connected && $this->cfg['con_required'])
		{
			die('Could not connect to memcached server');
		}

		$this->debug('stop');
		$this->cur_query = null;
	}

	function store ($title, $var)
	{
		if (!$this->connected) $this->connect();
		$this->data[$title] = $var;

		$this->cur_query = "cache->set('$title')";
		$this->debug('start');
		$this->debug('stop');
		$this->cur_query = null;
		$this->num_queries++;

		return (bool) $this->memcache->set($this->prefix . $title, $var);
	}

	function clean ()
	{
		if (!$this->connected) $this->connect();
		foreach ($this->known_items as $title => $script_name)
		{
			$this->cur_query = "cache->rm('$title')";
			$this->debug('start');
			$this->debug('stop');
			$this->cur_query = null;
			$this->num_queries++;

			$this->memcache->delete($this->prefix . $title, 0);
		}
	}

	function _fetch_from_store ()
	{
		if (!$items = $this->queued_items)
		{
			$src = $this->_debug_find_caller('enqueue');
			trigger_error("Datastore: item '$item' already enqueued [$src]", E_USER_ERROR);
		}

		if (!$this->connected) $this->connect();
		foreach ($items as $item)
		{
			$this->cur_query = "cache->get('$item')";
			$this->debug('start');
			$this->debug('stop');
			$this->cur_query = null;
			$this->num_queries++;

			$this->data[$item] = $this->memcache->get($this->prefix . $item);
		}
	}

	function is_installed ()
	{
		return class_exists('Memcache');
	}
}