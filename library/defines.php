<?php
/**
 * TorrentPier – Bull-powered BitTorrent tracker engine
 *
 * @copyright Copyright (c) 2005-2025 TorrentPier (https://torrentpier.com)
 * @link      https://github.com/torrentpier/torrentpier for the canonical source repository
 * @license   https://github.com/torrentpier/torrentpier/blob/master/LICENSE MIT License
 */

if (!defined('BB_ROOT')) {
    die(basename(__FILE__));
}

// Path (trailing slash '/' at the end: XX_PATH - without, XX_DIR - with)
define('ADMIN_DIR', BB_PATH . '/admin');
define('DATA_DIR', BB_PATH . '/data');
define('INT_DATA_DIR', BB_PATH . '/internal_data');
define('AJAX_HTML_DIR', BB_ROOT . '/internal_data/ajax_html/');
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

// System
define('APP_NAME', 'TorrentPier');
define('DEFAULT_CHARSET', 'UTF-8');
define('UPDATER_URL', 'https://api.github.com/repos/torrentpier/torrentpier/releases');
define('UPDATER_FILE', INT_DATA_DIR . '/updater.json');
define('COOKIE_DBG', 'bb_dbg');

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
define('SQL_DEBUG', true); // enable forum sql & cache debug
define('SQL_LOG_ERRORS', true); // all SQL_xxx options enabled only if SQL_DEBUG == TRUE
define('SQL_BB_LOG_NAME', 'sql_error_bb'); // mysql log filename (Board)
define('SQL_TR_LOG_NAME', 'sql_error_tr'); // mysql log filename (Tracker)
define('SQL_CALC_QUERY_TIME', true); // for stats
define('SQL_LOG_SLOW_QUERIES', true); // log sql slow queries
define('SQL_SLOW_QUERY_TIME', 10); // slow query in seconds
define('SQL_PREPEND_SRC', true); // prepend source file to sql query

// Log options
define('LOG_EXT', 'log'); // log file extension
define('LOG_SEPR', ' | ');
define('LOG_LF', "\n");
define('LOG_MAX_SIZE', 1048576); // bytes

// Error reporting
ini_set('error_reporting', E_ALL); // PHP error reporting mode | https://www.php.net/manual/en/errorfunc.constants.php
ini_set('display_errors', 1);
ini_set('display_startup_errors', 1);
define('MYSQLI_ERROR_REPORTING', MYSQLI_REPORT_ERROR); // MySQL error reporting mode | https://www.php.net/manual/mysqli-driver.report-mode.php
ini_set('log_errors', 1); // Enable logging (For native & Whoops)
ini_set('error_log', LOG_DIR . '/php_errors.log'); // path to log file enabled only if log_errors == 1 (native)
define('WHOOPS_LOG_FILE', LOG_DIR . '/php_whoops.log'); // log file enabled only if log_errors == 1 and APP_DEBUG == true (whoops)

// Triggers
define('BB_ENABLED', TRIGGERS_DIR . '/$on');
define('BB_DISABLED', TRIGGERS_DIR . '/$off');
define('CRON_ALLOWED', TRIGGERS_DIR . '/cron_allowed');
define('CRON_RUNNING', TRIGGERS_DIR . '/cron_running');

// Gzip
define('GZIP_OUTPUT_ALLOWED', extension_loaded('zlib') && !ini_get('zlib.output_compression'));
define('UA_GZIP_SUPPORTED', isset($_SERVER['HTTP_ACCEPT_ENCODING']) && str_contains($_SERVER['HTTP_ACCEPT_ENCODING'], 'gzip'));

// Migrations table
define('BB_MIGRATIONS', 'bb_migrations');

// Tracker shared constants
define('BB_BT_TORRENTS', 'bb_bt_torrents');
define('BB_BT_TRACKER', 'bb_bt_tracker');
define('BB_BT_TRACKER_SNAP', 'bb_bt_tracker_snap');
define('BB_BT_USERS', 'bb_bt_users');
define('BB_USERS', 'bb_users');
define('BT_AUTH_KEY_LENGTH', 20); // Passkey length

// Torrents (reserved: -1)
define('TOR_NOT_APPROVED', 0);
define('TOR_CLOSED', 1);
define('TOR_APPROVED', 2);
define('TOR_NEED_EDIT', 3);
define('TOR_NO_DESC', 4);
define('TOR_DUP', 5);
define('TOR_CLOSED_CPHOLD', 6);
define('TOR_CONSUMED', 7);
define('TOR_DOUBTFUL', 8);
define('TOR_CHECKING', 9);
define('TOR_TMP', 10);
define('TOR_PREMOD', 11);
define('TOR_REPLENISH', 12);

// Torrent types (Gold / Silver)
define('TOR_TYPE_DEFAULT', 0);
define('TOR_TYPE_GOLD', 1);
define('TOR_TYPE_SILVER', 2);

// DL-statuses
define('DL_STATUS_RELEASER', -1);
define('DL_STATUS_DOWN', 0);
define('DL_STATUS_COMPLETE', 1);
define('DL_STATUS_CANCEL', 3);
define('DL_STATUS_WILL', 4);

// Cron
define('CRON_LOG_ENABLED', true); // global ON/OFF
define('CRON_FORCE_LOG', false); // always log regardless of job settings
define('CRON_DIR', INC_DIR . '/cron/');
define('CRON_JOB_DIR', CRON_DIR . 'jobs/');
define('CRON_LOG_DIR', 'cron'); // inside LOG_DIR
define('CRON_LOG_FILE', 'cron'); // without ext

// Session variables
define('ONLY_NEW_POSTS', 1);
define('ONLY_NEW_TOPICS', 2);

// User UIDs
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
if (!defined('EXCLUDED_USERS_CSV')) {
    define('EXCLUDED_USERS_CSV', EXCLUDED_USERS);
}

// Ratio limits
define('TR_RATING_LIMITS', true);        // ON/OFF
define('MIN_DL_FOR_RATIO', 10737418240); // 10 GB in bytes, 0 - disable
