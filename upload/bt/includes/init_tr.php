<?php

if (!defined('IN_TRACKER')) die(basename(__FILE__));

// Exit if tracker is disabled
if ($tr_cfg['off'])
{
	msg_die($tr_cfg['off_reason']);
}
// Redirect browser
if ($tr_cfg['browser_redirect_url'])
{
	browser_redirect();
}

//
// Functions
//
function tracker_exit ()
{
	global $DBS;

	if (DBG_LOG)
	{
		$gen_time       = utime() - TIMESTART;
		$num_queries    = !empty($DBS) ? DB()->num_queries : '-';
		$sql_inittime   = !empty($DBS) ? DB()->sql_inittime : '  --  ';
		$sql_timetotal  = !empty($DBS) ? DB()->sql_timetotal : '  --  ';
		$sql_init_perc  = !empty($DBS) ? round($sql_inittime*100/$gen_time) : ' - ';
		$sql_total_perc = !empty($DBS) ? round($sql_timetotal*100/$gen_time) : ' - ';

		$str = array();
		$str[] = substr(time(), -4, 4);
		$str[] = sprintf('%.4f', $gen_time);
		$str[] = sprintf('%.4f'. LOG_SEPR .'%02d%%', $sql_inittime, $sql_init_perc);
		$str[] = sprintf('%.4f'. LOG_SEPR .'%02d%%', $sql_timetotal, $sql_total_perc);
		$str[] = $num_queries;
		$str[] = sprintf('%.1f', LOADAVG);
		$str = join(LOG_SEPR, $str) . LOG_LF;
		dbg_log($str, '!!gentime');
	}
/**!/
	bb_log("##\n". ob_get_contents() ."\n##", 'tr_output_'. date('m-d_H'));
#*/

	exit;
}

function silent_exit ()
{
	while (@ob_end_clean());

	tracker_exit();
}

function error_exit ($msg = '')
{
	if (DBG_LOG) dbg_log(' ', '!err-'. clean_filename($msg));

	if (!DEBUG)
	{
		silent_exit();
	}

	echo bencode(array('failure reason' => str_compact($msg)));

	tracker_exit();
}

function browser_redirect ()
{
	if (empty($_SERVER['HTTP_USER_AGENT'])) return;

	$user_agent = strtolower($_SERVER['HTTP_USER_AGENT']);

	$browser_ids = array(
		'amaya',
		'crawler',
		'dillo',
		'elinks',
		'gecko',
		'googlebot',
		'ibrowse',
		'icab',
		'konqueror',
		'lynx',
		'mozilla',
		'msie',
		'msnbot',
		'netpositive',
		'omniweb',
		'opera',
		'safari',
		'slurp',
		'w3m',
		'wget',
	);

	foreach ($browser_ids as $browser)
	{
		if (strpos($user_agent, $browser) !== false)
		{
			if (DBG_LOG)
			{
				dbg_log(' ', "redirect/$browser");

				dbg_log(
					TIMENOW                            . LOG_SEPR .
					encode_ip($_SERVER['REMOTE_ADDR']) . LOG_SEPR .
					$_SERVER['REMOTE_ADDR']            . LOG_SEPR .
					$_SERVER['QUERY_STRING']           . LOG_SEPR .
					$_SERVER['HTTP_USER_AGENT']        . LOG_SEPR .
					LOG_LF,
					"redirect/$browser.q.log"
				);
			}

			header('Location: '. $GLOBALS['tr_cfg']['browser_redirect_url']);
			tracker_exit();
		}
	}
}

// Database
class sql_db
{
	var $link          = null;
	var $result        = null;
	var $selected_db   = null;

	var $pconnect      = false;
	var $locked        = false;

	var $num_queries   = 0;
	var $sql_starttime = 0;
	var $sql_inittime  = 0;
	var $sql_timetotal = 0;

	var $dbg           = array();
	var $dbg_id        = 0;
	var $dbg_user      = false;
	var $cur_query     = null;

	/**
	* Constructor
	*/
	function sql_db ($cfg)
	{
		$this->dbg_user = (SQL_DEBUG && $cfg['dbg_user']);
		$this->pconnect = $cfg['persist'];

		// Connect to server
		$this->link = @$this->connect($cfg);

		// Select database
		$this->selected_db = @$this->select_db($cfg);

		// Set charset
		if ($cfg['charset'] && !@$this->sql_query("SET NAMES {$cfg['charset']}"))
		{
			error_exit("Could not set MySQL charset '{$cfg['charset']}'");
		}

		$this->num_queries = 0;
		$this->sql_inittime = $this->sql_timetotal;
	}

	/**
	* Open connection
	*/
	function connect ($cfg)
	{
		$this->cur_query = 'connect';
		$this->debug('start');

		$connect_type = ($this->pconnect) ? 'mysql_pconnect' : 'mysql_connect';

		if (!$link = $connect_type($cfg['dbhost'], $cfg['dbuser'], $cfg['dbpasswd']))
		{
			$this->log_error();
		}

		register_shutdown_function(array(&$this, 'sql_close'));

		$this->debug('end');
		$this->cur_query = null;

		if (DBG_LOG) dbg_log(' ', 'DB-connect'. ($link ? '' : '-FAIL'));

		if (!$link)
		{
			dummy_exit(1200);
		}

		return $link;
	}

	/**
	* Select database
	*/
	function select_db ($cfg)
	{
		$this->cur_query = 'select db';
		$this->debug('start');

		if (!mysql_select_db($cfg['dbname'], $this->link))
		{
			$this->log_error();
			error_exit("Could not select database '{$cfg['dbname']}'");
		}

		$this->debug('end');
		$this->cur_query = null;

		return $cfg['dbname'];
	}

	/**
	* Base query method
	*/
	function sql_query ($query, $type = 'buffered')
	{
		$this->cur_query = $query;
		$this->debug('start');

		$query_function = ($type === 'unbuffered') ? 'mysql_unbuffered_query' : 'mysql_query';

		if (!$this->result = $query_function($query, $this->link))
		{
			$this->log_error();
		}

		$this->debug('end');
		$this->cur_query = null;

		$this->num_queries++;

		return $this->result;
	}

	/**
	* Execute query WRAPPER (with error handling)
	*/
	function query ($query, $err_msg = '')
	{
		if (!$result = $this->sql_query($query))
		{
			$this->trigger_error($err_msg);
		}

		return $result;
	}

	/**
	* Return number of rows
	*/
	function num_rows ($result = false)
	{
		$num_rows = false;

		if ($result OR $result = $this->result)
		{
			$num_rows = is_resource($result) ? mysql_num_rows($result) : false;
		}

		return $num_rows;
	}

	/**
	* Return number of affected rows
	*/
	function affected_rows ()
	{
		return (is_resource($this->link)) ? mysql_affected_rows($this->link) : -1;
	}

	/**
	* Fetch current row
	*/
	function sql_fetchrow ($result, $result_type = MYSQL_ASSOC)
	{
		return (is_resource($result)) ? mysql_fetch_array($result, $result_type) : false;
	}

	/**
	* Alias of sql_fetchrow()
	*/
	function fetch_next ($result, $result_type = MYSQL_ASSOC)
	{
		return $this->sql_fetchrow($result, $result_type);
	}

	/**
	* Fetch row WRAPPER (with error handling)
	*/
	function fetch_row ($query, $err_msg = '')
	{
		if (!$result = $this->sql_query($query))
		{
			$this->trigger_error($err_msg);
		}

		return $this->sql_fetchrow($result);
	}

	/**
	* Fetch all rows
	*/
	function sql_fetchrowset ($result, $result_type = MYSQL_ASSOC)
	{
		$rowset = array();

		while ($row = mysql_fetch_array($result, $result_type))
		{
			$rowset[] = $row;
		}

		return $rowset;
	}

	/**
	* Fetch all rows WRAPPER (with error handling)
	*/
	function fetch_rowset ($query, $err_msg = '')
	{
		if (!$result = $this->sql_query($query, 'buffered'))
		{
			$this->trigger_error($err_msg);
		}

		return $this->sql_fetchrowset($result);
	}

	/**
	* Escape string used in sql query
	*/
	function escape ($v, $check_type = false)
	{
		if (!$check_type)
		{
			return mysql_real_escape_string($v);
		}

		switch (true)
		{
			case is_string ($v): return "'". mysql_real_escape_string($v) ."'";
			case is_int    ($v): return "$v";
			case is_bool   ($v): return ($v) ? '1' : '0';
			case is_float  ($v): return "'$v'";
			case is_null   ($v): return 'NULL';
		}
		// if $v has unsuitable type
		$this->trigger_error(__FUNCTION__ .' - wrong params');
	}

	/**
	* Return sql error array
	*/
	function sql_error ()
	{
		$return_ary = array(
			'code'    => '',
			'message' => 'not connected',
		);

		if (is_resource($this->link))
		{
			$return_ary = array(
				'code'    => mysql_errno($this->link),
				'message' => mysql_error($this->link),
			);
		}

		return $return_ary;
	}

	/**
	* Close sql connection
	*/
	function sql_close ()
	{
		if (is_resource($this->link))
		{
			mysql_close($this->link);
		}

		$this->link = $this->selected_db = null;

		if (DBG_LOG) dbg_log(str_repeat(' ', $this->num_queries), 'DB-num_queries');
	}

	/**
	* Get info about last query
	*/
	function query_info ()
	{
		$info = array();

		if ($num = $this->num_rows($this->result))
		{
			$info[] = "$num rows";
		}

		if (is_resource($this->link) AND $ext = mysql_info($this->link))
		{
			$info[] = "$ext";
		}
		else if (!$num && ($aff = $this->affected_rows($this->result) AND $aff != -1))
		{
			$info[] = "$aff rows";
		}

		return join(', ', $info);
	}

	/**
	* Store debug info
	*/
	function debug ($mode)
	{
		if (!SQL_DEBUG) return;

		if ($mode == 'start')
		{
			if (SQL_CALC_QUERY_TIME || DBG_LOG || SQL_LOG_SLOW_QUERIES)
			{
				$this->sql_starttime = utime();
			}
		}
		else if ($mode == 'end')
		{
			if (SQL_CALC_QUERY_TIME || DBG_LOG || SQL_LOG_SLOW_QUERIES)
			{
				$cur_query_time = utime() - $this->sql_starttime;
				$this->sql_timetotal += $cur_query_time;

				if (SQL_LOG_SLOW_QUERIES && $cur_query_time > SQL_SLOW_QUERY_TIME)
				{
					$msg  = date('m-d H:i:s') . LOG_SEPR;
					$msg .= sprintf('%03d', round($cur_query_time));
					$msg .= defined('LOADAVG') ? LOG_SEPR . sprintf('%.1f', LOADAVG) : '';
					$msg .= LOG_SEPR . str_compact($this->cur_query);
					$msg .= LOG_SEPR .' # '. $this->query_info();
					$msg .= LOG_SEPR . $this->debug_find_source();
					bb_log($msg . LOG_LF, 'sql_slow_tr');
				}
			}
		}
		return;
	}

	/**
	* Trigger error
	*/
	function trigger_error ($msg = '')
	{
		if (error_reporting())
		{
			if (!$msg) $msg = 'DB Error';

			if (DEBUG === true)
			{
				$err = $this->sql_error();
				$msg .= trim(sprintf(' #%06d %s', $err['code'], $err['message']));
			}
			else
			{
				$msg .= " [". $this->debug_find_source() ."]";
			}

			error_exit($msg);
		}
	}

	/**
	* Find caller source
	*/
	function debug_find_source ()
	{
		$source = '';
		$backtrace = debug_backtrace();

		foreach ($backtrace as $trace)
		{
			if ($trace['file'] !== __FILE__)
			{
				$source = str_replace(BB_PATH . DIR_SEPR, '', $trace['file']) .'('. $trace['line'] .')';
				break;
			}
		}

		return $source;
	}

	/**
	* Log error
	*/
	function log_error ()
	{
		if (!SQL_LOG_ERRORS) return;
		if (!error_reporting()) return;

		$msg = array();
		$err = $this->sql_error();
		$msg[] = str_compact(sprintf('#%06d %s', $err['code'], $err['message']));
		$msg[] = '';
		$msg[] = str_compact($this->cur_query);
		$msg[] = '';
		$msg[] = 'Source  : '. $this->debug_find_source();
		$msg[] = 'IP      : '. @$_SERVER['REMOTE_ADDR'];
		$msg[] = 'Date    : '. date('Y-m-d H:i:s');
		$msg[] = 'Agent   : '. @$_SERVER['HTTP_USER_AGENT'];
		$msg[] = 'Req_URI : '. @$_SERVER['REQUEST_URI'];
		$msg[] = 'Referer : '. @$_SERVER['HTTP_REFERER'];
		$msg[] = 'Method  : '. @$_SERVER['REQUEST_METHOD'];
		$msg[] = 'Request : '. trim(print_r($_REQUEST, true)) . str_repeat('_', 78) . LOG_LF;
		$msg[] = '';
		bb_log($msg, 'sql_error_tr');
	}
}

// Make the database connection
function db_init ()
{
	if (defined('SQL_LAYER'))
	{
		return;
	}
	define('SQL_LAYER', 'mysql');

	DB() = new sql_db(array(
		'dbms'      => DBMS,
		'dbhost'    => DBHOST,
		'dbname'    => DBNAME,
		'dbuser'    => DBUSER,
		'dbpasswd'  => DBPASSWD,
		'charset'   => DBCHARSET,
		'collation' => DBCOLLATION,
		'persist'   => PCONNECT,
		'dbg_user'  => false,
	));
}

##### LOG ##### // User req (by passkey)
if ($log_passkey && isset($log_passkey[$_GET[$passkey_key]]))
{
	bb_log(
		md5($_GET['info_hash'])           . LOG_SEPR .
		date('His')                       . LOG_SEPR .
		TIMENOW                           . LOG_SEPR .
		$_SERVER['QUERY_STRING']          . LOG_SEPR .
		$_SERVER['REMOTE_ADDR']           . LOG_SEPR .
		@$_SERVER['HTTP_X_FORWARDED_FOR'] . LOG_SEPR .
		@$_SERVER['HTTP_USER_AGENT']      . LOG_SEPR .
		LOG_LF,
		'passkey_'. $log_passkey[$_GET[$passkey_key]]
	);
}
### LOG END ###