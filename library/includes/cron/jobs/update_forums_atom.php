<?php
/**
 * TorrentPier – Bull-powered BitTorrent tracker engine
 *
 * @copyright Copyright (c) 2005-2024 TorrentPier (https://torrentpier.com)
 * @link      https://github.com/torrentpier/torrentpier for the canonical source repository
 * @license   https://github.com/torrentpier/torrentpier/blob/master/LICENSE MIT License
 */

if (!defined('BB_ROOT')) {
    die(basename(__FILE__));
}

global $bb_cfg;

$timecheck = TIMENOW - 600;
$forums_data = DB()->fetch_rowset("SELECT forum_id, allow_reg_tracker, forum_name FROM " . BB_FORUMS);

if (file_exists($bb_cfg['atom']['path'] . '/f/0.atom')) {
    if (filemtime($bb_cfg['atom']['path'] . '/f/0.atom') <= $timecheck) {
        \TorrentPier\Legacy\Atom::update_forum_feed(0, $forums_data);
    }
} else {
    \TorrentPier\Legacy\Atom::update_forum_feed(0, $forums_data);
}

foreach ($forums_data as $forum_data) {
    if (file_exists($bb_cfg['atom']['path'] . '/f/' . $forum_data['forum_id'] . '.atom')) {
        if (filemtime($bb_cfg['atom']['path'] . '/f/' . $forum_data['forum_id'] . '.atom') <= $timecheck) {
            \TorrentPier\Legacy\Atom::update_forum_feed($forum_data['forum_id'], $forum_data);
        }
    } else {
        \TorrentPier\Legacy\Atom::update_forum_feed($forum_data['forum_id'], $forum_data);
    }
}
