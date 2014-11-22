<?php

if (!defined('BB_ROOT')) die(basename(__FILE__));

class CACHES
{
	var $cfg = array();   // конфиг
	var $obj = array();   // кеш-объекты
	var $ref = array();   // ссылки на $obj (имя_кеша => кеш_объект)

	function CACHES ($cfg)
	{
		$this->cfg = $cfg['cache'];
		$this->obj['__stub'] = new cache_common();
	}

	function get_cache_obj ($cache_name)
	{
		if (!isset($this->ref[$cache_name]))
		{
			if (!$engine_cfg =& $this->cfg['engines'][$cache_name])
			{
				$this->ref[$cache_name] =& $this->obj['__stub'];
			}
			else
			{
				$cache_type =& $engine_cfg[0];
				$cache_cfg  =& $engine_cfg[1];

				switch ($cache_type)
				{
					case 'memcache':
						if (!isset($this->obj[$cache_name]))
						{
							$this->obj[$cache_name] = new cache_memcache($this->cfg['memcache'], $this->cfg['prefix']);
						}
						$this->ref[$cache_name] =& $this->obj[$cache_name];
						break;

					case 'sqlite':
						if (!isset($this->obj[$cache_name]))
						{
							$cache_cfg['pconnect']     = $this->cfg['pconnect'];
							$cache_cfg['db_file_path'] = $this->get_db_path($cache_name, $cache_cfg, '.sqlite.db');

							$this->obj[$cache_name] = new cache_sqlite($cache_cfg, $this->cfg['prefix']);
						}
						$this->ref[$cache_name] =& $this->obj[$cache_name];
						break;

					case 'db_sqlite':
						if (!isset($this->obj[$cache_name]))
						{
							$cache_cfg['pconnect']     = $this->cfg['pconnect'];
							$cache_cfg['db_file_path'] = $this->get_db_path($cache_name, $cache_cfg, '.sqlite.db');
							$cache_cfg['table_name']   = $cache_name;
							$cache_cfg['table_schema'] = $this->get_table_schema($cache_cfg);

							$this->obj[$cache_name] = new sqlite_common($cache_cfg);
						}
						$this->ref[$cache_name] =& $this->obj[$cache_name];
						break;

					case 'redis':
						if (!isset($this->obj[$cache_name]))
						{
							$this->obj[$cache_name] = new cache_redis($this->cfg['redis'], $this->cfg['prefix']);
						}
						$this->ref[$cache_name] =& $this->obj[$cache_name];
						break;

					case 'apc':
						if (!isset($this->obj[$cache_name]))
						{
							$this->obj[$cache_name] = new cache_apc($this->cfg['prefix']);
						}
						$this->ref[$cache_name] =& $this->obj[$cache_name];
						break;

					case 'xcache':
						if (!isset($this->obj[$cache_name]))
						{
							$this->obj[$cache_name] = new cache_xcache($this->cfg['prefix']);
						}
						$this->ref[$cache_name] =& $this->obj[$cache_name];
						break;

					default: //filecache
						if (!isset($this->obj[$cache_name]))
						{
							$this->obj[$cache_name] = new cache_file($this->cfg['db_dir'] . $cache_name .'/', $this->cfg['prefix']);
						}
						$this->ref[$cache_name] =& $this->obj[$cache_name];
						break;
				}
			}
		}

		return $this->ref[$cache_name];
	}

	function get_db_path ($name, $cfg, $ext)
	{
		if (!empty($cfg['shard_type']) && $cfg['shard_type'] != 'none')
		{
			return $this->cfg['db_dir'] . $name .'_*'. $ext;
		}
		else
		{
			return $this->cfg['db_dir'] . $name . $ext;
		}
	}

	function get_table_schema ($cfg)
	{
		return "CREATE TABLE {$cfg['table_name']} ( {$cfg['columns']} )";
	}
}