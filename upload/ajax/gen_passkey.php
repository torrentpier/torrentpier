<?php

if (!defined('IN_AJAX')) die(basename(__FILE__));

global $userdata, $lang;

$req_uid = (int) $this->request['user_id'];

if ($req_uid == $userdata['user_id'] || IS_ADMIN)
{
	if (empty($this->request['confirmed']))
	{
		$this->prompt_for_confirm($lang['BT_GEN_PASSKEY_NEW']);
	}

	if (!$passkey = generate_passkey($req_uid, IS_ADMIN))
	{
		$this->ajax_die('Could not insert passkey');
	}

	tracker_rm_user($req_uid);

	$this->response['passkey'] = $passkey;
}
else $this->ajax_die($lang['NOT_AUTHORISED']);