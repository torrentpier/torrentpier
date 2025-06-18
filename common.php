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
$allowedCDNs = ['HTTP_X_FORWARDED_FOR', 'HTTP_FASTLY_CLIENT_IP', 'HTTP_CF_CONNECTING_IP'];
foreach ($allowedCDNs as $allowedCDN) {
    if (isset($_SERVER[$allowedCDN]) && filter_var($_SERVER[$allowedCDN], FILTER_VALIDATE_IP, FILTER_FLAG_NO_PRIV_RANGE | FILTER_FLAG_NO_RES_RANGE)) {
        $_SERVER['REMOTE_ADDR'] = $_SERVER[$allowedCDN];
    }
}

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

/** @noinspection PhpUndefinedVariableInspection */
// Initialize Config singleton, bb_cfg from global file config
$config = \TorrentPier\Config::init($bb_cfg);

/**
 * Get the Config instance
 *
 * @return \TorrentPier\Config
 */
function config(): \TorrentPier\Config
{
    return \TorrentPier\Config::getInstance();
}

/**
 * Get the Censor instance
 *
 * @return \TorrentPier\Censor
 */
function censor(): \TorrentPier\Censor
{
    return \TorrentPier\Censor::getInstance();
}

/**
 * Get the Dev instance
 *
 * @return \TorrentPier\Dev
 */
function dev(): \TorrentPier\Dev
{
    return \TorrentPier\Dev::getInstance();
}

/**
 * Get the Language instance
 *
 * @return \TorrentPier\Language
 */
function lang(): \TorrentPier\Language
{
    return \TorrentPier\Language::getInstance();
}

/**
 * Get a language string (shorthand for lang()->get())
 *
 * @param string $key Language key, supports dot notation (e.g., 'DATETIME.TODAY')
 * @param mixed $default Default value if key doesn't exist
 * @return mixed Language string or default value
 */
function __(string $key, mixed $default = null): mixed
{
    return \TorrentPier\Language::getInstance()->get($key, $default);
}

/**
 * Echo a language string (shorthand for echo __())
 *
 * @param string $key Language key, supports dot notation
 * @param mixed $default Default value if key doesn't exist
 * @return void
 */
function _e(string $key, mixed $default = null): void
{
    echo \TorrentPier\Language::getInstance()->get($key, $default);
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
(\TorrentPier\Dev::init());

/**
 * Server variables initialize
 */
$server_protocol = config()->get('cookie_secure') ? 'https://' : 'http://';
$server_port = in_array((int)config()->get('server_port'), [80, 443], true) ? '' : ':' . config()->get('server_port');
define('FORUM_PATH', config()->get('script_path'));
define('FULL_URL', $server_protocol . config()->get('server_name') . $server_port . config()->get('script_path'));
unset($server_protocol, $server_port);

// Initialize the new DB factory with database configuration
TorrentPier\Database\DatabaseFactory::init(config()->get('db'), config()->get('db_alias', []));

/**
 * Get the Database instance
 *
 * @param string $db_alias
 * @return \TorrentPier\Database\Database
 */
function DB(string $db_alias = 'db'): \TorrentPier\Database\Database
{
    return TorrentPier\Database\DatabaseFactory::getInstance($db_alias);
}

// Initialize Unified Cache System
TorrentPier\Cache\UnifiedCacheSystem::getInstance(config()->all());

/**
 * Get cache manager instance (replaces legacy cache system)
 *
 * @param string $cache_name
 * @return \TorrentPier\Cache\CacheManager
 */
function CACHE(string $cache_name): \TorrentPier\Cache\CacheManager
{
    return TorrentPier\Cache\UnifiedCacheSystem::getInstance()->get_cache_obj($cache_name);
}

/**
 * Get datastore manager instance (replaces legacy datastore system)
 *
 * @return \TorrentPier\Cache\DatastoreManager
 */
function datastore(): \TorrentPier\Cache\DatastoreManager
{
    return TorrentPier\Cache\UnifiedCacheSystem::getInstance()->getDatastore(config()->get('datastore_type', 'file'));
}

/**
 * Backward compatibility: Global datastore variable
 * This allows existing code to continue using global $datastore
 */
$datastore = datastore();

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

    define('PEER_HASH_EXPIRE', round(config()->get('announce_interval') * (0.85 * config()->get('tracker.expire_factor'))));
    define('PEERS_LIST_EXPIRE', round(config()->get('announce_interval') * 0.7));
    define('SCRAPE_LIST_EXPIRE', round(config()->get('scrape_interval') * 0.7));

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
