<?php

if (!defined('BB_ROOT')) die(basename(__FILE__));

class cache_sqlite extends cache_common
{
	var $used   = true;
	var $db     = null;
	var $prefix = null;
	var $cfg    = array(
		'db_file_path' => '/path/to/cache.db.sqlite',
		'table_name'   => 'cache',
		'table_schema' => 'CREATE TABLE cache (
	                cache_name        VARCHAR(255),
	                cache_expire_time INT,
	                cache_value       TEXT,
	                PRIMARY KEY (cache_name)
	        )',
		'pconnect'     => true,
		'con_required' => true,
		'log_name'     => 'CACHE',
	);

	function cache_sqlite ($cfg, $prefix = null)
	{
		$this->cfg = array_merge($this->cfg, $cfg);
		$this->db = new sqlite_common($this->cfg);
		$this->prefix = $prefix;
	}

	function get ($name, $get_miss_key_callback = '', $ttl = 604800)
	{
		if (empty($name))
		{
			return is_array($name) ? array() : false;
		}
		$this->db->shard($name);
		$cached_items = array();
		$this->prefix_len = strlen($this->prefix);
		$this->prefix_sql = sqlite_escape_string($this->prefix);

		$name_ary = $name_sql = (array) $name;
		array_deep($name_sql, 'sqlite_escape_string');

		// get available items
		$rowset = $this->db->fetch_rowset("
			SELECT cache_name, cache_value
			FROM ". $this->cfg['table_name'] ."
			WHERE cache_name IN('$this->prefix_sql". join("','$this->prefix_sql", $name_sql) ."') AND cache_expire_time > ". TIMENOW ."
			LIMIT ". count($name) ."
		");

		$this->db->debug('start', 'unserialize()');
		foreach ($rowset as $row)
		{
			$cached_items[substr($row['cache_name'], $this->prefix_len)] = unserialize($row['cache_value']);
		}
		$this->db->debug('stop');

		// get miss items
		if ($get_miss_key_callback AND $miss_key = array_diff($name_ary, array_keys($cached_items)))
		{
			foreach ($get_miss_key_callback($miss_key) as $k => $v)
			{
				$this->set($this->prefix . $k, $v, $ttl);
				$cached_items[$k] = $v;
			}
		}
		// return
		if (is_array($this->prefix . $name))
		{
			return $cached_items;
		}
		else
		{
			return isset($cached_items[$name]) ? $cached_items[$name] : false;
		}
	}

	function set ($name, $value, $ttl = 604800)
	{
		$this->db->shard($this->prefix . $name);
		$name_sql  = sqlite_escape_string($this->prefix . $name);
		$expire    = TIMENOW + $ttl;
		$value_sql = sqlite_escape_string(serialize($value));

		$result = $this->db->query("REPLACE INTO ". $this->cfg['table_name'] ." (cache_name, cache_expire_time, cache_value) VALUES ('$name_sql', $expire, '$value_sql')");
		return (bool) $result;
	}

	function rm ($name = '')
	{
		if ($name)
		{
			$this->db->shard($this->prefix . $name);
			$result = $this->db->query("DELETE FROM ". $this->cfg['table_name'] ." WHERE cache_name = '". sqlite_escape_string($this->prefix . $name) ."'");
		}
		else
		{
			$result = $this->db->query("DELETE FROM ". $this->cfg['table_name']);
		}
		return (bool) $result;
	}

	function gc ($expire_time = TIMENOW)
	{
		$result = $this->db->query("DELETE FROM ". $this->cfg['table_name'] ." WHERE cache_expire_time < $expire_time");
		return ($result) ? $this->db->changes() : 0;
	}
}