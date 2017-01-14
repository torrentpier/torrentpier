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

//
// Functions
//
/**
 * @return bool
 */
function cron_get_file_lock()
{
    $lock_obtained = false;

    if (file_exists(CRON_ALLOWED)) {
        #		bb_log(date('H:i:s - ') . getmypid() .' -x-- FILE-LOCK try'. LOG_LF, CRON_LOG_DIR .'cron_check');

        $lock_obtained = rename(CRON_ALLOWED, CRON_RUNNING);
    } elseif (file_exists(CRON_RUNNING)) {
        cron_release_deadlock();
    } elseif (!file_exists(CRON_ALLOWED) && !file_exists(CRON_RUNNING)) {
        file_write('', CRON_ALLOWED);
        $lock_obtained = rename(CRON_ALLOWED, CRON_RUNNING);
    }

    return $lock_obtained;
}

/**
 * @param $mode
 */
function cron_track_running($mode)
{
    defined('CRON_STARTMARK') or define('CRON_STARTMARK', TRIGGERS_DIR . 'cron_started_at_' . date('Y-m-d_H-i-s') . '_by_pid_' . getmypid());

    if ($mode == 'start') {
        cron_touch_lock_file(CRON_RUNNING);
        file_write('', CRON_STARTMARK);
    } elseif ($mode == 'end') {
        unlink(CRON_STARTMARK);
    }
}

//
// Run cron
//
if (cron_get_file_lock()) {
    ignore_user_abort(true);
    register_shutdown_function('cron_release_file_lock');
    register_shutdown_function('cron_enable_board');

#	bb_log(date('H:i:s - ') . getmypid() .' --x- FILE-LOCK OBTAINED ###############'. LOG_LF, CRON_LOG_DIR .'cron_check');

    cron_track_running('start');

    require(CRON_DIR . 'cron_check.php');

    cron_track_running('end');
}

if (defined('IN_CRON')) {
    bb_log(date('H:i:s - ') . getmypid() . ' --x- ALL jobs FINISHED *************************************************' . LOG_LF, CRON_LOG_DIR . 'cron_check');
}
