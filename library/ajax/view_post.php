<?php
/**
 * TorrentPier – Bull-powered BitTorrent tracker engine
 *
 * @copyright Copyright (c) 2005-2025 TorrentPier (https://torrentpier.com)
 * @link      https://github.com/torrentpier/torrentpier for the canonical source repository
 * @license   https://github.com/torrentpier/torrentpier/blob/master/LICENSE MIT License
 */

if (!defined('IN_AJAX')) {
    die(basename(__FILE__));
}

global $user, $lang;

$post_id = isset($this->request['post_id']) ? (int)$this->request['post_id'] : null;
$topic_id = isset($this->request['topic_id']) ? (int)$this->request['topic_id'] : null;
$return_text = config()->get('show_post_bbcode_button.enabled') && isset($this->request['return_text']) && (bool)$this->request['return_text'];

if (is_null($post_id)) {
    $post_id = DB()->fetch_row("SELECT topic_first_post_id FROM " . BB_TOPICS . " WHERE topic_id = $topic_id", 'topic_first_post_id');
}

$post_text_sql = $return_text ? "pt.post_text," : "IF(h.post_html IS NULL, pt.post_text, NULL) AS post_text,";

$sql = "
	SELECT
	  p.*,
	  h.post_html, $post_text_sql
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
if ($return_text) {
    $this->response['post_text'] = $post_data['post_text'];
} else {
    $this->response['post_html'] = get_parsed_post($post_data);
}
