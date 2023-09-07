<?php
/**
 * TorrentPier – Bull-powered BitTorrent tracker engine
 *
 * @copyright Copyright (c) 2005-2023 TorrentPier (https://torrentpier.com)
 * @link      https://github.com/torrentpier/torrentpier for the canonical source repository
 * @license   https://github.com/torrentpier/torrentpier/blob/master/LICENSE MIT License
 */

if (!defined('IN_TRACKER')) {
    die(basename(__FILE__));
}

global $bb_cfg;

// Exit if tracker is disabled
if ($bb_cfg['tracker']['bt_off']) {
    msg_die($bb_cfg['tracker']['bt_off_reason']);
}

//
// Functions
//
function silent_exit()
{
    ob_end_clean();

    exit;
}

function error_exit($msg = '')
{
    silent_exit();

    echo \SandFox\Bencode\Bencode::encode(['failure reason' => str_compact($msg)]);

    exit;
}

function drop_fast_announce($lp_info)
{
    global $announce_interval;

    if ($lp_info['update_time'] < (TIMENOW - $announce_interval + 60)) {
        return; // if announce interval correct
    }

    $new_ann_intrv = $lp_info['update_time'] + $announce_interval - TIMENOW;

    dummy_exit($new_ann_intrv);
}

function msg_die($msg)
{
    $output = \SandFox\Bencode\Bencode::encode([
        'min interval' => (int)1800,
        'failure reason' => (string)$msg,
        'warning message' => (string)$msg,
    ]);

    die($output);
}
