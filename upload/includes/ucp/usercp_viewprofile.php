<?php

if (!defined('BB_ROOT')) die(basename(__FILE__));

require(INC_DIR .'bbcode.php');

$datastore->enqueue(array(
	'ranks',
));

if (empty($_GET[POST_USERS_URL]) || $_GET[POST_USERS_URL] == ANONYMOUS)
{
	bb_die($lang['NO_USER_ID_SPECIFIED']);
}
if (!$profiledata = get_userdata($_GET[POST_USERS_URL]))
{
	bb_die($lang['NO_USER_ID_SPECIFIED']);
}

if(bf($profiledata['user_opt'], 'user_opt', 'view_profile'))
{	meta_refresh(append_sid("login.php?redirect={$_SERVER['REQUEST_URI']}", true));
    bb_die("Пользователь {$profiledata['username']} запретил гостям просмотр своего профиля");}

//
// Calculate the number of days this user has been a member ($memberdays)
// Then calculate their posts per day
//
$regdate = $profiledata['user_regdate'];
$memberdays = max(1, round((TIMENOW - $regdate) / 86400));
$posts_per_day = $profiledata['user_posts'] / $memberdays;

// Get the users percentage of total posts
if ($profiledata['user_posts'] != 0)
{
	$total_posts = get_db_stat('postcount');
	$percentage = ($total_posts) ? min(100, ($profiledata['user_posts'] / $total_posts) * 100) : 0;
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

if (bf($profiledata['user_opt'], 'user_opt', 'viewemail') || IS_ADMIN)
{
	$email_uri = ($bb_cfg['board_email_form']) ? append_sid('profile.php?mode=email&amp;'. POST_USERS_URL .'='. $profiledata['user_id']) : 'mailto:'. $profiledata['user_email'];
	$email = '<a class="editable" href="'. $email_uri .'">'. $profiledata['user_email'] .'</a>';
}
else
{
	$email = '';
}

$temp_url = append_sid("search.php?search_author=1&amp;uid={$profiledata['user_id']}");
$search = '<a href="'. $temp_url .'">'. sprintf($lang['SEARCH_USER_POSTS'], $profiledata['username']) .'</a>';

// Report
//
// Get report user module and create report link
//
include(INC_DIR ."functions_report.php");
$report_user = report_modules('name', 'report_user');

if ($report_user && $report_user->auth_check('auth_write'))
{
	$template->assign_block_vars('switch_report_user', array());
	$template->assign_vars(array(
		'U_REPORT_USER' => append_sid('report.php?mode='. $report_user->mode .'&amp;id='. $profiledata['user_id']),
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
	'PERCENTAGE' 	=> $percentage .'%',
	'POST_DAY_STATS' => sprintf($lang['USER_POST_DAY_STATS'], $posts_per_day),
	'POST_PERCENT_STATS' => sprintf($lang['USER_POST_PCT_STATS'], $percentage),
	'SEARCH' 	=> $search,
	'PM' 		=> '<a href="'. append_sid('privmsg.php?mode=post&amp;'. POST_USERS_URL .'='. $profiledata['user_id']) .'">'. $lang['SEND_PRIVATE_MESSAGE'] .'</a>',
	'EMAIL' 	=> $email,
	'WWW' 		=> $profiledata['user_website'],
	'ICQ' 		=> $profiledata['user_icq'],
	'LAST_VISIT_TIME' => ($profiledata['user_lastvisit']) ? bb_date($profiledata['user_lastvisit']) : $lang['NEVER'],
	'LAST_ACTIVITY_TIME' => ($profiledata['user_session_time']) ? bb_date($profiledata['user_session_time']) : $lang['NEVER'],
	'LOCATION' => ($profiledata['user_from']) ? $profiledata['user_from'] : '',

	'USER_ACTIVE' 	=> $profiledata['user_active'],

	'OCCUPATION' 	=> $profiledata['user_occ'],
	'INTERESTS' 	=> $profiledata['user_interests'],
	'SKYPE'         => $profiledata['user_skype'],
	'GENDER'        => ($profiledata['user_gender']) ? $lang['GENDER_SELECT'][$profiledata['user_gender']] : '',
	'BIRTHDAY'      => ($bb_cfg['birthday']['enabled'] && $profiledata['user_birthday']) ? realdate($profiledata['user_birthday'], 'Y-m-d') : '',
	'AGE'           => ($bb_cfg['birthday']['enabled'] && $profiledata['user_birthday']) ? birthday_age($profiledata['user_birthday']) : '',
	'AVATAR_IMG' 	=> $avatar_img,

	'L_VIEWING_PROFILE' => sprintf($lang['VIEWING_USER_PROFILE'], $profiledata['username']),
	'L_ABOUT_USER_PROFILE' 	=> sprintf($lang['ABOUT_USER'], $profiledata['username']),
	'L_SEARCH_USER_POSTS_PROFILE'  => sprintf($lang['SEARCH_USER_POSTS'], '<b>'. $profiledata['username'] .'</b>'),

	'U_SEARCH_USER'     => "search.php?search_author=1&amp;uid={$profiledata['user_id']}",
	'U_SEARCH_TOPICS'   => "search.php?uid={$profiledata['user_id']}&amp;myt=1",
	'U_SEARCH_RELEASES' => "tracker.php?rid={$profiledata['user_id']}#results",

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
