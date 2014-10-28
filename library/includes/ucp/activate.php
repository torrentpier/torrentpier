<?php

if (!defined('IN_FORUM')) die('Hacking attempt');

if (empty($_GET['u']) || empty($_GET['act_key']))
{
	bb_die('Bad request');
}

$sql = "SELECT user_active, user_id, username, user_email, user_newpasswd, user_lang, user_actkey
	FROM " . BB_USERS . "
	WHERE user_id = " . intval($_GET[POST_USERS_URL]);
if (!($result = DB()->sql_query($sql)))
{
	bb_die('Could not obtain user information');
}

if ($row = DB()->sql_fetchrow($result))
{
	if ($row['user_active'] && trim($row['user_actkey']) == '')
	{
		bb_die($lang['ALREADY_ACTIVATED']);
	}
	else if ((trim($row['user_actkey']) == trim($_GET['act_key'])) && (trim($row['user_actkey']) != ''))
	{
		$sql_update_pass = ( $row['user_newpasswd'] != '' ) ? ", user_password = '" . md5(md5($row['user_newpasswd'])) . "', user_newpasswd = ''" : '';

		$sql = "UPDATE " . BB_USERS . "
			SET user_active = 1, user_actkey = ''" . $sql_update_pass . "
			WHERE user_id = " . $row['user_id'];
		if (!($result = DB()->sql_query($sql)))
		{
			bb_die('Could not update users table');
		}

		$message = ( $sql_update_pass == '' ) ? $lang['ACCOUNT_ACTIVE'] : $lang['PASSWORD_ACTIVATED'];
		bb_die($message);
	}
	else
	{
		bb_die($lang['WRONG_ACTIVATION']);
	}
}
else
{
	bb_die($lang['NO_SUCH_USER']);
}