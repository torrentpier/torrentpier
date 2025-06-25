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
if (!defined('IN_AJAX')) {
    exit(basename(__FILE__));
}

global $lang, $userdata;

$post_id = (int) $this->request['post_id'];
$mc_type = (int) $this->request['mc_type'];
$mc_text = (string) $this->request['mc_text'];
if ($mc_type != 0 && !$mc_text = prepare_message($mc_text)) {
    $this->ajax_die($lang['EMPTY_MESSAGE']);
}

$post = DB()->fetch_row('
	SELECT
		p.post_id, p.poster_id
	FROM      '.BB_POSTS." p
	WHERE p.post_id = $post_id
");
if (!$post) {
    $this->ajax_die('not post');
}

$data = [
    'mc_comment' => ($mc_type) ? $mc_text : '',
    'mc_type'    => $mc_type,
    'mc_user_id' => ($mc_type) ? $userdata['user_id'] : 0,
];
$sql_args = DB()->build_array('UPDATE', $data);
DB()->query('UPDATE '.BB_POSTS." SET $sql_args WHERE post_id = $post_id");

if ($mc_type && $post['poster_id'] != $userdata['user_id']) {
    $subject = sprintf($lang['MC_COMMENT_PM_SUBJECT'], $lang['MC_COMMENT'][$mc_type]['type']);
    $message = sprintf($lang['MC_COMMENT_PM_MSG'], get_username($post['poster_id']), make_url(POST_URL."$post_id#$post_id"), $lang['MC_COMMENT'][$mc_type]['type'], $mc_text);

    send_pm($post['poster_id'], $subject, $message);
    \TorrentPier\Sessions::cache_rm_user_sessions($post['poster_id']);
}

$mc_class = match ($mc_type) {
    1       => 'success',
    2       => 'info',
    3       => 'warning',
    4       => 'danger',
    default => '',
};

$this->response['mc_type'] = $mc_type;
$this->response['post_id'] = $post_id;
$this->response['mc_title'] = sprintf($lang['MC_COMMENT'][$mc_type]['title'], profile_url($userdata));
$this->response['mc_text'] = bbcode2html($mc_text);
$this->response['mc_class'] = $mc_class;
