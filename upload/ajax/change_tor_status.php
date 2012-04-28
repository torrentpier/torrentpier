<?php

if (!defined('IN_AJAX')) die(basename(__FILE__));

global $userdata, $bb_cfg, $lang;

if (!isset($this->request['attach_id']))
{
	$this->ajax_die($lang['EMPTY_ATTACH_ID']);
}
if (!isset($this->request['status']))
{
	$this->ajax_die($lang['TOR_DONT_CHANGE']);
}
$attach_id  = (int) $this->request['attach_id'];
$new_status = (int) $this->request['status'];

// Валидность статуса
if (!isset($lang['TOR_STATUS_NAME'][$new_status]))
{
	$this->ajax_die($lang['TOR_STATUS_FAILED']);
}

$tor = DB()->fetch_row("
	SELECT
		tor.forum_id, tor.topic_id, tor.tor_status, tor.checked_time, tor.checked_user_id, f.cat_id
	FROM       ". BB_BT_TORRENTS ." tor
	INNER JOIN ". BB_FORUMS      ." f ON(f.forum_id = tor.forum_id)
	WHERE tor.attach_id = $attach_id
	LIMIT 1
");
if (!$tor) $this->ajax_die($lang['TORRENT_FAILED']);

// Тот же статус
if ($tor['tor_status'] == $new_status)
{
	$this->ajax_die($lang['TOR_STATUS_DUB']);
}
// Запрет на изменение/присвоение CH-статуса модератором
if ($new_status == TOR_CLOSED_CPHOLD && !IS_ADMIN)
{
	$this->ajax_die($lang['TOR_DONT_CHANGE']);
}

// Права на изменение статуса
if ($tor['tor_status'] == TOR_CLOSED_CPHOLD)
{
	if (!IS_ADMIN) $this->verify_mod_rights($tor['forum_id']);
	DB()->query("UPDATE ". BB_TOPICS ." SET topic_status = ". TOPIC_UNLOCKED ." WHERE topic_id = {$tor['topic_id']} LIMIT 1");
}
else
{
	$this->verify_mod_rights($tor['forum_id']);
}

// Подтверждение изменения статуса, выставленного другим модератором
if ($tor['tor_status'] != TOR_NOT_APPROVED && $tor['checked_user_id'] != $userdata['user_id'] && $tor['checked_time'] + 2*3600 > TIMENOW)
{
	if (empty($this->request['confirmed']))
	{
		$msg  = $lang['TOR_STATUS_OF'] ." {$lang['TOR_STATUS_NAME'][$tor['tor_status']]}\n\n";
		$msg .= ($username = get_username($tor['checked_user_id'])) ? $lang['TOR_STATUS_CHANGED'] . html_entity_decode($username) .", ". delta_time($tor['checked_time']) . $lang['BACK'] ."\n\n" : "";
		$msg .= $lang['PROCEED'] .'?';
		$this->prompt_for_confirm($msg);
	}
}

change_tor_status($attach_id, $new_status);

$this->response['attach_id'] = $attach_id;
$this->response['status'] = $bb_cfg['tor_icons'][$new_status] .' <b> '. $lang['TOR_STATUS_NAME'][$new_status]. '</b> &middot; '. profile_url($userdata) .' &middot; <i>'. delta_time(TIMENOW) . $lang['BACK']. '</i>';