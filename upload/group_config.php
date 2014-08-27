<?php

define('IN_FORUM', true);
define('BB_SCRIPT', 'group_config');
define('BB_ROOT', './');
require(BB_ROOT .'common.php');
require(INC_DIR .'functions_group.php');

$page_cfg['include_bbcode_js'] = true;

// Start session management
$user->session_start(array('req_login' => true));

$group_id = isset($_REQUEST[POST_GROUPS_URL]) ? intval($_REQUEST[POST_GROUPS_URL]) : null;
$group_info = array();
$is_moderator = false;

$submit = !empty($_POST['submit']);

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

if ($is_moderator)
{
	// TODO Admin panel, some tasty features

	// Avatar
	if ($submit)
	{
		if (!empty($_FILES['avatar']['name']) && $bb_cfg['group_avatars']['up_allowed'])
		{
			require(INC_DIR .'functions_upload.php');
			$upload = new upload_common();

			if ($upload->init($bb_cfg['group_avatars'], $_FILES['avatar']) AND $upload->store('avatar', array("user_id" => GROUP_AVATAR_MASK . $group_id, "avatar_ext_id" => $group_info['avatar_ext_id'])))
			{
				$avatar_ext_id  = (int) $upload->file_ext_id;
			}
			else
			{
				bb_die(implode($upload->errors));
			}

			DB()->query("UPDATE ". BB_GROUPS ." SET avatar_ext_id = $avatar_ext_id WHERE group_id = $group_id LIMIT 1");
		}
	}

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

	$s_hidden_fields = '<input type="hidden" name="'. POST_GROUPS_URL .'" value="'. $group_id .'" />';

	$template->assign_vars(array(
		'PAGE_TITLE'             => $lang['GROUP_CONTROL_PANEL'],
		'GROUP_NAME'             => htmlCHR($group_info['group_name']),
		'GROUP_ID'               => $group_id,
		'GROUP_DESCRIPTION'      => htmlCHR($group_info['group_description']),
		'GROUP_SIGNATURE'        => htmlCHR($group_info['group_signature']),
		'U_GROUP_URL'            => GROUP_URL . $group_id,
		'GROUP_TYPE'             => $group_type,
		'S_GROUP_OPEN_TYPE'      => GROUP_OPEN,
		'S_GROUP_CLOSED_TYPE'    => GROUP_CLOSED,
		'S_GROUP_HIDDEN_TYPE'    => GROUP_HIDDEN,
		'S_GROUP_OPEN_CHECKED'   => ($group_info['group_type'] == GROUP_OPEN) ? ' checked="checked"' : '',
		'S_GROUP_CLOSED_CHECKED' => ($group_info['group_type'] == GROUP_CLOSED) ? ' checked="checked"' : '',
		'S_GROUP_HIDDEN_CHECKED' => ($group_info['group_type'] == GROUP_HIDDEN) ? ' checked="checked"' : '',
		'S_HIDDEN_FIELDS'        => $s_hidden_fields,
		'S_GROUP_CONFIG_ACTION'  => "group_config.php?" . POST_GROUPS_URL . "=$group_id",

		'AVATAR_EXPLAIN'     => sprintf($lang['AVATAR_EXPLAIN'], $bb_cfg['group_avatars']['max_width'], $bb_cfg['group_avatars']['max_height'], (round($bb_cfg['group_avatars']['max_size'] / 1024))),
		'AVATAR_URL_PATH'    => ($group_info['avatar_ext_id']) ? get_avatar_path(GROUP_AVATAR_MASK . $group_id, $group_info['avatar_ext_id']) : '',

		'RELEASE_GROUP'      => ($group_info['release_group']) ? true : false,
	));

	$template->set_filenames(array('body' => 'group_config.tpl'));
	$template->assign_vars(array('PAGE_TITLE' => $lang['GROUP_CONFIGURATION']));

	require(PAGE_HEADER);

	$template->pparse('body');

	require(PAGE_FOOTER);
}
else
{
	$redirect = 'index.php';

	if ($group_id)
	{
		$redirect = GROUP_URL . $group_id;
	}
	redirect($redirect);
}