<?php
/**
 * TorrentPier – Bull-powered BitTorrent tracker engine
 *
 * @copyright Copyright (c) 2005-2017 TorrentPier (https://torrentpier.me)
 * @link      https://github.com/torrentpier/torrentpier for the canonical source repository
 * @license   https://github.com/torrentpier/torrentpier/blob/master/LICENSE MIT License
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
    ob_end_clean();

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
