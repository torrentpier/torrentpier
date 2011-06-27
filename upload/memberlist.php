<?php

/*
	This file is part of TorrentPier

	TorrentPier is free software: you can redistribute it and/or modify
	it under the terms of the GNU General Public License as published by
	the Free Software Foundation, either version 3 of the License, or
	(at your option) any later version.

	TorrentPier is distributed in the hope that it will be useful,
	but WITHOUT ANY WARRANTY; without even the implied warranty of
	MERCHANTABILITY or FITNESS FOR A PARTICULAR PURPOSE.  See the
	GNU General Public License for more details.

	A copy of the GPL 2.0 should have been included with the program.
	If not, see http://www.gnu.org/licenses/

	Official SVN repository and contact information can be found at
	http://code.google.com/p/torrentpier/
 */

define('IN_PHPBB', true);
define('BB_SCRIPT', 'memberlist');
define('BB_ROOT', './');
require(BB_ROOT ."common.php");

$page_cfg['use_tablesorter'] = true;

$user->session_start(array('req_login' => true));

$start = abs(intval(request_var('start', 0)));
$mode  = (string) request_var('mode', 'joined');
$sort_order = (request_var('order', 'ASC') == 'ASC') ? 'ASC' : 'DESC';
$username   = request_var('username', '');
$paginationusername = $username;

//
// Memberlist sorting
//
$mode_types_text = array(
	$lang['SORT_JOINED'],
	$lang['SORT_USERNAME'],
	$lang['SORT_LOCATION'],
	$lang['SORT_POSTS'],
	$lang['SORT_EMAIL'],
	$lang['SORT_WEBSITE'],
	$lang['SORT_TOP_TEN']
);

$mode_types = array(
	'joined',
	'username',
	'location',
	'posts',
	'email',
	'website',
	'topten'
);

// <select> mode
$select_sort_mode = '<select name="mode">';

for ($i=0, $cnt=count($mode_types_text); $i < $cnt; $i++)
{
	$selected = ( $mode == $mode_types[$i] ) ? ' selected="selected"' : '';
	$select_sort_mode .= '<option value="' . $mode_types[$i] . '"' . $selected . '>' . $mode_types_text[$i] . '</option>';
}
$select_sort_mode .= '</select>';

// <select> order
$select_sort_order = '<select name="order">';

if ($sort_order == 'ASC')
{
	$select_sort_order .= '<option value="ASC" selected="selected">' . $lang['ASC'] . '</option><option value="DESC">' . $lang['DESC'] . '</option>';
}
else
{
	$select_sort_order .= '<option value="ASC">' . $lang['ASC'] . '</option><option value="DESC" selected="selected">' . $lang['DESC'] . '</option>';
}
$select_sort_order .= '</select>';

//
// Generate page
//
$template->assign_vars(array(
	'S_MODE_SELECT' => $select_sort_mode,
	'S_ORDER_SELECT' => $select_sort_order,
	'S_MODE_ACTION' => append_sid("memberlist.php"),
	'S_USERNAME' => $paginationusername,
));

switch( $mode )
{
	case 'joined':
		$order_by = "user_id $sort_order LIMIT $start, " . $bb_cfg['topics_per_page'];
		break;
	case 'username':
		$order_by = "username $sort_order LIMIT $start, " . $bb_cfg['topics_per_page'];
		break;
	case 'location':
		$order_by = "user_from $sort_order LIMIT $start, " . $bb_cfg['topics_per_page'];
		break;
	case 'posts':
		$order_by = "user_posts $sort_order LIMIT $start, " . $bb_cfg['topics_per_page'];
		break;
	case 'email':
		$order_by = "user_email $sort_order LIMIT $start, " . $bb_cfg['topics_per_page'];
		break;
	case 'website':
		$order_by = "user_website $sort_order LIMIT $start, " . $bb_cfg['topics_per_page'];
		break;
	case 'topten':
		$order_by = "user_posts $sort_order LIMIT 10";
		break;
	default:
		$order_by = "user_regdate $sort_order LIMIT $start, " . $bb_cfg['topics_per_page'];
		$mode = 'joined';
		break;
}

// per-letter selection
$by_letter = 'all';
$letters_range = 'a-z';
$letters_range .= iconv('windows-1251', 'UTF-8', chr(224));
$letters_range .= '-';
$letters_range .= iconv('windows-1251', 'UTF-8', chr(255));
$select_letter = $letter_sql = '';

$by_letter_req = (@$_REQUEST['letter']) ? strtolower(trim($_REQUEST['letter'])) : false;

if ($by_letter_req)
{
	if ($by_letter_req === 'all')
	{
		$by_letter = 'all';
		$letter_sql = '';
	}
	else if ($by_letter_req === 'others')
	{
		$by_letter = 'others';
		$letter_sql = "username REGEXP '^[!-@\\[-`].*$'";
	}
	else if ($letter_req = preg_replace("#[^$letters_range]#ui", '', iconv('windows-1251', 'UTF-8', $by_letter_req[0])))
	{
		$by_letter = DB()->escape($letter_req);
		$letter_sql = "LOWER(username) LIKE '$by_letter%'";
	}
}

// ENG
for ($i=ord('A'), $cnt=ord('Z'); $i <= $cnt; $i++)
{
	$select_letter .= ($by_letter == chr($i)) ? '<b>'. chr($i) .'</b>&nbsp;' : '<a class="genmed" href="'. append_sid("memberlist.php?letter=". chr($i) ."&amp;mode=$mode&amp;order=$sort_order") .'">'. chr($i) .'</a>&nbsp;';
}
// RUS
$select_letter .= ': ';
for ($i=224, $cnt=255; $i <= $cnt; $i++)
{
   $select_letter .= ($by_letter == iconv('windows-1251', 'UTF-8', chr($i))) ? '<b>'. iconv('windows-1251', 'UTF-8', chr($i-32)) .'</b>&nbsp;' : '<a class="genmed" href="'. append_sid("memberlist.php?letter=%". strtoupper(base_convert($i, 10, 16)) ."&amp;mode=$mode&amp;order=$sort_order") .'">'. iconv('windows-1251', 'UTF-8', chr($i-32)) .'</a>&nbsp;';
}

$select_letter .= ':&nbsp;';
$select_letter .= ($by_letter == 'others') ? '<b>'. $lang['OTHERS'] .'</b>&nbsp;' : '<a class="genmed" href="'. append_sid("memberlist.php?letter=others&amp;mode=$mode&amp;order=$sort_order") .'">'. $lang['OTHERS'] .'</a>&nbsp;';
$select_letter .= ':&nbsp;';
$select_letter .= ($by_letter == 'all') ? '<b>'. $lang['ALL'] .'</b>' : '<a class="genmed" href="'. append_sid("memberlist.php?letter=all&amp;mode=$mode&amp;order=$sort_order") .'">'. $lang['ALL'] .'</a>';

$template->assign_vars(array(
	'S_LETTER_SELECT'   => $select_letter,
	'S_LETTER_HIDDEN'   => '<input type="hidden" name="letter" value="'. $by_letter .'">',
));

// per-letter selection end
$sql = "SELECT username, user_id, user_opt, user_posts, user_regdate, user_from, user_from_flag, user_website, user_email, user_icq, user_avatar, user_avatar_type, user_allowavatar
         FROM ". BB_USERS ."
		 WHERE user_id NOT IN(". EXCLUDED_USERS_CSV .")";
if ( $username )
{
	$username = preg_replace('/\*/', '%', clean_username($username));
	$letter_sql = "username LIKE '". str_replace("\'", "''", $username) ."'";
}
$sql .= ($letter_sql) ? " AND $letter_sql" : '';
$sql .= " ORDER BY $order_by";

$result = DB()->sql_query($sql) OR message_die(GENERAL_ERROR, 'Could not query users', '', __LINE__, __FILE__, $sql);

if ( $row = DB()->sql_fetchrow($result) )
{
	$i = 0;
	do
	{
		$username = $row['username'];
		$user_id = $row['user_id'];
		$from = $row['user_from'];
// FLAGHACK-start
		$flag = ($row['user_from_flag'] && $row['user_from_flag'] != 'blank.gif') ? make_user_flag($row['user_from_flag']) : '';
// FLAGHACK-end

		$joined = bb_date($row['user_regdate'], $lang['DATE_FORMAT']);
		$posts = $row['user_posts'];
		$poster_avatar = false;

		if ($row['user_avatar_type'] && $user_id != ANONYMOUS && $row['user_allowavatar'])
		{
			switch ($row['user_avatar_type'])
			{
				case USER_AVATAR_UPLOAD:
					$poster_avatar = ($bb_cfg['allow_avatar_upload']) ? '<img src="'. $bb_cfg['avatar_path'] .'/'. $row['user_avatar'] .'" alt="" border="0" />' : false;
					break;
				case USER_AVATAR_REMOTE:
					$poster_avatar = ($bb_cfg['allow_avatar_remote']) ? '<img src="'. $row['user_avatar'] .'" alt="" border="0" />' : false;
					break;
				case USER_AVATAR_GALLERY:
					$poster_avatar = ($bb_cfg['allow_avatar_local']) ? '<img src="'. $bb_cfg['avatar_gallery_path'] .'/'. $row['user_avatar'] .'" alt="" border="0" />' : false;
					break;
			}
		}

		$pm = '<a class="txtb" href="'. append_sid("privmsg.php?mode=post&amp;". POST_USERS_URL ."=$user_id") .'">'. $lang['SEND_PM_TXTB'] .'</a>';
		$email = ($bb_cfg['board_email_form']) ? '<a class="txtb" href="'. append_sid("profile.php?mode=email&amp;". POST_USERS_URL ."=$user_id") .'">'. $lang['SEND_EMAIL_TXTB'] .'</a>' : false;
		$www = ($row['user_website']) ? '<a class="txtb" href="'. $row['user_website'] .'" target="_userwww">'. $lang['VISIT_WEBSITE_TXTB'] .'</a>' : false;

		$temp_url = append_sid("search.php?search_author=1&amp;uid=$user_id");
		$search_img = '<a href="' . $temp_url . '"><img src="' . $images['icon_search'] . '" alt="' . $lang['SEARCH_USER_POSTS'] . '" title="' . $lang['SEARCH_USER_POSTS'] . '" border="0" /></a>';
		$search = '<a href="' . $temp_url . '">' . $lang['SEARCH_USER_POSTS'] . '</a>';

		$row_class = !($i % 2) ? 'row1' : 'row2';

		$template->assign_block_vars('memberrow', array(
			'ROW_NUMBER'    => $i + ( $start + 1 ),
			'ROW_CLASS'     => $row_class,
			'USERNAME'      => $username,
			'FROM'          => $from,
			'FLAG'          => $flag,
			'JOINED_RAW'    => $row['user_regdate'],
			'JOINED'        => $joined,
			'POSTS'         => $posts,
			'AVATAR_IMG'    => $poster_avatar,
			'SEARCH'        => $search,
			'PM'            => $pm,
			'U_SEARCH_USER' => append_sid("search.php?mode=searchuser"),
			'EMAIL'         => $email,
			'WWW'           => $www,
			'U_VIEWPROFILE' => append_sid("profile.php?mode=viewprofile&amp;". POST_USERS_URL ."=$user_id"))
		);
		$i++;
	}
	while ( $row = DB()->sql_fetchrow($result) );
	DB()->sql_freeresult($result);
}
else
{
  	 $template->assign_block_vars('no_username', array(
       'NO_USER_ID_SPECIFIED' => $lang['NO_USER_ID_SPECIFIED']   )
 	 );
}
$paginationurl = "memberlist.php?mode=$mode&amp;order=$sort_order&amp;letter=$by_letter";
if ($paginationusername) $paginationurl .= "&amp;username=$paginationusername";
if ( $mode != 'topten' || $bb_cfg['topics_per_page'] < 10 )
{
	$sql = "SELECT COUNT(*) AS total FROM ". BB_USERS;
	$sql .=	($letter_sql) ? " WHERE $letter_sql" : '';
	if (!$result = DB()->sql_query($sql))
	{
		message_die(GENERAL_ERROR, 'Error getting total users', '', __LINE__, __FILE__, $sql);
	}
	if ($total = DB()->sql_fetchrow($result))
	{
		$total_members = $total['total'];
		$pagination = generate_pagination($paginationurl, $total_members, $bb_cfg['topics_per_page'], $start). '&nbsp;';
	}
	DB()->sql_freeresult($result);
}
else
{
	$pagination = '&nbsp;';
	$total_members = 10;
}
$template->assign_vars(array(
	'PAGE_TITLE' => $lang['MEMBERLIST'],
	'PAGINATION' => $pagination,
	'PAGE_NUMBER' => sprintf($lang['PAGE_OF'], ( floor( $start / $bb_cfg['topics_per_page'] ) + 1 ), ceil( $total_members / $bb_cfg['topics_per_page'] )),
));

print_page('memberlist.tpl');
