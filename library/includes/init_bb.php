<?php

/**
 * TorrentPier – Bull-powered BitTorrent tracker engine
 *
 * @copyright Copyright (c) 2005-2025 TorrentPier (https://torrentpier.com)
 * @link      https://github.com/torrentpier/torrentpier for the canonical source repository
 * @license   https://github.com/torrentpier/torrentpier/blob/master/LICENSE MIT License
 */

// Obtain and encode user IP
$client_ip = request()->getClientIp() ?? '127.0.0.1';
$user_ip = TorrentPier\Helpers\IPHelper::ip2long($client_ip);
define('CLIENT_IP', $client_ip);
define('USER_IP', $user_ip);

// Cookie params
$c = config()->get('cookie_prefix');
define('COOKIE_DATA', $c . 'data');
define('COOKIE_FORUM', $c . 'f');
define('COOKIE_MARK', $c . 'mark_read');
define('COOKIE_TOPIC', $c . 't');
define('COOKIE_PM', $c . 'pm');
unset($c);

define('COOKIE_SESSION', 0);
define('COOKIE_EXPIRED', TIMENOW - 31536000);
define('COOKIE_PERSIST', TIMENOW + 31536000);

define('COOKIE_MAX_TRACKS', 90);

/**
 * Set cookie
 */
function bb_setcookie(string $name, mixed $val, ?int $lifetime = null, bool $httponly = false): bool
{
    $lifetime ??= COOKIE_PERSIST;

    return setcookie($name, $val, [
        'expires' => $lifetime,
        'path' => config()->get('script_path'),
        'domain' => config()->get('cookie_domain'),
        'secure' => config()->get('cookie_secure'),
        'httponly' => $httponly,
        'samesite' => config()->get('cookie_same_site'),
    ]);
}

// User related
define('USER_ACTIVATION_NONE', 0);
define('USER_ACTIVATION_SELF', 1);

// Group settings
define('GROUP_OPEN', 0);
define('GROUP_CLOSED', 1);
define('GROUP_HIDDEN', 2);

// Forum state
define('FORUM_UNLOCKED', 0);
define('FORUM_LOCKED', 1);

// Topic status
define('TOPIC_UNLOCKED', 0);
define('TOPIC_LOCKED', 1);
define('TOPIC_MOVED', 2);

define('TOPIC_WATCH_NOTIFIED', 1);
define('TOPIC_WATCH_UNNOTIFIED', 0);

// Topic types
define('POST_NORMAL', 0);
define('POST_STICKY', 1);
define('POST_ANNOUNCE', 2);

// Search types
define('SEARCH_TYPE_POST', 0);
define('SEARCH_TYPE_TRACKER', 1);

// Ajax error codes
define('E_AJAX_GENERAL_ERROR', 1000);
define('E_AJAX_NEED_LOGIN', 1001);

// Private messaging
define('PRIVMSGS_READ_MAIL', 0);
define('PRIVMSGS_NEW_MAIL', 1);
define('PRIVMSGS_SENT_MAIL', 2);
define('PRIVMSGS_SAVED_IN_MAIL', 3);
define('PRIVMSGS_SAVED_OUT_MAIL', 4);
define('PRIVMSGS_UNREAD_MAIL', 5);
define('HAVE_UNREAD_PM', 1);
define('HAVE_NEW_PM', 2);

// URL PARAMETERS (hardcoding allowed)
define('POST_CAT_URL', 'c');
define('POST_FORUM_URL', 'f');
define('POST_GROUPS_URL', 'g');
define('POST_POST_URL', 'p');
define('POST_TOPIC_URL', 't');
define('POST_USERS_URL', 'u');

// Torrents
define('TOR_STATUS_NORMAL', 0);
define('TOR_STATUS_FROZEN', 1);

// Gender
define('MALE', 1);
define('FEMALE', 2);
define('NOGENDER', 0);

// Poll
define('POLL_DELETED', 0);
define('POLL_STARTED', 1);
define('POLL_FINISHED', 2);

// Group avatars
define('GROUP_AVATAR_MASK', 999000);

// Table names
define('BUF_TOPIC_VIEW', 'buf_topic_view');
define('BUF_LAST_SEEDER', 'buf_last_seeder');
define('BB_AUTH_ACCESS_SNAP', 'bb_auth_access_snap');
define('BB_AUTH_ACCESS', 'bb_auth_access');
define('BB_BANLIST', 'bb_banlist');
define('BB_BT_DLSTATUS', 'bb_bt_dlstatus');
define('BB_BT_DLSTATUS_SNAP', 'bb_bt_dlstatus_snap');
define('BB_BT_LAST_TORSTAT', 'bb_bt_last_torstat');
define('BB_BT_LAST_USERSTAT', 'bb_bt_last_userstat');
define('BB_BT_TORHELP', 'bb_bt_torhelp');
define('BB_BT_TORSTAT', 'bb_bt_torstat');
define('BB_CATEGORIES', 'bb_categories');
define('BB_CONFIG', 'bb_config');
define('BB_CRON', 'bb_cron');
define('BB_DISALLOW', 'bb_disallow');
define('BB_FORUMS', 'bb_forums');
define('BB_GROUPS', 'bb_groups');
define('BB_LOG', 'bb_log');
define('BB_POLL_USERS', 'bb_poll_users');
define('BB_POLL_VOTES', 'bb_poll_votes');
define('BB_POSTS_SEARCH', 'bb_posts_search');
define('BB_POSTS', 'bb_posts');
define('BB_POSTS_TEXT', 'bb_post_texts');
define('BB_POSTS_HTML', 'bb_posts_html');
define('BB_PRIVMSGS', 'bb_privmsgs');
define('BB_PRIVMSGS_TEXT', 'bb_privmsgs_text');
define('BB_RANKS', 'bb_ranks');
define('BB_SEARCH_REBUILD', 'bb_search_rebuild');
define('BB_SEARCH', 'bb_search_results');
define('BB_SESSIONS', 'bb_sessions');
define('BB_SMILIES', 'bb_smilies');
define('BB_TOPIC_TPL', 'bb_topic_tpl');
define('BB_TOPICS', 'bb_topics');
define('BB_TOPICS_WATCH', 'bb_topics_watch');
define('BB_TORRENT_DL', 'bb_torrent_dl');
define('BB_USER_DL_DAY', 'bb_user_dl_day');
define('BB_USER_GROUP', 'bb_user_group');
define('BB_WORDS', 'bb_words');
define('BB_THX', 'bb_thx');

define('TORRENT_EXT', 'torrent');
define('TORRENT_EXT_ID', 8);
define('TORRENT_MIMETYPE', 'application/x-bittorrent');

define('M3U_EXT', 'm3u');
define('M3U_EXT_ID', 14);

define('TOPIC_DL_TYPE_NORMAL', 0);
define('TOPIC_DL_TYPE_DL', 1);

define('SHOW_PEERS_COUNT', 1);
define('SHOW_PEERS_NAMES', 2);
define('SHOW_PEERS_FULL', 3);

define('SEARCH_ID_LENGTH', 12);
define('ACTKEY_LENGTH', 32);
define('SID_LENGTH', 20);
define('LOGIN_KEY_LENGTH', 32);
define('USERNAME_MIN_LENGTH', 3);
define('USERNAME_MAX_LENGTH', 30);
define('USEREMAIL_MAX_LENGTH', 230);
define('PASSWORD_MIN_LENGTH', 8);
define('PASSWORD_MAX_LENGTH', 128);

define('PAGE_HEADER', INC_DIR . '/page_header.php');
define('PAGE_FOOTER', INC_DIR . '/page_footer.php');

// Server URL constants
$serverProtocol = config()->get('cookie_secure') ? 'https://' : 'http://';
$serverPort = in_array((int)config()->get('server_port'), [80, 443], true) ? '' : ':' . config()->get('server_port');
define('FORUM_PATH', config()->get('script_path'));
define('FULL_URL', $serverProtocol . config()->get('server_name') . $serverPort . config()->get('script_path'));

define('CAT_URL', FORUM_PATH . 'categories/');
define('DL_URL', FORUM_PATH . 'dl/');
define('FORUM_URL', FORUM_PATH . 'forums/');
define('LOGIN_URL', FORUM_PATH . 'login');
define('MODCP_URL', FORUM_PATH . 'modcp?' . POST_FORUM_URL . '=');
define('PM_URL', FORUM_PATH . 'privmsg');
define('POST_URL', FORUM_PATH . 'viewtopic?' . POST_POST_URL . '=');
define('POSTING_URL', FORUM_PATH . 'posting');
define('PROFILE_URL', FORUM_PATH . 'members/');
define('SETTINGS_URL', FORUM_PATH . 'settings/');
define('REGISTER_URL', FORUM_PATH . 'register/');
define('PASSWORD_RECOVERY_URL', FORUM_PATH . 'password-recovery/');
define('WATCHLIST_URL', FORUM_PATH . 'profile/watchlist/');
define('ACTIVATE_URL', FORUM_PATH . 'activate/');  // Usage: ACTIVATE_URL . $key . '/'
define('BONUS_URL', FORUM_PATH . 'profile/bonus/');
define('TOPIC_URL', FORUM_PATH . 'threads/');
define('FILELIST_URL', FORUM_PATH . 'dl/');  // Usage: FILELIST_URL . $id . '/files/'
define('PLAYBACK_M3U_URL', FORUM_PATH . 'playback/');

define('USER_AGENT', strtolower(request()->getUserAgent() ?? ''));

define('HTML_SELECT_MAX_LENGTH', 60);
define('HTML_SF_SPACER', '&nbsp;|-&nbsp;');

define('HTML_CHECKED', ' checked ');
define('HTML_DISABLED', ' disabled ');
define('HTML_READONLY', ' readonly ');
define('HTML_SELECTED', ' selected ');

define('EMAIL_TYPE_HTML', 'text/html');
define('EMAIL_TYPE_TEXT', 'text/plain');

// $GPC
define('KEY_NAME', 0);   // position in $GPC['xxx']
define('DEF_VAL', 1);
define('GPC_TYPE', 2);

define('GET', 1);
define('POST', 2);
define('COOKIE', 3);
define('REQUEST', 4);
define('CHBOX', 5);
define('SELECT', 6);

// Functions
function send_no_cache_headers()
{
    header('Expires: Mon, 26 Jul 1997 05:00:00 GMT');
    header('Last-Modified: ' . gmdate('D, d M Y H:i:s') . ' GMT');
    header('Cache-Control: no-store, no-cache, must-revalidate');
    header('Cache-Control: post-check=0, pre-check=0', false);
    header('Pragma: no-cache');
}

/**
 * @param string $output
 */
function bb_exit($output = '')
{
    if ($output) {
        echo $output;
    }
    exit;
}

/**
 * @param string $title
 * @param bool $print
 * @return string
 */
function prn_r($var, $title = '', $print = true)
{
    $r = '<pre>' . ($title ? "<b>{$title}</b>\n\n" : '') . htmlspecialchars(print_r($var, true)) . '</pre>';
    if ($print) {
        echo $r;
    }

    return $r;
}

/**
 * Converts "<br/>" / "<br>" tags to "\n" line breaks
 */
function br2nl(string $string): string
{
    return preg_replace('#<br\s*/?>#i', "\n", $string);
}

/**
 * Adds commas between every group of thousands
 */
function commify(?float $num, int $decimals = 0, ?string $decimal_separator = '.', ?string $thousands_separator = ','): string
{
    return number_format($num ?? 0.0, $decimals, $decimal_separator, $thousands_separator);
}

/**
 * Convert HTML entities to their corresponding characters
 */
function html_ent_decode(string $string, int $flags = ENT_QUOTES, string $encoding = DEFAULT_CHARSET): string
{
    return html_entity_decode($string, $flags, $encoding);
}

/**
 * Makes URL from path
 */
function make_url(string $path = ''): string
{
    // Remove leading slash only, preserve trailing slash for SEO-friendly URLs
    return FULL_URL . ltrim($path, '/');
}

/**
 * Get asset URL with cache busting
 *
 * @param string $path Asset path relative to assets/ (e.g., 'js/main.js', 'images/logo.png')
 * @param string|null $type Optional asset type for version lookup ('js' or 'css')
 * @return string Full URL to asset
 */
function asset_url(string $path, ?string $type = null): string
{
    // Determine a version based on asset type or extension
    if ($type === null) {
        $ext = pathinfo($path, PATHINFO_EXTENSION);
        $type = match ($ext) {
            'js' => 'js',
            'css' => 'css',
            default => null,
        };
    }

    $version = match ($type) {
        'js' => config()->get('js_ver', 1),
        'css' => config()->get('css_ver', 1),
        default => null,
    };

    $url = FULL_URL . 'assets/' . ltrim($path, '/');

    // Add a cache-busting version for JS/CSS
    if ($version !== null) {
        $url .= '?v=' . $version;
    }

    return $url;
}

/**
 * Get image URL from assets/images/
 *
 * @param string $path Image path relative to assets/images/ (e.g., 'logo/logo.png')
 * @return string Full URL to image
 */
function image_url(string $path): string
{
    return FULL_URL . 'assets/images/' . ltrim($path, '/');
}

/**
 * Get URL builder instance for generating SEO-friendly URLs
 *
 * Usage:
 *   url()->topic($id, $title)
 *   url()->forum($id, $name)
 *   url()->profile($id, $username)
 *   url()->register()
 *   url()->settings()
 *   url()->passwordRecovery()
 */
function url(): TorrentPier\Router\SemanticUrl\UrlBuilder
{
    return TorrentPier\Router\SemanticUrl\UrlBuilder::instance();
}

/**
 * Functions
 */
require_once INC_DIR . '/functions.php';
require_once INC_DIR . '/bbcode.php';

// Merge database configuration with base configuration using singleton
config()->merge(bb_get_config(BB_CONFIG));

/**
 * Cron
 * Run cron if enabled or forced via START_CRON constant
 */
TorrentPier\Helpers\CronHelper::run(defined('START_CRON'));

/**
 * Exit if the board is disabled via trigger
 */
$isApiRequest = str_starts_with($_SERVER['REQUEST_URI'] ?? '', '/api/');
if ((config()->get('board_disable') || files()->isFile(BB_DISABLED)) && !defined('IN_ADMIN') && !$isApiRequest && !defined('IN_LOGIN')) {
    if (config()->get('board_disable')) {
        // admin lock
        send_no_cache_headers();
        bb_die('BOARD_DISABLE', 503);
    } elseif (files()->isFile(BB_DISABLED)) {
        // trigger lock
        TorrentPier\Helpers\CronHelper::releaseDeadlock();
        send_no_cache_headers();
        bb_die('BOARD_DISABLE_CRON', (TorrentPier\Helpers\CronHelper::isEnabled() ? 503 : null));
    }
}
