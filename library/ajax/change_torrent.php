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

global $lang;

if (!isset($this->request['t'])) {
    $this->ajax_die('Invalid AJAX topic');
}
if (!isset($this->request['u'])) {
    //$this->ajax_die('Invalid AJAX user');
}
if (!isset($this->request['type'])) {
    $this->ajax_die('Invalid AJAX type');
}

$topic_id = (int)$this->request['t'];
//$req_uid    = (int) $this->request['u'];
$type = (string)$this->request['type'];
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
        change_tor_type($topic_id, $tor_type);
        $url = make_url(TOPIC_URL . $topic_id);
        $title = $lang['CHANGE_TOR_TYPE'];
        break;

    case 'reg':
        tracker_register($topic_id);
        $url = (TOPIC_URL . $topic_id);
        break;

    case 'unreg':
        tracker_unregister($topic_id);
        $url = (TOPIC_URL . $topic_id);
        break;

    case 'del_torrent':
        if (empty($this->request['confirmed'])) {
            $this->prompt_for_confirm($lang['DEL_TORRENT']);
        }
        delete_torrent($topic_id);
        $url = make_url(TOPIC_URL . $topic_id);
        break;

    case 'del_torrent_move_topic':
        if (empty($this->request['confirmed'])) {
            $this->prompt_for_confirm($lang['DEL_MOVE_TORRENT']);
        }
        delete_torrent($topic_id);
        $url = make_url("modcp.php?t=$topic_id&mode=move");
        break;
}

$this->response['url'] = $url;
$this->response['title'] = $title;
