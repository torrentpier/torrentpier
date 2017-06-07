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

/**
 * Check PHP version
 */
if (version_compare(PHP_VERSION, '5.5', '<')) {
    die('TorrentPier requires PHP version 5.5+. Your PHP version ' . PHP_VERSION);
}

/**
 * Define some basic configuration arrays
 */
unset($stopwords, $synonyms_match, $synonyms_replace);
$userdata = $theme = $images = $lang = $nav_links = $bf = $attach_config = [];
$gen_simple_header = false;
$user = null;

/**
 * Start output buffering
 */
if (!defined('IN_AJAX')) {
    ob_start('send_page');
}

/**
 * Debug options
 */
if (DBG_USER) {
    ini_set('error_reporting', E_ALL);
    ini_set('display_errors', 1);
} else {
    unset($_COOKIE['explain']);
}

/**
 * Functions
 */
require INC_DIR . '/functions.php';
require INC_DIR . '/sessions.php';

if (DBG_USER) {
    require INC_DIR . '/functions_dev.php';
}

$bb_cfg = array_merge(bb_get_config(BB_CONFIG), $bb_cfg);

$log_action = new TorrentPier\Legacy\LogAction();
$html = new TorrentPier\Legacy\Common\Html();
$user = new TorrentPier\Legacy\Common\User();

$userdata =& $user->data;

/**
 * Cron
 */
if ((empty($_POST) && !defined('IN_ADMIN') && !defined('IN_AJAX') && !file_exists(CRON_RUNNING) && ($bb_cfg['cron_enabled'] || defined('START_CRON'))) || defined('FORCE_CRON')) {
    if (TIMENOW - $bb_cfg['cron_last_check'] > $bb_cfg['cron_check_interval']) {

        /** Update cron_last_check */
        bb_update_config(['cron_last_check' => TIMENOW + 10]);
        bb_log(date('H:i:s - ') . getmypid() . ' -x-- DB-LOCK try' . LOG_LF, CRON_LOG_DIR . '/cron_check');

        if (DB()->get_lock('cron', 1)) {
            bb_log(date('H:i:s - ') . getmypid() . ' --x- DB-LOCK OBTAINED !!!!!!!!!!!!!!!!!' . LOG_LF, CRON_LOG_DIR . '/cron_check');

            /** Run cron */
            if (TorrentPier\Helpers\CronHelper::getFileLock()) {
                /** снятие файловой блокировки */
                register_shutdown_function(function () {
                    TorrentPier\Helpers\CronHelper::releaseLockFile();
                });

                /** разблокировка форума */
                register_shutdown_function(function () {
                    TorrentPier\Helpers\CronHelper::enableBoard();
                });

                TorrentPier\Helpers\CronHelper::trackRunning('start');

                require(CRON_DIR . 'cron_check.php');

                TorrentPier\Helpers\CronHelper::trackRunning('end');
            }

            if (defined('IN_CRON')) {
                bb_log(date('H:i:s - ') . getmypid() . ' --x- ALL jobs FINISHED *************************************************' . LOG_LF, CRON_LOG_DIR . '/cron_check');
            }

            DB()->release_lock('cron');
        }
    }
}

/**
 * Exit if board is disabled via trigger
 */
if (($bb_cfg['board_disable'] || file_exists(BB_DISABLED)) && !defined('IN_ADMIN') && !defined('IN_AJAX') && !defined('IN_LOGIN')) {
    header('HTTP/1.0 503 Service Unavailable');
    if ($bb_cfg['board_disable']) {
        // admin lock
        send_no_cache_headers();
        bb_die('BOARD_DISABLE');
    } elseif (file_exists(BB_DISABLED)) {
        // trigger lock
        TorrentPier\Helpers\CronHelper::releaseDeadlock();
        send_no_cache_headers();
        bb_die('BOARD_DISABLE_CRON');
    }
}
