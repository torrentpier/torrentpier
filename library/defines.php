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

if (!defined('BB_ROOT')) {
    die(basename(__FILE__));
}

// Ratio limits
define('TR_RATING_LIMITS', true);        // ON/OFF
define('MIN_DL_FOR_RATIO', 10737418240); // 10 GB in bytes, 0 - disable

// Path (trailing slash '/' at the end: XX_PATH - without, XX_DIR - with)
define('BB_PATH', dirname(__DIR__));
define('ADMIN_DIR', BB_PATH . '/admin');
define('DATA_DIR', BB_PATH . '/data');
define('INT_DATA_DIR', BB_PATH . '/internal_data');
define('AJAX_HTML_DIR', BB_PATH . '/internal_data/ajax_html');
define('CACHE_DIR', BB_PATH . '/internal_data/cache');
define('LOG_DIR', BB_PATH . '/internal_data/log');
define('TRIGGERS_DIR', BB_PATH . '/internal_data/triggers');
define('AJAX_DIR', BB_PATH . '/library/ajax');
define('ATTACH_DIR', BB_PATH . '/library/attach_mod');
define('CFG_DIR', BB_PATH . '/library/config');
define('INC_DIR', BB_PATH . '/library/includes');
define('UCP_DIR', BB_PATH . '/library/includes/ucp');
define('LANG_ROOT_DIR', BB_PATH . '/library/language');
define('SITEMAP_DIR', BB_PATH . '/sitemap');
define('IMAGES_DIR', BB_PATH . '/styles/images');
define('TEMPLATES_DIR', BB_PATH . '/styles/templates');

// Templates
define('ADMIN_TPL_DIR', TEMPLATES_DIR . '/admin/');
define('XS_USE_ISSET', '1');
define('XS_TPL_PREFIX', 'tpl_');
define('XS_TAG_NONE', 0);
define('XS_TAG_BEGIN', 2);
define('XS_TAG_END', 3);
define('XS_TAG_INCLUDE', 4);
define('XS_TAG_IF', 5);
define('XS_TAG_ELSE', 6);
define('XS_TAG_ELSEIF', 7);
define('XS_TAG_ENDIF', 8);
define('XS_TAG_BEGINELSE', 11);

// Debug
define('DBG_LOG', false);    // enable forum debug (off on production)
define('DBG_TRACKER', false);    // enable tracker debug (off on production)
define('COOKIE_DBG', 'bb_dbg'); // debug cookie name
define('SQL_DEBUG', true);     // enable forum sql & cache debug
define('SQL_LOG_ERRORS', true);     // all SQL_xxx options enabled only if SQL_DEBUG == TRUE
define('SQL_CALC_QUERY_TIME', true);     // for stats
define('SQL_LOG_SLOW_QUERIES', true);     // log sql slow queries
define('SQL_SLOW_QUERY_TIME', 10);       // slow query in seconds
define('SQL_PREPEND_SRC_COMM', false);    // prepend source file comment to sql query

// Log options
define('LOG_EXT', 'log');
define('LOG_SEPR', ' | ');
define('LOG_LF', "\n");
define('LOG_MAX_SIZE', 1048576); // bytes

// Error reporting
ini_set('error_reporting', E_ALL);
ini_set('display_errors', 0);
ini_set('log_errors', 1);
ini_set('error_log', LOG_DIR . '/php_err.log');

// Triggers
define('BB_ENABLED', TRIGGERS_DIR . '/$on');
define('BB_DISABLED', TRIGGERS_DIR . '/$off');
define('CRON_ALLOWED', TRIGGERS_DIR . '/cron_allowed');
define('CRON_RUNNING', TRIGGERS_DIR . '/cron_running');

// Misc
define('MEM_USAGE', function_exists('memory_get_usage'));

// Gzip
define('GZIP_OUTPUT_ALLOWED', (extension_loaded('zlib') && !ini_get('zlib.output_compression')));
define('UA_GZIP_SUPPORTED', isset($_SERVER['HTTP_ACCEPT_ENCODING']) && strpos($_SERVER['HTTP_ACCEPT_ENCODING'], 'gzip') !== false);

// Torrents (reserved: -1)
define('TOR_NOT_APPROVED', 0);   // не проверено
define('TOR_CLOSED', 1);   // закрыто
define('TOR_APPROVED', 2);   // проверено
define('TOR_NEED_EDIT', 3);   // недооформлено
define('TOR_NO_DESC', 4);   // неоформлено
define('TOR_DUP', 5);   // повтор
define('TOR_CLOSED_CPHOLD', 6);   // закрыто правообладателем
define('TOR_CONSUMED', 7);   // поглощено
define('TOR_DOUBTFUL', 8);   // сомнительно
define('TOR_CHECKING', 9);   // проверяется
define('TOR_TMP', 10);  // временная
define('TOR_PREMOD', 11);  // премодерация

// Debug options
define('DBG_USER', (isset($_COOKIE[COOKIE_DBG])));

// Board / tracker shared constants and functions
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

// User Levels
define('DELETED', -1);
define('USER', 0);
define('ADMIN', 1);
define('MOD', 2);
define('GROUP_MEMBER', 20);
define('CP_HOLDER', 25);
define('EXCLUDED_USERS', implode(',', [GUEST_UID, BOT_UID]));

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

define('USERNAME_MIN_LENGTH', 3);

// URL PARAMETERS (hardcoding allowed)
define('POST_CAT_URL', 'c');
define('POST_FORUM_URL', 'f');
define('POST_GROUPS_URL', 'g');
define('POST_POST_URL', 'p');
define('POST_TOPIC_URL', 't');
define('POST_USERS_URL', 'u');

// Download Modes
define('INLINE_LINK', 1);
define('PHYSICAL_LINK', 2);

// Categories
define('NONE_CAT', 0);
define('IMAGE_CAT', 1);

// Misc
define('ADMIN_MAX_ATTACHMENTS', 50);
define('THUMB_DIR', 'thumbs');
define('MODE_THUMBNAIL', 1);

// Quota Types
define('QUOTA_UPLOAD_LIMIT', 1);
define('QUOTA_PM_LIMIT', 2);

// Torrents
define('TOR_STATUS_NORMAL', 0);
define('TOR_STATUS_FROZEN', 1);

// Gender
define('MALE', 1);
define('FEMALE', 2);
define('NOGENDER', 0);

// Poll
# 1 - обычный опрос
define('POLL_FINISHED', 2);

// Group avatars
define('GROUP_AVATAR_MASK', 999000);

// Table names
define('BUF_TOPIC_VIEW', 'buf_topic_view');
define('BUF_LAST_SEEDER', 'buf_last_seeder');
define('BB_ATTACH_CONFIG', 'bb_attachments_config');
define('BB_ATTACHMENTS_DESC', 'bb_attachments_desc');
define('BB_ATTACHMENTS', 'bb_attachments');
define('BB_AUTH_ACCESS_SNAP', 'bb_auth_access_snap');
define('BB_AUTH_ACCESS', 'bb_auth_access');
define('BB_BANLIST', 'bb_banlist');
define('BB_BT_DLSTATUS', 'bb_bt_dlstatus');
define('BB_BT_DLSTATUS_SNAP', 'bb_bt_dlstatus_snap');
define('BB_BT_LAST_TORSTAT', 'bb_bt_last_torstat');
define('BB_BT_LAST_USERSTAT', 'bb_bt_last_userstat');
define('BB_BT_TORHELP', 'bb_bt_torhelp');
define('BB_BT_TORSTAT', 'bb_bt_torstat');
define('BB_BT_TORRENTS', 'bb_bt_torrents');
define('BB_BT_TRACKER', 'bb_bt_tracker');
define('BB_BT_TRACKER_SNAP', 'bb_bt_tracker_snap');
define('BB_BT_USERS', 'bb_bt_users');
define('BB_CATEGORIES', 'bb_categories');
define('BB_CONFIG', 'bb_config');
define('BB_CRON', 'bb_cron');
define('BB_DISALLOW', 'bb_disallow');
define('BB_EXTENSION_GROUPS', 'bb_extension_groups');
define('BB_EXTENSIONS', 'bb_extensions');
define('BB_FORUMS', 'bb_forums');
define('BB_GROUPS', 'bb_groups');
define('BB_LOG', 'bb_log');
define('BB_POLL_USERS', 'bb_poll_users');
define('BB_POLL_VOTES', 'bb_poll_votes');
define('BB_POSTS_SEARCH', 'bb_posts_search');
define('BB_POSTS', 'bb_posts');
define('BB_POSTS_TEXT', 'bb_posts_text');
define('BB_POSTS_HTML', 'bb_posts_html');
define('BB_PRIVMSGS', 'bb_privmsgs');
define('BB_PRIVMSGS_TEXT', 'bb_privmsgs_text');
define('BB_QUOTA_LIMITS', 'bb_quota_limits');
define('BB_QUOTA', 'bb_attach_quota');
define('BB_RANKS', 'bb_ranks');
define('BB_SEARCH_REBUILD', 'bb_search_rebuild');
define('BB_SEARCH', 'bb_search_results');
define('BB_SESSIONS', 'bb_sessions');
define('BB_SMILIES', 'bb_smilies');
define('BB_TOPIC_TPL', 'bb_topic_tpl');
define('BB_TOPICS', 'bb_topics');
define('BB_TOPICS_WATCH', 'bb_topics_watch');
define('BB_USER_GROUP', 'bb_user_group');
define('BB_USERS', 'bb_users');
define('BB_WORDS', 'bb_words');

define('TORRENT_EXT', 'torrent');

define('TOPIC_DL_TYPE_NORMAL', 0);
define('TOPIC_DL_TYPE_DL', 1);

define('SHOW_PEERS_COUNT', 1);
define('SHOW_PEERS_NAMES', 2);
define('SHOW_PEERS_FULL', 3);

define('SEARCH_ID_LENGTH', 12);
define('SID_LENGTH', 20);
define('LOGIN_KEY_LENGTH', 12);
define('USERNAME_MAX_LENGTH', 25);
define('USEREMAIL_MAX_LENGTH', 40);

define('PAGE_HEADER', INC_DIR . '/page_header.php');
define('PAGE_FOOTER', INC_DIR . '/page_footer.php');

define('CAT_URL', 'index.php?c=');
define('DOWNLOAD_URL', 'dl.php?id=');
define('FORUM_URL', 'viewforum.php?f=');
define('GROUP_URL', 'group.php?g=');
define('LOGIN_URL', $bb_cfg['login_url']);
define('MODCP_URL', 'modcp.php?f=');
define('PM_URL', $bb_cfg['pm_url']);
define('POST_URL', 'viewtopic.php?p=');
define('POSTING_URL', $bb_cfg['posting_url']);
define('PROFILE_URL', 'profile.php?mode=viewprofile&amp;u=');
define('BONUS_URL', 'profile.php?mode=bonus');
define('TOPIC_URL', 'viewtopic.php?t=');

define('USER_AGENT', strtolower($_SERVER['HTTP_USER_AGENT']));

define('HTML_SELECT_MAX_LENGTH', 60);
define('HTML_WBR_LENGTH', 12);
define('HTML_CHECKED', ' checked="checked" ');
define('HTML_DISABLED', ' disabled="disabled" ');
define('HTML_READONLY', ' readonly="readonly" ');
define('HTML_SELECTED', ' selected="selected" ');
define('HTML_SF_SPACER', '&nbsp;|-&nbsp;');

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

// Cookie params
$c = $bb_cfg['cookie_prefix'];
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

$dl_link_css = [
    DL_STATUS_RELEASER => 'genmed',
    DL_STATUS_WILL => 'dlWill',
    DL_STATUS_DOWN => 'leechmed',
    DL_STATUS_COMPLETE => 'seedmed',
    DL_STATUS_CANCEL => 'dlCancel',
];

$dl_status_css = [
    DL_STATUS_RELEASER => 'genmed',
    DL_STATUS_WILL => 'dlWill',
    DL_STATUS_DOWN => 'dlDown',
    DL_STATUS_COMPLETE => 'dlComplete',
    DL_STATUS_CANCEL => 'dlCancel',
];

// Obtain and encode user IP
$client_ip = filter_var($_SERVER['REMOTE_ADDR'], FILTER_VALIDATE_IP) ? $_SERVER['REMOTE_ADDR'] : '127.0.0.1';
$user_ip = encode_ip($client_ip);
define('CLIENT_IP', $client_ip);
define('USER_IP', $user_ip);

define('CRON_LOG_ENABLED', true);  // global ON/OFF
define('CRON_FORCE_LOG', false); // always log regardless of job settings
define('CRON_DIR', INC_DIR . '/cron/');
define('CRON_JOB_DIR', CRON_DIR . 'jobs/');
define('CRON_LOG_DIR', 'cron'); // inside LOG_DIR
define('CRON_LOG_FILE', 'cron');  // without ext
