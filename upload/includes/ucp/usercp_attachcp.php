<?php

if ( !defined('IN_PHPBB') )
{
	die("Hacking attempt");
	exit;
}

require(BB_ROOT .'attach_mod/attachment_mod.php');

if (!$userdata['session_logged_in'])
{
	redirect("login.php?redirect={$_SERVER['REQUEST_URI']}");
}

// Page config
$page_cfg['use_tablesorter']   = true;

// Obtain initial var settings
$user_id = request_var(POST_USERS_URL, 0);

if (!$user_id)
{
	message_die(GENERAL_MESSAGE, $lang['NO_USER_ID_SPECIFIED']);
}

$profiledata = get_userdata($user_id);

if ($profiledata['user_id'] != $userdata['user_id'] && !IS_ADMIN)
{
	message_die(GENERAL_MESSAGE, $lang['NOT_AUTHORISED']);
}

$language = $bb_cfg['default_lang'];
if (!file_exists(LANG_ROOT_DIR ."lang_$language/lang_admin_attach.php"))
{
	$language = $attach_config['board_lang'];
}

include(LANG_ROOT_DIR ."lang_$language/lang_admin_attach.php");

$start      = request_var('start', 0);
$sort_order = request_var('order', 'ASC');
$sort_order = ($sort_order == 'ASC') ? 'ASC' : 'DESC';
$mode       = request_var('mode_a', '');

$mode_types_text = array($lang['SORT_FILENAME'], $lang['SORT_COMMENT'], $lang['SORT_EXTENSION'], $lang['SORT_SIZE'], $lang['SORT_DOWNLOADS'], $lang['SORT_POSTTIME'], /*$lang['SORT_POSTS']*/);
$mode_types = array('real_filename', 'comment', 'extension', 'filesize', 'downloads', 'post_time'/*, 'posts'*/);

if (!$mode)
{
	$mode = 'real_filename';
	$sort_order = 'ASC';
}

// Pagination?
$do_pagination = true;

// Set Order
$order_by = '';

switch ($mode)
{
	case 'filename':
		$order_by = 'ORDER BY a.real_filename ' . $sort_order . ' LIMIT ' . $start . ', ' . $bb_cfg['topics_per_page'];
		break;
	case 'comment':
		$order_by = 'ORDER BY a.comment ' . $sort_order . ' LIMIT ' . $start . ', ' . $bb_cfg['topics_per_page'];
		break;
	case 'extension':
		$order_by = 'ORDER BY a.extension ' . $sort_order . ' LIMIT ' . $start . ', ' . $bb_cfg['topics_per_page'];
		break;
	case 'filesize':
		$order_by = 'ORDER BY a.filesize ' . $sort_order . ' LIMIT ' . $start . ', ' . $bb_cfg['topics_per_page'];
		break;
	case 'downloads':
		$order_by = 'ORDER BY a.download_count ' . $sort_order . ' LIMIT ' . $start . ', ' . $bb_cfg['topics_per_page'];
		break;
	case 'post_time':
		$order_by = 'ORDER BY a.filetime ' . $sort_order . ' LIMIT ' . $start . ', ' . $bb_cfg['topics_per_page'];
		break;
	default:
		$mode = 'a.real_filename';
		$sort_order = 'ASC';
		$order_by = 'ORDER BY a.real_filename ' . $sort_order . ' LIMIT ' . $start . ', ' . $bb_cfg['topics_per_page'];
	break;
}

// Set select fields
$select_sort_mode = $select_sort_order = '';

if (sizeof($mode_types_text) > 0)
{
	$select_sort_mode = '<select name="mode_a">';

	for ($i = 0; $i < sizeof($mode_types_text); $i++)
	{
		$selected = ($mode == $mode_types[$i]) ? ' selected="selected"' : '';
		$select_sort_mode .= '<option value="' . $mode_types[$i] . '"' . $selected . '>' . $mode_types_text[$i] . '</option>';
	}
	$select_sort_mode .= '</select>';
}

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

$delete = (isset($_POST['delete'])) ? true : false;
$delete_id_list = (isset($_POST['delete_id_list'])) ? array_map('intval', $_POST['delete_id_list']) : array();

$confirm = (isset($_POST['confirm']) && $_POST['confirm']) ? true : false;

if ($confirm && sizeof($delete_id_list) > 0)
{
	$attachments = array();

	for ($i = 0; $i < sizeof($delete_id_list); $i++)
	{
		$sql = 'SELECT post_id
			FROM ' . BB_ATTACHMENTS . '
			WHERE attach_id = ' . intval($delete_id_list[$i]) . '
				AND user_id_1 = ' . intval($profiledata['user_id']);
		$result = DB()->sql_query($sql);

		if ($result)
		{
			$row = DB()->sql_fetchrow($result);
			DB()->sql_freeresult($result);

			delete_attachment(0, intval($delete_id_list[$i]));
		}
	}
}
else if ($delete && sizeof($delete_id_list) > 0)
{
	// Not confirmed, show confirmation message
	$hidden_fields = '<input type="hidden" name="view" value="' . @$view . '" />';
	$hidden_fields .= '<input type="hidden" name="mode_a" value="' . $mode . '" />';
	$hidden_fields .= '<input type="hidden" name="order" value="' . $sort_order . '" />';
	$hidden_fields .= '<input type="hidden" name="' . POST_USERS_URL . '" value="' . intval($profiledata['user_id']) . '" />';
	$hidden_fields .= '<input type="hidden" name="start" value="' . $start . '" />';
	$hidden_fields .= '<input type="hidden" name="sid" value="' . $userdata['session_id'] . '" />';

	for ($i = 0; $i < sizeof($delete_id_list); $i++)
	{
		$hidden_fields .= '<input type="hidden" name="delete_id_list[]" value="' . intval($delete_id_list[$i]) . '" />';
	}

	print_confirmation(array(
		'QUESTION'      => $lang['CONFIRM_DELETE_ATTACHMENTS'],
		'FORM_ACTION'   => "profile.php?mode=attachcp",
		'HIDDEN_FIELDS' => $hidden_fields,
	));
}

$hidden_fields = '';

$total_rows = 0;

$username = $profiledata['username'];

$s_hidden = '<input type="hidden" name="' . POST_USERS_URL . '" value="' . intval($profiledata['user_id']) . '">';
$s_hidden .= '<input type="hidden" name="sid" value="' . $userdata['session_id'] . '" />';

// Assign Template Vars
$template->assign_vars(array(
	'PAGE_TITLE' => $lang['USER_ACP_TITLE'],
	'USERNAME' => $profiledata['username'],
	'S_USER_HIDDEN' => $s_hidden,
	'S_MODE_ACTION'	=> BB_ROOT ."profile.php?mode=attachcp",
	'S_MODE_SELECT' => $select_sort_mode,
	'S_ORDER_SELECT' => $select_sort_order)
);

$sql = "SELECT attach_id
	FROM " . BB_ATTACHMENTS . "
	WHERE user_id_1 = " . intval($profiledata['user_id']) . "
	GROUP BY attach_id";

if ( !($result = DB()->sql_query($sql)) )
{
	message_die(GENERAL_ERROR, 'Couldn\'t query attachments', '', __LINE__, __FILE__, $sql);
}

$attach_ids = DB()->sql_fetchrowset($result);
$num_attach_ids = DB()->num_rows($result);
DB()->sql_freeresult($result);

$total_rows = $num_attach_ids;

$attachments = array();

if ($num_attach_ids > 0)
{
	$attach_id = array();

	for ($j = 0; $j < $num_attach_ids; $j++)
	{
		$attach_id[] = (int) $attach_ids[$j]['attach_id'];
	}

	$sql = "SELECT a.*
		FROM " . BB_ATTACHMENTS_DESC . " a
		WHERE a.attach_id IN (" . join(', ', $attach_id) . ") " .
		$order_by;

	if ( !($result = DB()->sql_query($sql)) )
	{
		message_die(GENERAL_ERROR, "Couldn't query attachments", '', __LINE__, __FILE__, $sql);
	}

	$attachments = DB()->sql_fetchrowset($result);
	$num_attach = DB()->num_rows($result);
	DB()->sql_freeresult($result);
}

if (sizeof($attachments) > 0)
{
	for ($i = 0; $i < sizeof($attachments); $i++)
	{
		// Is the Attachment assigned to more than one post?
		// If it's not assigned to any post, it's an private message thingy. ;)
		$post_titles = array();

		$sql = 'SELECT *
			FROM ' . BB_ATTACHMENTS . '
			WHERE attach_id = ' . (int) $attachments[$i]['attach_id'];

		if (!($result = DB()->sql_query($sql)))
		{
			message_die(GENERAL_ERROR, 'Couldn\'t query attachments', '', __LINE__, __FILE__, $sql);
		}

		$ids = DB()->sql_fetchrowset($result);
		$num_ids = DB()->num_rows($result);
		DB()->sql_freeresult($result);

		for ($j = 0; $j < $num_ids; $j++)
		{
			if ($ids[$j]['post_id'] != 0)
			{
				$sql = "SELECT t.topic_title
					FROM " . BB_TOPICS . " t, " . BB_POSTS . " p
					WHERE p.post_id = " . (int) $ids[$j]['post_id'] . " AND p.topic_id = t.topic_id
					GROUP BY t.topic_id, t.topic_title";

				if ( !($result = DB()->sql_query($sql)) )
				{
					message_die(GENERAL_ERROR, 'Couldn\'t query topic', '', __LINE__, __FILE__, $sql);
				}

				$row = DB()->sql_fetchrow($result);
				DB()->sql_freeresult($result);

				$post_title = $row['topic_title'];

				$post_title = str_short($post_title, 30);

				$view_topic = BB_ROOT .'viewtopic.php?' . POST_POST_URL . '=' . $ids[$j]['post_id'] . '#' . $ids[$j]['post_id'];

				$post_titles[] = '<a href="' . $view_topic . '" class="gen" target="_blank">' . $post_title . '</a>';
			}
		}

		// Iron out those Attachments assigned to us, but not more controlled by us. ;) (PM's)
		if (sizeof($post_titles) > 0)
		{
			$delete_box = '<input type="checkbox" class="a-chbox" name="delete_id_list[]" value="' . (int) $attachments[$i]['attach_id'] . '" />';

			for ($j = 0; $j < sizeof($delete_id_list); $j++)
			{
				if ($delete_id_list[$j] == $attachments[$i]['attach_id'])
				{
					$delete_box = '<input type="checkbox" name="delete_id_list[]" value="' . (int) $attachments[$i]['attach_id'] . '" checked />';
					break;
				}
			}

			$post_titles = join('<br />', $post_titles);

			$hidden_field = '<input type="hidden" name="attach_id_list[]" value="' . (int) $attachments[$i]['attach_id'] . '">';
			$hidden_field .= '<input type="hidden" name="sid" value="' . $userdata['session_id'] . '" />';

			$comment = str_replace("\n", '<br />', $attachments[$i]['comment']);

			$template->assign_block_vars('attachrow', array(
				'ROW_NUMBER'		=> $i + ($start + 1 ),

				'ATTACH_ID'         => $attachments[$i]['attach_id'],
				'FILENAME'          => htmlspecialchars($attachments[$i]['real_filename']),
				'COMMENT'			=> htmlspecialchars($comment),
				'EXTENSION'			=> $attachments[$i]['extension'],
				'SIZE_RAW'	 		=> $attachments[$i]['filesize'],
				'SIZE'				=> round(($attachments[$i]['filesize'] / MEGABYTE), 2),
				'DOWNLOAD_COUNT'	=> $attachments[$i]['download_count'],
				'POST_TIME_RAW'		=> $attachments[$i]['filetime'],
				'POST_TIME'			=> bb_date($attachments[$i]['filetime']),
				'POST_TITLE'		=> $post_titles,

				'S_DELETE_BOX'      => $delete_box,
				'S_HIDDEN'          => $hidden_field,
				'U_VIEW_ATTACHMENT' => BB_ROOT .'download.php?id=' . $attachments[$i]['attach_id'])
	//			'U_VIEW_POST' => ($attachments[$i]['post_id'] != 0) ? "../viewtopic.php?" . POST_POST_URL . "=" . $attachments[$i]['post_id'] . "#" . $attachments[$i]['post_id'] : '')
			);
		}
	}
}

// Generate Pagination
if ($do_pagination && $total_rows > $bb_cfg['topics_per_page'])
{
	generate_pagination(BB_ROOT ."profile.php?mode=attachcp&amp;mode_a=$mode&amp;order=$sort_order&amp;" . POST_USERS_URL . '=' . $profiledata['user_id'] . '&amp;sid=' . $userdata['session_id'], $total_rows, $bb_cfg['topics_per_page'], $start).'&nbsp;';
}

print_page('usercp_attachcp.tpl');
