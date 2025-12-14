<?php

/**
 * TorrentPier – Bull-powered BitTorrent tracker engine
 *
 * @copyright Copyright (c) 2005-2025 TorrentPier (https://torrentpier.com)
 * @link      https://github.com/torrentpier/torrentpier for the canonical source repository
 * @license   https://github.com/torrentpier/torrentpier/blob/master/LICENSE MIT License
 */

use Illuminate\Contracts\Container\BindingResolutionException;
use Illuminate\Support\Str;

if (isset($_REQUEST['GLOBALS'])) {
    die();
}

define('TIMESTART', utime());
define('TIMENOW', time());
define('BB_PATH', dirname(__DIR__));

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
if (!defined('BB_SCRIPT') && !defined('FRONT_CONTROLLER')) {
    define('BB_SCRIPT', '');
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
    die('🔩 Install: <a href="https://getcomposer.org/download/" target="_blank" rel="noreferrer" style="color:#0a25bb;">Install Composer</a> and run <code style="background:#222;color:#00e01f;padding:2px 6px;border-radius:3px;">composer install</code>, then <code style="background:#222;color:#00e01f;padding:2px 6px;border-radius:3px;">php bull app:install</code>');
}
require_once BB_PATH . '/vendor/autoload.php';

// Load ENV
try {
    $dotenv = Dotenv\Dotenv::createMutable(BB_PATH);
    $dotenv->load();
} catch (Dotenv\Exception\InvalidPathException $pathException) {
    die('🔩 Setup required: Run <code style="background:#222;color:#00e01f;padding:2px 6px;border-radius:3px;">php bull app:install</code> to configure TorrentPier');
}

// Load config
require_once BB_PATH . '/config/config.php';

// Local config
if (is_file(BB_PATH . '/config/config.local.php')) {
    require_once BB_PATH . '/config/config.local.php';
}

/**
 * Get the Application container instance
 *
 * @param string|null $abstract Service to resolve from the container
 * @param array $parameters Parameters for the service resolution
 * @throws BindingResolutionException
 * @return mixed Application instance or resolved service
 */
function app(?string $abstract = null, array $parameters = []): mixed
{
    /** @var TorrentPier\Application|null $app */
    static $app = null;

    if ($app === null) {
        // Create the application
        $app = require BB_PATH . '/bootstrap/app.php';

        // TODO: Migrate to proper Laravel-style Bootstrappers (app/Bootstrap/LoadConfiguration.php)
        // For now, pass raw config data through container instance binding
        /** @noinspection PhpUndefinedVariableInspection */
        global $bb_cfg;
        $app->instance('config.data', $bb_cfg);
    }

    if ($abstract === null) {
        return $app;
    }

    return $app->make($abstract, $parameters);
}

/**
 * Get the Config instance
 * @throws BindingResolutionException
 */
function config(): TorrentPier\Config
{
    return app(TorrentPier\Config::class);
}

/**
 * Get the HTTP Client instance
 * @throws BindingResolutionException
 */
function httpClient(): TorrentPier\Http\HttpClient
{
    return app(TorrentPier\Http\HttpClient::class);
}

/**
 * Get the HTTP Request instance
 *
 * Provides typed access to request parameters ($_GET, $_POST, $_COOKIE, $_SERVER, $_FILES)
 *
 * Usage:
 *   // Typed getters (POST priority over GET)
 *   request()->get('key')                   // mixed, from POST or GET
 *   request()->has('key')                   // bool, check if parameter exists
 *   request()->all()                        // array, all merged parameters
 *   request()->getInt('topic_id')           // int
 *   request()->getString('mode')            // string
 *   request()->getBool('flag')              // bool
 *   request()->getFloat('ratio')            // float
 *   request()->getArray('ids')              // array
 *
 *   // Specific source via properties (Symfony InputBag)
 *   request()->query->get('f')              // GET parameter
 *   request()->post->get('message')         // POST parameter
 *   request()->cookies->get('session_id')   // Cookie
 *   request()->server->get('REQUEST_URI')   // Server var
 *   request()->headers->get('User-Agent')   // Header
 *   request()->files->get('upload')         // Uploaded file
 *
 *   // Request metadata
 *   request()->getMethod()                  // HTTP method (GET, POST, etc.)
 *   request()->isPost()                     // Check if POST
 *   request()->isGet()                      // Check if GET
 *   request()->isAjax()                     // Check if AJAX/XHR
 *   request()->isSecure()                   // Check if HTTPS
 *   request()->getClientIp()                // Client IP address
 *   request()->getRequestUri()              // URI with query string
 *   request()->getPathInfo()                // Path without query string
 *   request()->getQueryString()             // Query string only
 *   request()->getHost()                    // Host name
 *   request()->getScheme()                  // http or https
 *   request()->getUserAgent()               // User-Agent header
 *   request()->getReferer()                 // Referer header
 *   request()->getContentType()             // Content-Type
 *   request()->getContent()                 // Raw request body
 *   request()->getSymfonyRequest()          // Underlying Symfony Request
 * @throws BindingResolutionException
 */
function request(): TorrentPier\Http\Request
{
    return app(TorrentPier\Http\Request::class);
}

/**
 * Format time difference in human-readable format
 *
 * @see \TorrentPier\Helpers\TimeHelper::humanTime()
 */
function humanTime(int|string $timestamp, int|string|null $reference = null): string
{
    return TorrentPier\Helpers\TimeHelper::humanTime($timestamp, $reference);
}

/**
 * Get the Censor instance
 * @throws BindingResolutionException
 */
function censor(): TorrentPier\Censor
{
    return app(TorrentPier\Censor::class);
}

/**
 * Whoops error handler singleton
 * @throws BindingResolutionException
 */
function whoops(): TorrentPier\Whoops\WhoopsManager
{
    return app(TorrentPier\Whoops\WhoopsManager::class);
}

/**
 * Tracy debug bar singleton
 * @throws BindingResolutionException
 */
function tracy(): TorrentPier\Tracy\TracyBarManager
{
    return app(TorrentPier\Tracy\TracyBarManager::class);
}

/**
 * Get the Language instance
 * @throws BindingResolutionException
 */
function lang(): TorrentPier\Language
{
    return app(TorrentPier\Language::class);
}

/**
 * Get a language string (shorthand for lang()->get())
 *
 * @param string $key Language key, supports dot notation (e.g., 'DATETIME.TODAY')
 * @param mixed $default Default value if key doesn't exist
 * @throws BindingResolutionException
 * @return mixed Language string or default value
 */
function __(string $key, mixed $default = null): mixed
{
    return app(TorrentPier\Language::class)->get($key, $default);
}

/**
 * Echo a language string (shorthand for echo __())
 *
 * @param string $key Language key, supports dot notation
 * @param mixed $default Default value if key doesn't exist
 * @throws BindingResolutionException
 */
function _e(string $key, mixed $default = null): void
{
    echo app(TorrentPier\Language::class)->get($key, $default);
}

/**
 * Get the Template instance
 *
 * When $root is provided, creates a new Template and registers it in the container.
 * When $root is null, returns the previously registered instance.
 *
 * @param string|null $root Template root directory (pass on first call to initialize)
 * @throws RuntimeException If called without $root before initialization
 * @throws BindingResolutionException
 */
function template(?string $root = null): TorrentPier\Template\Template
{
    if ($root !== null) {
        $template = new TorrentPier\Template\Template($root);
        app()->instance(TorrentPier\Template\Template::class, $template);

        return $template;
    }

    return app(TorrentPier\Template\Template::class);
}

/**
 * Get theme images array
 *
 * @param string|null $key Specific image key, or null for all images
 * @throws BindingResolutionException
 * @return mixed Image path, all images array, or empty string if key not found
 */
function theme_images(?string $key = null): mixed
{
    $twig = template()->getTwig();
    if (!$twig) {
        return $key === null ? [] : '';
    }

    $themeVars = $twig->getGlobals();
    $images = $themeVars['images'] ?? [];

    if ($key === null) {
        return $images;
    }

    return $images[$key] ?? '';
}

/**
 * Initialize debug
 */
define('APP_ENV', env('APP_ENV', 'production'));
if (APP_ENV === 'development') {
    define('DBG_USER', true); // forced debug
} else {
    define('DBG_USER', isset($_COOKIE[COOKIE_DBG]));
}
whoops()->init();
tracy()->init();

/**
 * Server variables initialize
 */
$server_protocol = config()->get('cookie_secure') ? 'https://' : 'http://';
$server_port = in_array((int)config()->get('server_port'), [80, 443], true) ? '' : ':' . config()->get('server_port');
define('FORUM_PATH', config()->get('script_path'));
define('FULL_URL', $server_protocol . config()->get('server_name') . $server_port . config()->get('script_path'));
unset($server_protocol, $server_port);

/**
 * Get the Database instance
 * @throws BindingResolutionException
 */
function DB(): TorrentPier\Database\Database
{
    return app(TorrentPier\Database\Database::class);
}

/**
 * Get cache manager instance (replaces legacy cache system)
 * @throws BindingResolutionException
 */
function CACHE(string $cache_name): TorrentPier\Cache\CacheManager
{
    return app(TorrentPier\Cache\UnifiedCacheSystem::class)->get_cache_obj($cache_name);
}

/**
 * Get datastore manager instance (replaces legacy datastore system)
 * @throws BindingResolutionException
 */
function datastore(): TorrentPier\Cache\DatastoreManager
{
    return app(TorrentPier\Cache\DatastoreManager::class);
}

/**
 * User singleton helper
 * @throws BindingResolutionException
 */
function user(): TorrentPier\Legacy\Common\User
{
    return app(TorrentPier\Legacy\Common\User::class);
}

/**
 * Userdata helper - returns user data array
 *
 * @param string|null $key Optional key to get specific value
 */
function userdata(?string $key = null): mixed
{
    $data = user()->data;

    return $key === null ? $data : ($data[$key] ?? null);
}

/**
 * LogAction singleton helper
 * @throws BindingResolutionException
 */
function log_action(): TorrentPier\Legacy\LogAction
{
    return app(TorrentPier\Legacy\LogAction::class);
}

/**
 * Html helper singleton
 * @throws BindingResolutionException
 */
function html(): TorrentPier\Legacy\Common\Html
{
    return app(TorrentPier\Legacy\Common\Html::class);
}

/**
 * Simple header flag getter/setter
 *
 * @param bool|null $set Set value (true/false) or null to just get current value
 * @return bool Current value
 */
function simple_header(?bool $set = null): bool
{
    static $value = false;
    if ($set !== null) {
        $value = $set;
    }

    return $value;
}

/**
 * BBCode parser singleton
 * @throws BindingResolutionException
 */
function bbcode(): TorrentPier\Legacy\BBCode
{
    return app(TorrentPier\Legacy\BBCode::class);
}

/**
 * Ajax handler singleton
 * @throws BindingResolutionException
 */
function ajax(): TorrentPier\Ajax
{
    return app(TorrentPier\Ajax::class);
}

/**
 * Manticore search singleton
 * @throws BindingResolutionException
 */
function manticore(): ?TorrentPier\ManticoreSearch
{
    return app(TorrentPier\ManticoreSearch::class);
}

/**
 * Bitfields helper - returns bitfield definitions
 *
 * @param string|null $type Optional type ('forum_perm' or 'user_opt')
 */
function bitfields(?string $type = null): array
{
    static $bf = null;
    if ($bf === null) {
        $bf = [
            'forum_perm' => [
                'auth_view' => AUTH_VIEW,
                'auth_read' => AUTH_READ,
                'auth_mod' => AUTH_MOD,
                'auth_post' => AUTH_POST,
                'auth_reply' => AUTH_REPLY,
                'auth_edit' => AUTH_EDIT,
                'auth_delete' => AUTH_DELETE,
                'auth_sticky' => AUTH_STICKY,
                'auth_announce' => AUTH_ANNOUNCE,
                'auth_vote' => AUTH_VOTE,
                'auth_pollcreate' => AUTH_POLLCREATE,
                'auth_attachments' => AUTH_ATTACH,
                'auth_download' => AUTH_DOWNLOAD,
            ],
            'user_opt' => [
                'user_viewemail' => 0,
                'dis_sig' => 1,
                'dis_avatar' => 2,
                'dis_pm' => 3,
                'user_viewonline' => 4,
                'user_notify' => 5,
                'user_notify_pm' => 6,
                'dis_passkey' => 7,
                'user_porn_forums' => 8,
                'user_callseed' => 9,
                'user_empty' => 10,
                'dis_topic' => 11,
                'dis_post' => 12,
                'dis_post_edit' => 13,
                'user_dls' => 14,
                'user_retracker' => 15,
                'user_hide_torrent_client' => 16,
                'user_hide_peer_country' => 17,
                'user_hide_peer_username' => 18,
            ],
        ];
    }

    return $type === null ? $bf : ($bf[$type] ?? []);
}

/**
 * Read tracker singleton - tracks read status of topics and forums
 * @throws BindingResolutionException
 */
function read_tracker(): TorrentPier\ReadTracker
{
    return app(TorrentPier\ReadTracker::class);
}

/**
 * Get topic tracking data
 *
 * @return array Reference to a tracking array
 */
function &tracking_topics(): array
{
    return read_tracker()->getTopics();
}

/**
 * Get forum tracking data
 *
 * @return array Reference to a tracking array
 */
function &tracking_forums(): array
{
    return read_tracker()->getForums();
}

/**
 * Get forum tree data (categories and forums hierarchy)
 *
 * @param bool $refresh Refresh cached data before returning
 * @throws BindingResolutionException
 * @return array Forum tree data
 */
function forum_tree(bool $refresh = false): array
{
    $instance = app(TorrentPier\Forum\ForumTree::class);
    if ($refresh) {
        $instance->refresh();
    }

    return $instance->get();
}

/**
 * Page configuration getter/setter (replaces global $page_cfg)
 *
 * Usage:
 *   page_cfg('key', $value)  - set a value
 *   page_cfg('key')          - get a value
 *   page_cfg()               - get all config
 *
 * @param string|null $key Config key
 * @param mixed $value Value to set (null to get)
 * @return mixed Config value or all config array
 */
function page_cfg(?string $key = null, mixed $value = null): mixed
{
    static $config = [];

    if ($key === null) {
        return $config;
    }

    if (func_num_args() === 2) {
        $config[$key] = $value;

        return $value;
    }

    return $config[$key] ?? null;
}

/**
 * Get CSS class for download link by status
 *
 * @param int $status Download status constant (DL_STATUS_*)
 * @return string CSS class name
 */
function dl_link_css(int $status): string
{
    static $map = [
        DL_STATUS_RELEASER => 'genmed',
        DL_STATUS_WILL => 'dlWill',
        DL_STATUS_DOWN => 'leechmed',
        DL_STATUS_COMPLETE => 'seedmed',
        DL_STATUS_CANCEL => 'dlCancel',
    ];

    return $map[$status] ?? 'genmed';
}

/**
 * Get CSS class for download status display
 *
 * @param int $status Download status constant (DL_STATUS_*)
 * @return string CSS class name
 */
function dl_status_css(int $status): string
{
    static $map = [
        DL_STATUS_RELEASER => 'genmed',
        DL_STATUS_WILL => 'dlWill',
        DL_STATUS_DOWN => 'dlDown',
        DL_STATUS_COMPLETE => 'dlComplete',
        DL_STATUS_CANCEL => 'dlCancel',
    ];

    return $map[$status] ?? 'gen';
}

/**
 * Get list of all download status constants
 *
 * @return array List of DL_STATUS_* constants
 */
function dl_status_list(): array
{
    return [
        DL_STATUS_RELEASER,
        DL_STATUS_WILL,
        DL_STATUS_DOWN,
        DL_STATUS_COMPLETE,
        DL_STATUS_CANCEL,
    ];
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
    return is_string($id) && preg_match('#^[a-zA-Z0-9]{' . $length . '}$#', $id);
}

function clean_filename($fname)
{
    static $s = ['\\', '/', ':', '*', '?', '"', '<', '>', '|', ' '];

    return str_replace($s, '_', Str::squish($fname));
}

/**
 * Convert special characters to HTML entities
 */
function htmlCHR($txt, bool $double_encode = false, int $quote_style = ENT_QUOTES, ?string $charset = DEFAULT_CHARSET): string
{
    return (string)htmlspecialchars($txt ?? '', $quote_style, $charset, $double_encode);
}


/**
 * Calculates user ratio
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
                'recs' => $recursions,
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
 * Array deep merge
 */
function array_deep_merge(array $base, array $overlay): array
{
    foreach ($overlay as $key => $value) {
        if (is_array($value) && isset($base[$key]) && is_array($base[$key])) {
            $base[$key] = array_deep_merge($base[$key], $value);
        } else {
            $base[$key] = $value;
        }
    }

    return $base;
}

/**
 * Hide BB_PATH
 */
function hide_bb_path(string $path): string
{
    return ltrim(str_replace(BB_PATH, '', $path), '/\\');
}

/**
 * Returns memory usage statistic
 *
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
            throw new RuntimeException(__FUNCTION__ . ": invalid param '{$param}'");
    }
}

/**
 * Some shared definitions
 */
// Initialize demo mode
define('IN_DEMO_MODE', env('APP_DEMO_MODE', false));
// Initialize debug mode
define('IN_DEBUG_MODE', env('APP_DEBUG_MODE', false));

// Ratio status
define('RATIO_ENABLED', TR_RATING_LIMITS && MIN_DL_FOR_RATIO > 0);

// Initialization
if (!defined('IN_TRACKER')) {
    // Init board
    require_once INC_DIR . '/init_bb.php';
} else {
    define('DUMMY_PEER', pack('Nn', TorrentPier\Helpers\IPHelper::ip2long(request()->server->get('REMOTE_ADDR')), request()->query->getInt('port') ?: random_int(1000, 65000)));

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
        // Exit if the tracker is disabled via ON/OFF trigger
        if (is_file(BB_DISABLED)) {
            dummy_exit(random_int(60, 2400));
        }
    }
}
