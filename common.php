<?php
/**
 * TorrentPier – Bull-powered BitTorrent tracker engine
 *
 * @copyright Copyright (c) 2005-2023 TorrentPier (https://torrentpier.com)
 * @link      https://github.com/torrentpier/torrentpier for the canonical source repository
 * @license   https://github.com/torrentpier/torrentpier/blob/master/LICENSE MIT License
 */

if (isset($_REQUEST['GLOBALS'])) {
    die();
}

define('TIMESTART', utime());
define('TIMENOW', time());
define('BB_PATH', __DIR__);

if (empty($_SERVER['REMOTE_ADDR'])) {
    $_SERVER['REMOTE_ADDR'] = '127.0.0.1';
}
if (empty($_SERVER['HTTP_USER_AGENT'])) {
    $_SERVER['HTTP_USER_AGENT'] = '';
}
if (empty($_SERVER['HTTP_REFERER'])) {
    $_SERVER['HTTP_REFERER'] = '';
}
if (empty($_SERVER['SERVER_NAME'])) {
    $_SERVER['SERVER_NAME'] = '';
}
if (empty($_SERVER['SERVER_ADDR'])) {
    $_SERVER['SERVER_ADDR'] = getenv('SERVER_ADDR');
}

if (!defined('BB_ROOT')) {
    define('BB_ROOT', './');
}
if (!defined('BB_SCRIPT')) {
    define('BB_SCRIPT', null);
}

header('X-Frame-Options: SAMEORIGIN');

// Cloudflare
if (isset($_SERVER['HTTP_CF_CONNECTING_IP'])) {
    $_SERVER['REMOTE_ADDR'] = $_SERVER['HTTP_CF_CONNECTING_IP'];
}

// Get all constants
require_once BB_PATH . '/library/defines.php';

// Composer
if (!file_exists(BB_PATH . '/vendor/autoload.php')) {
    die('Please <a href="https://getcomposer.org/download/" target="_blank" rel="noreferrer" style="color:#0a25bb;">install composer</a> and run <code style="background:#222;color:#00e01f;padding:2px 6px;border-radius:3px;">composer install</code>');
}
require_once BB_PATH . '/vendor/autoload.php';

/**
 * Gets the value of an environment variable.
 *
 * @param string $key
 * @param mixed $default
 * @return mixed
 */
function env(string $key, $default = null)
{
    return \TorrentPier\Env::get($key, $default);
}

// Load ENV
try {
    $dotenv = Dotenv\Dotenv::createMutable(BB_PATH);
    $dotenv->load();
} catch (\Dotenv\Exception\InvalidPathException $pathException) {
    die('Please rename from <code style="background:#222;color:#00e01f;padding:2px 6px;border-radius:3px;">.env.example</code> to <code style="background:#222;color:#00e01f;padding:2px 6px;border-radius:3px;">.env</code>, and configure it');
}

// Load config
require_once BB_PATH . '/library/config.php';

// Local config
if (file_exists(BB_PATH . '/library/config.local.php')) {
    require_once BB_PATH . '/library/config.local.php';
}

/**
 * Progressive error reporting
 */
\TorrentPier\Dev::initDebug();

/**
 * Server variables initialize
 */
$server_protocol = $bb_cfg['cookie_secure'] ? 'https://' : 'http://';
$server_port = in_array((int)$bb_cfg['server_port'], [80, 443], true) ? '' : ':' . $bb_cfg['server_port'];
define('FORUM_PATH', $bb_cfg['script_path']);
define('FULL_URL', $server_protocol . $bb_cfg['server_name'] . $server_port . $bb_cfg['script_path']);
unset($server_protocol, $server_port);

// Board / tracker shared constants and functions
define('BB_BT_TORRENTS', 'bb_bt_torrents');
define('BB_BT_TRACKER', 'bb_bt_tracker');
define('BB_BT_TRACKER_SNAP', 'bb_bt_tracker_snap');
define('BB_BT_USERS', 'bb_bt_users');

define('BT_AUTH_KEY_LENGTH', 10);
define('PEER_HASH_PREFIX', 'peer_');
define('PEERS_LIST_PREFIX', 'peers_list_');
define('PEER_HASH_EXPIRE', round($bb_cfg['announce_interval'] * (0.85 * $bb_cfg['tracker']['expire_factor']))); // sec
define('PEERS_LIST_EXPIRE', round($bb_cfg['announce_interval'] * 0.7)); // sec

define('DL_STATUS_RELEASER', -1);
define('DL_STATUS_DOWN', 0);
define('DL_STATUS_COMPLETE', 1);
define('DL_STATUS_CANCEL', 3);
define('DL_STATUS_WILL', 4);

define('TOR_TYPE_GOLD', 1);
define('TOR_TYPE_SILVER', 2);

define('GUEST_UID', -1);
define('BOT_UID', -746);

/**
 * Database
 */
$DBS = new TorrentPier\Legacy\Dbs($bb_cfg);

function DB(string $db_alias = 'db')
{
    global $DBS;
    return $DBS->get_db_obj($db_alias);
}

/**
 * Cache
 */
$CACHES = new TorrentPier\Legacy\Caches($bb_cfg);

function CACHE(string $cache_name)
{
    global $CACHES;
    return $CACHES->get_cache_obj($cache_name);
}

/**
 * Datastore
 */
switch ($bb_cfg['datastore_type']) {
    case 'memcache':
        $datastore = new TorrentPier\Legacy\Datastore\Memcache($bb_cfg['cache']['memcache'], $bb_cfg['cache']['prefix']);
        break;

    case 'sqlite':
        $default_cfg = [
            'db_file_path' => $bb_cfg['cache']['db_dir'] . 'datastore.sqlite.db',
            'pconnect' => true,
            'con_required' => true,
        ];
        $datastore = new TorrentPier\Legacy\Datastore\Sqlite($default_cfg, $bb_cfg['cache']['prefix']);
        break;

    case 'redis':
        $datastore = new TorrentPier\Legacy\Datastore\Redis($bb_cfg['cache']['redis'], $bb_cfg['cache']['prefix']);
        break;

    case 'filecache':
    default:
        $datastore = new TorrentPier\Legacy\Datastore\File($bb_cfg['cache']['db_dir'] . 'datastore/', $bb_cfg['cache']['prefix']);
}

if (CHECK_REQIREMENTS['status'] && !CACHE('bb_cache')->get('system_req')) {
    // [1] Check PHP Version
    if (!\TorrentPier\Helpers\IsHelper::isPHP(CHECK_REQIREMENTS['php_min_version'])) {
        die("TorrentPier requires PHP version " . CHECK_REQIREMENTS['php_min_version'] . "+ Your PHP version " . PHP_VERSION);
    }

    // [2] Check installed PHP Extensions on server
    $data = [];
    foreach (CHECK_REQIREMENTS['ext_list'] as $ext) {
        if (!extension_loaded($ext)) {
            $data[] = '<code style="background:#222;color:#00e01f;padding:2px 6px;border-radius:3px;">' . $ext . '</code>';
        }
    }
    if (!empty($data)) {
        die(sprintf("TorrentPier requires %s extension(s) installed on server", implode(', ', $data)));
    }

    CACHE('bb_cache')->set('system_req', true);
}

// Functions
function utime()
{
    return array_sum(explode(' ', microtime()));
}

function bb_log($msg, $file_name, $return_path = false)
{
    if (is_array($msg)) {
        $msg = implode(LOG_LF, $msg);
    }
    $file_name .= (LOG_EXT) ? '.' . LOG_EXT : '';

    $path = (LOG_DIR . '/' . $file_name);
    if ($return_path) {
        return $path;
    }

    return file_write($msg, $path);
}

function file_write($str, $file, $max_size = LOG_MAX_SIZE, $lock = true, $replace_content = false)
{
    $bytes_written = false;
    clearstatcache();

    if (($max_size && file_exists($file) && is_file($file)) && filesize($file) >= $max_size) {
        $file_parts = pathinfo($file);
        $new_name = ($file_parts['dirname'] . '/' . $file_parts['filename'] . '_[old]_' . date('Y-m-d_H-i-s_') . getmypid() . '.' . $file_parts['extension']);
        clearstatcache();
        if (!file_exists($new_name) && !is_file($new_name)) {
            rename($file, $new_name);
        }
    }
    clearstatcache();
    if (bb_mkdir(dirname($file))) {
        if ($fp = fopen($file, 'ab+')) {
            if ($lock) {
                flock($fp, LOCK_EX);
            }
            if ($replace_content) {
                ftruncate($fp, 0);
                fseek($fp, 0, SEEK_SET);
            }
            $bytes_written = fwrite($fp, $str);
            fclose($fp);
        }
    }

    return $bytes_written;
}

function bb_mkdir($path, $mode = 0777)
{
    $old_um = umask(0);
    $dir = mkdir_rec($path, $mode);
    umask($old_um);
    return $dir;
}

function mkdir_rec($path, $mode): bool
{
    if (is_dir($path)) {
        return ($path !== '.' && $path !== '..') && is_writable($path);
    }

    return mkdir_rec(dirname($path), $mode) && mkdir($path, $mode);
}

function verify_id($id, $length): bool
{
    return (is_string($id) && preg_match('#^[a-zA-Z0-9]{' . $length . '}$#', $id));
}

function clean_filename($fname)
{
    static $s = ['\\', '/', ':', '*', '?', '"', '<', '>', '|', ' '];
    return str_replace($s, '_', str_compact($fname));
}

/**
 * @param string $str
 * @return string
 */
function str_compact($str)
{
    return preg_replace('#\s+#u', ' ', trim($str ?? ''));
}

/**
 * Generate a "random" alphanumeric string.
 *
 * Should not be considered sufficient for cryptography, etc.
 *
 * @param int|string $length
 * @return string
 */
function make_rand_str($length = 10): string
{
    $pool = '0123456789abcdefghijklmnopqrstuvwxyzABCDEFGHIJKLMNOPQRSTUVWXYZ';

    return substr(str_shuffle(str_repeat($pool, (int)$length)), 0, $length);
}

function array_deep(&$var, $fn, $one_dimensional = false, $array_only = false)
{
    if (is_array($var)) {
        foreach ($var as $k => $v) {
            if (is_array($v)) {
                if ($one_dimensional) {
                    unset($var[$k]);
                } elseif ($array_only) {
                    $var[$k] = $fn($v);
                } else {
                    array_deep($var[$k], $fn);
                }
            } elseif (!$array_only) {
                $var[$k] = $fn($v);
            }
        }
    } elseif (!$array_only) {
        $var = $fn($var);
    }
}

/**
 * Hide BB_PATH
 *
 * @param string $path
 * @return string
 */
function hide_bb_path(string $path): string
{
    return ltrim(str_replace(BB_PATH, '', $path), '/\\');
}

function sys($param)
{
    switch ($param) {
        case 'la':
            return function_exists('sys_getloadavg') ? implode(' ', sys_getloadavg()) : 0;
            break;
        case 'mem':
            return memory_get_usage();
            break;
        case 'mem_peak':
            return memory_get_peak_usage();
            break;
        default:
            trigger_error("invalid param: $param", E_USER_ERROR);
    }
}

// Board or tracker init
if (!defined('IN_TRACKER')) {
    require_once INC_DIR . '/init_bb.php';
} else {
    define('DUMMY_PEER', pack('Nn', \TorrentPier\Helpers\IPHelper::ip2long($_SERVER['REMOTE_ADDR']), !empty($_GET['port']) ? (int)$_GET['port'] : random_int(1000, 65000)));

    function dummy_exit($interval = 1800)
    {
        $output = \SandFox\Bencode\Bencode::encode([
            'interval' => (int)$interval,
            'min interval' => (int)$interval,
            'peers' => (string)DUMMY_PEER,
        ]);

        die($output);
    }

    header('Content-Type: text/plain');
    header('Pragma: no-cache');

    if (!defined('IN_ADMIN')) {
        // Exit if tracker is disabled via ON/OFF trigger
        if (file_exists(BB_DISABLED)) {
            dummy_exit(random_int(60, 2400));
        }
    }
}
