<?php
/**
 * TorrentPier – Bull-powered BitTorrent tracker engine
 *
 * @copyright Copyright (c) 2005-2025 TorrentPier (https://torrentpier.com)
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
    $_SERVER['SERVER_NAME'] = getenv('SERVER_NAME');
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
date_default_timezone_set('UTC');

// Set remote address
$trustedProxies = [
    // Optional trusted proxy validation (empty array = disabled)
    // '127.0.0.1'
];

$allowedCDNs = [
    'HTTP_CF_CONNECTING_IP',
    'HTTP_FASTLY_CLIENT_IP',
    'HTTP_X_REAL_IP',
    'HTTP_X_FORWARDED_FOR',
    // Add your custom headers here if needed
    // Example: 'HTTP_TRUE_CLIENT_IP',        // Akamai
    // Example: 'HTTP_X_CLIENT_IP',           // Custom proxy
    // Example: 'HTTP_INCAP_CLIENT_IP',       // Incapsula
];

if (empty($trustedProxies) || in_array($_SERVER['REMOTE_ADDR'], $trustedProxies)) {
    foreach ($allowedCDNs as $header) {
        if (!isset($_SERVER[$header])) {
            continue;
        }

        if ($header === 'HTTP_X_FORWARDED_FOR') {
            // Handle X-Forwarded-For which may contain multiple IPs
            $ips = explode(',', $_SERVER[$header]);
            $clientIP = trim($ips[0]);
        } else {
            $clientIP = $_SERVER[$header];
        }

        if (filter_var($clientIP, FILTER_VALIDATE_IP, FILTER_FLAG_NO_PRIV_RANGE | FILTER_FLAG_NO_RES_RANGE)) {
            $_SERVER['REMOTE_ADDR'] = $clientIP;
            break;
        }
    }
}
unset($trustedProxies, $clientIP, $allowedCDNs);

// Get all constants
require_once BB_PATH . '/library/defines.php';

// Composer
if (!is_file(BB_PATH . '/vendor/autoload.php')) {
    die('🔩 Manual install: <a href="https://getcomposer.org/download/" target="_blank" rel="noreferrer" style="color:#0a25bb;">Install composer</a> and run <code style="background:#222;color:#00e01f;padding:2px 6px;border-radius:3px;">composer install</code>.<br/>☕️ Quick install: Run <code style="background:#222;color:#00e01f;padding:2px 6px;border-radius:3px;">php install.php</code> in CLI mode.');
}
require_once BB_PATH . '/vendor/autoload.php';

/**
 * Gets the value of an environment variable.
 *
 * @param string $key
 * @param mixed|null $default
 * @return mixed
 */
function env(string $key, mixed $default = null): mixed
{
    return \TorrentPier\Env::get($key, $default);
}

// Load ENV
try {
    $dotenv = Dotenv\Dotenv::createMutable(BB_PATH);
    $dotenv->load();
} catch (\Dotenv\Exception\InvalidPathException $pathException) {
    die('🔩 Manual install: Rename from <code style="background:#222;color:#00e01f;padding:2px 6px;border-radius:3px;">.env.example</code> to <code style="background:#222;color:#00e01f;padding:2px 6px;border-radius:3px;">.env</code>, and configure it.<br/>☕️ Quick install: Run <code style="background:#222;color:#00e01f;padding:2px 6px;border-radius:3px;">php install.php</code> in CLI mode.');
}

// Load config
require_once BB_PATH . '/library/config.php';

// Local config
if (is_file(BB_PATH . '/library/config.local.php')) {
    require_once BB_PATH . '/library/config.local.php';
}

/**
 * Initialize debug
 */
define('APP_ENV', env('APP_ENV', 'production'));
if (APP_ENV === 'local') {
    define('DBG_USER', true); // forced debug
} else {
    define('DBG_USER', isset($_COOKIE[COOKIE_DBG]));
}
(new \TorrentPier\Dev());

/**
 * Server variables initialize
 */
$server_protocol = $bb_cfg['cookie_secure'] ? 'https://' : 'http://';
$server_port = in_array((int)$bb_cfg['server_port'], [80, 443], true) ? '' : ':' . $bb_cfg['server_port'];
define('FORUM_PATH', $bb_cfg['script_path']);
define('FULL_URL', $server_protocol . $bb_cfg['server_name'] . $server_port . $bb_cfg['script_path']);
unset($server_protocol, $server_port);

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
    case 'apcu':
        $datastore = new TorrentPier\Legacy\Datastore\APCu($bb_cfg['cache']['prefix']);
        break;
    case 'memcached':
        $datastore = new TorrentPier\Legacy\Datastore\Memcached($bb_cfg['cache']['memcached'], $bb_cfg['cache']['prefix']);
        break;
    case 'sqlite':
        $datastore = new TorrentPier\Legacy\Datastore\Sqlite($bb_cfg['cache']['db_dir'] . 'datastore', $bb_cfg['cache']['prefix']);
        break;
    case 'redis':
        $datastore = new TorrentPier\Legacy\Datastore\Redis($bb_cfg['cache']['redis'], $bb_cfg['cache']['prefix']);
        break;
    case 'filecache':
    default:
        $datastore = new TorrentPier\Legacy\Datastore\File($bb_cfg['cache']['db_dir'] . 'datastore/', $bb_cfg['cache']['prefix']);
}

// Functions
function utime()
{
    return array_sum(explode(' ', microtime()));
}

function bb_log($msg, $file_name = 'logs', $return_path = false)
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

    if (is_file($file) && ($max_size && (filesize($file) >= $max_size))) {
        $file_parts = pathinfo($file);
        $new_name = ($file_parts['dirname'] . '/' . $file_parts['filename'] . '_[old]_' . date('Y-m-d_H-i-s_') . getmypid() . '.' . $file_parts['extension']);
        clearstatcache();
        if (!is_file($new_name)) {
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
 * Convert special characters to HTML entities
 *
 * @param $txt
 * @param bool $double_encode
 * @param int $quote_style
 * @param ?string $charset
 * @return string
 */
function htmlCHR($txt, bool $double_encode = false, int $quote_style = ENT_QUOTES, ?string $charset = DEFAULT_CHARSET): string
{
    return (string)htmlspecialchars($txt ?? '', $quote_style, $charset, $double_encode);
}

/**
 * @param string $str
 * @return string
 */
function str_compact($str)
{
    return preg_replace('/\s\s+/', ' ', trim($str ?? ''));
}

/**
 * Generate a "random" alphanumeric string.
 *
 * Should not be considered sufficient for cryptography, etc.
 *
 * @param int $length
 * @return string
 * @throws Exception
 */
function make_rand_str(int $length = 10): string
{
    $pool = str_shuffle('0123456789abcdefghijklmnopqrstuvwxyzABCDEFGHIJKLMNOPQRSTUVWXYZ');

    $randomString = '';
    for ($i = 0; $i < $length; $i++) {
        $randomString .= $pool[random_int(0, 61)];
    }

    return $randomString;
}

/**
 * Calculates user ratio
 *
 * @param array $btu
 * @return float|null
 */
function get_bt_ratio(array $btu): ?float
{
    return
        (!empty($btu['u_down_total']) && $btu['u_down_total'] > MIN_DL_FOR_RATIO)
            ? round((($btu['u_up_total'] + $btu['u_up_release'] + $btu['u_up_bonus']) / $btu['u_down_total']), 2)
            : null;
}

function array_deep(&$var, $fn, $one_dimensional = false, $array_only = false, $timeout = false)
{
    if ($timeout) {
        static $recursions = 0;
        if (time() > (TIMENOW + $timeout)) {
            return [
                'timeout' => true,
                'recs' => $recursions
            ];
        }
        $recursions++;
    }
    if (is_array($var)) {
        foreach ($var as $k => $v) {
            if (is_array($v)) {
                if ($one_dimensional) {
                    unset($var[$k]);
                } elseif ($array_only) {
                    $var[$k] = $fn($v);
                } else {
                    array_deep($var[$k], $fn, timeout: $timeout);
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

/**
 * Returns memory usage statistic
 *
 * @param string $param
 * @return int|void
 */
function sys(string $param)
{
    switch ($param) {
        case 'mem':
            return memory_get_usage();
        case 'mem_peak':
            return memory_get_peak_usage();
        default:
            trigger_error("invalid param: $param", E_USER_ERROR);
    }
}

/**
 * Some shared defines
 */
// Initialize demo mode
define('IN_DEMO_MODE', env('APP_DEMO_MODE', false));

// Ratio status
define('RATIO_ENABLED', TR_RATING_LIMITS && MIN_DL_FOR_RATIO > 0);

// Initialization
if (!defined('IN_TRACKER')) {
    // Init board
    require_once INC_DIR . '/init_bb.php';
} else {
    define('DUMMY_PEER', pack('Nn', \TorrentPier\Helpers\IPHelper::ip2long($_SERVER['REMOTE_ADDR']), !empty($_GET['port']) ? (int)$_GET['port'] : random_int(1000, 65000)));

    define('PEER_HASH_EXPIRE', round($bb_cfg['announce_interval'] * (0.85 * $bb_cfg['tracker']['expire_factor'])));
    define('PEERS_LIST_EXPIRE', round($bb_cfg['announce_interval'] * 0.7));
    define('SCRAPE_LIST_EXPIRE', round($bb_cfg['scrape_interval'] * 0.7));

    define('PEER_HASH_PREFIX', 'peer_');
    define('PEERS_LIST_PREFIX', 'peers_list_');
    define('SCRAPE_LIST_PREFIX', 'scrape_list_');

    // Init tracker
    require_once BB_PATH . '/bt/includes/init_tr.php';

    header('Content-Type: text/plain');
    header('Pragma: no-cache');

    if (!defined('IN_ADMIN')) {
        // Exit if tracker is disabled via ON/OFF trigger
        if (is_file(BB_DISABLED)) {
            dummy_exit(random_int(60, 2400));
        }
    }
}
