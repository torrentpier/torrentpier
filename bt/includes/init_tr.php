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

if (!defined('IN_TRACKER')) {
    die(basename(__FILE__));
}

global $bb_cfg;

// Exit if tracker is disabled
if ($bb_cfg['tracker']['off']) {
    msg_die($bb_cfg['tracker']['off_reason']);
}

//
// Functions
//
function tracker_exit()
{
    global $DBS;

    if (DBG_LOG && DBG_TRACKER) {
        if ($gen_time = utime() - TIMESTART) {
            $sql_init_perc = round($DBS->sql_inittime * 100 / $gen_time);
            $sql_total_perc = round($DBS->sql_timetotal * 100 / $gen_time);

            $str = array();
            $str[] = substr(TIMENOW, -4, 4);
            $str[] = sprintf('%.4f', $gen_time);
            $str[] = sprintf('%.4f' . LOG_SEPR . '%02d%%', $DBS->sql_inittime, $sql_init_perc);
            $str[] = sprintf('%.4f' . LOG_SEPR . '%02d%%', $DBS->sql_timetotal, $sql_total_perc);
            $str[] = $DBS->num_queries;
            $str[] = sprintf('%.1f', sys('la'));
            $str = implode(LOG_SEPR, $str) . LOG_LF;
            dbg_log($str, '!!gentime');
        }
    }
    exit;
}

function silent_exit()
{
    while (ob_end_clean()) ;

    tracker_exit();
}

function error_exit($msg = '')
{
    if (DBG_LOG) {
        dbg_log(' ', '!err-' . clean_filename($msg));
    }

    silent_exit();

    echo \Rych\Bencode\Bencode::encode(['failure reason' => str_compact($msg)]);

    tracker_exit();
}
