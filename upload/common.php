<?php

if (isset($_REQUEST['GLOBALS'])) die();

ignore_user_abort(true);
define('TIMESTART', utime());
define('TIMENOW',   time());

if (empty($_SERVER['REMOTE_ADDR']))     $_SERVER['REMOTE_ADDR'] = '127.0.0.1';
if (empty($_SERVER['HTTP_USER_AGENT'])) $_SERVER['HTTP_USER_AGENT'] = '';
if (empty($_SERVER['HTTP_REFERER']))    $_SERVER['HTTP_REFERER'] = '';
if (empty($_SERVER['SERVER_NAME']))     $_SERVER['SERVER_NAME'] = '';

if (!defined('BB_ROOT')) define('BB_ROOT', './');
if (!defined('IN_FORUM') && !defined('IN_TRACKER')) define('IN_FORUM', true);

header('X-Frame-Options: SAMEORIGIN');

// Get initial config
require(BB_ROOT .'config.php');

$server_protocol = ($bb_cfg['cookie_secure']) ? 'https://' : 'http://';
$server_port = ($bb_cfg['server_port'] != 80) ? ':'. $bb_cfg['server_port'] : '';
define('FORUM_PATH', $bb_cfg['script_path']);
define('FULL_URL', $server_protocol . $bb_cfg['server_name'] . $server_port . $bb_cfg['script_path']);
unset($server_protocol, $server_port);

// Debug options
define('DBG_USER', (isset($_COOKIE[COOKIE_DBG])));

// Board/Tracker shared constants and functions
define('BB_BT_TORRENTS',     'bb_bt_torrents');
define('BB_BT_TRACKER',      'bb_bt_tracker');
define('BB_BT_TRACKER_SNAP', 'bb_bt_tracker_snap');
define('BB_BT_USERS',        'bb_bt_users');

define('BT_AUTH_KEY_LENGTH', 10);

define('DL_STATUS_RELEASER', -1);
define('DL_STATUS_DOWN',      0);
define('DL_STATUS_COMPLETE',  1);
define('DL_STATUS_CANCEL',    3);
define('DL_STATUS_WILL',      4);

define('TOR_TYPE_GOLD',       1);
define('TOR_TYPE_SILVER',     2);

define('GUEST_UID', -1);
define('BOT_UID',   -746);

// DBS
class DBS
{
	var $cfg   = array();   // $srv_name  => $srv_cfg
	var $srv   = array();   // $srv_name  => $db_obj
	var $alias = array();   // $srv_alias => $srv_name

	var $log_file      = 'sql_queries';
	var $log_counter   = 0;
	var $num_queries   = 0;
	var $sql_inittime  = 0;
	var $sql_timetotal = 0;

	function DBS ($cfg)
	{
		$this->cfg   = $cfg['db'];
		$this->alias = $cfg['db_alias'];

		foreach ($this->cfg as $srv_name => $srv_cfg)
		{
			$this->srv[$srv_name] = null;
		}
	}

	// получение/инициализация класса для сервера $srv_name
	function get_db_obj ($srv_name_or_alias = 'db1')
	{
		$srv_name = $this->get_srv_name($srv_name_or_alias);

		if (!is_object($this->srv[$srv_name]))
		{
			$this->srv[$srv_name] = new sql_db($this->cfg[$srv_name]);
			$this->srv[$srv_name]->db_server = $srv_name;
		}
		return $this->srv[$srv_name];
	}

	// определение имени сервера
	function get_srv_name ($name)
	{
		if (isset($this->alias[$name]))
		{
			$srv_name = $this->alias[$name];
		}
		else if (isset($this->cfg[$name]))
		{
			$srv_name = $name;
		}
		else
		{
			$srv_name = 'db1';
		}
		return $srv_name;
	}
}

$DBS = new DBS($bb_cfg);

function DB ($db_alias = 'db1')
{
	global $DBS;
	return $DBS->get_db_obj($db_alias);
}

// Cache
define('PEER_HASH_PREFIX',  'peer_');
define('PEERS_LIST_PREFIX', 'peers_list_');

define('PEER_HASH_EXPIRE',  round($bb_cfg['announce_interval'] * (0.85*$tr_cfg['expire_factor'])));  // sec
define('PEERS_LIST_EXPIRE', round($bb_cfg['announce_interval'] * 0.7));  // sec

if (!function_exists('sqlite_escape_string'))
{
	function sqlite_escape_string($string)
	{
		return SQLite3::escapeString($string);
	}
}

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

					case 'eaccelerator':
						if (!isset($this->obj[$cache_name]))
						{
							$this->obj[$cache_name] = new cache_eaccelerator($this->cfg['prefix']);
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

$CACHES = new CACHES($bb_cfg);

function CACHE ($cache_name)
{
	global $CACHES;
	return $CACHES->get_cache_obj($cache_name);
}

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

class cache_memcache extends cache_common
{
	var $used      = true;
	var $engine    = 'Memcache';
	var $cfg       = null;
	var $prefix    = null;
	var $memcache  = null;
	var $connected = false;

	function cache_memcache ($cfg, $prefix = null)
	{
		if (!$this->is_installed())
		{
			die('Error: Memcached extension not installed');
		}

		$this->cfg      = $cfg;
		$this->prefix   = $prefix;
		$this->memcache = new Memcache;
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

	function get ($name, $get_miss_key_callback = '', $ttl = 0)
	{
		if (!$this->connected) $this->connect();

		$this->cur_query = "cache->get('$name')";
		$this->debug('start');
		$this->debug('stop');
		$this->cur_query = null;
		$this->num_queries++;

		return ($this->connected) ? $this->memcache->get($this->prefix . $name) : false;
	}

	function set ($name, $value, $ttl = 0)
	{
		if (!$this->connected) $this->connect();

		$this->cur_query = "cache->set('$name')";
		$this->debug('start');
		$this->debug('stop');
		$this->cur_query = null;
		$this->num_queries++;

		return ($this->connected) ? $this->memcache->set($this->prefix . $name, $value, false, $ttl) : false;
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

			return ($this->connected) ? $this->memcache->delete($this->prefix . $name, 0) : false;
		}
		else
		{
			return ($this->connected) ? $this->memcache->flush() : false;
		}
	}

	function is_installed ()
	{
		return class_exists('Memcache');
	}
}

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

class sqlite_common extends cache_common
{
	var $cfg = array(
	        'db_file_path' => 'sqlite.db',
	        'table_name'   => 'table_name',
	        'table_schema' => 'CREATE TABLE table_name (...)',
	        'pconnect'     => true,
	        'con_required' => true,
	        'log_name'     => 'SQLite',
	        'shard_type'   => 'none',     #  none, string, int (тип перевичного ключа для шардинга)
	        'shard_val'    => 0,          #  для string - кол. начальных символов, для int - делитель (будет использован остаток от деления)
	    );
	var $engine    = 'SQLite';
	var $dbh       = null;
	var $connected = false;
	var $shard_val = false;

	var $table_create_attempts = 0;

	function sqlite_common ($cfg)
	{
		$this->cfg = array_merge($this->cfg, $cfg);
		$this->dbg_enabled = sql_dbg_enabled();
	}

	function connect ()
	{
		$this->cur_query = ($this->dbg_enabled) ? 'connect to: '. $this->cfg['db_file_path'] : 'connect';
		$this->debug('start');

		if (@$this->dbh = new SQLite3($this->cfg['db_file_path']))
		{
			$this->connected = true;
		}

		if (DBG_LOG) dbg_log(' ', $this->cfg['log_name'] .'-connect'. ($this->connected ? '' : '-FAIL'));

		if (!$this->connected && $this->cfg['con_required'])
		{
			trigger_error('SQLite not connected', E_USER_ERROR);
		}

		$this->debug('stop');
		$this->cur_query = null;
	}

	function create_table ()
	{
		$this->table_create_attempts++;
		return $this->dbh->query($this->cfg['table_schema']);
	}

	function shard ($name)
	{
		$type = $this->cfg['shard_type'];

		if ($type == 'none') return;
		if (is_array($name))  trigger_error('cannot shard: $name is array', E_USER_ERROR);

		// define shard_val
		if ($type == 'string')
		{
			$shard_val = substr($name, 0, $this->cfg['shard_val']);
		}
		else
		{
			$shard_val = $name % $this->cfg['shard_val'];
		}
		// все запросы должны быть к одному и тому же шарду
		if ($this->shard_val !== false)
		{
			if ($shard_val != $this->shard_val)
			{
				trigger_error("shard cannot be reassigned. [{$this->shard_val}, $shard_val, $name]", E_USER_ERROR);
			}
			else
			{
				return;
			}
		}
		$this->shard_val = $shard_val;
		$this->cfg['db_file_path'] = str_replace('*', $shard_val, $this->cfg['db_file_path']);
	}

	function query ($query)
	{
		if (!$this->connected) $this->connect();

		$this->cur_query = $query;
		$this->debug('start');

		if (!$result = @$this->dbh->query($query))
		{
			$rowsresult = $this->dbh->query("PRAGMA table_info({$this->cfg['table_name']})");
			$rowscount = 0;
			while ($row = $rowsresult->fetchArray(SQLITE3_ASSOC))
			{
				$rowscount++;
			}
			if (!$this->table_create_attempts && !$rowscount)
			{
				if ($this->create_table())
				{
					$result = $this->dbh->query($query);
				}
			}
			if (!$result)
			{
				$this->trigger_error($this->get_error_msg());
			}
		}

		$this->debug('stop');
		$this->cur_query = null;

		$this->num_queries++;

		return $result;
	}

	function fetch_row ($query)
	{
		$result = $this->query($query);
		return is_resource($result) ? $result->fetchArray(SQLITE3_ASSOC) : false;
	}

	function fetch_rowset ($query)
	{
		$result = $this->query($query);
		$rowset = array();
		while ($row = $result->fetchArray(SQLITE3_ASSOC))
		{
			$rowset[] = $row;
		}
		return $rowset;
	}

	function changes ()
	{
		return is_resource($this->dbh) ? $this->dbh->changes() : 0;
	}

	function escape ($str)
	{
		return sqlite_escape_string($str);
	}

	function get_error_msg ()
	{
		return 'SQLite error #'. ($err_code = $this->dbh->lastErrorCode()) .': '. $this->dbh->lastErrorMsg();
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
		return ($result) ? sqlite_changes($this->db->dbh) : 0;
	}

	function trigger_error ($msg = 'DB Error')
	{
		if (error_reporting()) trigger_error($msg, E_USER_ERROR);
	}
}

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

class cache_eaccelerator extends cache_common
{
	var $used   = true;
	var $engine = 'eAccelerator';
	var $prefix = null;

	function cache_eaccelerator ($prefix = null)
	{
		if (!$this->is_installed())
		{
			die('Error: eAccelerator extension not installed');
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

		return eaccelerator_get($this->prefix . $name);
	}

	function set ($name, $value, $ttl = 0)
	{
		$this->cur_query = "cache->set('$name')";
		$this->debug('start');
		$this->debug('stop');
		$this->cur_query = null;
		$this->num_queries++;

		return eaccelerator_put($this->prefix . $name, $value, $ttl);
	}

	function rm ($name = '')
	{
		$this->cur_query = "cache->rm('$name')";
		$this->debug('start');
		$this->debug('stop');
		$this->cur_query = null;
		$this->num_queries++;

		return eaccelerator_rm($this->prefix . $name);
	}

	function is_installed ()
	{
		return function_exists('eaccelerator_get');
	}
}

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

class cache_file extends cache_common
{
	var $used   = true;
	var $engine = 'Filecache';
	var $dir    = null;
	var $prefix = null;

	function cache_file ($dir, $prefix = null)
	{
		$this->dir    = $dir;
		$this->prefix = $prefix;
		$this->dbg_enabled = sql_dbg_enabled();
	}

	function get ($name, $get_miss_key_callback = '', $ttl = 0)
	{
		$filename = $this->dir . clean_filename($this->prefix . $name) . '.php';

		$this->cur_query = "cache->set('$name')";
		$this->debug('start');

		if (file_exists($filename))
		{
			require($filename);
		}

		$this->debug('stop');
		$this->cur_query = null;

		return (!empty($filecache['value'])) ? $filecache['value'] : false;
	}

	function set ($name, $value, $ttl = 86400)
	{
		if (!function_exists('var_export'))
		{
			return false;
		}

		$this->cur_query = "cache->set('$name')";
		$this->debug('start');

		$filename   = $this->dir . clean_filename($this->prefix . $name) . '.php';
		$expire     = TIMENOW + $ttl;
		$cache_data = array(
			'expire'  => $expire,
			'value'   => $value,
		);

		$filecache = "<?php\n";
		$filecache .= "if (!defined('BB_ROOT')) die(basename(__FILE__));\n";
		$filecache .= '$filecache = ' . var_export($cache_data, true) . ";\n";
		$filecache .= '?>';

		$this->debug('stop');
		$this->cur_query = null;
		$this->num_queries++;

		return (bool) file_write($filecache, $filename, false, true, true);
	}

	function rm ($name = '')
	{
		$clear = false;
		if ($name)
		{
			$this->cur_query = "cache->rm('$name')";
			$this->debug('start');

			$filename = $this->dir . clean_filename($this->prefix . $name) . '.php';
			if (file_exists($filename))
			{
				$clear = (bool) unlink($filename);
			}

			$this->debug('stop');
			$this->cur_query = null;
			$this->num_queries++;
		}
		else
		{
			if (is_dir($this->dir))
			{
				if ($dh = opendir($this->dir))
				{
					while (($file = readdir($dh)) !== false)
					{
						if ($file != "." && $file != "..")
						{
							$filename = $this->dir . $file;

							unlink($filename);
							$clear = true;
						}
					}
					closedir($dh);
				}
			}
		}
		return $clear;
	}

	function gc ($expire_time = TIMENOW)
	{
		$clear = false;

		if (is_dir($this->dir))
		{
			if ($dh = opendir($this->dir))
			{
				while (($file = readdir($dh)) !== false)
				{
					if ($file != "." && $file != "..")
					{
						$filename = $this->dir . $file;

						require($filename);

						if(!empty($filecache['expire']) && ($filecache['expire'] < $expire_time))
						{
							unlink($filename);
							$clear = true;
						}
					}
				}
				closedir($dh);
			}
		}

		return $clear;
	}
}

/**
* Datastore
*/
class datastore_common
{
	/**
	* Директория с builder-скриптами (внутри INC_DIR)
	*/
	var $ds_dir = 'datastore/';
	/**
	* Готовая к употреблению data
	* array('title' => data)
	*/
	var $data = array();
	/**
	* Список элементов, которые будут извлечены из хранилища при первом же запросе get()
	* до этого момента они ставятся в очередь $queued_items для дальнейшего извлечения _fetch()'ем
	* всех элементов одним запросом
	* array('title1', 'title2'...)
	*/
	var $queued_items = array();

	/**
	* 'title' => 'builder script name' inside "includes/datastore" dir
	*/
	var $known_items = array(
		'cat_forums'             => 'build_cat_forums.php',
		'jumpbox'                => 'build_cat_forums.php',
		'viewtopic_forum_select' => 'build_cat_forums.php',
		'latest_news'            => 'build_cat_forums.php',
		'network_news'           => 'build_cat_forums.php',
		'ads'                    => 'build_cat_forums.php',
		'moderators'             => 'build_moderators.php',
		'stats'                  => 'build_stats.php',
		'ranks'                  => 'build_ranks.php',
		'attach_extensions'      => 'build_attach_extensions.php',
		'smile_replacements'     => 'build_smilies.php',
	);

	/**
	* Constructor
	*/
	function datastore_common () {}

	/**
	* @param  array(item1_title, item2_title...) or single item's title
	*/
	function enqueue ($items)
	{
		foreach ((array) $items as $item)
		{
			// игнор уже поставленного в очередь либо уже извлеченного
			if (!in_array($item, $this->queued_items) && !isset($this->data[$item]))
			{
				$this->queued_items[] = $item;
			}
		}
	}

	function &get ($title)
	{
		if (!isset($this->data[$title]))
		{
			$this->enqueue($title);
			$this->_fetch();
		}
		return $this->data[$title];
	}

	function store ($item_name, $item_data) {}

	function rm ($items)
	{
		foreach ((array) $items as $item)
		{
			unset($this->data[$item]);
		}
	}

	function update ($items)
	{
		if ($items == 'all')
		{
			$items = array_keys(array_unique($this->known_items));
		}
		foreach ((array) $items as $item)
		{
			$this->_build_item($item);
		}
	}

	function _fetch ()
	{
		$this->_fetch_from_store();

		foreach ($this->queued_items as $title)
		{
			if (!isset($this->data[$title]) || $this->data[$title] === false)
			{
				$this->_build_item($title);
			}
		}

		$this->queued_items = array();
	}

	function _fetch_from_store () {}

	function _build_item ($title)
	{
		if (!empty($this->known_items[$title]))
		{
			require(INC_DIR . $this->ds_dir . $this->known_items[$title]);
		}
		else
		{
			trigger_error("Unknown datastore item: $title", E_USER_ERROR);
		}
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

		$ds_title = sqlite_escape_string($this->prefix . $item_name);
		$ds_data  = sqlite_escape_string(serialize($item_data));

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
		$prefix_sql = sqlite_escape_string($this->prefix);

		array_deep($items, 'sqlite_escape_string');
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

class datastore_redis extends datastore_common
{
	var $cfg       = null;
	var $redis     = null;
	var $prefix    = null;
	var $connected = false;
	var $engine    = 'Redis';

	function datastore_redis ($cfg, $prefix = null)
	{
		if (!$this->is_installed())
		{
			die('Error: Redis extension not installed');
		}

		$this->cfg = $cfg;
		$this->redis = new Redis();
		$this->dbg_enabled = sql_dbg_enabled();
		$this->prefix = $prefix;
	}

	function connect ()
	{
		$this->cur_query = 'connect '. $this->cfg['host'] .':'. $this->cfg['port'];
		$this->debug('start');

		if (@$this->redis->connect($this->cfg['host'],$this->cfg['port']))
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

	function store ($title, $var)
	{
		if (!$this->connected) $this->connect();
		$this->data[$title] = $var;

		$this->cur_query = "cache->set('$title')";
		$this->debug('start');
		$this->debug('stop');
		$this->cur_query = null;
		$this->num_queries++;

		return (bool) $this->redis->set($this->prefix . $title, serialize($var));
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

			$this->redis->del($this->prefix . $title);
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

			$this->data[$item] = unserialize($this->redis->get($this->prefix . $item));
		}
	}

	function is_installed ()
	{
		return class_exists('Redis');
	}
}

class datastore_eaccelerator extends datastore_common
{
	var $engine    = 'eAccelerator';
	var $prefix    = null;

	function datastore_eaccelerator ($prefix = null)
	{
		if (!$this->is_installed())
		{
			die('Error: eAccelerator extension not installed');
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

		eaccelerator_put($this->prefix . $title, $var);
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

			eaccelerator_rm($this->prefix . $title);
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

			$this->data[$item] = eaccelerator_get($this->prefix . $item);
		}
	}

	function is_installed ()
	{
		return function_exists('eaccelerator_get');
	}
}

class datastore_xcache extends datastore_common
{
	var $prefix = null;
	var $engine = 'XCache';

	function datastore_xcache ($prefix = null)
	{
		if (!$this->is_installed())
		{
			die('Error: XCache extension not installed');
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

		return (bool) xcache_set($this->prefix . $title, $var);
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

			xcache_unset($this->prefix . $title);
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
			$this->cur_query = "cache->set('$item')";
			$this->debug('start');
			$this->debug('stop');
			$this->cur_query = null;
			$this->num_queries++;

			$this->data[$item] = xcache_get($this->prefix . $item);
		}
	}

	function is_installed ()
	{
		return function_exists('xcache_get');
	}
}

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

class datastore_file extends datastore_common
{
	var $dir    = null;
	var $prefix = null;
	var $engine = 'Filecache';

	function datastore_file ($dir, $prefix = null)
	{
		$this->prefix = $prefix;
		$this->dir = $dir;
		$this->dbg_enabled = sql_dbg_enabled();
	}

	function store ($title, $var)
	{
		$this->cur_query = "cache->set('$title')";
		$this->debug('start');

		$this->data[$title] = $var;

		$filename   = $this->dir . clean_filename($this->prefix . $title) . '.php';

		$filecache = "<?php\n";
		$filecache .= "if (!defined('BB_ROOT')) die(basename(__FILE__));\n";
		$filecache .= '$filecache = ' . var_export($var, true) . ";\n";
		$filecache .= '?>';

		$this->debug('stop');
		$this->cur_query = null;
		$this->num_queries++;

		return (bool) file_write($filecache, $filename, false, true, true);
	}

	function clean ()
	{
		$dir = $this->dir;

		if (is_dir($dir))
		{
			if ($dh = opendir($dir))
			{
				while (($file = readdir($dh)) !== false)
				{
					if ($file != "." && $file != "..")
					{
						$filename = $dir . $file;

						unlink($filename);
					}
				}
				closedir($dh);
			}
		}
	}

	function _fetch_from_store ()
	{
		if (!$items = $this->queued_items)
		{
			$src = $this->_debug_find_caller('enqueue');
			trigger_error("Datastore: item '$item' already enqueued [$src]", E_USER_ERROR);
		}

		foreach($items as $item)
		{
			$filename = $this->dir . $this->prefix . $item . '.php';

			$this->cur_query = "cache->get('$item')";
			$this->debug('start');
			$this->debug('stop');
			$this->cur_query = null;
			$this->num_queries++;

			if(file_exists($filename))
			{
				require($filename);

				$this->data[$item] = $filecache;
			}
		}
	}
}

// Initialize Datastore
switch ($bb_cfg['datastore_type'])
{
	case 'memcache':
		$datastore = new datastore_memcache($bb_cfg['cache']['memcache'], $bb_cfg['cache']['prefix']);
		break;

	case 'sqlite':
		$default_cfg = array(
			'db_file_path' => $bb_cfg['cache']['db_dir'] .'datastore.sqlite.db',
			'pconnect'     => true,
			'con_required' => true,
		);
		$datastore = new datastore_sqlite($default_cfg, $bb_cfg['cache']['prefix']);
		break;

	case 'redis':
		$datastore = new datastore_redis($bb_cfg['cache']['redis'], $bb_cfg['cache']['prefix']);
		break;

	case 'eaccelerator':
		$datastore = new datastore_eaccelerator($bb_cfg['cache']['prefix']);
		break;

	case 'xcache':
		$datastore = new datastore_xcache($bb_cfg['cache']['prefix']);
		break;

	case 'apc':
		$datastore = new datastore_apc($bb_cfg['cache']['prefix']);
		break;

	case 'filecache':
		default: $datastore = new datastore_file($bb_cfg['cache']['db_dir'] . 'datastore/', $bb_cfg['cache']['prefix']);
}

function sql_dbg_enabled ()
{
	return (SQL_DEBUG && DBG_USER && !empty($_COOKIE['sql_log']));
}

function short_query ($sql, $esc_html = false)
{
	$max_len = 100;
	$sql = str_compact($sql);

	if (!empty($_COOKIE['sql_log_full']))
	{
		if (mb_strlen($sql, 'UTF-8') > $max_len)
		{
			$sql = mb_substr($sql, 0, 50) .' [...cut...] '. mb_substr($sql, -50);
		}
	}

	return ($esc_html) ? htmlCHR($sql, true) : $sql;
}

// Functions
function utime ()
{
	return array_sum(explode(' ', microtime()));
}

function bb_log ($msg, $file_name)
{
	if (is_array($msg))
	{
		$msg = join(LOG_LF, $msg);
	}
	$file_name .= (LOG_EXT) ? '.'. LOG_EXT : '';
	return file_write($msg, LOG_DIR . $file_name);
}

function file_write ($str, $file, $max_size = LOG_MAX_SIZE, $lock = true, $replace_content = false)
{
	$bytes_written = false;

	if ($max_size && @filesize($file) >= $max_size)
	{
		$old_name = $file; $ext = '';
		if (preg_match('#^(.+)(\.[^\\/]+)$#', $file, $matches))
		{
			$old_name = $matches[1]; $ext = $matches[2];
		}
		$new_name = $old_name .'_[old]_'. date('Y-m-d_H-i-s_') . getmypid() . $ext;
		clearstatcache();
		if (@file_exists($file) && @filesize($file) >= $max_size && !@file_exists($new_name))
		{
			@rename($file, $new_name);
		}
	}
	if (!$fp = @fopen($file, 'ab'))
	{
		if ($dir_created = bb_mkdir(dirname($file)))
		{
			$fp = @fopen($file, 'ab');
		}
	}
	if ($fp)
	{
		if ($lock)
		{
			@flock($fp, LOCK_EX);
		}
		if ($replace_content)
		{
			@ftruncate($fp, 0);
			@fseek($fp, 0, SEEK_SET);
		}
		$bytes_written = @fwrite($fp, $str);
		@fclose($fp);
	}

	return $bytes_written;
}

function bb_mkdir ($path, $mode = 0777)
{
	$old_um = umask(0);
	$dir = mkdir_rec($path, $mode);
	umask($old_um);
	return $dir;
}

function mkdir_rec ($path, $mode)
{
	if (is_dir($path))
	{
		return ($path !== '.' && $path !== '..') ? is_writable($path) : false;
	}
	else
	{
		return (mkdir_rec(dirname($path), $mode)) ? @mkdir($path, $mode) : false;
	}
}

function verify_id ($id, $length)
{
	return (is_string($id) && preg_match('#^[a-zA-Z0-9]{'. $length .'}$#', $id));
}

function clean_filename ($fname)
{
	static $s = array('\\', '/', ':', '*', '?', '"', '<', '>', '|', ' ');
	return str_replace($s, '_', str_compact($fname));
}

function encode_ip ($ip)
{
	$d = explode('.', $ip);
	return sprintf('%02x%02x%02x%02x', $d[0], $d[1], $d[2], $d[3]);
}

function decode_ip ($ip)
{
	return long2ip("0x{$ip}");
}

function ip2int ($ip)
{
	return (float) sprintf('%u', ip2long($ip));  // для совместимости с 32 битными системами
}

// long2ip( mask_ip_int(ip2int('1.2.3.4'), 24) ) = '1.2.3.255'
function mask_ip_int ($ip, $mask)
{
	$ip_int = is_numeric($ip) ? $ip : ip2int($ip);
	$ip_masked = $ip_int | ((1 << (32 - $mask)) - 1);
	return (float) sprintf('%u', $ip_masked);
}

function bb_crc32 ($str)
{
	return (float) sprintf('%u', crc32($str));
}

function hexhex ($value)
{
	return dechex(hexdec($value));
}

function verify_ip ($ip)
{
	return preg_match('#^(\d{1,3}\.){3}\d{1,3}$#', $ip);
}

function str_compact ($str)
{
	return preg_replace('#\s+#u', ' ', trim($str));
}

function make_rand_str ($len = 10)
{
	$str = '';
	while (strlen($str) < $len)
	{
		$str .= str_shuffle(preg_replace('#[^0-9a-zA-Z]#', '', crypt(uniqid(mt_rand(), true))));
	}
	return substr($str, 0, $len);
}

// bencode: based on OpenTracker
function bencode ($var)
{
	if (is_string($var))
	{
		return strlen($var) .':'. $var;
	}
	else if (is_int($var))
	{
		return 'i'. $var .'e';
	}
	else if (is_float($var))
	{
		return 'i'. sprintf('%.0f', $var) .'e';
	}
	else if (is_array($var))
	{
		if (count($var) == 0)
		{
			return 'de';
		}
		else
		{
			$assoc = false;

			foreach ($var as $key => $val)
			{
				if (!is_int($key))
				{
					$assoc = true;
					break;
				}
			}

			if ($assoc)
			{
				ksort($var, SORT_REGULAR);
				$ret = 'd';

				foreach ($var as $key => $val)
				{
					$ret .= bencode($key) . bencode($val);
				}
				return $ret .'e';
			}
			else
			{
				$ret = 'l';

				foreach ($var as $val)
				{
					$ret .= bencode($val);
				}
				return $ret .'e';
			}
		}
	}
	else
	{
		trigger_error('bencode error: wrong data type', E_USER_ERROR);
	}
}

function array_deep (&$var, $fn, $one_dimensional = false, $array_only = false)
{
	if (is_array($var))
	{
		foreach ($var as $k => $v)
		{
			if (is_array($v))
			{
				if ($one_dimensional)
				{
					unset($var[$k]);
				}
				else if ($array_only)
				{
					$var[$k] = $fn($v);
				}
				else
				{
					array_deep($var[$k], $fn);
				}
			}
			else if (!$array_only)
			{
				$var[$k] = $fn($v);
			}
		}
	}
	else if (!$array_only)
	{
		$var = $fn($var);
	}
}

function hide_bb_path ($path)
{
	return ltrim(str_replace(BB_PATH, '', $path), '/\\');
}

function tr_drop_request ($drop_type)
{
	if (DBG_LOG) dbg_log(' ', "request-dropped-$drop_type");
	dummy_exit(mt_rand(60, 600));
}

function sys ($param)
{
	switch ($param)
	{
		case 'la':
			return function_exists('sys_getloadavg') ? join(' ', sys_getloadavg()) : 0;
			break;
		case 'mem':
			return function_exists('memory_get_usage') ? memory_get_usage() : 0;
			break;
		case 'mem_peak':
			return function_exists('memory_get_peak_usage') ? memory_get_peak_usage() : 0;
			break;
		default:
			trigger_error("invalid param: $param", E_USER_ERROR);
	}
}

function ver_compare ($version1, $operator, $version2)
{
	return version_compare($version1, $version2, $operator);
}

function dbg_log ($str, $file)
{
	$dir = LOG_DIR . (defined('IN_TRACKER') ? 'dbg_tr/' : 'dbg_bb/') . date('m-d_H') .'/';
	return file_write($str, $dir . $file, false, false);
}

function log_get ($file = '', $prepend_str = false)
{
	log_request($file, $prepend_str, false);
}

function log_post ($file = '', $prepend_str = false)
{
	log_request($file, $prepend_str, true);
}

function log_request ($file = '', $prepend_str = false, $add_post = true)
{
	global $user;

	$file = ($file) ? $file : 'req/'. date('m-d');
	$str = array();
	$str[] = date('m-d H:i:s');
	if ($prepend_str !== false) $str[] = $prepend_str;
	if (!empty($user->data)) $str[] = $user->id ."\t". html_entity_decode($user->name);
	$str[] = sprintf('%-15s', $_SERVER['REMOTE_ADDR']);
	$str[] = @$_SERVER['REQUEST_URI'];
	$str[] = @$_SERVER['HTTP_USER_AGENT'];
	$str[] = @$_SERVER['HTTP_REFERER'];
	if (!empty($_POST) && $add_post) $str[] = "post: ". str_compact(urldecode(http_build_query($_POST)));
	$str = join("\t", $str) . "\n";
	bb_log($str, $file);
}

// Board init
if (defined('IN_FORUM'))
{
	require(INC_DIR .'init_bb.php');
}
// Tracker init
else if (defined('IN_TRACKER'))
{
	define('DUMMY_PEER', pack('Nn', ip2long($_SERVER['REMOTE_ADDR']), !empty($_GET['port']) ? intval($_GET['port']) : mt_rand(1000, 65000)));

	function dummy_exit ($interval = 1800)
	{
		$output = bencode(array(
			'interval'     => (int)    $interval,
			'min interval' => (int)    $interval,
			'peers'        => (string) DUMMY_PEER,
		));

		die($output);
	}

	header('Content-Type: text/plain');
	header('Pragma: no-cache');

	if (!defined('IN_ADMIN'))
	{
		// Exit if tracker is disabled via ON/OFF trigger
		if (file_exists(BB_DISABLED))
		{
			dummy_exit(mt_rand(60, 2400));  #  die('d14:failure reason20:temporarily disablede');
		}
	}
}