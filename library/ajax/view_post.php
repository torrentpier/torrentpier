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

global $user, $lang;

$post_id = (int)$this->request['post_id'];
$topic_id = (int)$this->request['topic_id'];

if (!$post_id) {
    $post_id = DB()->fetch_row("SELECT topic_first_post_id FROM " . BB_TOPICS . " WHERE topic_id = $topic_id", 'topic_first_post_id');
}

$sql = "
	SELECT
	  p.*,
	  h.post_html, IF(h.post_html IS NULL, pt.post_text, NULL) AS post_text,
	  f.auth_read
	FROM       " . BB_POSTS . " p
	INNER JOIN " . BB_POSTS_TEXT . " pt ON(pt.post_id = p.post_id)
	 LEFT JOIN " . BB_POSTS_HTML . " h  ON(h.post_id = pt.post_id)
	INNER JOIN " . BB_FORUMS . " f  ON(f.forum_id = p.forum_id)
	WHERE
	  p.post_id = $post_id
	LIMIT 1
";

if (!$post_data = DB()->fetch_row($sql)) {
    $this->ajax_die($lang['TOPIC_POST_NOT_EXIST']);
}

// Auth check
if ($post_data['auth_read'] == AUTH_REG) {
    if (IS_GUEST) {
        $this->ajax_die($lang['NEED_TO_LOGIN_FIRST']);
    }
} elseif ($post_data['auth_read'] != AUTH_ALL) {
    $is_auth = auth(AUTH_READ, $post_data['forum_id'], $user->data, $post_data);
    if (!$is_auth['auth_read']) {
        $this->ajax_die($lang['TOPIC_POST_NOT_EXIST']);
    }
}

$this->response['post_id'] = $post_id;
$this->response['topic_id'] = $topic_id;
$this->response['post_html'] = get_parsed_post($post_data);
