<?php

// ACP Header - START
if (!empty($setmodules))
{
	$module['General']['Mass_Email'] = basename(__FILE__);
	return;
}
require('./pagestart.php');
// ACP Header - END

@set_time_limit(1200);

$message = '';
$subject = '';

//
// Do the job ...
//
if ( isset($_POST['submit']) )
{
	$subject = stripslashes(trim($_POST['subject']));
	$message = stripslashes(trim($_POST['message']));

	$error = FALSE;
	$error_msg = '';

	if ( empty($subject) )
	{
		$error = true;
		$error_msg .= ( !empty($error_msg) ) ? '<br />' . $lang['EMPTY_SUBJECT'] : $lang['EMPTY_SUBJECT'];
	}

	if ( empty($message) )
	{
		$error = true;
		$error_msg .= ( !empty($error_msg) ) ? '<br />' . $lang['EMPTY_MESSAGE'] : $lang['EMPTY_MESSAGE'];
	}

	$group_id = intval($_POST[POST_GROUPS_URL]);

	$sql = ( $group_id != -1 ) ? "SELECT u.user_email FROM " . BB_USERS . " u, " . BB_USER_GROUP . " ug WHERE ug.group_id = $group_id AND ug.user_pending <> 1 AND u.user_id = ug.user_id" : "SELECT user_email FROM " . BB_USERS;
	if ( !($result = DB()->sql_query($sql)) )
	{
		message_die(GENERAL_ERROR, 'Could not select group members', '', __LINE__, __FILE__, $sql);
	}

	if ( $row = DB()->sql_fetchrow($result) )
	{
		$bcc_list = array();
		do
		{
			$bcc_list[] = $row['user_email'];
		}
		while ( $row = DB()->sql_fetchrow($result) );

		DB()->sql_freeresult($result);
	}
	else
	{
		$message = ( $group_id != -1 ) ? $lang['GROUP_NOT_EXIST'] : $lang['NO_SUCH_USER'];

		$error = true;
		$error_msg .= ( !empty($error_msg) ) ? '<br />' . $message : $message;
	}

	if ( !$error )
	{
		include(INC_DIR . 'emailer.class.php');

		//
		// Let's do some checking to make sure that mass mail functions
		// are working in win32 versions of php.
		//
		if ( preg_match('/[c-z]:\\\.*/i', getenv('PATH')) && !$bb_cfg['smtp_delivery'])
		{
			$ini_val = ( @phpversion() >= '4.0.0' ) ? 'ini_get' : 'get_cfg_var';

			// We are running on windows, force delivery to use our smtp functions
			// since php's are broken by default
			$bb_cfg['smtp_delivery'] = 1;
			$bb_cfg['smtp_host'] = @$ini_val('SMTP');
		}

		$emailer = new emailer($bb_cfg['smtp_delivery']);

		$emailer->from($bb_cfg['board_email']);
		$emailer->replyto($bb_cfg['board_email']);

		for ($i = 0; $i < count($bcc_list); $i++)
		{
			$emailer->bcc($bcc_list[$i]);
		}

		$email_headers = 'X-AntiAbuse: Board servername - ' . $bb_cfg['server_name'] . "\n";
		$email_headers .= 'X-AntiAbuse: User_id - ' . $userdata['user_id'] . "\n";
		$email_headers .= 'X-AntiAbuse: Username - ' . $userdata['username'] . "\n";
		$email_headers .= 'X-AntiAbuse: User IP - ' . CLIENT_IP . "\n";

		$emailer->use_template('admin_send_email');
		$emailer->email_address($bb_cfg['board_email']);
		$emailer->set_subject($subject);
		$emailer->extra_headers($email_headers);

		$emailer->assign_vars(array(
			'SITENAME' => $bb_cfg['sitename'],
			'BOARD_EMAIL' => $bb_cfg['board_email'],
			'MESSAGE' => $message)
		);
		$emailer->send();
		$emailer->reset();

		message_die(GENERAL_MESSAGE, $lang['EMAIL_SENT'] . '<br /><br />' . sprintf($lang['CLICK_RETURN_ADMIN_INDEX'],  '<a href="index.php?pane=right">', '</a>'));
	}
}

if ( @$error )
{
	$template->assign_vars(array('ERROR_MESSAGE' => $error_msg));
}

//
// Initial selection
//

$sql = "SELECT group_id, group_name
	FROM ".BB_GROUPS . "
	WHERE group_single_user <> 1";
if ( !($result = DB()->sql_query($sql)) )
{
	message_die(GENERAL_ERROR, 'Could not obtain list of groups', '', __LINE__, __FILE__, $sql);
}

$select_list = '<select name = "' . POST_GROUPS_URL . '"><option value = "-1">' . $lang['ALL_USERS'] . '</option>';
if ( $row = DB()->sql_fetchrow($result) )
{
	do
	{
		$select_list .= '<option value = "' . $row['group_id'] . '">' . $row['group_name'] . '</option>';
	}
	while ( $row = DB()->sql_fetchrow($result) );
}
$select_list .= '</select>';

//
// Generate page
//
require(PAGE_HEADER);

$template->assign_vars(array(
	'MESSAGE' => $message,
	'SUBJECT' => $subject,

	'L_NOTICE' => @$notice,

	'S_USER_ACTION' => 'admin_mass_email.php',
	'S_GROUP_SELECT' => $select_list)
);

print_page('admin_mass_email.tpl', 'admin');