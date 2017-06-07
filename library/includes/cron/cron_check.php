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

bb_log(date('H:i:s - ') . getmypid() . ' --x- SELECT jobs' . LOG_LF, CRON_LOG_DIR . '/cron_check');

// Get cron jobs
$cron_jobs = DB()->fetch_rowset("
	SELECT * FROM " . BB_CRON . "
	WHERE cron_active = 1
		AND next_run <= NOW()
	ORDER BY run_order
");

// Run cron jobs
if ($cron_jobs) {
    bb_log(date('H:i:s - ') . getmypid() . ' --x- RUN jobs' . LOG_LF, CRON_LOG_DIR . '/cron_check');

    foreach ($cron_jobs as $job) {
        if ($job['disable_board']) {
            TorrentPier\Helpers\CronHelper::disableBoard();
            break;
        }
    }

    require(CRON_DIR . 'cron_run.php');

    // Update cron_last_check
    bb_update_config(array('cron_last_check' => TIMENOW + 10));
} else {
    bb_log(date('H:i:s - ') . getmypid() . ' --x- no active jobs found ----------------------------------------------' . LOG_LF, CRON_LOG_DIR . '/cron_check');
}
