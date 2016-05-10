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

header('X-Frame-Options: SAMEORIGIN');

// Cloudflare
if (isset($_SERVER['HTTP_CF_CONNECTING_IP']))
{
	$_SERVER['REMOTE_ADDR'] = $_SERVER['HTTP_CF_CONNECTING_IP'];
}

// Get all constants
require_once(BB_ROOT . 'library/defines.php');

// Composer
require_once(BB_ROOT . 'vendor/autoload.php');

// Get initial config
require_once(BB_ROOT . 'library/config.php');

$di = new \TorrentPier\Di();

// TODO: Need to get locale from settings
$di['settings.locale'] = function($di) {
	return 'en';
};

$di->register(new \TorrentPier\ServiceProviders\ConfigServiceProvider, [
	'config.file.system.main' => __DIR__ . '/library/config.php',
	'config.file.local.main' => __DIR__ . '/library/config.local.php',
]);

$di->register(new \TorrentPier\ServiceProviders\LogServiceProvider(), [
    'config.log.handlers' => $di->config->log->handlers
]);

$di->register(new \TorrentPier\ServiceProviders\DbServiceProvider, [
	'config.db' => $di->config->db->toArray()
]);

$di->register(new \TorrentPier\ServiceProviders\AuthServiceProvider());

$di->register(new \TorrentPier\ServiceProviders\SphinxServiceProvider, [
	'config.sphinx' => $di->config->sphinx->toArray()
]);

$di->register(new \TorrentPier\ServiceProviders\RequestServiceProvider());
$di->register(new \TorrentPier\ServiceProviders\ViewServiceProvider());

$di->register(new \TorrentPier\ServiceProviders\TranslationServiceProvider(), [
	'config.debug' => $di->config->debug,
	'config.translator.dir_cache' => $di->config->translator->dir_cache,
	'config.translator.resources' => $di->config->translator->resources->toArray()
]);

$di->register(new \TorrentPier\ServiceProviders\TwigServiceProvider, [
	'config.debug' => $di->config->debug,
	'config.twig.dir_templates' => $di->config->twig->dir_templates,
	'config.twig.dir_cache' => $di->config->twig->dir_cache
]);

$di->register(new \TorrentPier\ServiceProviders\CaptchaServiceProvider, [
	'config.captcha.secret_key' => $di->config->captcha->secret_key
]);

$bb_cfg        = $di->config->toArray();
$page_cfg      = $di->config->page->toArray();
$tr_cfg        = $di->config->tracker->toArray();
$rating_limits = $di->config->rating->toArray();
define('BB_CFG_LOADED', true);

// Load Zend Framework
use Zend\Loader\StandardAutoloader;
$loader = new StandardAutoloader(array('autoregister_zf' => true));
$loader->register();

$server_protocol = ($bb_cfg['cookie_secure']) ? 'https://' : 'http://';
$server_port = (in_array($bb_cfg['server_port'], array(80, 443))) ? '' : ':' . $bb_cfg['server_port'];
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

define('PEER_HASH_PREFIX',   'peer_');
define('PEERS_LIST_PREFIX',  'peers_list_');
define('PEER_HASH_EXPIRE',   round($bb_cfg['announce_interval'] * (0.85 * $tr_cfg['expire_factor']))); // sec
define('PEERS_LIST_EXPIRE',  round($bb_cfg['announce_interval'] * 0.7)); // sec

define('DL_STATUS_RELEASER', -1);
define('DL_STATUS_DOWN',      0);
define('DL_STATUS_COMPLETE',  1);
define('DL_STATUS_CANCEL',    3);
define('DL_STATUS_WILL',      4);

define('TOR_TYPE_GOLD',       1);
define('TOR_TYPE_SILVER',     2);

define('GUEST_UID', -1);
define('BOT_UID',   -746);

/**
 * Database
 * TODO: @deprecated
 */
// Core DB class
require(CORE_DIR . 'dbs.php');

$DBS = new DBS([
	'db' => [
		'db' => [
			$di->config->db->hostname,
			$di->config->db->database,
			$di->config->db->username,
			$di->config->db->password,
			$di->config->db->charset,
			false
		]
	],
	'db_alias' => $bb_cfg['db_alias']
]);

/**
 * TODO: @deprecated
 */
function DB ($db_alias = 'db')
{
	global $DBS;
	return $DBS->get_db_obj($db_alias);
}

/**
 * Cache
 */
// Main cache class
require(INC_DIR . 'cache/common.php');
// Main datastore class
require(INC_DIR . 'datastore/common.php');

// Core CACHE class
require(CORE_DIR . 'caches.php');
$CACHES = new CACHES($bb_cfg);

/**
 * TODO: @deprecated
 */
function CACHE ($cache_name)
{
	global $CACHES;
	return $CACHES->get_cache_obj($cache_name);
}

// Common cache classes
require(INC_DIR . 'cache/memcache.php');
require(INC_DIR . 'cache/sqlite.php');
require(INC_DIR . 'cache/redis.php');
require(INC_DIR . 'cache/apc.php');
require(INC_DIR . 'cache/xcache.php');
require(INC_DIR . 'cache/file.php');

/**
* Datastore
*/
// Common datastore classes
require(INC_DIR . 'datastore/memcache.php');
require(INC_DIR . 'datastore/sqlite.php');
require(INC_DIR . 'datastore/redis.php');
require(INC_DIR . 'datastore/apc.php');
require(INC_DIR . 'datastore/xcache.php');
require(INC_DIR . 'datastore/file.php');

// Initialize datastore
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

	case 'apc':
		$datastore = new datastore_apc($bb_cfg['cache']['prefix']);
		break;

	case 'xcache':
		$datastore = new datastore_xcache($bb_cfg['cache']['prefix']);
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

	if (file_exists($file) && $max_size && filesize($file) >= $max_size)
	{
		$old_name = $file; $ext = '';
		if (preg_match('#^(.+)(\.[^\\/]+)$#', $file, $matches))
		{
			$old_name = $matches[1]; $ext = $matches[2];
		}
		$new_name = $old_name .'_[old]_'. date('Y-m-d_H-i-s_') . getmypid() . $ext;
		clearstatcache();
		if (file_exists($file) && filesize($file) >= $max_size && !file_exists($new_name))
		{
			rename($file, $new_name);
		}
	}
	if (!$fp = fopen($file, 'ab'))
	{
		if ($dir_created = bb_mkdir(dirname($file)))
		{
			$fp = fopen($file, 'ab');
		}
	}
	if ($fp)
	{
		if ($lock)
		{
			flock($fp, LOCK_EX);
		}
		if ($replace_content)
		{
			ftruncate($fp, 0);
			fseek($fp, 0, SEEK_SET);
		}
		$bytes_written = fwrite($fp, $str);
		fclose($fp);
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
		return (mkdir_rec(dirname($path), $mode)) ? mkdir($path, $mode) : false;
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
		$str .= str_shuffle(preg_replace('#[^0-9a-zA-Z]#', '', password_hash(uniqid(mt_rand(), true), PASSWORD_BCRYPT)));
	}
	return substr($str, 0, $len);
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

	if (isset($_SERVER['REQUEST_URI'])) {
		$str[] = $_SERVER['REQUEST_URI'];
	}
	if (isset($_SERVER['HTTP_USER_AGENT'])) {
		$str[] = $_SERVER['HTTP_USER_AGENT'];
	}
	if (isset($_SERVER['HTTP_REFERER'])) {
		$str[] = $_SERVER['HTTP_REFERER'];
	}

	if (!empty($_POST) && $add_post) $str[] = "post: ". str_compact(urldecode(http_build_query($_POST)));
	$str = join("\t", $str) . "\n";
	bb_log($str, $file);
}

// Board init
if (!defined('IN_TRACKER'))
{
	require(INC_DIR .'init_bb.php');
}
// Tracker init
else if (defined('IN_TRACKER'))
{
	define('DUMMY_PEER', pack('Nn', ip2long($_SERVER['REMOTE_ADDR']), !empty($_GET['port']) ? intval($_GET['port']) : mt_rand(1000, 65000)));

	function dummy_exit ($interval = 1800)
	{
		$output = \Rych\Bencode\Bencode::encode(array(
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
			dummy_exit(mt_rand(60, 2400));
		}
	}
}
