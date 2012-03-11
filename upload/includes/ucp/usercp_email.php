<?php

if ( !defined('IN_PHPBB') )
{
	die("Hacking attempt");
	exit;
}

// Is send through board enabled? No, return to index
if (!$bb_cfg['board_email_form'])
{
	redirect("index.php");
}

if ( !empty($_GET[POST_USERS_URL]) || !empty($_POST[POST_USERS_URL]) )
{
	$user_id = ( !empty($_GET[POST_USERS_URL]) ) ? intval($_GET[POST_USERS_URL]) : intval($_POST[POST_USERS_URL]);
}
else
{
	message_die(GENERAL_MESSAGE, $lang['NO_USER_SPECIFIED']);
}

if ( !$userdata['session_logged_in'] )
{
	redirect("login.php?redirect=profile.php&mode=email&" . POST_USERS_URL . "=$user_id");
}

$sql = "SELECT username, user_email, user_lang
	FROM " . BB_USERS . "
	WHERE user_id = $user_id";
if ( $row = DB()->fetch_row($sql) )
{
	$username = $row['username'];
	$user_email = $row['user_email'];
	$user_lang = $row['user_lang'];

	if ( true || IS_ADMIN )  //  TRUE instead of missing user_opt "prevent_email"
	{
		if ( isset($_POST['submit']) )
		{
			$error = FALSE;

			if ( !empty($_POST['subject']) )
			{
				$subject = trim(stripslashes($_POST['subject']));
			}
			else
			{
				$error = TRUE;
				$error_msg = ( !empty($error_msg) ) ? $error_msg . '<br />' . $lang['EMPTY_SUBJECT_EMAIL'] : $lang['EMPTY_SUBJECT_EMAIL'];
			}

			if ( !empty($_POST['message']) )
			{
				$message = trim(stripslashes($_POST['message']));
			}
			else
			{
				$error = TRUE;
				$error_msg = ( !empty($error_msg) ) ? $error_msg . '<br />' . $lang['EMPTY_MESSAGE_EMAIL'] : $lang['EMPTY_MESSAGE_EMAIL'];
			}

			if ( !$error )
			{
				require(INC_DIR . 'emailer.class.php');
				$emailer = new emailer($bb_cfg['smtp_delivery']);

				$emailer->from($userdata['user_email']);
				$emailer->replyto($userdata['user_email']);

				$email_headers = 'X-AntiAbuse: Board servername - ' . $bb_cfg['server_name'] . "\n";
				$email_headers .= 'X-AntiAbuse: User_id - ' . $userdata['user_id'] . "\n";
				$email_headers .= 'X-AntiAbuse: Username - ' . $userdata['username'] . "\n";
				$email_headers .= 'X-AntiAbuse: User IP - ' . CLIENT_IP . "\n";

				$emailer->use_template('profile_send_email', $user_lang);
				$emailer->email_address($user_email);
				$emailer->set_subject($subject);
				$emailer->extra_headers($email_headers);

				$emailer->assign_vars(array(
					'SITENAME' => $bb_cfg['sitename'],
					'BOARD_EMAIL' => $bb_cfg['board_email'],
					'FROM_USERNAME' => $userdata['username'],
					'TO_USERNAME' => $username,
					'MESSAGE' => $message)
				);
				$emailer->send();
				$emailer->reset();

				if ( !empty($_POST['cc_email']) )
				{
					$emailer->from($userdata['user_email']);
					$emailer->replyto($userdata['user_email']);
					$emailer->use_template('profile_send_email');
					$emailer->email_address($userdata['user_email']);
					$emailer->set_subject($subject);

					$emailer->assign_vars(array(
						'SITENAME' => $bb_cfg['sitename'],
						'BOARD_EMAIL' => $bb_cfg['board_email'],
						'FROM_USERNAME' => $userdata['username'],
						'TO_USERNAME' => $username,
						'MESSAGE' => $message)
					);
					$emailer->send();
					$emailer->reset();
				}

				sleep(7);
				$message = $lang['EMAIL_SENT'] . '<br /><br />' . sprintf($lang['CLICK_RETURN_INDEX'],  '<a href="index.php">', '</a>');
				message_die(GENERAL_MESSAGE, $message);
			}
		}

		if (!empty($error))
		{
			$template->assign_vars(array('ERROR_MESSAGE' => $error_msg));
		}

		$template->assign_vars(array(
			'USERNAME' => $username,
			'S_HIDDEN_FIELDS' => '',
			'S_POST_ACTION' => "profile.php?mode=email&amp;" . POST_USERS_URL . "=$user_id",
		));

		print_page('usercp_email.tpl');
	}
	else
	{
		message_die(GENERAL_MESSAGE, $lang['USER_PREVENT_EMAIL']);
	}
}
else
{
	message_die(GENERAL_MESSAGE, $lang['USER_NOT_EXIST']);
}