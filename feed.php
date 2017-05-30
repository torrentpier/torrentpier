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

define('BB_SCRIPT', 'feed');
define('BB_ROOT', './');
require __DIR__ . '/common.php';

$user->session_start(array('req_login' => true));

$mode = isset($_REQUEST['mode']) ? $_REQUEST['mode'] : '';
$type = isset($_POST['type']) ? $_POST['type'] : '';
$id = isset($_POST['id']) ? $_POST['id'] : 0;
$timecheck = TIMENOW - 600;

if (!$mode) {
    bb_simple_die($lang['ATOM_NO_MODE']);
}

if ($mode == 'get_feed_url' && ($type == 'f' || $type == 'u') && $id >= 0) {
    if ($type == 'f') {
        // Check if the user has actually sent a forum ID
        $sql = "SELECT allow_reg_tracker, forum_name FROM " . BB_FORUMS . " WHERE forum_id = $id LIMIT 1";
        if (!$forum_data = DB()->fetch_row($sql)) {
            if ($id == 0) {
                $forum_data = array();
            } else {
                bb_simple_die($lang['ATOM_ERROR'] . ' #1');
            }
        }
        if (file_exists($bb_cfg['atom']['path'] . '/f/' . $id . '.atom') && filemtime($bb_cfg['atom']['path'] . '/f/' . $id . '.atom') > $timecheck) {
            redirect($bb_cfg['atom']['url'] . '/f/' . $id . '.atom');
        } else {
            require_once INC_DIR . '/functions_atom.php';
            if (update_forum_feed($id, $forum_data)) {
                redirect($bb_cfg['atom']['url'] . '/f/' . $id . '.atom');
            } else {
                bb_simple_die($lang['ATOM_NO_FORUM']);
            }
        }
    }
    if ($type == 'u') {
        // Check if the user has actually sent a user ID
        if ($id < 1) {
            bb_simple_die($lang['ATOM_ERROR'] . ' #2');
        }
        if (!$username = get_username($id)) {
            bb_simple_die($lang['ATOM_ERROR'] . ' #3');
        }
        if (file_exists($bb_cfg['atom']['path'] . '/u/' . floor($id / 5000) . '/' . ($id % 100) . '/' . $id . '.atom') && filemtime($bb_cfg['atom']['path'] . '/u/' . floor($id / 5000) . '/' . ($id % 100) . '/' . $id . '.atom') > $timecheck) {
            redirect($bb_cfg['atom']['url'] . '/u/' . floor($id / 5000) . '/' . ($id % 100) . '/' . $id . '.atom');
        } else {
            require_once INC_DIR . '/functions_atom.php';
            if (update_user_feed($id, $username)) {
                redirect($bb_cfg['atom']['url'] . '/u/' . floor($id / 5000) . '/' . ($id % 100) . '/' . $id . '.atom');
            } else {
                bb_simple_die($lang['ATOM_NO_USER']);
            }
        }
    }
} else {
    bb_simple_die($lang['ATOM_ERROR'] . ' #4');
}
