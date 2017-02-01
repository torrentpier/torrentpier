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

if (!defined('IN_AJAX')) {
    die(basename(__FILE__));
}

global $userdata, $bb_cfg, $lang;

if (!isset($this->request['attach_id'])) {
    $this->ajax_die($lang['EMPTY_ATTACH_ID']);
}
if (!isset($this->request['type'])) {
    $this->ajax_die('type');
}
$attach_id = (int)$this->request['attach_id'];
$type = (string)$this->request['type'];

$torrent = DB()->fetch_row("
		SELECT
			a.post_id, d.physical_filename, d.extension, d.tracker_status,
			t.topic_first_post_id,
			p.poster_id, p.topic_id, p.forum_id,
			f.allow_reg_tracker
		FROM
			" . BB_ATTACHMENTS . " a,
			" . BB_ATTACHMENTS_DESC . " d,
			" . BB_POSTS . " p,
			" . BB_TOPICS . " t,
			" . BB_FORUMS . " f
		WHERE
			    a.attach_id = $attach_id
			AND d.attach_id = $attach_id
			AND p.post_id = a.post_id
			AND t.topic_id = p.topic_id
			AND f.forum_id = p.forum_id
		LIMIT 1
	");

if (!$torrent) {
    $this->ajax_die($lang['INVALID_ATTACH_ID']);
}

if ($torrent['poster_id'] == $userdata['user_id'] && !IS_AM) {
    if ($type == 'del_torrent' || $type == 'reg' || $type == 'unreg') {
        true;
    } else {
        $this->ajax_die($lang['ONLY_FOR_MOD']);
    }
} elseif (!IS_AM) {
    $this->ajax_die($lang['ONLY_FOR_MOD']);
}

$title = $url = '';
switch ($type) {
    case 'set_gold':
    case 'set_silver':
    case 'unset_silver_gold':
        if ($type == 'set_silver') {
            $tor_type = TOR_TYPE_SILVER;
        } elseif ($type == 'set_gold') {
            $tor_type = TOR_TYPE_GOLD;
        } else {
            $tor_type = 0;
        }
        change_tor_type($attach_id, $tor_type);
        $title = $lang['CHANGE_TOR_TYPE'];
        $url = make_url(TOPIC_URL . $torrent['topic_id']);
        break;

    case 'reg':
        tracker_register($attach_id);
        $url = (TOPIC_URL . $torrent['topic_id']);
        break;

    case 'unreg':
        tracker_unregister($attach_id);
        $url = (TOPIC_URL . $torrent['topic_id']);
        break;

    case 'del_torrent':
        if (empty($this->request['confirmed'])) {
            $this->prompt_for_confirm($lang['DEL_TORRENT']);
        }
        delete_torrent($attach_id);
        $url = make_url(TOPIC_URL . $torrent['topic_id']);
        break;

    case 'del_torrent_move_topic':
        if (empty($this->request['confirmed'])) {
            $this->prompt_for_confirm($lang['DEL_MOVE_TORRENT']);
        }
        delete_torrent($attach_id);
        $url = make_url("modcp.php?t={$torrent['topic_id']}&mode=move&sid={$userdata['session_id']}");
        break;
}

$this->response['url'] = $url;
$this->response['title'] = $title;
