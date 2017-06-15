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

$attach_id = (int)$this->request['attach_id'];
$mode = (string)$this->request['mode'];

if ($bb_cfg['tor_comment']) {
    $comment = (string)$this->request['comment'];
}

$tor = DB()->fetch_row("
	SELECT
		tor.poster_id, tor.forum_id, tor.topic_id, tor.tor_status, tor.checked_time, tor.checked_user_id, f.cat_id, t.topic_title
	FROM       " . BB_BT_TORRENTS . " tor
	INNER JOIN " . BB_FORUMS . " f ON(f.forum_id = tor.forum_id)
	INNER JOIN " . BB_TOPICS . " t ON(t.topic_id = tor.topic_id)
	WHERE tor.attach_id = $attach_id
	LIMIT 1
");

if (!$tor) {
    $this->ajax_die($lang['TORRENT_FAILED']);
}

switch ($mode) {
    case 'status':
        $new_status = (int)$this->request['status'];

        // Валидность статуса
        if (!isset($lang['TOR_STATUS_NAME'][$new_status])) {
            $this->ajax_die($lang['TOR_STATUS_FAILED']);
        }
        if (!isset($this->request['status'])) {
            $this->ajax_die($lang['TOR_DONT_CHANGE']);
        }
        if (!IS_AM) {
            $this->ajax_die($lang['NOT_MODERATOR']);
        }

        // Тот же статус
        if ($tor['tor_status'] == $new_status) {
            $this->ajax_die($lang['TOR_STATUS_DUB']);
        }

        // Запрет на изменение/присвоение CH-статуса модератором
        if ($new_status == TOR_CLOSED_CPHOLD && !IS_ADMIN) {
            $this->ajax_die($lang['TOR_DONT_CHANGE']);
        }

        // Права на изменение статуса
        if ($tor['tor_status'] == TOR_CLOSED_CPHOLD) {
            if (!IS_ADMIN) {
                $this->verify_mod_rights($tor['forum_id']);
            }
            DB()->query("UPDATE " . BB_TOPICS . " SET topic_status = " . TOPIC_UNLOCKED . " WHERE topic_id = {$tor['topic_id']}");
        } else {
            $this->verify_mod_rights($tor['forum_id']);
        }

        // Подтверждение изменения статуса, выставленного другим модератором
        if ($tor['tor_status'] != TOR_NOT_APPROVED && $tor['checked_user_id'] != $userdata['user_id'] && $tor['checked_time'] + 2 * 3600 > TIMENOW) {
            if (empty($this->request['confirmed'])) {
                $msg = $lang['TOR_STATUS_OF'] . " {$lang['TOR_STATUS_NAME'][$tor['tor_status']]}\n\n";
                $msg .= ($username = get_username($tor['checked_user_id'])) ? $lang['TOR_STATUS_CHANGED'] . html_entity_decode($username) . ", " . delta_time($tor['checked_time']) . $lang['TOR_BACK'] . "\n\n" : "";
                $msg .= $lang['PROCEED'] . '?';
                $this->prompt_for_confirm($msg);
            }
        }

        change_tor_status($attach_id, $new_status);

        $this->response['status'] = $bb_cfg['tor_icons'][$new_status] . ' <b> ' . $lang['TOR_STATUS_NAME'][$new_status] . '</b> &middot; ' . profile_url($userdata) . ' &middot; <i>' . delta_time(TIMENOW) . $lang['TOR_BACK'] . '</i>';

        if ($bb_cfg['tor_comment'] && (($comment && $comment != $lang['COMMENT']) || in_array($new_status, $bb_cfg['tor_reply']))) {
            if ($tor['poster_id'] > 0) {
                $subject = sprintf($lang['TOR_MOD_TITLE'], $tor['topic_title']);
                $message = sprintf($lang['TOR_MOD_MSG'], get_username($tor['poster_id']), make_url(TOPIC_URL . $tor['topic_id']), $bb_cfg['tor_icons'][$new_status] . ' ' . $lang['TOR_STATUS_NAME'][$new_status]);

                if ($comment && $comment != $lang['COMMENT']) {
                    $message .= "\n\n[b]" . $lang['COMMENT'] . '[/b]: ' . $comment;
                }

                send_pm($tor['poster_id'], $subject, $message, $userdata['user_id']);
                cache_rm_user_sessions($tor['poster_id']);
            }
        }
        break;

    case 'status_reply':
        if (!$bb_cfg['tor_comment']) {
            $this->ajax_die($lang['MODULE_OFF']);
        }

        $subject = sprintf($lang['TOR_AUTH_TITLE'], $tor['topic_title']);
        $message = sprintf($lang['TOR_AUTH_MSG'], get_username($tor['checked_user_id']), make_url(TOPIC_URL . $tor['topic_id']), $tor['topic_title']);

        if ($comment && $comment != $lang['COMMENT']) {
            $message .= "\n\n[b]" . $lang['COMMENT'] . '[/b]: ' . $comment;
        }

        send_pm($tor['checked_user_id'], $subject, $message, $userdata['user_id']);
        cache_rm_user_sessions($tor['checked_user_id']);
        break;
}

$this->response['attach_id'] = $attach_id;
