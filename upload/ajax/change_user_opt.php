<?php

if (!defined('IN_AJAX')) die(basename(__FILE__));

global $bf, $lang;

$user_id = (int) $this->request['user_id'];
$new_opt = bb_json_decode($this->request['user_opt']);

if (!$user_id OR !$u_data = get_userdata($user_id))
{
	$this->ajax_die('invalid user_id');
}

if (!is_array($new_opt))
{
	$this->ajax_die('invalid new_opt');
}

foreach ($bf['user_opt'] as $opt_name => $opt_bit)
{
	if (isset($new_opt[$opt_name]))
	{
		setbit($u_data['user_opt'], $opt_bit, !empty($new_opt[$opt_name]));
	}
}

DB()->query("UPDATE ". BB_USERS ." SET user_opt = {$u_data['user_opt']} WHERE user_id = $user_id LIMIT 1");

// Удаляем данные из кеша
cache_rm_user_sessions ($user_id);

$this->response['resp_html'] = $lang['SAVED'];