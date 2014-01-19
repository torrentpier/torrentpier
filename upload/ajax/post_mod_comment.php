<?php

if (!defined('IN_AJAX')) die(basename(__FILE__));

global $lang, $userdata;

$post_id = (int) $this->request['post_id'];
$post = DB()->fetch_row("SELECT t.*, f.*, p.*, pt.post_text
			FROM ". BB_TOPICS ." t, ". BB_FORUMS ." f, ". BB_POSTS ." p, ". BB_POSTS_TEXT ." pt
			WHERE p.post_id = $post_id
				AND t.topic_id = p.topic_id
				AND f.forum_id = t.forum_id
				AND p.post_id  = pt.post_id
			LIMIT 1");
if (!$post) $this->ajax_die('not post');

$type = (int) $this->request['mc_type'];
$text = (string) $this->request['mc_text'];
$text = prepare_message($text);
if (!$text) $this->ajax_die('no text');

DB()->query("UPDATE ". BB_POSTS ." SET post_mod_comment = '". DB()->escape($text) ."', post_mod_comment_type = $type, post_mc_mod_id = ". $userdata['user_id'] .", post_mc_mod_name = '". $userdata['username'] ."' WHERE post_id = $post_id LIMIT 1");

$this->response['type'] = $type;
$this->response['post_id'] = $post_id;

if ($type == 0) $this->response['html'] = '';
elseif ($type == 1) $this->response['html'] = '<div class="mcBlock"><table cellspacing="0" cellpadding="0" border="0"><tr><td class="mcTd1C">K</td><td class="mcTd2C">'. profile_url($userdata) .'&nbsp;'. $lang['WROTE'] .':<br /><br />'. bbcode2html($text) .'</td></tr></table></div>';
elseif ($type == 2) $this->response['html'] = '<div class="mcBlock"><table cellspacing="0" cellpadding="0" border="0"><tr><td class="mcTd1W">!</td><td class="mcTd2W">'. profile_url($userdata) .'&nbsp;'. $lang['WROTE'] .':<br /><br />'. bbcode2html($text) .'</td></tr></table></div>';