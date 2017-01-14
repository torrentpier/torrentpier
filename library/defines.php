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
define('BB_PATH', realpath(BB_ROOT));
define('ADMIN_DIR', BB_PATH . '/admin/');
define('DATA_DIR', BB_PATH . '/data/');
define('INT_DATA_DIR', BB_PATH . '/internal_data/');
define('AJAX_HTML_DIR', BB_ROOT . '/internal_data/ajax_html/');
define('CACHE_DIR', BB_PATH . '/internal_data/cache/');
define('LOG_DIR', BB_PATH . '/internal_data/log/');
define('SITEMAP_DIR', BB_PATH . '/internal_data/sitemap/');
define('TRIGGERS_DIR', BB_PATH . '/internal_data/triggers/');
define('AJAX_DIR', BB_ROOT . '/library/ajax/');
define('CFG_DIR', BB_PATH . '/library/config/');
define('INC_DIR', BB_PATH . '/library/includes/');
define('CLASS_DIR', BB_PATH . '/library/includes/classes/');
define('CORE_DIR', BB_PATH . '/library/includes/core/');
define('UCP_DIR', BB_PATH . '/library/includes/ucp/');
define('LANG_ROOT_DIR', BB_PATH . '/library/language/');
define('IMAGES_DIR', BB_PATH . '/styles/images/');
define('TEMPLATES_DIR', BB_PATH . '/styles/templates/');

// Templates
define('ADMIN_TPL_DIR', TEMPLATES_DIR . '/admin/');

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
ini_set('error_log', LOG_DIR . 'php_err.log');

// Triggers
define('BB_ENABLED', TRIGGERS_DIR . '$on');
define('BB_DISABLED', TRIGGERS_DIR . '$off');
define('CRON_ALLOWED', TRIGGERS_DIR . 'cron_allowed');
define('CRON_RUNNING', TRIGGERS_DIR . 'cron_running');

// Misc
define('MEM_USAGE', function_exists('memory_get_usage'));

// Gzip
define('GZIP_OUTPUT_ALLOWED', (extension_loaded('zlib') && !ini_get('zlib.output_compression')));

// TODO: temporary, move to another place
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
