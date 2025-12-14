<?php

/**
 * TorrentPier – Bull-powered BitTorrent tracker engine
 *
 * @copyright Copyright (c) 2005-2025 TorrentPier (https://torrentpier.com)
 * @link      https://github.com/torrentpier/torrentpier for the canonical source repository
 * @license   https://github.com/torrentpier/torrentpier/blob/master/LICENSE MIT License
 */

use Illuminate\Support\Str;

if (!defined('IN_TRACKER')) {
    die(basename(__FILE__));
}

// Exit if tracker is disabled
if (config()->get('tracker.bt_off')) {
    msg_die(config()->get('tracker.bt_off_reason'));
}

//
// Functions
//
function silent_exit($msg = '')
{
    echo Arokettu\Bencode\Bencode::encode(['warning message' => Str::squish($msg)]);

    exit;
}

function error_exit($msg = '')
{
    echo Arokettu\Bencode\Bencode::encode(['failure reason' => Str::squish($msg)]);

    exit;
}

function drop_fast_announce($lp_info, $lp_cached_peers = [])
{
    $announce_interval = config()->get('announce_interval');

    if ($lp_info['update_time'] < (TIMENOW - $announce_interval + 60)) {
        return; // if announce interval correct
    }

    $new_ann_intrv = $lp_info['update_time'] + $announce_interval - TIMENOW;

    dummy_exit($new_ann_intrv, $lp_cached_peers);
}

function msg_die($msg)
{
    $output = Arokettu\Bencode\Bencode::encode([
        'interval' => (int)1800,
        'failure reason' => (string)$msg,
    ]);

    die($output);
}

function dummy_exit($interval = 1800, $cache_dict = [])
{
    $output = [
        'interval' => (int)$interval,
        'peers' => (string)DUMMY_PEER,
        'external ip' => inet_pton(request()->server->get('REMOTE_ADDR')),
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

    $output = Arokettu\Bencode\Bencode::encode($output);

    die($output);
}
