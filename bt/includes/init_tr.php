<?php
/**
 * TorrentPier – Bull-powered BitTorrent tracker engine
 *
 * @copyright Copyright (c) 2005-2024 TorrentPier (https://torrentpier.com)
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
function silent_exit($msg = '')
{
    echo \Arokettu\Bencode\Bencode::encode(['warning message' => str_compact($msg)]);

    exit;
}

function error_exit($msg = '')
{
    echo \Arokettu\Bencode\Bencode::encode(['failure reason' => str_compact($msg)]);

    exit;
}

function drop_fast_announce($lp_info, $lp_cached_peers = [])
{
    global $announce_interval;

    if ($lp_info['update_time'] < (TIMENOW - $announce_interval + 60)) {
        return; // if announce interval correct
    }

    $new_ann_intrv = $lp_info['update_time'] + $announce_interval - TIMENOW;

    dummy_exit($new_ann_intrv, $lp_cached_peers);
}

function msg_die($msg)
{
    $output = \Arokettu\Bencode\Bencode::encode([
        'interval' => (int)1800,
        'failure reason' => (string)$msg,
    ]);

    die($output);
}

function sql_die(int $code)
{
    $error = null;

    switch ($code) {
        case 1264:
            $error = 'SQL: Out of range';
            break;
    }

    if (isset($error)) {
        msg_die($error . " ($code)");
    }
}

function dummy_exit($interval = 1800, $cache_dict = [])
{
    $output = [
        'interval' => (int)$interval,
        'peers' => (string)DUMMY_PEER,
        'external ip' => inet_pton($_SERVER['REMOTE_ADDR']),
    ];

    if (!empty($cache_dict)) {
        $output['complete'] = $cache_dict['complete'];
        $output['incomplete'] = $cache_dict['incomplete'];
        $output['downloaded'] = $cache_dict['downloaded'];
    }

    if (isset($cache_dict['peers'])) {
        $output['peers'] = $cache_dict['peers'];
    }

    if (isset($cache_dict['peers6'])) {
        $output['peers6'] = $cache_dict['peers6'];
    }

    $output = \Arokettu\Bencode\Bencode::encode($output);

    die($output);
}
