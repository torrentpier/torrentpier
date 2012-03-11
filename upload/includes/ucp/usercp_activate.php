<?php

if ( !defined('IN_PHPBB') )
{
	die('Hacking attempt');
	exit;
}

if (empty($_GET['u']) || empty($_GET['act_key']))
{
	bb_die('Bad request');
}

$sql = "SELECT user_active, user_id, username, user_email, user_newpasswd, user_lang, user_actkey
	FROM " . BB_USERS . "
	WHERE user_id = " . intval($_GET[POST_USERS_URL]);
if ( !($result = DB()->sql_query($sql)) )
{
	message_die(GENERAL_ERROR, 'Could not obtain user information', '', __LINE__, __FILE__, $sql);
}

if ( $row = DB()->sql_fetchrow($result) )
{
	if ( $row['user_active'] && trim($row['user_actkey']) == '' )
	{
		message_die(GENERAL_MESSAGE, $lang['ALREADY_ACTIVATED']);
	}
	else if ((trim($row['user_actkey']) == trim($_GET['act_key'])) && (trim($row['user_actkey']) != ''))
	{
		if (intval($bb_cfg['require_activation']) == USER_ACTIVATION_ADMIN && $row['user_newpasswd'] == '')
		{
			if (!$userdata['session_logged_in'])
			{
				redirect('login.php?redirect=profile.php&mode=activate&' . POST_USERS_URL . '=' . $row['user_id'] . '&act_key=' . trim($_GET['act_key']));
			}
			else if (!IS_ADMIN)
			{
				message_die(GENERAL_MESSAGE, $lang['NOT_AUTHORISED']);
			}
		}

		$sql_update_pass = ( $row['user_newpasswd'] != '' ) ? ", user_password = '" . DB()->escape($row['user_newpasswd']) . "', user_newpasswd = ''" : '';

		$sql = "UPDATE " . BB_USERS . "
			SET user_active = 1, user_actkey = ''" . $sql_update_pass . "
			WHERE user_id = " . $row['user_id'];
		if ( !($result = DB()->sql_query($sql)) )
		{
			message_die(GENERAL_ERROR, 'Could not update users table', '', __LINE__, __FILE__, $sql_update);
		}

		if ( intval($bb_cfg['require_activation']) == USER_ACTIVATION_ADMIN && $sql_update_pass == '' )
		{
			include(INC_DIR . 'emailer.class.php');
			$emailer = new emailer($bb_cfg['smtp_delivery']);

			$emailer->from($bb_cfg['board_email']);
			$emailer->replyto($bb_cfg['board_email']);

			$emailer->use_template('admin_welcome_activated', $row['user_lang']);
			$emailer->email_address($row['user_email']);
			$emailer->set_subject($lang['ACCOUNT_ACTIVATED_SUBJECT']);

			$emailer->assign_vars(array(
				'SITENAME' => $bb_cfg['sitename'],
				'USERNAME' => $row['username'],
				'PASSWORD' => $password_confirm,
				'EMAIL_SIG' => (!empty($bb_cfg['board_email_sig'])) ? str_replace('<br />', "\n", "-- \n" . $bb_cfg['board_email_sig']) : '')
			);
			$emailer->send();
			$emailer->reset();

			message_die(GENERAL_MESSAGE, $lang['ACCOUNT_ACTIVE_ADMIN']);
		}
		else
		{
			$message = ( $sql_update_pass == '' ) ? $lang['ACCOUNT_ACTIVE'] : $lang['PASSWORD_ACTIVATED'];
			message_die(GENERAL_MESSAGE, $message);
		}
	}
	else
	{
		message_die(GENERAL_MESSAGE, $lang['WRONG_ACTIVATION']);
	}
}
else
{
	message_die(GENERAL_MESSAGE, $lang['NO_SUCH_USER']);
}