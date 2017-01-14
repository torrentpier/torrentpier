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
require_once __DIR__ . '/common.php';
require_once(INC_DIR . 'functions_atom.php');

/** @var \TorrentPier\Di $di */
$di = \TorrentPier\Di::getInstance();

/** @var \TorrentPier\Db\Adapter $db */
$db = $di->db;

$user->session_start(['req_login' => true]);

$mode = $di->request->request->get('mode');
$type = $di->request->request->get('type');
$id = $di->request->query->getInt('id');

if (!$mode) {
    bb_simple_die($di->translator->trans('Do not specify a mode for the feed'));
}

if ($mode == 'get_feed_url' && ($type == 'f' || $type == 'u') && $id >= 0) {
    if ($type == 'f') {
        /** @var \TorrentPier\Db\PrepareStatement $result */
        $forum_data = $db->select(BB_FORUMS, function (\Zend\Db\Sql\Select $select) use ($id) {
            $select->columns(['atom_tr_allowed' => 'allow_reg_tracker', 'atom_forum_name' => 'forum_name']);
            $select->where(function (\Zend\Db\Sql\Where $where) use ($id) {
                $where->equalTo('forum_id', $id);
            });
        })->one();

        if (!$forum_data) {
            if ($id == 0) {
                $forum_data = [];
            } else {
                \TorrentPier\Log::error('No forum data to atom feed');
            }
        }
        if (file_exists($di->config->get('atom.path') . '/f/' . $id . '.atom') && filemtime($di->config->get('atom.path') . '/f/' . $id . '.atom') > TIMENOW - 600) {
            redirect($di->config->get('atom.url') . '/f/' . $id . '.atom');
        } else {
            if (update_forum_feed($id, $forum_data)) {
                redirect($di->config->get('atom.url') . '/f/' . $id . '.atom');
            } else {
                bb_simple_die($di->translator->trans('This forum does not have a feed'));
            }
        }
    }

    if ($type == 'u') {
        if ($id < 1) {
            \TorrentPier\Log::error('Incorrect atom feed user_id');
        }
        if (!$username = get_username($id)) {
            \TorrentPier\Log::error('Can not receive the username for atom feed');
        }
        if (file_exists($di->config->get('atom.path') . '/u/' . floor($id / 5000) . '/' . ($id % 100) . '/' . $id . '.atom') && filemtime($di->config->get('atom.path') . '/u/' . floor($id / 5000) . '/' . ($id % 100) . '/' . $id . '.atom') > TIMENOW - 600) {
            redirect($di->config->get('atom.url') . '/u/' . floor($id / 5000) . '/' . ($id % 100) . '/' . $id . '.atom');
        } else {
            if (update_user_feed($id, $username)) {
                redirect($di->config->get('atom.url') . '/u/' . floor($id / 5000) . '/' . ($id % 100) . '/' . $id . '.atom');
            } else {
                bb_simple_die($di->translator->trans('This user does not have a feed'));
            }
        }
    }
} else {
    \TorrentPier\Log::error('Unknown atom feed mode');
}
