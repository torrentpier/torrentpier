<?php

if (!defined('IN_AJAX')) die(basename(__FILE__));

global $bb_cfg;

global $userdata, $bb_cfg, $lang;

if (!isset($this->request['attach_id']))
{
	$this->ajax_die('empty attach_id');
}
if (!isset($this->request['status']))
{
	$this->ajax_die('empty tor_status');
}
$attach_id  = (int) $this->request['attach_id'];
$new_status = (int) $this->request['status'];

// Валидность статуса
if (!isset($lang['tor_status'][$new_status]))
{
	$this->ajax_die("Такого статуса не существует: $new_status");
}

$tor = DB()->fetch_row("
	SELECT
		tor.forum_id, tor.topic_id, tor.tor_status, tor.checked_time, tor.checked_user_id, f.cat_id
	FROM       ". BB_BT_TORRENTS ." tor
	INNER JOIN ". BB_FORUMS      ." f ON(f.forum_id = tor.forum_id)
	WHERE tor.attach_id = $attach_id
	LIMIT 1
");
if (!$tor) $this->ajax_die('torrent not found');

// Тот же статус
if ($tor['tor_status'] == $new_status)
{
	$this->ajax_die('Раздача имеет тот же статус');
}
// Запрет на изменение/присвоение CH-статуса модератором
if ($new_status == TOR_CLOSED_CPHOLD && !(IS_ADMIN || IS_CP_HOLDER))
{
	$this->ajax_die('Изменение статуса невозможно');
}

// Права на изменение статуса
if ($tor['tor_status'] == TOR_CLOSED_CPHOLD)
{
	if (!(IS_ADMIN || IS_CP_HOLDER))
	{
		$this->verify_mod_rights($tor['forum_id']);
	}
	DB()->query("UPDATE ". BB_TOPICS ." SET topic_status = ". TOPIC_UNLOCKED ." WHERE topic_id = {$tor['topic_id']} LIMIT 1");
	$new_status = TOR_NOT_APPROVED;
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
		$msg  = "Раздача имеет статус: {$lang['tor_status'][$tor['tor_status']]}\n\n";
		$msg .= ($username = get_username($tor['checked_user_id'])) ? "Статус изменен: ". html_entity_decode($username) .", ". delta_time($tor['checked_time']) ." назад\n\n" : "";
		$msg .= "Продолжить?";
		$this->prompt_for_confirm($msg);
	}
}

change_tor_status($attach_id, $new_status);

$this->response['attach_id'] = $attach_id;
$this->response['status']    = $bb_cfg['tor_icons'][$new_status] .' '. $lang['tor_status'][$new_status];