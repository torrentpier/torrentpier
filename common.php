<?php
/**
 * MIT License
 *
 * Copyright (c) 2005-2017 TorrentPier
 *
 * Permission is hereby granted, free of charge, to any person obtaining a copy
 * of this software and associated documentation files (the "Software"), to deal
 * in the Software without restriction, including without limitation the rights
 * to use, copy, modify, merge, publish, distribute, sublicense, and/or sell
 * copies of the Software, and to permit persons to whom the Software is
 * furnished to do so, subject to the following conditions:
 *
 * The above copyright notice and this permission notice shall be included in all
 * copies or substantial portions of the Software.
 *
 * THE SOFTWARE IS PROVIDED "AS IS", WITHOUT WARRANTY OF ANY KIND, EXPRESS OR
 * IMPLIED, INCLUDING BUT NOT LIMITED TO THE WARRANTIES OF MERCHANTABILITY,
 * FITNESS FOR A PARTICULAR PURPOSE AND NONINFRINGEMENT. IN NO EVENT SHALL THE
 * AUTHORS OR COPYRIGHT HOLDERS BE LIABLE FOR ANY CLAIM, DAMAGES OR OTHER
 * LIABILITY, WHETHER IN AN ACTION OF CONTRACT, TORT OR OTHERWISE, ARISING FROM,
 * OUT OF OR IN CONNECTION WITH THE SOFTWARE OR THE USE OR OTHER DEALINGS IN THE
 * SOFTWARE.
 */

if (isset($_REQUEST['GLOBALS'])) {
    die();
}

define('TIMESTART', utime());
define('TIMENOW', time());

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

if (!defined('BB_ROOT')) {
    define('BB_ROOT', './');
}
if (!defined('BB_SCRIPT')) {
    define('BB_SCRIPT', 'undefined');
}

header('X-Frame-Options: SAMEORIGIN');

// Cloudflare
if (isset($_SERVER['HTTP_CF_CONNECTING_IP'])) {
    $_SERVER['REMOTE_ADDR'] = $_SERVER['HTTP_CF_CONNECTING_IP'];
}

// Get all constants
require_once __DIR__ . '/library/defines.php';

// Composer
if (!file_exists(__DIR__ . '/vendor/autoload.php')) {
    die('Please <a href="https://getcomposer.org/download/" target="_blank" rel="noreferrer" style="color:#0a25bb;">install composer</a> and run <code style="background:#222;color:#00e01f;padding:2px 6px;border-radius:3px;">composer install</code>');
}
require_once __DIR__ . '/vendor/autoload.php';

/**
 * Gets the value of an environment variable. Supports boolean, empty and null.
 *
 * @param  string $key
 * @param  mixed $default
 * @return mixed
 */
function env($key, $default = null)
{
    $value = getenv($key);
    if (!$value) return value($default);
    switch (strtolower($value)) {
        case 'true':
        case '(true)':
            return true;
        case 'false':
        case '(false)':
            return false;
        case '(null)':
            return null;
        case '(empty)':
            return '';
    }
    return $value;
}

/**
 * Return the default value of the given value.
 *
 * @param  mixed $value
 * @return mixed
 */
function value($value)
{
    return $value instanceof Closure ? $value() : $value;
}

// Get initial config
if (!getenv('APP_DEBUG') && file_exists(__DIR__ . '/.env')) {
    (new Symfony\Component\Dotenv\Dotenv())->load(__DIR__ . '/.env');
}
require_once __DIR__ . '/library/config.php';

// Local config
if (file_exists(__DIR__ . '/library/config.local.php')) {
    require_once __DIR__ . '/library/config.local.php';
}

$server_protocol = $bb_cfg['cookie_secure'] ? 'https://' : 'http://';
$server_port = in_array((int)$bb_cfg['server_port'], array(80, 443), true) ? '' : ':' . $bb_cfg['server_port'];
define('FORUM_PATH', $bb_cfg['script_path']);
define('FULL_URL', $server_protocol . $bb_cfg['server_name'] . $server_port . $bb_cfg['script_path']);
unset($server_protocol, $server_port);

// Debug options
define('DBG_USER', (isset($_COOKIE[COOKIE_DBG])));

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
 * Progressive error reporting
 */
if ($bb_cfg['bugsnag']['enabled'] && env('APP_ENV', 'production') !== 'local') {
    /** @var Bugsnag\Handler $bugsnag */
    $bugsnag = Bugsnag\Client::make($bb_cfg['bugsnag']['api_key']);
    Bugsnag\Handler::register($bugsnag);
}

if (DBG_USER && env('APP_ENV', 'production') === 'local') {
    /** @var Whoops\Run $whoops */
    $whoops = new \Whoops\Run;
    $whoops->pushHandler(new \Whoops\Handler\PrettyPageHandler);
    $whoops->register();
}

/**
 * Database
 */
$DBS = new TorrentPier\Legacy\Dbs($bb_cfg);

function DB($db_alias = 'db')
{
    global $DBS;
    return $DBS->get_db_obj($db_alias);
}

/**
 * Cache
 */
$CACHES = new TorrentPier\Legacy\Caches($bb_cfg);

function CACHE($cache_name)
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
        $default_cfg = array(
            'db_file_path' => $bb_cfg['cache']['db_dir'] . 'datastore.sqlite.db',
            'pconnect' => true,
            'con_required' => true,
        );
        $datastore = new TorrentPier\Legacy\Datastore\Sqlite($default_cfg, $bb_cfg['cache']['prefix']);
        break;

    case 'redis':
        $datastore = new TorrentPier\Legacy\Datastore\Redis($bb_cfg['cache']['redis'], $bb_cfg['cache']['prefix']);
        break;

    case 'apc':
        $datastore = new TorrentPier\Legacy\Datastore\Apc($bb_cfg['cache']['prefix']);
        break;

    case 'xcache':
        $datastore = new TorrentPier\Legacy\Datastore\Xcache($bb_cfg['cache']['prefix']);
        break;

    case 'filecache':
    default:
        $datastore = new TorrentPier\Legacy\Datastore\File($bb_cfg['cache']['db_dir'] . 'datastore/', $bb_cfg['cache']['prefix']);
}

function sql_dbg_enabled()
{
    return (SQL_DEBUG && DBG_USER && !empty($_COOKIE['sql_log']));
}

function short_query($sql, $esc_html = false)
{
    $max_len = 100;
    $sql = str_compact($sql);

    if (!empty($_COOKIE['sql_log_full'])) {
        if (mb_strlen($sql, 'UTF-8') > $max_len) {
            $sql = mb_substr($sql, 0, 50) . ' [...cut...] ' . mb_substr($sql, -50);
        }
    }

    return $esc_html ? htmlCHR($sql, true) : $sql;
}

// Functions
function utime()
{
    return array_sum(explode(' ', microtime()));
}

function bb_log($msg, $file_name)
{
    if (is_array($msg)) {
        $msg = implode(LOG_LF, $msg);
    }
    $file_name .= (LOG_EXT) ? '.' . LOG_EXT : '';
    return file_write($msg, LOG_DIR . '/' . $file_name);
}

function file_write($str, $file, $max_size = LOG_MAX_SIZE, $lock = true, $replace_content = false)
{
    $bytes_written = false;

    if ($max_size && file_exists($file) && filesize($file) >= $max_size) {
        $old_name = $file;
        $ext = '';
        if (preg_match('#^(.+)(\.[^\\/]+)$#', $file, $matches)) {
            $old_name = $matches[1];
            $ext = $matches[2];
        }
        $new_name = $old_name . '_[old]_' . date('Y-m-d_H-i-s_') . getmypid() . $ext;
        clearstatcache();
        if (!file_exists($new_name)) {
            rename($file, $new_name);
        }
    }
    if (!$fp = fopen($file, 'ab')) {
        if ($dir_created = bb_mkdir(dirname($file))) {
            $fp = fopen($file, 'ab');
        }
    }
    if ($fp) {
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

    return $bytes_written;
}

function bb_mkdir($path, $mode = 0777)
{
    $old_um = umask(0);
    $dir = mkdir_rec($path, $mode);
    umask($old_um);
    return $dir;
}

function mkdir_rec($path, $mode)
{
    if (is_dir($path)) {
        return ($path !== '.' && $path !== '..') ? is_writable($path) : false;
    }

    return mkdir_rec(dirname($path), $mode) ? mkdir($path, $mode) : false;
}

function verify_id($id, $length)
{
    return (is_string($id) && preg_match('#^[a-zA-Z0-9]{' . $length . '}$#', $id));
}

function clean_filename($fname)
{
    static $s = array('\\', '/', ':', '*', '?', '"', '<', '>', '|', ' ');
    return str_replace($s, '_', str_compact($fname));
}

/**
 * Декодирование оригинального IP
 * @param $ip
 * @return string
 */
function encode_ip($ip)
{
    return Longman\IPTools\Ip::ip2long($ip);
}

/**
 * Восстановление декодированного IP
 * @param $ip
 * @return string
 */
function decode_ip($ip)
{
    return Longman\IPTools\Ip::long2ip($ip);
}

/**
 * Проверка IP на валидность
 *
 * @param $ip
 * @return bool
 */
function verify_ip($ip)
{
    return Longman\IPTools\Ip::isValid($ip);
}

function bb_crc32($str)
{
    return (float)sprintf('%u', crc32($str));
}

function hexhex($value)
{
    return dechex(hexdec($value));
}

function str_compact($str)
{
    return preg_replace('#\s+#u', ' ', trim($str));
}

function make_rand_str($len = 10)
{
    $str = '';
    while (strlen($str) < $len) {
        $str .= str_shuffle(preg_replace('#[^0-9a-zA-Z]#', '', password_hash(uniqid(mt_rand(), true), PASSWORD_BCRYPT)));
    }
    return substr($str, 0, $len);
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

function hide_bb_path($path)
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
            return function_exists('memory_get_usage') ? memory_get_usage() : 0;
            break;
        case 'mem_peak':
            return function_exists('memory_get_peak_usage') ? memory_get_peak_usage() : 0;
            break;
        default:
            trigger_error("invalid param: $param", E_USER_ERROR);
    }
}

function ver_compare($version1, $operator, $version2)
{
    return version_compare($version1, $version2, $operator);
}

function dbg_log($str, $file)
{
    $dir = LOG_DIR . (defined('IN_TRACKER') ? '/dbg_tr/' : '/dbg_bb/') . date('m-d_H') . '/';
    return file_write($str, $dir . $file, false, false);
}

function log_get($file = '', $prepend_str = false)
{
    log_request($file, $prepend_str, false);
}

function log_post($file = '', $prepend_str = false)
{
    log_request($file, $prepend_str, true);
}

function log_request($file = '', $prepend_str = false, $add_post = true)
{
    global $user;

    $file = $file ?: 'req/' . date('m-d');
    $str = array();
    $str[] = date('m-d H:i:s');
    if ($prepend_str !== false) {
        $str[] = $prepend_str;
    }
    if (!empty($user->data)) {
        $str[] = $user->id . "\t" . html_entity_decode($user->name);
    }
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

    if (!empty($_POST) && $add_post) {
        $str[] = "post: " . str_compact(urldecode(http_build_query($_POST)));
    }
    $str = implode("\t", $str) . "\n";
    bb_log($str, $file);
}

// Board or tracker init
if (!defined('IN_TRACKER')) {
    require INC_DIR . '/init_bb.php';
} else {
    define('DUMMY_PEER', pack('Nn', ip2long($_SERVER['REMOTE_ADDR']), !empty($_GET['port']) ? (int)$_GET['port'] : mt_rand(1000, 65000)));

    function dummy_exit($interval = 1800)
    {
        $output = \Rych\Bencode\Bencode::encode([
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
            dummy_exit(mt_rand(60, 2400));
        }
    }
}
