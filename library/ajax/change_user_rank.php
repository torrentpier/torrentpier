<?php

if (!defined('IN_AJAX')) die(basename(__FILE__));

global $datastore, $lang;

$ranks   = $datastore->get('ranks');
$rank_id = intval($this->request['rank_id']);

if (!$user_id = intval($this->request['user_id']) OR !$profiledata = get_userdata($user_id))
{
	$this->ajax_die("invalid user_id: $user_id");
}

if ($rank_id != 0 && !isset($ranks[$rank_id]))
{
	$this->ajax_die("invalid rank_id: $rank_id");
}

DB()->query("UPDATE ". BB_USERS ." SET user_rank = $rank_id WHERE user_id = $user_id LIMIT 1");

cache_rm_user_sessions($user_id);

$user_rank = ($rank_id) ? '<span class="'. $ranks[$rank_id]['rank_style'] .'">'. $ranks[$rank_id]['rank_title'] .'</span>' : '';

$this->response['html'] = ($rank_id) ? $lang['AWARDED_RANK'] . "<b> $user_rank </b>" : $lang['SHOT_RANK'];
$this->response['rank_name'] = ($rank_id) ? $user_rank : $lang['USER'];
