<?php

define('IN_FORUM', true);
define('BB_SCRIPT', 'online');
define('BB_ROOT', './');
require(BB_ROOT .'common.php');

// Start session management
$user->session_start(array('req_login' => true));
$page_cfg['use_tablesorter'] = true;

//
// Output page header and load viewonline template
//
$template->assign_vars(array(
	'PAGE_TITLE' => $lang['WHOSONLINE'],
));

//
// Get auth data
//
$is_auth_ary = array();
$is_auth_ary = auth(AUTH_VIEW, AUTH_LIST_ALL, $userdata);

//
// Get user list
//
$sql = "SELECT u.user_id, u.username, u.user_opt, u.user_rank, s.session_logged_in, s.session_time, s.session_ip
	FROM ".BB_USERS." u, ".BB_SESSIONS." s
	WHERE u.user_id = s.session_user_id
		AND s.session_time >= ".( TIMENOW - 300 ) . "
	ORDER BY u.username ASC, s.session_ip ASC";
if ( !($result = DB()->sql_query($sql)) )
{
	message_die(GENERAL_ERROR, 'Could not obtain regd user/online information', '', __LINE__, __FILE__, $sql);
}

$guest_users = 0;
$registered_users = 0;
$hidden_users = 0;

$reg_counter = 0;
$guest_counter = 0;
$prev_user = 0;
$prev_ip = '';

$user_id = 0;

while ( $row = DB()->sql_fetchrow($result) )
{
	$view_online = false;

	if ( $row['session_logged_in'] )
	{
		$user_id = $row['user_id'];

		if ( $user_id != $prev_user )
		{
			$username = profile_url($row);

			if ( bf($row['user_opt'], 'user_opt', 'allow_viewonline') )
			{
				$view_online = IS_AM;
				$hidden_users++;

				$username = '<i>' . $username . '</i>';
			}
			else
			{
				$view_online = true;
				$registered_users++;
			}

			$which_counter = 'reg_counter';
			$which_row = 'reg_user_row';
			$prev_user = $user_id;
		}
	}
	else
	{
		if ( $row['session_ip'] != $prev_ip )
		{
			$username = $lang['GUEST'];
			$view_online = true;
			$guest_users++;

			$which_counter = 'guest_counter';
			$which_row = 'guest_user_row';
		}
	}

	$prev_ip = $row['session_ip'];
	$user_ip = hexdec(substr($prev_ip, 0, 2)) . '.' . hexdec(substr($prev_ip, 2, 2)) . '.' . hexdec(substr($prev_ip, 4, 2)) . '.' . hexdec(substr($prev_ip, 6, 2));

	if ( $view_online )
	{
		$row_class = !($which_counter % 2) ? 'row1' : 'row2';

		$template->assign_block_vars("$which_row", array(
			'ROW_CLASS'  => $row_class,
			'USER'       => $username,
			'LASTUPDATE' => bb_date($row['session_time']),
			'LASTUPDATE_RAW' => $row['session_time'],
			'USERIP'     => $user_ip,
			'U_WHOIS_IP' => $bb_cfg['whois_info'] . $user_ip,
		));

		$which_counter++;
	}
}

$template->assign_vars(array(
	'TOTAL_USERS_ONLINE' => $registered_users + $hidden_users + $guest_users,
	'TOTAL_REGISTERED_USERS_ONLINE' => sprintf($lang['REG_USERS_ONLINE'], $registered_users) . sprintf($lang['HIDDEN_USERS_ONLINE'], $hidden_users),
	'TOTAL_GUEST_USERS_ONLINE' => sprintf($lang['GUEST_USERS_ONLINE'], $guest_users))
);

print_page('viewonline.tpl');
