<?php

if (!defined('BB_ROOT')) die(basename(__FILE__));

class datastore_sqlite extends datastore_common
{
	var $engine = 'SQLite';
	var $db     = null;
	var $prefix = null;
	var $cfg    = array(
		'db_file_path' => '/path/to/datastore.db.sqlite',
		'table_name'   => 'datastore',
		'table_schema' => 'CREATE TABLE datastore (
	            ds_title       VARCHAR(255),
	            ds_data        TEXT,
	            PRIMARY KEY (ds_title)
	        )',
		'pconnect'     => true,
		'con_required' => true,
		'log_name'     => 'DATASTORE',
	);

	function datastore_sqlite ($cfg, $prefix = null)
	{
		$this->cfg = array_merge($this->cfg, $cfg);
		$this->db  = new sqlite_common($this->cfg);
		$this->prefix = $prefix;
	}

	function store ($item_name, $item_data)
	{
		$this->data[$item_name] = $item_data;

		$ds_title = SQLite3::escapeString($this->prefix . $item_name);
		$ds_data  = SQLite3::escapeString(serialize($item_data));

		$result = $this->db->query("REPLACE INTO ". $this->cfg['table_name'] ." (ds_title, ds_data) VALUES ('$ds_title', '$ds_data')");

		return (bool) $result;
	}

	function clean ()
	{
		$this->db->query("DELETE FROM ". $this->cfg['table_name']);
	}

	function _fetch_from_store ()
	{
		if (!$items = $this->queued_items) return;

		$prefix_len = strlen($this->prefix);
		$prefix_sql = SQLite3::escapeString($this->prefix);

		array_deep($items, 'SQLite3::escapeString');
		$items_list = $prefix_sql . join("','$prefix_sql", $items);

		$rowset = $this->db->fetch_rowset("SELECT ds_title, ds_data FROM ". $this->cfg['table_name'] ." WHERE ds_title IN ('$items_list')");

		$this->db->debug('start', "unserialize()");
		foreach ($rowset as $row)
		{
			$this->data[substr($row['ds_title'], $prefix_len)] = unserialize($row['ds_data']);
		}
		$this->db->debug('stop');
	}
}