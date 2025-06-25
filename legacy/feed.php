<?php

/**
 * TorrentPier – Bull-powered BitTorrent tracker engine.
 *
 * @copyright Copyright (c) 2005-2025 TorrentPier (https://torrentpier.com)
 *
 * @link      https://github.com/torrentpier/torrentpier for the canonical source repository
 *
 * @license   https://github.com/torrentpier/torrentpier/blob/master/LICENSE MIT License
 */
define('BB_SCRIPT', 'feed');

require __DIR__.'/common.php';

// Init userdata
$user->session_start(['req_login' => true]);

$mode = $_REQUEST['mode'] ?? '';
$type = $_POST['type'] ?? '';
$id = isset($_POST['id']) ? (int) $_POST['id'] : 0;
$timecheck = TIMENOW - 600;

if (!$mode) {
    bb_simple_die($lang['ATOM_NO_MODE']);
}

if ($mode === 'get_feed_url' && ($type === 'f' || $type === 'u') && $id >= 0) {
    if ($type == 'f') {
        // Check if the user has actually sent a forum ID
        $sql = 'SELECT allow_reg_tracker, forum_name FROM '.BB_FORUMS." WHERE forum_id = $id LIMIT 1";
        if (!$forum_data = DB()->fetch_row($sql)) {
            if ($id == 0) {
                $forum_data = [];
            } else {
                bb_simple_die($lang['ATOM_ERROR'].' #1');
            }
        }
        if (is_file(config()->get('atom.path').'/f/'.$id.'.atom') && filemtime(config()->get('atom.path').'/f/'.$id.'.atom') > $timecheck) {
            redirect(config()->get('atom.url').'/f/'.$id.'.atom');
        } else {
            if (\TorrentPier\Legacy\Atom::update_forum_feed($id, $forum_data)) {
                redirect(config()->get('atom.url').'/f/'.$id.'.atom');
            } else {
                bb_simple_die($lang['ATOM_NO_FORUM']);
            }
        }
    }
    if ($type === 'u') {
        // Check if the user has actually sent a user ID
        if ($id < 1) {
            bb_simple_die($lang['ATOM_ERROR'].' #2');
        }
        if (!$username = get_username($id)) {
            bb_simple_die($lang['ATOM_ERROR'].' #3');
        }
        if (is_file(config()->get('atom.path').'/u/'.floor($id / 5000).'/'.($id % 100).'/'.$id.'.atom') && filemtime(config()->get('atom.path').'/u/'.floor($id / 5000).'/'.($id % 100).'/'.$id.'.atom') > $timecheck) {
            redirect(config()->get('atom.url').'/u/'.floor($id / 5000).'/'.($id % 100).'/'.$id.'.atom');
        } else {
            if (\TorrentPier\Legacy\Atom::update_user_feed($id, $username)) {
                redirect(config()->get('atom.url').'/u/'.floor($id / 5000).'/'.($id % 100).'/'.$id.'.atom');
            } else {
                bb_simple_die($lang['ATOM_NO_USER']);
            }
        }
    }
} else {
    bb_simple_die($lang['ATOM_ERROR'].' #4');
}
