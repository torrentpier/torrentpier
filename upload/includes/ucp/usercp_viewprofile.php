<?php

if ( !defined('IN_PHPBB') )
{
	die("Hacking attempt");
	exit;
}

require(INC_DIR .'bbcode.php');

$datastore->enqueue(array(
	'ranks',
));

if (!$userdata['session_logged_in'])
{
	redirect(append_sid("login.php?redirect={$_SERVER['REQUEST_URI']}", TRUE));
}
if ( empty($_GET[POST_USERS_URL]) || $_GET[POST_USERS_URL] == ANONYMOUS )
{
	message_die(GENERAL_MESSAGE, $lang['NO_USER_ID_SPECIFIED']);
}
$profiledata = get_userdata($_GET[POST_USERS_URL]);

if (!$profiledata)
{
	message_die(GENERAL_MESSAGE, $lang['NO_USER_ID_SPECIFIED']);
}

//
// Calculate the number of days this user has been a member ($memberdays)
// Then calculate their posts per day
//
$regdate = $profiledata['user_regdate'];
$memberdays = max(1, round( ( time() - $regdate ) / 86400 ));
$posts_per_day = $profiledata['user_posts'] / $memberdays;

// Get the users percentage of total posts
if ( $profiledata['user_posts'] != 0  )
{
	$total_posts = get_db_stat('postcount');
	$percentage = ( $total_posts ) ? min(100, ($profiledata['user_posts'] / $total_posts) * 100) : 0;
}
else
{
	$percentage = 0;
}
$avatar_img = get_avatar($profiledata['user_avatar'], $profiledata['user_avatar_type'], !bf($profiledata['user_opt'], 'user_opt', 'allow_avatar'));

if (!$ranks = $datastore->get('ranks'))
{
	$datastore->update('ranks');
	$ranks = $datastore->get('ranks');
}
$poster_rank = $rank_image = $rank_select = '';

if ($user_rank = $profiledata['user_rank'] AND isset($ranks[$user_rank]))
{
	$rank_image = ($ranks[$user_rank]['rank_image']) ? '<img src="'. $ranks[$user_rank]['rank_image'] .'" alt="" title="" border="0" />' : '';
	$poster_rank = $ranks[$user_rank]['rank_title'];
}

if (IS_ADMIN)
{
	$rank_select = array($lang['NO'] => 0);
	foreach ($ranks as $row)
	{
		$rank_select[$row['rank_title']] = $row['rank_id'];
	}
	$rank_select = build_select('rank-sel', $rank_select, $user_rank);
}

$temp_url = append_sid("privmsg.php?mode=post&amp;" . POST_USERS_URL . "=" . $profiledata['user_id']);
$pm_img = '<a href="' . $temp_url . '"><img src="' . $images['icon_pm'] . '" alt="' . $lang['SEND_PRIVATE_MESSAGE'] . '" title="' . $lang['SEND_PRIVATE_MESSAGE'] . '" border="0" /></a>';

$location = ($profiledata['user_from']) ? $profiledata['user_from'] : '';

$pm = '<a href="' . $temp_url . '">' . $lang['SEND_PRIVATE_MESSAGE'] . '</a>';

if ( bf($profiledata['user_opt'], 'user_opt', 'viewemail') || IS_ADMIN )
{
	$email_uri = ( $bb_cfg['board_email_form'] ) ? append_sid("profile.php?mode=email&amp;" . POST_USERS_URL .'=' . $profiledata['user_id']) : 'mailto:' . $profiledata['user_email'];
	$email_img = '<a href="' . $email_uri . '"><img src="' . $images['icon_email'] . '" alt="' . $lang['SEND_EMAIL'] . '" title="' . $lang['SEND_EMAIL'] . '" border="0" /></a>';
	$email = '<a href="' . $email_uri . '">' . $lang['SEND_EMAIL'] . '</a>';
}
else
{
	$email_img = '';
	$email = '';
}
$www_img = ( $profiledata['user_website'] ) ? '<a href="' . $profiledata['user_website'] . '" target="_userwww"><img src="' . $images['icon_www'] . '" alt="' . $lang['VISIT_WEBSITE'] . '" title="' . $lang['VISIT_WEBSITE'] . '" border="0" /></a>' : '';
$www = ( $profiledata['user_website'] ) ? '<a href="' . $profiledata['user_website'] . '" target="_userwww">' . $profiledata['user_website'] . '</a>' : '';
if ( !empty($profiledata['user_icq']) )
{
	$icq_status_img = '<a href="http://wwp.icq.com/' . $profiledata['user_icq'] . '#pager"><img src="http://web.icq.com/whitepages/online?icq=' . $profiledata['user_icq'] . '&img=5" width="18" height="18" border="0" /></a>';
	$icq_img = '<a href="http://www.icq.com/people/searched=1&uin=' . $profiledata['user_icq'] . '"><img src="' . $images['icon_icq'] . '" alt="' . $lang['ICQ'] . '" title="' . $lang['ICQ'] . '" border="0" /></a>';
	$icq =  '<a href="http://www.icq.com/people/' . $profiledata['user_icq'] . '">' . $profiledata['user_icq'] . '</a>';
}
else
{
	$icq_status_img = '';
	$icq_img = '';
	$icq = '';
}
$temp_url = append_sid("search.php?search_author=1&amp;uid={$profiledata['user_id']}");
$search_img = '<a href="' . $temp_url . '"><img src="' . $images['icon_search'] . '" alt="' . $lang['SEARCH_USER_POSTS'] . '" title="' . sprintf($lang['SEARCH_USER_POSTS'], $profiledata['username']) . '" border="0" /></a>';
$search = '<a href="' . $temp_url . '">' . sprintf($lang['SEARCH_USER_POSTS'], $profiledata['username']) . '</a>';

// Report
//
// Get report user module and create report link
//
include(INC_DIR . "functions_report.php");
$report_user = report_modules('name', 'report_user');

if ($report_user && $report_user->auth_check('auth_write'))
{
	$template->assign_block_vars('switch_report_user', array());
	$template->assign_vars(array(
		'U_REPORT_USER' => append_sid("report.php?mode=" . $report_user->mode . '&amp;id=' . $profiledata['user_id']),
		'L_REPORT_USER' => $report_user->lang['WRITE_REPORT'])
	);
}
// Report [END]

//
// Generate page
//
if ($profiledata['user_id'] == $userdata['user_id'] || IS_ADMIN)
{
	require(BB_ROOT .'attach_mod/attachment_mod.php');
	display_upload_attach_box_limits($profiledata['user_id']);
}

// IP Mod (c) Pandora
// Не админ у админа инфу смотреть не может
if ($profiledata['user_level'] == ADMIN && !IS_ADMIN)
{
	$reg_ip	= $last_ip = 'скрыт';
// Модератор у модератора, ИП не может смотерть (шифруемся)
} elseif ($profiledata['user_level'] == MOD && IS_MOD)
{
	$reg_ip	= $last_ip = 'скрыт';
// В иных случаях может
} else {
	$reg_ip	= decode_ip($profiledata['user_reg_ip']);
	$last_ip = decode_ip($profiledata['user_last_ip']);
}
// IP Mod End

$signature = ($bb_cfg['allow_sig'] && $profiledata['user_sig']) ? $profiledata['user_sig'] : '';

if(bf($profiledata['user_opt'], 'user_opt', 'allow_sig'))
{
	if($profiledata['user_id'] == $userdata['user_id'])
	{		$signature = $lang['SIGNATURE_DISABLE'];
	}
	else
	{		$signature = '';	}
}
else if ($signature)
{
	$signature = bbcode2html($signature);
}

$template->assign_vars(array(
	'PAGE_TITLE' 	=> sprintf($lang['VIEWING_USER_PROFILE'], $profiledata['username']),
	'USERNAME' 		=> $profiledata['username'],
	'PROFILE_USER_ID' => $profiledata['user_id'],
	'USER_REGDATE' 	=> bb_date($profiledata['user_regdate']),
	'POSTER_RANK' 	=> $poster_rank,
	'RANK_IMAGE' 	=> $rank_image,
	'RANK_SELECT' 	=> $rank_select,
	'POSTS_PER_DAY' => $posts_per_day,
	'POSTS' 		=> $profiledata['user_posts'],
	'PERCENTAGE' 	=> $percentage . '%',
	'POST_DAY_STATS' => sprintf($lang['USER_POST_DAY_STATS'], $posts_per_day),
	'POST_PERCENT_STATS' => sprintf($lang['USER_POST_PCT_STATS'], $percentage),
	'SEARCH_IMG' => $search_img,
	'SEARCH' 	=> $search,
	'PM_IMG' 	=> $pm_img,
	'PM' 		=> $pm,
	'EMAIL_IMG' => $email_img,
	'EMAIL' 	=> $email,
	'WWW_IMG' 	=> $www_img,
	'WWW' 		=> $www,
	'ICQ_STATUS_IMG' => $icq_status_img,
	'ICQ_IMG' 	=> $icq_img,
	'ICQ' 		=> $icq,
	'LAST_VISIT_TIME' => ($profiledata['user_lastvisit']) ? bb_date($profiledata['user_lastvisit']) : $lang['NEVER'],
	'LAST_ACTIVITY_TIME' => ($profiledata['user_session_time']) ? bb_date($profiledata['user_session_time']) : $lang['NEVER'],
	'LOCATION' => $location,

	'REG_IP'    => $reg_ip,
	'LAST_IP'   => $last_ip,

	'USER_ACTIVE' 	=> $profiledata['user_active'],

	'OCCUPATION' 	=> ( $profiledata['user_occ'] ) ? $profiledata['user_occ'] : '',
	'INTERESTS' 	=> ( $profiledata['user_interests'] ) ? $profiledata['user_interests'] : '',
	'GENDER'        => ( $profiledata['user_gender'] ) ? $lang['GENDER_SELECT'][$profiledata['user_gender']] : '',
	'BIRTHDAY'      => ( $profiledata['user_birthday'] ) ? realdate($profiledata['user_birthday'], 'Y-m-d') : '',
	'AGE'           => ( $profiledata['user_birthday'] ) ? bb_date(TIMENOW, 'Y') - realdate($profiledata['user_birthday'], 'Y') : '',
	'AVATAR_IMG' 	=> $avatar_img,

	'L_VIEWING_PROFILE' => sprintf($lang['VIEWING_USER_PROFILE'], $profiledata['username']),
	'L_ABOUT_USER_PROFILE' 	=> sprintf($lang['ABOUT_USER'], $profiledata['username']),
	'L_SEARCH_USER_POSTS_PROFILE'  => sprintf($lang['SEARCH_USER_POSTS'], '<b>'. $profiledata['username'] .'</b>'),

	'U_SEARCH_USER'     => "search.php?search_author=1&amp;uid={$profiledata['user_id']}",
	'U_SEARCH_TOPICS'   => "search.php?uid={$profiledata['user_id']}&amp;myt=1",
	'U_SEARCH_RELEASES' => "tracker.php?rid={$profiledata['user_id']}#results",
	'L_SEARCH_RELEASES' => $lang['SEARCH_USER_RELEASES'],

	'S_PROFILE_ACTION'  => "profile.php",

	'SIGNATURE'  => $signature,
));

//bt
// Show users torrent-profile
define('IN_VIEWPROFILE', TRUE);
include(INC_DIR .'ucp/torrent_userprofile.php');
//bt end

$template->assign_vars(array(
	'SHOW_ACCESS_PRIVILEGE' => IS_ADMIN,
	'L_ACCESS'              => $lang['ACCESS'],
	'L_ACCESS_SRV_LOAD'     => $lang['ACCESS_SRV_LOAD'],
	'IGNORE_SRV_LOAD'       => ($profiledata['user_level'] != USER || $profiledata['ignore_srv_load']) ? $lang['NO'] : $lang['YES'],
	'IGNORE_SRV_LOAD_EDIT'  => ($profiledata['user_level'] == USER),
));

if (IS_ADMIN)
{
	$template->assign_vars(array(
		'EDITABLE_TPLS' => true,

		'U_MANAGE'      => "profile.php?mode=editprofile&amp;u={$profiledata['user_id']}",
		'U_PERMISSIONS' => "admin/admin_ug_auth.php?mode=user&amp;u={$profiledata['user_id']}",
	));

	$ajax_user_opt = bb_json_encode(array(
		'allow_avatar'     => bf($profiledata['user_opt'], 'user_opt', 'allow_avatar'),
		'allow_sig'        => bf($profiledata['user_opt'], 'user_opt', 'allow_sig'),
		'allow_passkey'    => bf($profiledata['user_opt'], 'user_opt', 'allow_passkey'),
		'allow_pm'         => bf($profiledata['user_opt'], 'user_opt', 'allow_pm'),
		'allow_post'       => bf($profiledata['user_opt'], 'user_opt', 'allow_post'),
		'allow_post_edit'  => bf($profiledata['user_opt'], 'user_opt', 'allow_post_edit'),
		'allow_topic'      => bf($profiledata['user_opt'], 'user_opt', 'allow_topic'),
	));

	$template->assign_vars(array(
		'EDITABLE_TPLS'    => true,
		'AJAX_USER_OPT'    => $ajax_user_opt,
		'EMAIL_ADDRESS'    => htmlCHR($profiledata['user_email']),
	));
}
else
{
	$user_restrictions = array();

	$template->assign_var('USER_RESTRICTIONS', join('</li><li>', $user_restrictions));
}

print_page('usercp_viewprofile.tpl');
