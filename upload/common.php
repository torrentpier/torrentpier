<?php

ignore_user_abort(true);
define('TIMESTART', utime());
define('TIMENOW',   time());

if (isset($_REQUEST['GLOBALS']) || isset($_FILES['GLOBALS'])) die();

if (!defined('BB_ROOT')) define('BB_ROOT', './');
if (!defined('IN_PHPBB') && !defined('IN_TRACKER')) define('IN_PHPBB', true);

// Get initial config
require(BB_ROOT .'config.php');
require(BB_ROOT .'config_mods.php');

if (!defined('DBCHARSET')) define('DBCHARSET', 'utf8');

$server_protocol = ($bb_cfg['cookie_secure']) ? 'https://' : 'http://';
$server_port = ($bb_cfg['server_port'] != 80) ? ':'. $bb_cfg['server_port'] : '';
define('FORUM_PATH', $bb_cfg['script_path']);
define('FULL_URL', $server_protocol . $bb_cfg['server_name'] . $server_port . $bb_cfg['script_path']);
unset($server_protocol, $server_port);

// Debug options
define('DBG_USER', (isset($_COOKIE[COOKIE_DBG]) || DEBUG === true));

// Board/Tracker shared constants and functions
define('BB_BT_TORRENTS',      'bb_bt_torrents');
define('BB_BT_TRACKER',       'bb_bt_tracker');
define('BB_BT_TRACKER_SNAP',  'bb_bt_tracker_snap');
define('BB_BT_USERS',         'bb_bt_users');

define('BT_AUTH_KEY_LENGTH', 10);

define('DL_STATUS_RELEASER', -1);
define('DL_STATUS_DOWN',      0);
define('DL_STATUS_COMPLETE',  1);
define('DL_STATUS_CANCEL',    3);
define('DL_STATUS_WILL',      4);

define('TOR_TYPE_GOLD',       1);
define('TOR_TYPE_SILVER',     2);

define('ANONYMOUS', -1);

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
					case 'cache_memcache':
						if (!$mc_name =& $cache_cfg[0])
						{
							trigger_error("empty mc_name for $cache_name cache", E_USER_ERROR);
						}
						if (!$mc_cfg =& $this->cfg['memcache'][$mc_name])
						{
							trigger_error("mc_cfg for $cache_name not found", E_USER_ERROR);
						}
						if (!isset($this->obj[$mc_name]))
						{
							$servers      = (array) $mc_cfg[0];
							$pconnect     = $mc_cfg[1];
							$con_required = $mc_cfg[2];

							$this->obj[$mc_name] = new cache_memcache(array(
								'mc_name'      => $mc_name,
								'mc_class'     => $this->cfg['mc_class'],  // $bb_cfg['cache']['mc_class']
								'servers'      => $servers,
								'pconnect'     => $pconnect,               // $bb_cfg['cache']['pconnect']
								'con_required' => $con_required,
							));
						}
						$this->ref[$cache_name] =& $this->obj[$mc_name];
						break;

					case 'cache_sqlite':
						if (!isset($this->obj[$cache_name]))
						{
							$cache_cfg['pconnect']     = $this->cfg['pconnect'];
							$cache_cfg['db_file_path'] = $this->get_db_path($cache_name, $cache_cfg, '_cache.sqlite.db');

							$this->obj[$cache_name] = new cache_sqlite($cache_cfg);
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

                    case 'filecache':
                        if (!isset($this->obj[$cache_name]))
						{
							$this->obj[$cache_name] = new cache_file($this->cfg['db_dir'] . $cache_name .'/');
						}
						$this->ref[$cache_name] =& $this->obj[$cache_name];
						break;

					default:
						trigger_error("invalid cache_type: $cache_type", E_USER_ERROR);
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
	function get ($key, $get_miss_key_callback = '', $prefix = '', $ttl = 604800)
	{
		if ($get_miss_key_callback) return $get_miss_key_callback($key);
		return is_array($key) ? array() : false;
	}
	/**
	* Store value of variable
	*/
	function set ($key, $value, $ttl = 604800, $prefix = '')
	{
		return false;
	}
	/**
	* Remove variable
	*/
	function rm ($key, $prefix = '')
	{
		return false;
	}
}

class cache_memcache extends cache_common
{
	var $cfg  = array(
				'mc_class' => 'class_name',   // $bb_cfg['cache']['mc_class']
			);
	var $used = true;
	var $db   = null;

	function cache_memcache ($cfg)
	{
		$this->cfg = array_merge($this->cfg, $cfg);
		$this->db = new $this->cfg['mc_class']($this->cfg);
	}

	function get ($key, $get_miss_key_callback = '', $prefix = '', $ttl = 604800)
	{
		if (empty($key))
		{
			return is_array($key) ? array() : false;
		}
		$cached_items = array();
		$prefix_len   = strlen($prefix);
		$key_ary      = (array) $key;
		$key_get      = array();

		foreach ($key_ary as $k)
		{
			$key_get[] = $prefix . $k;
		}

		// get available items
		foreach ($this->db->get($key_get) as $k => $v)
		{
			$cached_items[substr($k, $prefix_len)] = $v;
		}

		// get miss items
		if ($get_miss_key_callback AND $miss_key = array_diff($key_ary, array_keys($cached_items)))
		{
			foreach ($get_miss_key_callback($miss_key) as $k => $v)
			{
				$this->set($prefix.$k, $v, $ttl);
				$cached_items[$k] = $v;
			}
		}
		// return
		if (is_array($key))
		{
			return $cached_items;
		}
		else
		{
			return isset($cached_items[$key]) ? $cached_items[$key] : false;
		}
	}

	function set ($key, $value, $ttl = 604800, $prefix = '')
	{
		return $this->db->set($prefix.$key, $value, $ttl);
	}

	function rm ($key, $prefix = '')
	{
		return $this->db->rm($prefix.$key);
	}
}

class memcache_common extends cache_dbg_common
{
	var $cfg = array(
	             'mc_name'      => null,                // $bb_cfg['cache']['memcache'][ key ]
	             'servers'      => array('host:port'),  // $bb_cfg['cache']['memcache'][ val ]
	             'pconnect'     => false,               // $bb_cfg['cache']['pconnect']
	             'con_required' => false,
	             'log_name'     => 'memcache',
	           );
	var $engine = 'Memcache';
	var $mc     = null;

	function memcache_common ($cfg = array())
	{
		$this->mc = new Memcache();

		$this->cfg = array_merge($this->cfg, $cfg);
		$this->dbg_enabled = sql_dbg_enabled();

		$this->init();
	}

	function init ()
	{
		$this->cur_query = ($this->dbg_enabled) ? "addServer(". join(", ", (array)$this->cfg['servers']) .", ". (int) $this->cfg['pconnect'] .", ". (int) $this->cfg['con_required'] .")" : '';
		$this->debug('start');

		foreach ($this->cfg['servers'] as $srv)
		{
			list($host, $port) = explode(':', $srv);
			$this->mc->addServer($host, $port, $this->cfg['pconnect']);
		}
		$this->mc->setCompressThreshold(5000);

		$this->debug('stop');
	}

	function get ($key)
	{
		$this->cur_query = ($this->dbg_enabled) ? "get(". join(", ", (array)$key) .")" : '';
		$this->debug('start');

		$result = $this->mc->get($key);

		if (!is_array($result))
		{
			// обработки ошибок нет
			$result = array();
		}

		$this->debug('stop');
		return $result;
	}

	function set ($key, $value, $ttl = 604800, $flag = 0)
	{
		$this->cur_query = ($this->dbg_enabled) ? "set($key, ". str_compact(print_r($value, true)) .")" : '';
		$this->debug('start');

		$result = $this->mc->set($key, $value, $flag, $ttl);

		$this->debug('stop');
		return $result;
	}

	function rm ($key)
	{
		$this->cur_query = ($this->dbg_enabled) ? "rm('$key')" : '';
		$this->debug('start');

		$result = $this->mc->delete($key, 0);

		$this->debug('stop');
		return $result;
	}
}

class memcached_common extends cache_dbg_common
{
	var $cfg = array(
	             'mc_name'      => null,                // $bb_cfg['cache']['memcache'][ key ]
	             'servers'      => array('host:port'),  // $bb_cfg['cache']['memcache'][ val ]
	             'pconnect'     => false,               // $bb_cfg['cache']['pconnect']
	             'con_required' => false,
	             'log_name'     => 'memcached',
	           );
	var $engine = 'Memcached';
	var $mc     = null;

	function memcached_common ($cfg = array())
	{
		$this->cfg = array_merge($this->cfg, $cfg);
		$this->dbg_enabled = sql_dbg_enabled();

		$persistent_id = ($this->cfg['pconnect']) ? $this->cfg['mc_name'] : '';

		if ($this->dbg_enabled)
		{
			$pconnect = ($this->cfg['pconnect']) ? 'pcon' : 'not_pcon';
			$con_req  = ($this->cfg['con_required']) ? 'req' : 'not_req';
			$this->engine .= "<b class=normal>($persistent_id), $pconnect, $con_req</b>";
		}

		$this->mc = new Memcached($persistent_id);

		$this->init();
	}

	function init ()
	{
		if (!count($this->mc->getServerList()))
		{
			$this->cur_query = ($this->dbg_enabled) ? "addServer(". join(", ", (array)$this->cfg['servers']) .")" : '';
			$this->debug('start');

			foreach ($this->cfg['servers'] as $srv)
			{
				list($host, $port) = explode(':', $srv);
				$this->mc->addServer($host, $port);
			}

			$this->debug('stop');
		}
	}

	function get ($key)
	{
		$this->cur_query = ($this->dbg_enabled) ? "get(". join(", ", (array)$key) .")" : '';
		$this->debug('start');

		$result = $this->mc->getMulti((array)$key);

		if (!is_array($result))
		{
			$res_code = $this->mc->getResultCode();
			$res_msg  = $this->mc->getResultMessage();
			$err_txt  = "Memcached({$this->cfg['mc_name']})::get failed [$res_code, $res_msg]";

			if ($this->cfg['con_required'])
			{
				trigger_error($err_txt, E_USER_ERROR);
			}
			else
			{
				bb_log(join(" ", array(date('d-m H:i:s'), $err_txt))."\n", 'mc_err');
			}
			$result = array();
		}

		$this->debug('stop');
		return $result;
	}

	function set ($key, $value, $ttl = 604800, $flag = 0)
	{
		$this->cur_query = ($this->dbg_enabled) ? "set($key, ". str_compact(print_r($value, true)) .")" : '';
		$this->debug('start');

		$result = $this->mc->set($key, $value, $ttl);

		$this->debug('stop');
		return $result;
	}

	function rm ($key)
	{
		$this->cur_query = ($this->dbg_enabled) ? "rm('$key')" : '';
		$this->debug('start');

		$result = $this->mc->delete($key, 0);

		$this->debug('stop');
		return $result;
	}
}

class cache_sqlite extends cache_common
{
	var $used = true;
	var $db   = null;
	var $cfg  = array(
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

	function cache_sqlite ($cfg)
	{
		$this->cfg = array_merge($this->cfg, $cfg);
		$this->db = new sqlite_common($this->cfg);
	}

	function get ($key, $get_miss_key_callback = '', $prefix = '', $ttl = 604800)
	{
		if (empty($key))
		{
			return is_array($key) ? array() : false;
		}
		$this->db->shard($prefix.$key);
		$cached_items = array();
		$prefix_len   = strlen($prefix);
		$prefix_sql   = sqlite_escape_string($prefix);

		$key_ary = $key_sql = (array) $key;
		array_deep($key_sql, 'sqlite_escape_string');

		// get available items
		$rowset = $this->db->fetch_rowset("
			SELECT cache_name, cache_value
			FROM ". $this->cfg['table_name'] ."
			WHERE cache_name IN('$prefix_sql". join("','$prefix_sql", $key_sql) ."') AND cache_expire_time > ". TIMENOW ."
			LIMIT ". count($key) ."
		");

		$this->db->debug('start', 'unserialize()');
		foreach ($rowset as $row)
		{
			$cached_items[substr($row['cache_name'], $prefix_len)] = unserialize($row['cache_value']);
		}
		$this->db->debug('stop');

		// get miss items
		if ($get_miss_key_callback AND $miss_key = array_diff($key_ary, array_keys($cached_items)))
		{
			foreach ($get_miss_key_callback($miss_key) as $k => $v)
			{
				$this->set($prefix.$k, $v, $ttl);
				$cached_items[$k] = $v;
			}
		}
		// return
		if (is_array($key))
		{
			return $cached_items;
		}
		else
		{
			return isset($cached_items[$key]) ? $cached_items[$key] : false;
		}
	}

	function set ($key, $value, $ttl = 604800, $prefix = '')
	{
		$this->db->shard($prefix.$key);
		$key_sql   = sqlite_escape_string($prefix.$key);
		$expire    = TIMENOW + $ttl;
		$value_sql = sqlite_escape_string(serialize($value));

		$result = $this->db->query("REPLACE INTO ". $this->cfg['table_name'] ." (cache_name, cache_expire_time, cache_value) VALUES ('$key_sql', $expire, '$value_sql')");
		return (bool) $result;
	}

	function rm ($key, $prefix = '')
	{
		$this->db->shard($prefix.$key);
		$result = $this->db->query("DELETE FROM ". $this->cfg['table_name'] ." WHERE cache_name = '". sqlite_escape_string($prefix.$key) ."'");
		return (bool) $result;
	}

	function gc ($expire_time = TIMENOW)
	{
		$result = $this->db->query("DELETE FROM ". $this->cfg['table_name'] ." WHERE cache_expire_time < $expire_time");
		return ($result) ? sqlite_changes($this->db->dbh) : 0;
	}
}

class sqlite_common extends cache_dbg_common
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
	var $engine    = 'SQLite DB';
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
		$this->cur_query = ($this->dbg_enabled) ? ($this->cfg['pconnect'] ? 'p' : '') .'connect to: '. $this->cfg['db_file_path'] : 'connect';
		$this->debug('start');

		$connect_type = ($this->cfg['pconnect']) ? 'sqlite_popen' : 'sqlite_open';

		if ($this->cfg['shard_type'] != 'none' && $this->shard_val === false)
		{
			trigger_error("cannot shard: shard_val not defined for {$this->cfg['db_file_path']}", E_USER_ERROR);
		}

		if (@$this->dbh = $connect_type($this->cfg['db_file_path'], 0666, $sqlite_error))
		{
			$this->connected = true;
		}

		if (!$this->connected && $this->cfg['con_required'])
		{
			trigger_error($sqlite_error, E_USER_ERROR);
		}

		$this->debug('stop');
		$this->cur_query = null;
	}

	function create_table ()
	{
		$this->table_create_attempts++;
		return sqlite_query($this->dbh, $this->cfg['table_schema']);
	}

	function shard ($key)
	{
		$type = $this->cfg['shard_type'];

		if ($type == 'none') return;
		if (is_array($key))  trigger_error('cannot shard: $key is array', E_USER_ERROR);

		// define shard_val
		if ($type == 'string')
		{
			$shard_val = substr($key, 0, $this->cfg['shard_val']);
		}
		else
		{
			$shard_val = $key % $this->cfg['shard_val'];
		}
		// все запросы должны быть к одному и тому же шарду
		if ($this->shard_val !== false)
		{
			if ($shard_val != $this->shard_val)
			{
				trigger_error("shard cannot be reassigned. [{$this->shard_val}, $shard_val, $key]", E_USER_ERROR);
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

		if (!$result = @sqlite_unbuffered_query($this->dbh, $query, SQLITE_ASSOC))
		{
			if (!$this->table_create_attempts && !sqlite_num_rows(sqlite_query($this->dbh, "PRAGMA table_info({$this->cfg['table_name']})")))
			{
				if ($this->create_table())
				{
					$result = sqlite_unbuffered_query($this->dbh, $query, SQLITE_ASSOC);
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
		return is_resource($result) ? sqlite_fetch_array($result, SQLITE_ASSOC) : false;
	}

	function fetch_rowset ($query)
	{
		$result = $this->query($query);
		return is_resource($result) ? sqlite_fetch_all($result, SQLITE_ASSOC) : array();
	}

	function changes ()
	{
		return is_resource($this->dbh) ? sqlite_changes($this->dbh) : 0;
	}

	function escape ($str)
	{
		return sqlite_escape_string($str);
	}

	function get_error_msg ()
	{
		return 'SQLite error #'. ($err_code = sqlite_last_error($this->dbh)) .': '. sqlite_error_string($err_code);
	}

	function trigger_error ($msg = 'DB Error')
	{
		if (error_reporting()) trigger_error($msg, E_USER_ERROR);
	}
}

class cache_dbg_common
{
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

function sql_dbg_enabled ()
{
	return (SQL_DEBUG && DBG_USER && !empty($_COOKIE['sql_log']));
}

function short_query ($sql, $esc_html = false)
{
	$max_len = 2500;
	$sql = str_compact($sql);

	if (empty($_COOKIE['sql_log_full']))
	{
		if (strlen($sql) > $max_len)
		{
			$sql = substr($sql, 0, $max_len-500) .' [...cut...] '. substr($sql, -480);
		}
	}

	return ($esc_html) ? htmlCHR($sql, true) : $sql;
}

class cache_file extends cache_common
{
	var $used = true;

	var $dir  = null;

	function cache_file ($dir)
	{
		$this->dir = $dir;
	}

	function get ($name)
	{
		$filename = $this->dir . clean_filename($name) . '.php';

		if(file_exists($filename))
		{
			require($filename);
		}

		return (!empty($filecache['value'])) ? $filecache['value'] : false;
	}

	function set ($name, $value, $ttl = 86400)
	{
		if (!function_exists('var_export'))
		{
			return false;
		}

		$filename   = $this->dir . clean_filename($name) . '.php';
		$expire     = TIMENOW + $ttl;
		$cache_data = array(
			'expire'  => $expire,
			'value'   => $value,
		);

		$filecache = "<?php\n";
		$filecache .= "if (!defined('BB_ROOT')) die(basename(__FILE__));\n";
		$filecache .= '$filecache = ' . var_export($cache_data, true) . ";\n";
		$filecache .= '?>';

		return (bool) file_write($filecache, $filename, false, true, true);
	}

	function rm ($name)
	{
		$filename   = $this->dir . clean_filename($name) . '.php';
		if (file_exists($filename))
		{
			return (bool) unlink($filename);
		}
		return false;
	}

	function gc ($expire_time = TIMENOW)
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

						require($filename);

						if(!empty($filecache['expire']) && ($filecache['expire'] < $expire_time))
						{
							unlink($filename);
						}
					}
				}
				closedir($dh);
			}
		}

		return;
	}
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
	return (preg_match('#^[a-zA-Z0-9]{'. $length .'}$#', $id) && is_string($id));
}

function clean_filename ($fname)
{
	static $s = array('\\', '/', ':', '*', '?', '"', '<', '>', '|', ' ');
	return str_replace($s, '_', str_compact($fname));
}

function encode_ip ($dotquad_ip)
{
	$ip_sep = explode('.', $dotquad_ip);
	if (count($ip_sep) == 4)
	{
		return sprintf('%02x%02x%02x%02x', $ip_sep[0], $ip_sep[1], $ip_sep[2], $ip_sep[3]);
	}

	$ip_sep = explode(':', preg_replace('/(^:)|(:$)/', '', $dotquad_ip));
	$res = '';
	foreach ($ip_sep as $x)
	{
		$res .= sprintf('%0'. ($x == '' ? (9 - count($ip_sep)) * 4 : 4) .'s', $x);
	}
	return $res;
}

function decode_ip ($int_ip)
{
	$int_ip = trim($int_ip);

	if (strlen($int_ip) == 32)
	{
		$int_ip = substr(chunk_split($int_ip, 4, ':'), 0, 39);
		$int_ip = ':'. implode(':', array_map("hexhex", explode(':',$int_ip))) .':';
		preg_match_all("/(:0)+/", $int_ip, $zeros);
		if (count($zeros[0]) > 0)
		{
			$match = '';
			foreach($zeros[0] as $zero)
				if (strlen($zero) > strlen($match))
					$match = $zero;
			$int_ip = preg_replace('/'. $match .'/', ':', $int_ip, 1);
		}
		return preg_replace('/(^:([^:]))|(([^:]):$)/', '$2$4', $int_ip);
	}
	if (strlen($int_ip) !== 8) $int_ip = '00000000';
	$hexipbang = explode('.', chunk_split($int_ip, 2, '.'));
	return hexdec($hexipbang[0]). '.' . hexdec($hexipbang[1]) . '.' . hexdec($hexipbang[2]) . '.' . hexdec($hexipbang[3]);
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
	return preg_replace('#\s+#', ' ', trim($str));
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

// bencode: based on OpenTracker [http://whitsoftdev.com/opentracker]
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
	dummy_exit(mt_rand(300, 900));
}

function get_loadavg ()
{
	if (is_callable('sys_getloadavg'))
	{
		$loadavg = join(' ', sys_getloadavg());
	}
	else if (strpos(PHP_OS, 'Linux') !== false)
	{
		$loadavg = @file_get_contents('/proc/loadavg');
	}

	return !empty($loadavg) ? $loadavg : 0;
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

// Board init
if (defined('IN_PHPBB'))
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
			dummy_exit(mt_rand(1200, 2400));  #  die('d14:failure reason20:temporarily disablede');
		}

		// Limit server load
		if ($bb_cfg['max_srv_load'] || $bb_cfg['tr_working_second'])
		{
			if ((!empty($_GET['uploaded']) || !empty($_GET['downloaded'])) && (!isset($_GET['event']) || $_GET['event'] === 'started'))
			{
				if ($bb_cfg['tr_working_second'] && (TIMENOW % $bb_cfg['tr_working_second']))
				{
					tr_drop_request('wrk_sec');
				}
				else if ($bb_cfg['max_srv_load'] && LOADAVG)
				{
					if (LOADAVG > $bb_cfg['max_srv_load'])
					{
						tr_drop_request('load');
					}
				}
			}
		}
	}
}
