<?php

define('IN_FORUM',   true);
define('BB_SCRIPT', 'groupcp');
define('BB_ROOT', './');
require(BB_ROOT ."common.php");
require(INC_DIR .'functions_group.php');

$page_cfg['use_tablesorter'] = true;

$s_member_groups = $s_pending_groups = $s_member_groups_opt = $s_pending_groups_opt = '';
$select_sort_mode = $select_sort_order = '';

function generate_user_info(&$row, $date_format, $group_mod, &$from, &$posts, &$joined, &$pm, &$email, &$www, &$user_time)
{
	global $lang, $images, $bb_cfg;

	$from   = ( !empty($row['user_from']) ) ? $row['user_from'] : '';
	$joined = bb_date($row['user_regdate']);
	$user_time   = ( !empty($row['user_time']) ) ? bb_date($row['user_time']) : $lang['NONE'];
	$posts  = ( $row['user_posts'] ) ? $row['user_posts'] : 0;
	$pm     = ($bb_cfg['text_buttons']) ? '<a class="txtb" href="'. (PM_URL . "?mode=post&amp;". POST_USERS_URL ."=".$row['user_id']) .'">'. $lang['SEND_PM_TXTB'] .'</a>' : '<a href="' . (PM_URL . "?mode=post&amp;". POST_USERS_URL ."=".$row['user_id']) .'"><img src="' . $images['icon_pm'] . '" alt="' . $lang['SEND_PRIVATE_MESSAGE'] . '" title="' . $lang['SEND_PRIVATE_MESSAGE'] . '" border="0" /></a>';

	if (bf($row['user_opt'], 'user_opt', 'user_viewemail') || $group_mod)
	{
		$email_uri = ($bb_cfg['board_email_form']) ? ("profile.php?mode=email&amp;". POST_USERS_URL ."=".$row['user_id']) : 'mailto:'. $row['user_email'];
		$email = '<a class="editable" href="'. $email_uri .'">'. $row['user_email'] .'</a>';
	}
	else $email = '';

	if ($row['user_website'])
	{
		$www = ($bb_cfg['text_buttons']) ? '<a class="txtb" href="'. $row['user_website'] .'"  target="_userwww">'. $lang['VISIT_WEBSITE_TXTB'] .'</a>' : '<a class="txtb" href="'. $row['user_website'] .'" target="_userwww"><img src="' . $images['icon_www'] . '" alt="' . $lang['VISIT_WEBSITE'] . '" title="' . $lang['VISIT_WEBSITE'] . '" border="0" /></a>';
	}
	else $www = '';

	return;
}

$user->session_start(array('req_login' => true));

set_die_append_msg();

$group_id = isset($_REQUEST[POST_GROUPS_URL]) ? intval($_REQUEST[POST_GROUPS_URL]) : null;
$start    = isset($_REQUEST['start']) ? abs(intval($_REQUEST['start'])) : 0;
$per_page = $bb_cfg['groupcp_members_per_page'];

$group_info = array();
$is_moderator = false;

if ($group_id)
{
	if (!$group_info = get_group_data($group_id))
	{
		bb_die($lang['GROUP_NOT_EXIST']);
	}
	if (!$group_info['group_id'] || !$group_info['group_moderator'] || !$group_info['moderator_name'])
	{
		bb_die("Invalid group data [group_id: $group_id]");
	}
	$is_moderator = ($userdata['user_id'] == $group_info['group_moderator'] || IS_ADMIN);
}

if (!$group_id)
{
	// Show the main screen where the user can select a group.
	$groups = array();
	$pending = 10;
	$member  = 20;

	$sql = "
		SELECT
			g.group_name, g.group_description, g.group_id, g.group_type,
			IF(ug.user_id IS NOT NULL, IF(ug.user_pending = 1, $pending, $member), 0) AS membership,
			g.group_moderator, u.username AS moderator_name,
			IF(g.group_moderator = ug.user_id, 1, 0) AS is_group_mod,
			COUNT(ug2.user_id) AS members, SUM(ug2.user_pending) AS candidates
		FROM
			". BB_GROUPS ." g
		LEFT JOIN
			". BB_USER_GROUP ." ug ON
			    ug.group_id = g.group_id
			AND ug.user_id = ". $userdata['user_id'] ."
		LEFT JOIN
			". BB_USER_GROUP ." ug2 ON
			    ug2.group_id = g.group_id
		LEFT JOIN
			". BB_USERS ." u ON g.group_moderator = u.user_id
		WHERE
			g.group_single_user = 0
		GROUP BY g.group_id
		ORDER BY
			is_group_mod DESC,
			membership   DESC,
			g.group_type ASC,
			g.group_name ASC
	";

	foreach (DB()->fetch_rowset($sql) as $row)
	{
		if ($row['is_group_mod'])
		{
			$type = 'MOD';
		}
		else if ($row['membership'] == $member)
		{
			$type = 'MEMBER';
		}
		else if ($row['membership'] == $pending)
		{
			$type = 'PENDING';
		}
		else if ($row['group_type'] == GROUP_OPEN)
		{
			$type = 'OPEN';
		}
		else if ($row['group_type'] == GROUP_CLOSED)
		{
			$type = 'CLOSED';
		}
		else if ($row['group_type'] == GROUP_HIDDEN && IS_ADMIN)
		{
			$type = 'HIDDEN';
		}
		else
		{
			continue;
		}

		$data = array('id' => $row['group_id'], 'm' => ($row['members'] - $row['candidates']), 'c' => $row['candidates']);

		$groups[$type][$row['group_name']] = $data;
	}

	function build_group($params)
	{
		global $lang;

		$options = '';
		foreach ($params as $name => $data)
		{
			$text  = htmlCHR(str_short(rtrim($name), HTML_SELECT_MAX_LENGTH));

			$members = ($data['m']) ? $lang['MEMBERS_IN_GROUP'] .': '. $data['m'] : $lang['NO_GROUP_MEMBERS'];
			$candidates  = ($data['c']) ? $lang['PENDING_MEMBERS'] .': '. $data['c'] : $lang['NO_PENDING_GROUP_MEMBERS'];

			$options .= '<li class="pad_2"><a href="'. GROUP_URL . $data['id'] .'" class="med bold">'. $text .'</a></li>';
			$options .= '<ul><li class="seedmed">'. $members .'</li>';
			if (IS_AM)
			{
				$options .= '<li class="leechmed">'. $candidates .'</li>';
			}
			$options .= '</ul>';

		}
		return $options;
	}

	if ($groups)
	{
		$s_hidden_fields = '';

		foreach ($groups as $type => $grp)
		{
			$template->assign_block_vars('groups', array(
				'MEMBERSHIP'   => $lang["GROUP_MEMBER_{$type}"],
				'GROUP_SELECT' => build_group($grp),
			));
		}

		$template->assign_vars(array(
			'SELECT_GROUP'       => true,
			'PAGE_TITLE'         => $lang['GROUP_CONTROL_PANEL'],
			'S_USERGROUP_ACTION' => 'groupcp.php',
			'S_HIDDEN_FIELDS'    => $s_hidden_fields,
		));
	}
	else
	{
		if(IS_ADMIN)
		{
			redirect('admin/admin_groups.php');
		}
		else bb_die($lang['NO_GROUPS_EXIST']);
	}
}
else if (!empty($_POST['groupstatus']))
{
	if (!$is_moderator)
	{
		bb_die($lang['NOT_GROUP_MODERATOR']);
	}

	$new_group_type = (int) $_POST['group_type'];

	if (!in_array($new_group_type, array(GROUP_OPEN, GROUP_CLOSED, GROUP_HIDDEN), true))
	{
		bb_die("Invalid group type: $new_group_type");
	}

	DB()->query("
		UPDATE ". BB_GROUPS ." SET
			group_type = $new_group_type
		WHERE group_id = $group_id
			AND group_single_user = 0
		LIMIT 1
	");

	$message = $lang['GROUP_TYPE_UPDATED'] .'<br /><br />';
	$message .= sprintf($lang['CLICK_RETURN_GROUP'], '<a href="'. GROUP_URL ."$group_id" .'">', '</a>') .'<br /><br />';
	$message .= sprintf($lang['CLICK_RETURN_INDEX'], '<a href="'. "index.php" .'">', '</a>');

	bb_die($message);
}
else if (@$_POST['joingroup'])
{
	if ($group_info['group_type'] != GROUP_OPEN)
	{
		bb_die($lang['THIS_CLOSED_GROUP']);
	}

	$sql = "SELECT g.group_id, g.group_name, ug.user_id, u.user_email, u.username, u.user_lang
		FROM ". BB_GROUPS ." g
		LEFT JOIN ". BB_USERS ." u ON(u.user_id = g.group_moderator)
		LEFT JOIN ". BB_USER_GROUP ." ug ON(ug.group_id = g.group_id AND ug.user_id = {$userdata['user_id']})
		WHERE g.group_id = $group_id
			AND group_single_user = 0
			AND g.group_type = ". GROUP_OPEN ."
		LIMIT 1";

	$row = $moderator = DB()->fetch_row($sql);

	if (!$row['group_id'])
	{
		bb_die($lang['NO_GROUPS_EXIST']);
	}
	if ($row['user_id'])
	{
		bb_die($lang['ALREADY_MEMBER_GROUP']);
	}

	add_user_into_group($group_id, $userdata['user_id'], 1, TIMENOW);

	if ($bb_cfg['groupcp_send_email'])
	{
		require(INC_DIR .'emailer.class.php');
		$emailer = new emailer($bb_cfg['smtp_delivery']);

		$emailer->from($bb_cfg['sitename'] ." <{$bb_cfg['board_email']}>");
		$emailer->email_address($moderator['username'] ." <{$moderator['user_email']}>");

		$emailer->use_template('group_request', $moderator['user_lang']);

		$emailer->assign_vars(array(
			'USER'            => $userdata['username'],
			'SITENAME'        => $bb_cfg['sitename'],
			'GROUP_MODERATOR' => $moderator['username'],
			'U_GROUPCP'       => make_url(GROUP_URL . $group_id),
		));

		$emailer->send();
		$emailer->reset();
	}

	$message = $lang['GROUP_JOINED'] .'<br /><br />';
	$message .= sprintf($lang['CLICK_RETURN_GROUP'], '<a href="'. GROUP_URL ."$group_id" .'">', '</a>') .'<br /><br />';
	$message .= sprintf($lang['CLICK_RETURN_INDEX'], '<a href="'. "index.php" .'">', '</a>');

	bb_die($message);
}
else if (!empty($_POST['unsub']) || !empty($_POST['unsubpending']))
{
	delete_user_group($group_id, $userdata['user_id']);

	$message = $lang['UNSUB_SUCCESS'] .'<br /><br />';
	$message .= sprintf($lang['CLICK_RETURN_GROUP'], '<a href="'. GROUP_URL ."$group_id" .'">', '</a>') .'<br /><br />';
	$message .= sprintf($lang['CLICK_RETURN_INDEX'], '<a href="'. "index.php" .'">', '</a>');

	bb_die($message);
}
else
{
	// Handle Additions, removals, approvals and denials
	$group_moderator = $group_info['group_moderator'];

	if (!empty($_POST['add']) || !empty($_POST['remove']) || !empty($_POST['approve']) || !empty($_POST['deny']))
	{
		if (!$is_moderator)
		{
			bb_die($lang['NOT_GROUP_MODERATOR']);
		}

		if (!empty($_POST['add']))
		{
			if (!$row = get_userdata(@$_POST['username'], true))
			{
				bb_die($lang['COULD_NOT_ADD_USER']);
			}

			add_user_into_group($group_id, $row['user_id']);

			if ($bb_cfg['groupcp_send_email'])
			{
				require(INC_DIR .'emailer.class.php');
				$emailer = new emailer($bb_cfg['smtp_delivery']);

				$emailer->from($bb_cfg['sitename'] ." <{$bb_cfg['board_email']}>");
				$emailer->email_address($row['username'] ." <{$row['user_email']}>");

				$emailer->use_template('group_added', $row['user_lang']);

				$emailer->assign_vars(array(
					'SITENAME'   => $bb_cfg['sitename'],
					'GROUP_NAME' => $group_info['group_name'],
					'U_GROUPCP'  => make_url(GROUP_URL . $group_id),
				));

				$emailer->send();
				$emailer->reset();
			}
		}
		else
		{
			if (((!empty($_POST['approve']) || !empty($_POST['deny'])) && !empty($_POST['pending_members'])) || (!empty($_POST['remove']) && !empty($_POST['members'])))
			{
				$members = (!empty($_POST['approve']) || !empty($_POST['deny'])) ? $_POST['pending_members'] : $_POST['members'];

				$sql_in = array();
				foreach ($members as $members_id)
				{
					$sql_in[] = (int) $members_id;
				}
				if (!$sql_in = join(',', $sql_in))
				{
					bb_die($lang['NONE_SELECTED']);
				}

				if (!empty($_POST['approve']))
				{
					DB()->query("
						UPDATE ". BB_USER_GROUP ." SET
							user_pending = 0
						WHERE user_id IN($sql_in)
							AND group_id = $group_id
					");

					update_user_level($sql_in);
				}
				else if (!empty($_POST['deny']) || !empty($_POST['remove']))
				{
					DB()->query("
						DELETE FROM ". BB_USER_GROUP ."
						WHERE user_id IN($sql_in)
							AND group_id = $group_id
					");

					if (!empty($_POST['remove']))
					{
						update_user_level($sql_in);
					}
				}
				// Email users when they are approved
				if (!empty($_POST['approve']) && $bb_cfg['groupcp_send_email'])
				{
					$sql_select = "SELECT username, user_email, user_lang
						FROM ". BB_USERS ."
						WHERE user_id IN($sql_in)";

					if (!$result = DB()->sql_query($sql_select))
					{
						bb_die('Could not get user email information');
					}

					require(INC_DIR .'emailer.class.php');
					$emailer = new emailer($bb_cfg['smtp_delivery']);

					$emailer->from($bb_cfg['sitename'] ." <{$bb_cfg['board_email']}>");

					foreach (DB()->fetch_rowset($sql_select) as $row)
					{
						$emailer->use_template('group_approved', $row['user_lang']);
						$emailer->email_address($row['username'] ." <{$row['user_email']}>");
					}

					$emailer->assign_vars(array(
						'SITENAME'   => $bb_cfg['sitename'],
						'GROUP_NAME' => $group_info['group_name'],
						'U_GROUPCP'  => make_url(GROUP_URL . $group_id),
					));

					$emailer->send();
					$emailer->reset();
				}
			}
		}
	}
	// END approve or deny

	// Get moderator details for this group
	$group_moderator = DB()->fetch_row("
		SELECT *
		FROM ". BB_USERS ."
		WHERE user_id = ". $group_info['group_moderator'] ."
	");

	// Members
	$count_members = DB()->fetch_rowset("
		SELECT u.username, u.user_rank, u.user_id, u.user_opt, u.user_posts, u.user_regdate, u.user_from, u.user_website, u.user_email, ug.user_pending, ug.user_time
		FROM ". BB_USER_GROUP ." ug, ". BB_USERS ." u
		WHERE ug.group_id = $group_id
			AND ug.user_pending = 0
			AND ug.user_id <> ". $group_moderator['user_id'] ."
			AND u.user_id = ug.user_id
		ORDER BY u.username
	");
	$count_members = count($count_members);

	// Get user information for this group
	$modgroup_pending_count = 0;

	// Members
	$group_members = DB()->fetch_rowset("
		SELECT u.username, u.user_rank, u.user_id, u.user_opt, u.user_posts, u.user_regdate, u.user_from, u.user_website, u.user_email, ug.user_pending, ug.user_time
		FROM ". BB_USER_GROUP ." ug, ". BB_USERS ." u
		WHERE ug.group_id = $group_id
			AND ug.user_pending = 0
			AND ug.user_id <> ". $group_moderator['user_id'] ."
			AND u.user_id = ug.user_id
		ORDER BY u.username
		LIMIT $start, $per_page
	");
	$members_count = count($group_members);

	generate_pagination(GROUP_URL . $group_id, $count_members, $per_page, $start);

	// Pending
	if ($is_moderator)
	{
		$modgroup_pending_list = DB()->fetch_rowset("
			SELECT u.username, u.user_rank, u.user_id, u.user_opt, u.user_posts, u.user_regdate, u.user_from, u.user_website, u.user_email
			FROM ". BB_USER_GROUP ." ug, ". BB_USERS ." u
			WHERE ug.group_id = $group_id
				AND ug.user_pending = 1
				AND u.user_id = ug.user_id
			ORDER BY u.username
			LIMIT 200
		");
		$modgroup_pending_count = count($modgroup_pending_list);
	}

	// Current user membership
	$is_group_member = $is_group_pending_member = false;

	$sql = "SELECT user_pending
		FROM ". BB_USER_GROUP ."
		WHERE group_id = $group_id
			AND user_id = ". $userdata['user_id'] ."
		LIMIT 1";

	if ($row = DB()->fetch_row($sql))
	{
		if ($row['user_pending'] == 0)
		{
			$is_group_member = true;
		}
		else
		{
			$is_group_pending_member = true;
		}
	}

	if ($userdata['user_id'] == $group_moderator['user_id'])
	{
		$group_details = $lang['ARE_GROUP_MODERATOR'];
		$s_hidden_fields = '<input type="hidden" name="'. POST_GROUPS_URL .'" value="'. $group_id .'" />';
	}
	else if ($is_group_member || $is_group_pending_member)
	{
		$template->assign_vars(array(
			'SHOW_UNSUBSCRIBE_CONTROLS' => true,
			'CONTROL_NAME' => ($is_group_member) ? 'unsub' : 'unsubpending',
		));
		$group_details = ($is_group_pending_member) ? $lang['PENDING_THIS_GROUP'] : $lang['MEMBER_THIS_GROUP'];
		$s_hidden_fields = '<input type="hidden" name="'. POST_GROUPS_URL .'" value="'. $group_id .'" />';
	}
	else if (IS_GUEST)
	{
		$group_details = $lang['LOGIN_TO_JOIN'];
		$s_hidden_fields = '';
	}
	else
	{
		if ($group_info['group_type'] == GROUP_OPEN)
		{
			$template->assign_var('SHOW_SUBSCRIBE_CONTROLS');

			$group_details = $lang['THIS_OPEN_GROUP'];
			$s_hidden_fields = '<input type="hidden" name="'. POST_GROUPS_URL .'" value="'. $group_id .'" />';
		}
		else if ($group_info['group_type'] == GROUP_CLOSED)
		{
			$group_details = $lang['THIS_CLOSED_GROUP'];
			$s_hidden_fields = '';
		}
		else if ($group_info['group_type'] == GROUP_HIDDEN)
		{
			$group_details = $lang['THIS_HIDDEN_GROUP'];
			$s_hidden_fields = '';
		}
	}

	// Add the moderator
	$username = $group_moderator['username'];
	$user_id = $group_moderator['user_id'];

	generate_user_info($group_moderator, $bb_cfg['default_dateformat'], $is_moderator, $from, $posts, $joined, $pm, $email, $www, $user_time);

	$group_type = '';
	if ($group_info['group_type'] == GROUP_OPEN)
	{
		$group_type = $lang['GROUP_OPEN'];
	}
	elseif ($group_info['group_type'] == GROUP_CLOSED)
	{
		$group_type = $lang['GROUP_CLOSED'];
	}
	elseif ($group_info['group_type'] == GROUP_HIDDEN)
	{
		$group_type = $lang['GROUP_HIDDEN'];
	}

	$i = 0;
	$template->assign_vars(array(
		'ROW_NUMBER'             => $i + ($start + 1),
		'GROUP_INFO'             => true,
		'PAGE_TITLE'             => $lang['GROUP_CONTROL_PANEL'],
		'GROUP_NAME'             => htmlCHR($group_info['group_name']),
		'GROUP_DESCRIPTION'      => $group_info['group_description'],
		'GROUP_DETAILS'          => $group_details,
		'MOD_USER'               => profile_url($group_moderator),
		'MOD_FROM'               => $from,
		'MOD_JOINED'             => $joined,
		'MOD_POSTS'              => $posts,
		'MOD_PM'                 => $pm,
		'MOD_EMAIL'              => $email,
		'MOD_WWW'                => $www,
		'MOD_TIME'               => (!empty($group_info['group_time'])) ? bb_date($group_info['group_time']) : $lang['NONE'],
		'U_SEARCH_USER'          => "search.php?mode=searchuser",
		'GROUP_TYPE'             => $group_type,
		'S_GROUP_OPEN_TYPE'      => GROUP_OPEN,
		'S_GROUP_CLOSED_TYPE'    => GROUP_CLOSED,
		'S_GROUP_HIDDEN_TYPE'    => GROUP_HIDDEN,
		'S_GROUP_OPEN_CHECKED'   => ($group_info['group_type'] == GROUP_OPEN) ? ' checked="checked"' : '',
		'S_GROUP_CLOSED_CHECKED' => ($group_info['group_type'] == GROUP_CLOSED) ? ' checked="checked"' : '',
		'S_GROUP_HIDDEN_CHECKED' => ($group_info['group_type'] == GROUP_HIDDEN) ? ' checked="checked"' : '',
		'S_HIDDEN_FIELDS'        => $s_hidden_fields,
		'S_MODE_SELECT'          => $select_sort_mode,
		'S_ORDER_SELECT'         => $select_sort_order,
		'S_GROUPCP_ACTION'       => "groupcp.php?" . POST_GROUPS_URL . "=$group_id",
	));

	// Dump out the remaining users
	foreach ($group_members as $i => $member)
	{
		$user_id = $member['user_id'];

		generate_user_info($member, $bb_cfg['default_dateformat'], $is_moderator, $from, $posts, $joined, $pm, $email, $www, $user_time);

		if ($group_info['group_type'] != GROUP_HIDDEN || $is_group_member || $is_moderator)
		{
			$row_class = !($i % 2) ? 'row1' : 'row2';

			$template->assign_block_vars('member', array(
				'ROW_NUMBER' => $i + ( $start + 1 ),
				'ROW_CLASS'  => $row_class,
				'USER'       => profile_url($member),
				'FROM'       => $from,
				'JOINED'     => $joined,
				'POSTS'      => $posts,
				'USER_ID'    => $user_id,
				'PM'         => $pm,
				'EMAIL'      => $email,
				'WWW'        => $www,
				'TIME'       => $user_time,
			));

			if ($is_moderator)
			{
				$template->assign_block_vars('member.switch_mod_option', array());
			}
		}
	}

	// No group members
	if (!$members_count)
	{
		$template->assign_block_vars('switch_no_members', array());
	}

	// No group members
	if ($group_info['group_type'] == GROUP_HIDDEN && !$is_group_member && !$is_moderator)
	{
		$template->assign_block_vars('switch_hidden_group', array());
	}

	//
	// We've displayed the members who belong to the group, now we
	// do that pending memebers...
	//
	if ($is_moderator && $modgroup_pending_list)
	{
		foreach ($modgroup_pending_list as $i => $member)
		{
			$user_id = $member['user_id'];

			generate_user_info($member, $bb_cfg['default_dateformat'], $is_moderator, $from, $posts, $joined, $pm, $email, $www, $user_time);

			$row_class = !($i % 2) ? 'row1' : 'row2';

			$user_select = '<input type="checkbox" name="member[]" value="'. $user_id .'">';

			$template->assign_block_vars('pending', array(
				'ROW_CLASS' => $row_class,
				'USER'      => profile_url($member),
				'FROM'      => $from,
				'JOINED'    => $joined,
				'POSTS'     => $posts,
				'USER_ID'   => $user_id,
				'PM'        => $pm,
				'EMAIL'     => $email,
			));
		}

		$template->assign_vars(array(
			'PENDING_USERS' => true,
		));
	}

	if ($is_moderator)
	{
		$template->assign_block_vars('switch_mod_option', array());
		$template->assign_block_vars('switch_add_member', array());
	}
}

print_page('groupcp.tpl');