<?php

if (!defined('IN_FORUM')) die("Hacking attempt");

// Is send through board enabled? No, return to index
if (!$bb_cfg['board_email_form'])
{
	redirect("index.php");
}

set_die_append_msg();

if ( !empty($_GET[POST_USERS_URL]) || !empty($_POST[POST_USERS_URL]) )
{
	$user_id = ( !empty($_GET[POST_USERS_URL]) ) ? intval($_GET[POST_USERS_URL]) : intval($_POST[POST_USERS_URL]);
}
else
{
	bb_die($lang['NO_USER_SPECIFIED']);
}

if ( !$userdata['session_logged_in'] )
{
	redirect(LOGIN_URL . "?redirect=profile.php&mode=email&" . POST_USERS_URL . "=$user_id");
}

$errors = array();

$sql = "SELECT username, user_id, user_rank, user_email, user_lang
	FROM " . BB_USERS . "
	WHERE user_id = $user_id
";

if ($row = DB()->fetch_row($sql))
{
	$username   = $row['username'];
	$user_email = $row['user_email'];
	$user_lang  = $row['user_lang'];

	if ( true || IS_ADMIN )  //  TRUE instead of missing user_opt "prevent_email"
	{
		if (isset($_POST['submit']))
		{
			$subject = trim(html_entity_decode($_POST['subject']));
			$message = trim(html_entity_decode($_POST['message']));

			if (!$subject) $errors[] = $lang['EMPTY_SUBJECT_EMAIL'];
			if (!$message) $errors[] = $lang['EMPTY_MESSAGE_EMAIL'];

			if (!$errors)
			{
				require(CLASS_DIR .'emailer.php');
				$emailer = new emailer($bb_cfg['smtp_delivery']);

				$emailer->from($userdata['username'] ." <{$userdata['user_email']}>");
				$emailer->email_address($username ." <$user_email>");

				$emailer->use_template('profile_send_email', $user_lang);
				$emailer->set_subject($subject);

				$emailer->assign_vars(array(
					'SITENAME'      => $bb_cfg['sitename'],
					'FROM_USERNAME' => $userdata['username'],
					'TO_USERNAME'   => $username,
					'MESSAGE'       => $message,
				));
				$emailer->send();
				$emailer->reset();

				bb_die($lang['EMAIL_SENT']);
			}
		}

		$template->assign_vars(array(
			'USERNAME' => profile_url($row),
			'S_HIDDEN_FIELDS' => '',
			'S_POST_ACTION' => "profile.php?mode=email&amp;" . POST_USERS_URL . "=$user_id",
			'ERROR_MESSAGE'	=> ($errors) ? join('<br />', array_unique($errors)) : '',
		));

		print_page('usercp_email.tpl');
	}
	else
	{
		bb_die($lang['USER_PREVENT_EMAIL']);
	}
}
else
{
	bb_die($lang['USER_NOT_EXIST']);
}