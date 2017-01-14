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

global $lang, $userdata;

$post_id = (int)$this->request['post_id'];
$mc_type = (int)$this->request['mc_type'];
$mc_text = (string)$this->request['mc_text'];
if (!$mc_text = prepare_message($mc_text)) {
    $this->ajax_die($lang['EMPTY_MESSAGE']);
}

$post = DB()->fetch_row("
	SELECT
		p.post_id, p.poster_id
	FROM      " . BB_POSTS . " p
	WHERE p.post_id = $post_id
");
if (!$post) {
    $this->ajax_die('not post');
}

$data = array(
    'mc_comment' => ($mc_type) ? $mc_text : '',
    'mc_type' => $mc_type,
    'mc_user_id' => ($mc_type) ? $userdata['user_id'] : 0,
);
$sql_args = DB()->build_array('UPDATE', $data);
DB()->query("UPDATE " . BB_POSTS . " SET $sql_args WHERE post_id = $post_id");

if ($mc_type && $post['poster_id'] != $userdata['user_id']) {
    $subject = sprintf($lang['MC_COMMENT_PM_SUBJECT'], $lang['MC_COMMENT'][$mc_type]['type']);
    $message = sprintf($lang['MC_COMMENT_PM_MSG'], get_username($post['poster_id']), make_url(POST_URL . "$post_id#$post_id"), $lang['MC_COMMENT'][$mc_type]['type'], $mc_text);

    send_pm($post['poster_id'], $subject, $message);
    cache_rm_user_sessions($post['poster_id']);
}

switch ($mc_type) {
    case 1: // Комментарий
        $mc_class = 'success';
        break;
    case 2: // Информация
        $mc_class = 'info';
        break;
    case 3: // Предупреждение
        $mc_class = 'warning';
        break;
    case 4: // Нарушение
        $mc_class = 'danger';
        break;
    default:
        $mc_class = '';
        break;
}

$this->response['mc_type'] = $mc_type;
$this->response['post_id'] = $post_id;
$this->response['mc_title'] = sprintf($lang['MC_COMMENT'][$mc_type]['title'], profile_url($userdata));
$this->response['mc_text'] = bbcode2html($mc_text);
$this->response['mc_class'] = $mc_class;
