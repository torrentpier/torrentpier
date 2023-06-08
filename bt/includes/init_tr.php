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
if ($bb_cfg['tracker']['off']) {
    msg_die($bb_cfg['tracker']['off_reason']);
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
