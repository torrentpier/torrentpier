<?php

// ACP Header - START
if (!empty($setmodules))
{
	$module['Users']['Search'] = basename(__FILE__);
	return;
}
require('./pagestart.php');
// ACP Header - END

require(INC_DIR .'functions_selects.php');

include(LANG_ROOT_DIR ."lang_{$bb_cfg['default_lang']}/lang_user_search.php");

$total_sql = '';

if(!isset($_POST['dosearch'])&&!isset($_GET['dosearch']))
{
	$sql = "SELECT group_id, group_name
				FROM ".BB_GROUPS."
					WHERE group_single_user = 0
						ORDER BY group_name ASC";

	if(!$result = DB()->sql_query($sql))
	{
		message_die(GENERAL_ERROR, 'Could not select group data', '', __LINE__, __FILE__, $sql);
	}

	$group_list = '';

	if(DB()->num_rows($result) != 0)
	{
		$template->assign_block_vars('groups_exist', array());

		while($row = DB()->sql_fetchrow($result))
		{
			$group_list .= '<option value="'.$row['group_id'].'">'.strip_tags(htmlspecialchars($row['group_name'])).'</option>';
		}
	}


	$sql = "SELECT * FROM " . BB_RANKS . "
		WHERE rank_special = 1
		ORDER BY rank_title";
	if ( !($result = DB()->sql_query($sql)) )
	{
		message_die(GENERAL_ERROR, 'Could not obtain ranks data', '', __LINE__, __FILE__, $sql);
	}
	$rank_select_box = '';
	if(DB()->num_rows($result) != 0)
	{
		$template->assign_block_vars('ranks_exist', array());
		while( $row = DB()->sql_fetchrow($result) )
		{
			$rank = $row['rank_title'];
			$rank_id = $row['rank_id'];
			$rank_select_box .= '<option value="' . $rank_id . '">' . $rank . '</option>';
		}
	}


	$language_list = language_select('', 'language_type');
	$timezone_list = tz_select('', 'timezone_type');

	$sql = "SELECT f.forum_id, f.forum_name, f.forum_parent, c.cat_id, c.cat_title
				FROM ( ". BB_FORUMS ." AS f INNER JOIN ". BB_CATEGORIES ." AS c ON c.cat_id = f.cat_id )
				ORDER BY c.cat_order, f.forum_order ASC";

	if(!$result = DB()->sql_query($sql))
	{
		message_die(GENERAL_ERROR, 'Could not select forum data', '', __LINE__, __FILE__, $sql);
	}

	$forums = array();

	if(DB()->num_rows($result) != 0)
	{
		$template->assign_block_vars('forums_exist', array());

		$last_cat_id = -1;

		$forums_list = '';

		while($row = DB()->sql_fetchrow($result))
		{
			if($row['cat_id'] != $last_cat_id)
			{
				$forums_list .= '<optgroup label="'.htmlCHR($row['cat_title']).'">';
				$last_cat_id = $row['cat_id'];
			}

			$forums_list .= '<option value="'.$row['forum_id'].'">'.(($row['forum_parent']) ? HTML_SF_SPACER : '').htmlCHR($row['forum_name']).'</option>';
		}
	}

	$styles_list = $bb_cfg['tpl_name'];

	$lastvisited = array(1, 7, 14, 30, 60, 120, 365, 500, 730, 1000);
	$lastvisited_list = '';

	foreach($lastvisited as $days)
	{
		$lastvisited_list .= '<option value="'.$days.'">'.$days.' '. ( ( $days > 1 ) ? $lang['DAYS'] : $lang['DAY'] ) .'</option>';
	}

	$template->assign_vars(array(
		'TPL_ADMIN_USER_SEARCH_MAIN' => true,

		'YEAR' => date("Y"),
		'MONTH' => date("m"),
		'DAY' => date("d"),
		'GROUP_LIST' => $group_list,
		'RANK_SELECT_BOX' => $rank_select_box,
		'LANGUAGE_LIST' => $language_list,
		'TIMEZONE_LIST' => $timezone_list,
		'FORUMS_LIST' => $forums_list,
		'STYLE_LIST' => $styles_list,
		'LASTVISITED_LIST' => $lastvisited_list,

		'S_SEARCH_ACTION' => 'admin_user_search.php',
	));
}
else
{
	$mode = '';

	// validate mode
	if(isset($_POST['search_username'])||isset($_GET['search_username']))
	{
		$mode = 'search_username';
	}
	else if(isset($_POST['search_email'])||isset($_GET['search_email']))
	{
		$mode = 'search_email';
	}
	else if(isset($_POST['search_ip'])||isset($_GET['search_ip']))
	{
		$mode = 'search_ip';
	}
	else if(isset($_POST['search_joindate'])||isset($_GET['search_joindate']))
	{
		$mode = 'search_joindate';
	}
	else if(isset($_POST['search_group'])||isset($_GET['search_group']))
	{
		$mode = 'search_group';
	}
	else if(isset($_POST['search_rank'])||isset($_GET['search_rank']))
	{
		$mode = 'search_rank';
	}
	else if(isset($_POST['search_postcount'])||isset($_GET['search_postcount']))
	{
		$mode = 'search_postcount';
	}
	else if(isset($_POST['search_userfield'])||isset($_GET['search_userfield']))
	{
		$mode = 'search_userfield';
	}
	else if(isset($_POST['search_lastvisited'])||isset($_GET['search_lastvisited']))
	{
		$mode = 'search_lastvisited';
	}
	else if(isset($_POST['search_language'])||isset($_GET['search_language']))
	{
		$mode = 'search_language';
	}
	else if(isset($_POST['search_timezone'])||isset($_GET['search_timezone']))
	{
		$mode = 'search_timezone';
	}
	else if(isset($_POST['search_style'])||isset($_GET['search_style']))
	{
		$mode = 'search_style';
	}
	else if(isset($_POST['search_moderators'])||isset($_GET['search_moderators']))
	{
		$mode = 'search_moderators';
	}
	else if(isset($_POST['search_misc'])||isset($_GET['search_misc']))
	{
		$mode = 'search_misc';
	}

	// validate fields (that they exist)
	switch($mode)
	{
		case 'search_username':
			$username = ( isset($_GET['username']) ) ? $_GET['username'] : $_POST['username'];
			$regex = ( @$_POST['search_username_regex'] ) ? true : ( @$_GET['regex'] ) ? true : false;

			if(!$username)
			{
				message_die(GENERAL_MESSAGE, $lang['SEARCH_INVALID_USERNAME']);
			}

			break;
		case 'search_email':
			$email = ( isset($_GET['email']) ) ? $_GET['email'] : $_POST['email'];
			$regex = ( @$_POST['search_email_regex'] ) ? true : ( @$_GET['regex'] ) ? true : false;

			if(!$email)
			{
				message_die(GENERAL_MESSAGE, $lang['SEARCH_INVALID_EMAIL']);
			}

			break;
		case 'search_ip':
			$ip_address = ( isset($_POST['ip_address'] ) ) ? $_POST['ip_address'] : $_GET['ip_address'];

			if(!$ip_address)
			{
				message_die(GENERAL_MESSAGE, $lang['SEARCH_INVALID_IP']);
			}
			break;
		case 'search_joindate':
			$date_type = ( isset($_POST['date_type'] ) ) ? $_POST['date_type'] : $_GET['date_type'];
			$date_day = ( isset($_POST['date_day'] ) ) ? $_POST['date_day'] : $_GET['date_day'];
			$date_month = ( isset($_POST['date_month'] ) ) ? $_POST['date_month'] : $_GET['date_month'];
			$date_year = ( isset($_POST['date_year'] ) ) ? $_POST['date_year'] : $_GET['date_year'];

			if(!$date_type || !$date_day || !$date_month || !$date_year)
			{
				message_die(GENERAL_MESSAGE, $lang['SEARCH_INVALID_DATE']);
			}
			break;
		case 'search_group':
			$group_id = ( isset($_POST['group_id'] ) ) ? $_POST['group_id'] : $_GET['group_id'];
			if(!$group_id)
			{
				message_die(GENERAL_MESSAGE, $lang['SEARCH_INVALID_GROUP']);
			}
			break;
		case 'search_rank':
			$rank_id = ( isset($_POST['rank_id'] ) ) ? $_POST['rank_id'] : $_GET['rank_id'];
			if(!$rank_id)
			{
				message_die(GENERAL_MESSAGE, $lang['SEARCH_INVALID_RANK']);
			}
			break;
		case 'search_postcount':
			$postcount_type = ( isset($_POST['postcount_type'] ) ) ? $_POST['postcount_type'] : $_GET['postcount_type'];
			$postcount_value = ( isset($_POST['postcount_value'] ) ) ? $_POST['postcount_value'] : $_GET['postcount_value'];

			if(!$postcount_type || ( !$postcount_value && $postcount_value != 0))
			{
				message_die(GENERAL_MESSAGE, $lang['SEARCH_INVALID_POSTCOUNT']);
			}
			break;
		case 'search_userfield':
			$userfield_type = ( isset($_POST['userfield_type'] ) ) ? $_POST['userfield_type'] : $_GET['userfield_type'];
			$userfield_value = ( isset($_POST['userfield_value'] ) ) ? $_POST['userfield_value'] : $_GET['userfield_value'];
			$regex = ( @$_POST['search_userfield_regex'] ) ? true : ( @$_GET['regex'] ) ? true : false;

			if(!$userfield_type || !$userfield_value)
			{
				message_die(GENERAL_MESSAGE, $lang['SEARCH_INVALID_USERFIELD']);
			}

			break;
		case 'search_lastvisited':
			$lastvisited_days = ( isset($_POST['lastvisited_days'] ) ) ? $_POST['lastvisited_days'] : $_GET['lastvisited_days'];
			$lastvisited_type = ( isset($_POST['lastvisited_type'] ) ) ? $_POST['lastvisited_type'] : $_GET['lastvisited_type'];

			if(!$lastvisited_days || !$lastvisited_type)
			{
				message_die(GENERAL_MESSAGE, $lang['SEARCH_INVALID_LASTVISITED']);
			}

			break;
		case 'search_language':
			$language_type = ( isset($_POST['language_type'] ) ) ? $_POST['language_type'] : $_GET['language_type'];

			if(!$language_type)
			{
				message_die(GENERAL_MESSAGE, $lang['SEARCH_INVALID_LANGUAGE']);
			}

			break;
		case 'search_timezone':
			$timezone_type = ( isset($_POST['timezone_type'] ) ) ? $_POST['timezone_type'] : $_GET['timezone_type'];

			if(!$timezone_type && $timezone_type != 0)
			{
				message_die(GENERAL_MESSAGE, $lang['SEARCH_INVALID_TIMEZONE']);
			}

			break;
		case 'search_style':
			$style_type = ( isset($_POST['style_type'] ) ) ? $_POST['style_type'] : $_GET['style_type'];

			if(!$style_type)
			{
				message_die(GENERAL_MESSAGE, $lang['SEARCH_INVALID_STYLE']);
			}

			break;
		case 'search_moderators':
			$moderators_forum = ( isset($_POST['moderators_forum'] ) ) ? $_POST['moderators_forum'] : $_GET['moderators_forum'];

			if(!$moderators_forum)
			{
				message_die(GENERAL_MESSAGE, $lang['SEARCH_INVALID_MODERATORS']);
			}

			break;
		case 'search_misc':
		default:
			$misc = ( isset($_POST['misc'] ) ) ? $_POST['misc'] : $_GET['misc'];
			if(!$misc)
			{
				message_die(GENERAL_MESSAGE, $lang['SEARCH_INVALID']);
			}
	}

	$base_url = 'admin_user_search.php?dosearch=true';

	$select_sql = "SELECT u.user_id, u.username, u.user_rank, u.user_email, u.user_posts, u.user_regdate, u.user_level, u.user_active, u.user_lastvisit
						FROM ". BB_USERS ." AS u";

	$lower_b = 'LOWER(';
	$lower_e = ')';
	if(@$regex)
	{
		switch(SQL_LAYER)
		{
			case 'postgres':
				$op = '~';
				break;
			case 'oracle':
				// Oracle uses a different syntax, we'll handle that a little later
				break;
			case 'mysql':
			case 'mysql4':
				$op = 'REGEXP';
				break;
			default:
				message_die(GENERAL_MESSAGE, $lang['SEARCH_NO_REGEXP']);
		}

		$lower_b = '';
		$lower_e = '';
	}

	// validate data & prepare sql
	switch($mode)
	{
		case 'search_username':
			$base_url .= '&search_username=true&username='.rawurlencode(stripslashes($username));

			$text = sprintf($lang['SEARCH_FOR_USERNAME'], strip_tags(htmlspecialchars(stripslashes($username))));

			if(!$regex)
			{
				$username = preg_replace('/\*/', '%', trim(strip_tags(strtolower($username))));

				if(strstr($username, '%'))
				{
					$op = 'LIKE';
				}
				else
				{
					$op = '=';
				}
			}
			else
			{
				$username = preg_replace('/\\\\\\\(?<!\'|"|NULL)/', '\\', $username);
			}

			if($username == '')
			{
				message_die(GENERAL_MESSAGE, $lang['SEARCH_INVALID_USERNAME']);
			}

			if($regex && SQL_LAYER == 'oracle')
			{
				$total_sql .= "SELECT COUNT(user_id) AS total
								FROM ".BB_USERS."
									WHERE REGEXP_LIKE(username, '".DB()->escape($username)."')
										AND user_id <> ".GUEST_UID;

				$select_sql .= "	WHERE REGEXP_LIKE(u.username, '".DB()->escape($username)."')
										AND u.user_id <> ".GUEST_UID;
			}
			else
			{
				$total_sql .= "SELECT COUNT(user_id) AS total
								FROM ".BB_USERS."
									WHERE {$lower_b}username{$lower_e} $op '".DB()->escape($username)."'
										AND user_id <> ".GUEST_UID;

				$select_sql .= "	WHERE {$lower_b}u.username{$lower_e} $op '".DB()->escape($username)."'
										AND u.user_id <> ".GUEST_UID;
			}
			break;
		case 'search_email':
			$base_url .= '&search_email=true&email='.rawurlencode(stripslashes($email));

			$text = sprintf($lang['SEARCH_FOR_EMAIL'], strip_tags(htmlspecialchars(stripslashes($email))));

			if(!$regex)
			{
				$email = preg_replace('/\*/', '%', trim(strip_tags(strtolower($email))));

				if(strstr($email, '%'))
				{
					$op = 'LIKE';
				}
				else
				{
					$op = '=';
				}
			}
			else
			{
				$email = preg_replace('/\\\\\\\(?<!\'|"|NULL)/', '\\', $email);
			}

			if($email == '')
			{
				message_die(GENERAL_MESSAGE, $lang['SEARCH_INVALID_EMAIL']);
			}

			if($regex && SQL_LAYER == 'oracle')
			{
				$total_sql .= "SELECT COUNT(user_id) AS total
								FROM ".BB_USERS."
									WHERE REGEXP_LIKE(user_email, '".DB()->escape($email)."')
										AND user_id <> ".GUEST_UID;

				$select_sql .= "	WHERE REGEXP_LIKE(u.user_email, '".DB()->escape($email)."')
										AND u.user_id <> ".GUEST_UID;
			}
			else
			{
				$total_sql .= "SELECT COUNT(user_id) AS total
								FROM ".BB_USERS."
									WHERE {$lower_b}user_email{$lower_e} $op '".DB()->escape($email)."'
										AND user_id <> ".GUEST_UID;

				$select_sql .= "	WHERE {$lower_b}u.user_email{$lower_e} $op '".DB()->escape($email)."'
										AND u.user_id <> ".GUEST_UID;
			}
			break;
		case 'search_ip':
			$base_url .= '&search_ip=true&ip_address='.rawurlencode(stripslashes($ip_address));

			// Remove any whitespace
			$ip_address = trim($ip_address);

			$text = sprintf($lang['SEARCH_FOR_IP'], strip_tags(htmlspecialchars(stripslashes($ip_address))));

			unset($users);
			$users = array();

			// Let's see if they entered a full valid IPv4 address
			if( preg_match('/^([0-9]{1,2}|[0-2][0-9]{0,2})(\.([0-9]{1,2}|[0-2][0-9]{0,2})){3}$/', $ip_address) )
			{
				// Encode the ip into hexademicals
				$ip = encode_ip($ip_address);

				// Because we will be deleting based on IP's, we will store the encoded IP alone
				$users[] = $ip;
			}
			// We will also support wildcards, is this an xxx.xxx.* address?
			else if( preg_match('/^([0-9]{1,2}|[0-2][0-9]{0,2})(\.([0-9]{1,2}|[0-2][0-9]{0,2})){0,2}\.\*/', $ip_address) )
			{
				// Alright, now we do the ugly part, converting them to encoded ips
				// We need to deal with the three ways it can be done
				// xxx.*
				// xxx.xxx.*
				// xxx.xxx.xxx.*

				// First we will split the IP into its quads
				$ip_split = explode('.', $ip_address);

				// Now we'll work with which type of wildcard we have
				switch( count($ip_split) )
				{
					// xxx.xxx.xxx.*
					case 4:
						// We will encode the ip into hexademical quads
						$users[] = encode_ip($ip_split[0].".".$ip_split[1].".".$ip_split[2].".255");
						break;
					// xxx.xxx.*
					case 3:
						// We will encode the ip into hexademical quads again..
						$users[] = encode_ip($ip_split[0].".".$ip_split[1].".255.255");
						break;
					// xxx.*
					case 2:
						// We will encode the ip into hexademical quads again again....
						$users[] = encode_ip($ip_split[0].".255.255.255");
						break;
				}
			}
			// Lastly, let's see if they have a range in the last quad, like xxx.xxx.xxx.xxx - xxx.xxx.xxx.yyy
			else if( preg_match('/^([0-9]{1,2}|[0-2][0-9]{0,2})(\.([0-9]{1,2}|[0-2][0-9]{0,2})){3}(\s)*-(\s)*([0-9]{1,2}|[0-2][0-9]{0,2})(\.([0-9]{1,2}|[0-2][0-9]{0,2})){3}$/', $ip_address) )
			{
				// We will split the two ranges
				$range = preg_split('/[-\s]+/', $ip_address);

				// This is where break the start and end ips into quads
				$start_range = explode('.', $range[0]);
				$end_range = explode('.', $range[1]);

				// Confirm if we are in the same subnet or the last quad in the beginning range is greater than the last in the ending range
				if( ($start_range[0].$start_range[1].$start_range[2] != $end_range[0].$end_range[1].$end_range[2]) || ($start_range[3] > $end_range[3]) )
				{
					message_die(GENERAL_MESSAGE, $lang['SEARCH_INVALID_IP']);
				}

				// Ok, we need to store each IP in the range..
				for( $i = $start_range[3]; $i <= $end_range[3]; $i++ )
				{
					// let's put it in the big array..
					$users[] = encode_ip($start_range[0].".".$start_range[1  ].".".$start_range[2].".".$i);
				}
			}
			// This is not a valid IP based on what we want..
			else
			{
				message_die(GENERAL_MESSAGE, $lang['SEARCH_INVALID_IP']);
			}

			$ip_in_sql = $ip_like_sql = $ip_like_sql_flylast = $ip_like_sql_flyreg = '';

			foreach($users as $address)
			{
				// Is this IP a range?
				if( preg_match('/(ff){1,3}$/i', $address) )
				{
					// num.xxx.xxx.xxx
					if( preg_match('/[0-9a-f]{2}ffffff/i', $address) )
					{
						$ip_start = substr($address, 0, 2);
					}
					// num.num.xxx.xxx
					else if( preg_match('/[0-9a-f]{4}ffff/i', $address) )
					{
						$ip_start = substr($address, 0, 4);

					}
					// num.num.num.xxx
					else if( preg_match('/[0-9a-f]{6}ff/i', $address) )
					{
						$ip_start = substr($address, 0, 6);
					}

					$ip_like_sql_flylast = $ip_like_sql . ( $ip_like_sql != '' ) ? " OR user_last_ip LIKE '".$ip_start."%'" : "user_last_ip LIKE '".$ip_start."%'";
					$ip_like_sql_flyreg = $ip_like_sql . ( $ip_like_sql != '' ) ? " OR user_reg_ip LIKE '".$ip_start."%'" : "user_reg_ip LIKE '".$ip_start."%'";
					$ip_like_sql .= ( $ip_like_sql != '' ) ? " OR poster_ip LIKE '".$ip_start."%'" : "poster_ip LIKE '".$ip_start."%'";
				}
				else
				{
					$ip_in_sql .= ( $ip_in_sql == '' ) ? "'$address'" : ", '$address'";
				}
			}

			$where_sql = '';
			$where_sql .= ( $ip_in_sql != '' ) ? "poster_ip IN ($ip_in_sql)": "";
			$where_sql .= ( $ip_like_sql != '' ) ? ( $where_sql != "" ) ? " OR $ip_like_sql" : "$ip_like_sql": "";

			if (!$where_sql) bb_die('invalid request');

			// start search
			$no_result_search = false;
			$ip_users_sql = '';
			$sql = "SELECT poster_id
						FROM ".BB_POSTS."
							WHERE poster_id <> ".GUEST_UID."
								AND ($where_sql)
							GROUP BY poster_id";

			if(!$result = DB()->sql_query($sql))
			{
				message_die(GENERAL_ERROR, "Could not count users", '', __LINE__, __FILE__, $sql);
			}

			if(DB()->num_rows($result)==0)
			{
				$no_result_search = true;
				// message_die(GENERAL_MESSAGE, $lang['SEARCH_NO_RESULTS']);
			}
			else
			{
				$total_pages['total'] = DB()->num_rows($result);

				$total_sql = NULL;

				$ip_users_sql = '';

				while($row = DB()->sql_fetchrow($result))
				{
					$ip_users_sql .= ( $ip_users_sql == '' ) ? $row['poster_id'] : ', '.$row['poster_id'];
				}
			}

			// fly_indiz addon [START]
			// user last ip
			$where_sql = '';
			$where_sql .= ( $ip_in_sql != '' ) ? "user_last_ip IN ($ip_in_sql)": "";
			$where_sql .= ( $ip_like_sql_flylast != '' ) ? ( $where_sql != "" ) ? " OR $ip_like_sql_flylast" : "$ip_like_sql_flylast": "";
			$sql = "SELECT user_id
						FROM ".BB_USERS."
							WHERE user_id <> ".GUEST_UID."
								AND ($where_sql)
							GROUP BY user_id";
			if(!$result = DB()->sql_query($sql))
			{
				message_die(GENERAL_ERROR, "Could not count users", '', __LINE__, __FILE__, $sql);
			}
			if(DB()->num_rows($result)!=0)
			{
				if ($no_result_search == true) $no_result_search = false;
				$total_pages['total'] = DB()->num_rows($result);
				$total_sql = NULL;
				while($row = DB()->sql_fetchrow($result))
				{
					$ip_users_sql .= ( $ip_users_sql == '' ) ? $row['user_id'] : ', '.$row['user_id'];
				}
			}
			// user reg ip
			$where_sql = '';
			$where_sql .= ( $ip_in_sql != '' ) ? "user_reg_ip IN ($ip_in_sql)": "";
			$where_sql .= ( $ip_like_sql_flyreg != '' ) ? ( $where_sql != "" ) ? " OR $ip_like_sql_flyreg" : "$ip_like_sql_flyreg": "";
			$sql = "SELECT user_id
						FROM ".BB_USERS."
							WHERE user_id <> ".GUEST_UID."
								AND ($where_sql)
							GROUP BY user_id";
			if(!$result = DB()->sql_query($sql))
			{
				message_die(GENERAL_ERROR, "Could not count users", '', __LINE__, __FILE__, $sql);
			}
			if(DB()->num_rows($result)!=0)
			{
				if ($no_result_search == true) $no_result_search = false;
				$total_pages['total'] = DB()->num_rows($result);
				$total_sql = NULL;
				while($row = DB()->sql_fetchrow($result))
				{
					$ip_users_sql .= ( $ip_users_sql == '' ) ? $row['user_id'] : ', '.$row['user_id'];
				}
			}
			if ($no_result_search == true)
			{
				message_die(GENERAL_MESSAGE, $lang['SEARCH_NO_RESULTS']);
			}
			// fly_indiz addon [END]

			$select_sql .= "	WHERE u.user_id IN ($ip_users_sql)";

			break;
		case 'search_joindate':
			$base_url .= '&search_joindate=true&date_type='. rawurlencode($date_type) .'&date_day='. rawurlencode($date_day) .'&date_month='. rawurlencode($date_month) .'&date_year='. rawurlencode(stripslashes($date_year));

			$date_type = trim(strtolower($date_type));

			if($date_type != 'before' && $date_type != 'after')
			{
				message_die(GENERAL_MESSAGE, $lang['SEARCH_INVALID_DATE']);
			}

			$date_day = intval($date_day);

			if( !preg_match('/^([1-9]|[0-2][0-9]|3[0-1])$/', $date_day) )
			{
				message_die(GENERAL_MESSAGE, $lang['SEARCH_INVALID_DAY']);
			}

			$date_month = intval($date_month);

			if( !preg_match('/^(0?[1-9]|1[0-2])$/', $date_month) )
			{
				message_die(GENERAL_MESSAGE, $lang['SEARCH_INVALID_MONTH']);
			}

			$date_year = intval($date_year);

			if( !preg_match('/^(20[0-9]{2}|19[0-9]{2})$/', $date_year) )
			{
				message_die(GENERAL_MESSAGE, $lang['SEARCH_INVALID_YEAR']);
			}

			$text = sprintf($lang['SEARCH_FOR_DATE'], strip_tags(htmlspecialchars(stripslashes($date_type))), $date_year, $date_month, $date_day);

			$time = mktime(0,0,0,$date_month, $date_day, $date_year);

			if($date_type == 'before')
			{
				$arg = '<';
			}
			else
			{
				$arg = '>';
			}

			$total_sql .= "SELECT COUNT(user_id) AS total
							FROM ".BB_USERS."
								WHERE user_regdate $arg $time
									AND user_id <> ".GUEST_UID;

			$select_sql .= "	WHERE u.user_regdate $arg $time
									AND u.user_id <> ".GUEST_UID;

			break;
		case 'search_group':
			$group_id = intval($group_id);

			$base_url .= '&search_group=true&group_id='. rawurlencode($group_id);

			if(!$group_id)
			{
				message_die(GENERAL_MESSAGE, $lang['SEARCH_INVALID_GROUP']);
			}

			$sql = "SELECT group_name
						FROM ".BB_GROUPS."
							WHERE group_id = $group_id
								AND group_single_user = 0";

			if(!$result = DB()->sql_query($sql))
			{
				message_die(GENERAL_ERROR, 'Could not select group data', '', __LINE__, __FILE__, $sql);
			}

			if(DB()->num_rows($result)==0)
			{
				message_die(GENERAL_MESSAGE, $lang['SEARCH_INVALID_GROUP']);
			}

			$group_name = DB()->sql_fetchrow($result);

			$text = sprintf($lang['SEARCH_FOR_GROUP'], strip_tags(htmlspecialchars($group_name['group_name'])));

			$total_sql .= "SELECT COUNT(u.user_id) AS total
							FROM ".BB_USERS." AS u, ".BB_USER_GROUP." AS ug
								WHERE u.user_id = ug.user_id
										AND ug.group_id = $group_id
										AND u.user_id <> ".GUEST_UID;

			$select_sql .= ", ".BB_USER_GROUP." AS ug
								WHERE u.user_id = ug.user_id
										AND ug.group_id = $group_id
										AND u.user_id <> ".GUEST_UID;

			break;
		case 'search_rank':
			$rank_id = intval($rank_id);

			$base_url .= '&search_rank=true&rank_id='. rawurlencode($rank_id);

			if(!$rank_id)
			{
				message_die(GENERAL_MESSAGE, $lang['SEARCH_INVALID_RANK']);
			}

			$sql = "SELECT rank_title
						FROM ".BB_RANKS."
							WHERE rank_id = $rank_id
								AND rank_special = 1";

			if(!$result = DB()->sql_query($sql))
			{
				message_die(GENERAL_ERROR, 'Could not select rank data', '', __LINE__, __FILE__, $sql);
			}

			if(DB()->num_rows($result)==0)
			{
				message_die(GENERAL_MESSAGE, $lang['SEARCH_INVALID_RANK']);
			}

			$rank_title = DB()->sql_fetchrow($result);

			$text = sprintf($lang['SEARCH_FOR_RANK'], strip_tags(htmlspecialchars($rank_title['rank_title'])));

			$total_sql .= "SELECT COUNT(user_id) AS total
							FROM ".BB_USERS."
								WHERE user_rank = $rank_id
									AND user_id <> ".GUEST_UID;

			$select_sql .= "	WHERE u.user_rank = $rank_id
									AND u.user_id <> ".GUEST_UID;

			break;
		case 'search_postcount':
			$postcount_type = trim(strtolower($postcount_type));
			$postcount_value = trim(strtolower($postcount_value));

			$base_url .= '&search_postcount=true&postcount_type='. rawurlencode($postcount_type) .'&postcount_value='. rawurlencode(stripslashes($postcount_value));

			switch($postcount_type)
			{
				case 'greater':
					$postcount_value = intval($postcount_value);

					$text = sprintf($lang['SEARCH_FOR_POSTCOUNT_GREATER'], $postcount_value);

					$total_sql .= "SELECT COUNT(user_id) AS total
									FROM ".BB_USERS."
										WHERE user_posts > $postcount_value
											AND user_id <> ".GUEST_UID;

					$select_sql .= "	WHERE u.user_posts > $postcount_value
											AND u.user_id <> ".GUEST_UID;
					break;
				case 'lesser':
					$postcount_value = intval($postcount_value);

					$text = sprintf($lang['SEARCH_FOR_POSTCOUNT_LESSER'], $postcount_value);

					$total_sql .= "SELECT COUNT(user_id) AS total
									FROM ".BB_USERS."
										WHERE user_posts < $postcount_value
											AND user_id <> ".GUEST_UID;

					$select_sql .= "	WHERE u.user_posts < $postcount_value
											AND u.user_id <> ".GUEST_UID;
					break;
				case 'equals':
					// looking for a -
					if(strstr($postcount_value, '-'))
					{
						$range = preg_split('/[-\s]+/', $postcount_value);

						$range_begin = intval($range[0]);
						$range_end = intval($range[1]);

						if($range_begin > $range_end)
						{
							message_die(GENERAL_MESSAGE, $lang['SEARCH_INVALID_POSTCOUNT']);
						}

						$text = sprintf($lang['SEARCH_FOR_POSTCOUNT_RANGE'], $range_begin, $range_end);

						$total_sql .= "SELECT COUNT(user_id) AS total
										FROM ".BB_USERS."
											WHERE user_posts >= $range_begin
												AND user_posts <= $range_end
												AND user_id <> ".GUEST_UID;

						$select_sql .= "	WHERE u.user_posts >= $range_begin
												AND u.user_posts <= $range_end
												AND u.user_id <> ".GUEST_UID;
					}
					else
					{
						$postcount_value = intval($postcount_value);

						$text = sprintf($lang['SEARCH_FOR_POSTCOUNT_EQUALS'], $postcount_value);

						$total_sql .= "SELECT COUNT(user_id) AS total
										FROM ".BB_USERS."
											WHERE user_posts = $postcount_value
												AND user_id <> ".GUEST_UID;

						$select_sql .= "	WHERE u.user_posts = $postcount_value
												AND u.user_id <> ".GUEST_UID;
					}
					break;
				default:
					message_die(GENERAL_MESSAGE, $lang['SEARCH_INVALID']);
			}

			break;
		case 'search_userfield':
			$base_url .= '&search_userfield=true&userfield_type='. rawurlencode($userfield_type) .'&userfield_value='. rawurlencode(stripslashes($userfield_value));

			$text = strip_tags(htmlspecialchars(stripslashes($userfield_value)));

			if(!$regex)
			{
				$userfield_value = preg_replace('/\*/', '%', trim(strip_tags(strtolower($userfield_value))));

				if(strstr($userfield_value, '%'))
				{
					$op = 'LIKE';
				}
				else
				{
					$op = '=';
				}
			}
			else
			{
				$userfield_value = preg_replace('/\\\\\\\(?<!\'|"|NULL)/', '\\', $userfield_value);
			}

			if($userfield_value == '')
			{
				message_die(GENERAL_MESSAGE, $lang['SEARCH_INVALID_USERFIELD']);
			}

			$userfield_type = trim(strtolower($userfield_type));

			switch($userfield_type)
			{
				case 'icq':
					$text = sprintf($lang['SEARCH_FOR_USERFIELD_ICQ'],$text);
					$field = 'user_icq';
					break;
				case 'skype':
					$text = sprintf($lang['SEARCH_FOR_USERFIELD_SKYPE'],$text);
					$field = 'user_skype';
					break;
				case 'website':
					$text = sprintf($lang['SEARCH_FOR_USERFIELD_WEBSITE'],$text);
					$field = 'user_website';
					break;
				case 'location':
					$text = sprintf($lang['SEARCH_FOR_USERFIELD_LOCATION'],$text);
					$field = 'user_from';
					break;
				case 'interests':
					$text = sprintf($lang['SEARCH_FOR_USERFIELD_INTERESTS'],$text);
					$field = 'user_interests';
					break;
				case 'occupation':
					$text = sprintf($lang['SEARCH_FOR_USERFIELD_OCCUPATION'],$text);
					$field = 'user_occ';
					break;
				default:
					message_die(GENERAL_MESSAGE, $lang['SEARCH_INVALID']);
			}

			if($regex && SQL_LAYER == 'oracle')
			{
				$total_sql .= "SELECT COUNT(user_id) AS total
								FROM ".BB_USERS."
									WHERE REGEXP_LIKE($field, '".DB()->escape($userfield_value)."')
										AND user_id <> ".GUEST_UID;

				$select_sql .= "	WHERE REGEXP_LIKE(u.$field, '".DB()->escape($userfield_value)."')
										AND u.user_id <> ".GUEST_UID;
			}
			else
			{
				$total_sql .= "SELECT COUNT(user_id) AS total
								FROM ".BB_USERS."
									WHERE {$lower_b}$field{$lower_e} $op '".DB()->escape($userfield_value)."'
										AND user_id <> ".GUEST_UID;

				$select_sql .= "	WHERE {$lower_b}u.$field{$lower_e} $op '".DB()->escape($userfield_value)."'
										AND u.user_id <> ".GUEST_UID;
			}

			break;
		case 'search_lastvisited':
			$lastvisited_type = trim(strtolower($lastvisited_type));
			$lastvisited_days = intval($lastvisited_days);

			$base_url .= '&search_lastvisited=true&lastvisited_type='. rawurlencode(stripslashes($lastvisited_type)) .'&lastvisited_days='. rawurlencode($lastvisited_days);

			$lastvisited_seconds = ( TIMENOW - ( ( ( $lastvisited_days * 24 ) * 60 ) * 60 ) );

			switch($lastvisited_type)
			{
				case 'in':
					$text = sprintf($lang['SEARCH_FOR_LASTVISITED_INTHELAST'], $lastvisited_days, ( ( $lastvisited_days > 1 ) ? $lang['DAYS'] : $lang['DAY'] ) );

					$total_sql .= "SELECT COUNT(user_id) AS total
									FROM ".BB_USERS."
										WHERE user_lastvisit >= $lastvisited_seconds
											AND user_id <> ".GUEST_UID;

					$select_sql .= "	WHERE u.user_lastvisit >= $lastvisited_seconds
											AND u.user_id <> ".GUEST_UID;
					break;
				case 'after':
					$text = sprintf($lang['SEARCH_FOR_LASTVISITED_AFTERTHELAST'], $lastvisited_days, ( ( $lastvisited_days > 1 ) ? $lang['DAYS'] : $lang['DAY'] ));

					$total_sql .= "SELECT COUNT(user_id) AS total
									FROM ".BB_USERS."
										WHERE user_lastvisit < $lastvisited_seconds
											AND user_id <> ".GUEST_UID;

					$select_sql .= "	WHERE u.user_lastvisit < $lastvisited_seconds
											AND u.user_id <> ".GUEST_UID;

					break;
				default:
					message_die(GENERAL_MESSAGE, $lang['SEARCH_INVALID_LASTVISITED']);
			}

			break;
		case 'search_language':
			$base_url .= '&search_language=true&language_type='. rawurlencode(stripslashes($language_type));

			$language_type = trim(strtolower(stripslashes($language_type)));

			if($language_type == '')
			{
				message_die(GENERAL_MESSAGE, $lang['SEARCH_INVALID_LANGUAGE']);
			}

			$text = sprintf($lang['SEARCH_FOR_LANGUAGE'], strip_tags(htmlspecialchars($language_type)));

			$total_sql .= "SELECT COUNT(user_id) AS total
							FROM ".BB_USERS."
								WHERE user_lang = '".DB()->escape($language_type)."'
									AND user_id <> ".GUEST_UID;

			$select_sql .= "	WHERE u.user_lang = '".DB()->escape($language_type)."'
									AND u.user_id <> ".GUEST_UID;

			break;
		case 'search_timezone':
			$base_url .= '&search_timezone=true&timezone_type='. rawurlencode(stripslashes($timezone_type));
			$text = sprintf($lang['SEARCH_FOR_TIMEZONE'], strip_tags(htmlspecialchars(stripslashes($timezone_type))));

			$timezone_type = intval($timezone_type);

			$total_sql .= "SELECT COUNT(user_id) AS total
							FROM ".BB_USERS."
								WHERE user_timezone = $timezone_type
									AND user_id <> ".GUEST_UID;

			$select_sql .= "	WHERE u.user_timezone = $timezone_type
									AND u.user_id <> ".GUEST_UID;

			break;
		case 'search_style':
			message_die(GENERAL_MESSAGE, 'Disabled');
			break;
		case 'search_moderators':
			$base_url .= '&search_moderators=true&moderators_forum='. rawurlencode(stripslashes($moderators_forum));
			$moderators_forum = intval($moderators_forum);

			$sql = "SELECT forum_name
						FROM ".BB_FORUMS."
							WHERE forum_id = ".$moderators_forum;


			if(!$result = DB()->sql_query($sql))
			{
				message_die(GENERAL_ERROR, 'Could not select forum data', '', __LINE__, __FILE__, $sql);
			}

			if(DB()->num_rows($result)==0)
			{
				message_die(GENERAL_MESSAGE, $lang['SEARCH_INVALID_MODERATORS']);
			}

			$forum_name = DB()->sql_fetchrow($result);

			$text = sprintf($lang['SEARCH_FOR_MODERATORS'], htmlCHR($forum_name['forum_name']));

			$total_sql .= "SELECT COUNT(DISTINCT u.user_id) AS total
							FROM ".BB_USERS." AS u, ".BB_GROUPS." AS g, ".BB_USER_GROUP." AS ug, ".BB_AUTH_ACCESS." AS aa
								WHERE u.user_id = ug.user_id
									AND ug.group_id = g.group_id
									AND	g.group_id = aa.group_id
									AND aa.forum_id = ". $moderators_forum ."
									AND aa.forum_perm & ". BF_AUTH_MOD ."
									AND u.user_id <> ".GUEST_UID;

			$select_sql .= ", ".BB_GROUPS." AS g, ".BB_USER_GROUP." AS ug, ".BB_AUTH_ACCESS." AS aa
								WHERE u.user_id = ug.user_id
									AND ug.group_id = g.group_id
									AND	g.group_id = aa.group_id
									AND aa.forum_id = ". $moderators_forum ."
									AND aa.forum_perm & ". BF_AUTH_MOD ."
									AND u.user_id <> ".GUEST_UID."
								GROUP BY u.user_id, u.username, u.user_email, u.user_posts, u.user_regdate, u.user_level, u.user_active, u.user_lastvisit";
			break;
		case 'search_misc':
		default:
			$misc = trim(strtolower($misc));

			$base_url .= '&search_misc=true&misc='. rawurlencode(stripslashes($misc));

			switch($misc)
			{
				case 'admins':
					$text = $lang['SEARCH_FOR_ADMINS'];

					$total_sql .= "SELECT COUNT(user_id) AS total
									FROM ".BB_USERS."
										WHERE user_level = ".ADMIN."
											AND user_id <> ".GUEST_UID;

					$select_sql .= "	WHERE u.user_level = ".ADMIN."
											AND u.user_id <> ".GUEST_UID;
					break;
				case 'mods':
					$text = $lang['SEARCH_FOR_MODS'];

					$total_sql .= "SELECT COUNT(user_id) AS total
									FROM ".BB_USERS."
										WHERE user_level = ".MOD."
											AND user_id <> ".GUEST_UID;

					$select_sql .= "	WHERE u.user_level = ".MOD."
											AND u.user_id <> ".GUEST_UID;
					break;
				case 'banned':
					$text = $lang['SEARCH_FOR_BANNED'];

					$total_sql .= "SELECT COUNT(u.user_id) AS total
									FROM ".BB_USERS." AS u, ".BB_BANLIST." AS b
										WHERE u.user_id = b.ban_userid
											AND u.user_id <> ".GUEST_UID;

					$select_sql .= ", ".BB_BANLIST." AS b
										WHERE u.user_id = b.ban_userid
											AND u.user_id <> ".GUEST_UID;

					break;
				case 'disabled':
					$text = $lang['SEARCH_FOR_DISABLED'];

					$total_sql .= "SELECT COUNT(user_id) AS total
									FROM ".BB_USERS."
										WHERE user_active = 0
											AND user_id <> ".GUEST_UID;

					$select_sql .= "	WHERE u.user_active = 0
											AND u.user_id <> ".GUEST_UID;

					break;
				case 'disabled_pms':
					$text = $lang['SEARCH_FOR_DISABLED_PMS'];

					$total_sql .= "SELECT COUNT(user_id) AS total
									FROM ".BB_USERS."
										WHERE user_allow_pm = 0
											AND user_id <> ".GUEST_UID;

					$select_sql .= "	WHERE u.user_allow_pm = 0
											AND u.user_id <> ".GUEST_UID;

					break;
				default:
					message_die(GENERAL_MESSAGE, $lang['SEARCH_INVALID']);
			}
	}

	if(@$regex)
	{
		$base_url .= '&regex=1';
	}

	$select_sql .= "	ORDER BY ";

	switch(strtolower(@$_GET['sort']))
	{
		case 'regdate':
			$sort = 'regdate';

			$select_sql .= 'u.user_regdate';
			break;
		case 'posts':
			$sort = 'posts';

			$select_sql .= 'u.user_posts';
			break;
		case 'user_email':
			$sort = 'user_email';

			$select_sql .= 'u.user_email';
			break;
		case 'lastvisit':
			$sort = 'lastvisit';

			$select_sql .= 'u.user_lastvisit';
			break;
		case 'username':
		default:
			$sort = 'username';

			$select_sql .= 'u.username';
	}

	switch(@$_GET['order'])
	{
		case 'DESC':
			$order = 'DESC';
			$o_order = 'ASC';
			break;
		default:
			$o_order = 'DESC';
			$order = 'ASC';
	}

	$select_sql .= " $order";

	$page = ( isset($_GET['page']) ) ? intval($_GET['page']) : intval(trim(@$_POST['page']));

	if($page < 1)
	{
		$page = 1;
	}

	if($page == 1)
	{
		$offset = 0;
	}
	else
	{
		$offset = ( ($page - 1) * $bb_cfg['topics_per_page']);
	}

	$limit = "LIMIT $offset, ".$bb_cfg['topics_per_page'];

	$select_sql .= " $limit";

	if(!is_null($total_sql))
	{
		if(!$result = DB()->sql_query($total_sql))
		{
			message_die(GENERAL_ERROR, "Could not count users", '', __LINE__, __FILE__, $total_sql);
		}

		$total_pages = DB()->sql_fetchrow($result);

		if($total_pages['total'] == 0)
		{
			message_die(GENERAL_MESSAGE, $lang['SEARCH_NO_RESULTS']);
		}
	}
	$num_pages = ceil( ( $total_pages['total'] / $bb_cfg['topics_per_page'] ) );

	$pagination = '';

	if($page > 1)
	{
		$pagination .= '<a href="'.$base_url.'&sort='.$sort.'&order='.$order.'&page='.($page - 1).'">'. $lang['PREVIOUS'] .'</a>';
	}

	if($page < $num_pages)
	{
		$pagination .= ( $pagination == '' ) ? '<a href="'.$base_url.'&sort='.$sort.'&order='.$order.'&page='. ($page + 1) .'">'.$lang['NEXT'].'</a>' : ' | <a href="'.$base_url.'&sort='.$sort.'&order='.$order.'&page='. ($page + 1) .'">'.$lang['NEXT'].'</a>';
	}

	$template->assign_vars(array(
		'TPL_ADMIN_USER_SEARCH_RESULTS' => true,

		'PAGE_NUMBER' => sprintf($lang['PAGE_OF'], $page, $num_pages),
		'PAGINATION' => $pagination,
		'NEW_SEARCH' => sprintf($lang['SEARCH_USERS_NEW'],$text, $total_pages['total'], 'admin_user_search.php'),

		'U_USERNAME' => ($sort == 'username') ? "$base_url&sort=$sort&order=$o_order" : "$base_url&sort=username&order=$order",
		'U_EMAIL' => ($sort == 'user_email') ? "$base_url&sort=$sort&order=$o_order" : "$base_url&sort=user_email&order=$order",
		'U_POSTS' => ($sort == 'posts') ? "$base_url&sort=$sort&order=$o_order" : "$base_url&sort=posts&order=$order",
		'U_JOINDATE' => ($sort == 'regdate') ? "$base_url&sort=$sort&order=$o_order" : "$base_url&sort=regdate&order=$order",
		'U_LASTVISIT' => ($sort == 'lastvisit') ? "$base_url&sort=$sort&order=$o_order" : "$base_url&sort=lastvisit&order=$order",

		'S_POST_ACTION' => "$base_url&sort=$sort&order=$order"
	));

	if(!$result = DB()->sql_query($select_sql))
	{
		message_die(GENERAL_ERROR, "Could not select user data", '', __LINE__, __FILE__, $select_sql);
	}

	$rowset = DB()->sql_fetchrowset($result);

	$users_sql = '';

	foreach($rowset as $array)
	{
		$users_sql .= ( $users_sql == '' ) ? $array['user_id'] : ', '.$array['user_id'];
	}

	$sql = "SELECT ban_userid AS user_id
				FROM ". BB_BANLIST ."
					WHERE ban_userid IN ($users_sql)";

	if(!$result = DB()->sql_query($sql))
	{
		message_die(GENERAL_ERROR, "Could not select banned data", '', __LINE__, __FILE__, $sql);
	}

	unset($banned);

	$banned = array();

	while($row = DB()->sql_fetchrow($result))
	{
		$banned[$row['user_id']] = true;
	}

	for($i = 0; $i < count($rowset); $i++)
	{
		$row_class = !($i % 2) ? 'row1' : 'row2';

		$template->assign_block_vars('userrow', array(
			'ROW_CLASS' => $row_class,
			'USER' => profile_url($rowset[$i]),
			'EMAIL' => $rowset[$i]['user_email'],
			'JOINDATE' => bb_date($rowset[$i]['user_regdate']),
			'LASTVISIT' => bb_date($rowset[$i]['user_lastvisit']),
			'POSTS' => $rowset[$i]['user_posts'],
			'BAN' => ( ( !isset($banned[$rowset[$i]['user_id']]) ) ? $lang['NOT_BANNED'] : $lang['BANNED'] ),
			'ABLED' => ( ( $rowset[$i]['user_active'] ) ? $lang['ENABLED'] : $lang['DISABLED'] ),

			'U_VIEWPOSTS' => "../search.php?search_author=1&amp;uid={$rowset[$i]['user_id']}",
			'U_MANAGE' => '../profile.php?mode=editprofile&'. POST_USERS_URL .'='.$rowset[$i]['user_id'].'&admin=1',
			'U_PERMISSIONS' => 'admin_ug_auth.php?mode=user&'. POST_USERS_URL .'='. $rowset[$i]['user_id'],
		));
	}
}

print_page('admin_user_search.tpl', 'admin');