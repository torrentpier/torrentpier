<?php
/**
*
* @package attachment_mod
* @version $Id: functions_includes.php,v 1.3 2005/11/06 16:32:19 acydburn Exp $
* @copyright (c) 2002 Meik Sievertsen
* @license http://opensource.org/licenses/gpl-license.php GNU Public License
*
*/

/**
* These are functions called directly from phpBB2 Files
*/

/**
* Include the FAQ-File (faq.php)
*/
function attach_faq_include($lang_file)
{
	global $bb_cfg, $faq, $attach_config;

	if (intval($attach_config['disable_mod']))
	{
		return;
	}

	if ($lang_file == 'lang_faq')
	{
		$language = attach_mod_get_lang('lang_faq_attach');
		include(LANG_ROOT_DIR ."lang_$language/lang_faq_attach.php");
	}
}

/**
* Setup Basic Authentication
*/
// moved to auth

/**
* Setup Forum Authentication (admin/admin_forumauth.php)
*/
//admin/admin_forumauth.php


/**
* Setup Usergroup Authentication
*/
//admin/admin_ug_auth.php

/**
* Setup s_auth_can in viewforum and viewtopic (viewtopic.php/viewforum.php)
*/
function attach_build_auth_levels($is_auth, &$s_auth_can)
{
	global $lang, $attach_config, $forum_id;

	if (intval($attach_config['disable_mod']))
	{
		return;
	}

	// If you want to have the rules window link within the forum view too, comment out the two lines, and comment the third line
//	$rules_link = '(<a href="' . BB_ROOT . 'attach_rules.php?f=' . $forum_id . '" target="_blank">Rules</a>)';
//	$s_auth_can .= ( ( $is_auth['auth_attachments'] ) ? $rules_link . ' ' . $lang['RULES_ATTACH_CAN'] : $lang['RULES_ATTACH_CANNOT'] ) . '<br />';
	$s_auth_can .= (($is_auth['auth_attachments']) ? $lang['RULES_ATTACH_CAN'] : $lang['RULES_ATTACH_CANNOT'] ) . '<br />';

	$s_auth_can .= (($is_auth['auth_download']) ? $lang['RULES_DOWNLOAD_CAN'] : $lang['RULES_DOWNLOAD_CANNOT'] ) . '<br />';
}

/**
* Called from admin_users.php and admin_groups.php in order to process Quota Settings (admin/admin_users.php:admin/admin_groups.php)
*/
function attachment_quota_settings($admin_mode, $submit = false, $mode)
{
	global $template, $lang, $attach_config;

	if (!intval($attach_config['allow_ftp_upload']))
	{
		if ($attach_config['upload_dir'][0] == '/' || ($attach_config['upload_dir'][0] != '/' && $attach_config['upload_dir'][1] == ':'))
		{
			$upload_dir = $attach_config['upload_dir'];
		}
		else
		{
			$upload_dir = BB_ROOT . $attach_config['upload_dir'];
		}
	}
	else
	{
		$upload_dir = $attach_config['download_path'];
	}

	include(BB_ROOT .'attach_mod/includes/functions_selects.php');
	if (!function_exists("process_quota_settings"))
		include(BB_ROOT . 'attach_mod/includes/functions_admin.php');

	$user_id = 0;

	if ($admin_mode == 'user')
	{
		// We overwrite submit here... to be sure
		$submit = (isset($_POST['submit'])) ? true : false;

		if (!$submit && $mode != 'save')
		{
			$user_id = get_var(POST_USERS_URL, 0);
			$u_name = get_var('username', '');

			if (!$user_id && !$u_name)
			{
				message_die(GENERAL_MESSAGE, $lang['NO_USER_ID_SPECIFIED'] );
			}

			if ($user_id)
			{
				$this_userdata['user_id'] = $user_id;
			}
			else
			{
				// Get userdata is handling the sanitizing of username
				$this_userdata = get_userdata($_POST['username'], true);
			}

			$user_id = (int) $this_userdata['user_id'];
		}
		else
		{
			$user_id = get_var('id', 0);

			if (!$user_id)
			{
				message_die(GENERAL_MESSAGE, $lang['NO_USER_ID_SPECIFIED'] );
			}
		}
	}

	if ($admin_mode == 'user' && !$submit && $mode != 'save')
	{
		// Show the contents
		$sql = 'SELECT quota_limit_id, quota_type FROM ' . BB_QUOTA . '
			WHERE user_id = ' . (int) $user_id;

		if( !($result = DB()->sql_query($sql)) )
		{
			message_die(GENERAL_ERROR, 'Unable to get Quota Settings', '', __LINE__, __FILE__, $sql);
		}

		$pm_quota = $upload_quota = 0;

		if ($row = DB()->sql_fetchrow($result))
		{
			do
			{
				if ($row['quota_type'] == QUOTA_UPLOAD_LIMIT)
				{
					$upload_quota = $row['quota_limit_id'];
				}
				else if ($row['quota_type'] == QUOTA_PM_LIMIT)
				{
					$pm_quota = $row['quota_limit_id'];
				}
			}
			while ($row = DB()->sql_fetchrow($result));
		}
		else
		{
			// Set Default Quota Limit
			$upload_quota = $attach_config['default_upload_quota'];
			$pm_quota = $attach_config['default_pm_quota'];

		}
		DB()->sql_freeresult($result);

		$template->assign_vars(array(
			'S_SELECT_UPLOAD_QUOTA'		=> quota_limit_select('user_upload_quota', $upload_quota),
			'S_SELECT_PM_QUOTA'			=> quota_limit_select('user_pm_quota', $pm_quota),
		));
	}

	if ($admin_mode == 'user' && $submit && @$_POST['deleteuser'])
	{
		process_quota_settings($admin_mode, $user_id, QUOTA_UPLOAD_LIMIT, 0);
		process_quota_settings($admin_mode, $user_id, QUOTA_PM_LIMIT, 0);
	}
	else if ($admin_mode == 'user' && $submit && $mode == 'save')
	{
		// Get the contents
		$upload_quota = get_var('user_upload_quota', 0);
		$pm_quota = get_var('user_pm_quota', 0);

		process_quota_settings($admin_mode, $user_id, QUOTA_UPLOAD_LIMIT, $upload_quota);
		process_quota_settings($admin_mode, $user_id, QUOTA_PM_LIMIT, $pm_quota);
	}

	if ($admin_mode == 'group' && $mode == 'newgroup')
	{
		return;
	}

	if ($admin_mode == 'group' && !$submit && isset($_POST['edit']))
	{
		// Get group id again, we do not trust phpBB here, Mods may be installed ;)
		$group_id = get_var(POST_GROUPS_URL, 0);

		// Show the contents
		$sql = 'SELECT quota_limit_id, quota_type FROM ' . BB_QUOTA . '
			WHERE group_id = ' . (int) $group_id;

		if( !($result = DB()->sql_query($sql)) )
		{
			message_die(GENERAL_ERROR, 'Unable to get Quota Settings', '', __LINE__, __FILE__, $sql);
		}

		$pm_quota = $upload_quota = 0;

		if ($row = DB()->sql_fetchrow($result))
		{
			do
			{
				if ($row['quota_type'] == QUOTA_UPLOAD_LIMIT)
				{
					$upload_quota = $row['quota_limit_id'];
				}
				else if ($row['quota_type'] == QUOTA_PM_LIMIT)
				{
					$pm_quota = $row['quota_limit_id'];
				}
			}
			while ($row = DB()->sql_fetchrow($result));
		}
		else
		{
			// Set Default Quota Limit
			$upload_quota = $attach_config['default_upload_quota'];
			$pm_quota = $attach_config['default_pm_quota'];
		}
		DB()->sql_freeresult($result);

		$template->assign_vars(array(
			'S_SELECT_UPLOAD_QUOTA'	=> quota_limit_select('group_upload_quota', $upload_quota),
			'S_SELECT_PM_QUOTA'		=> quota_limit_select('group_pm_quota', $pm_quota),
		));
	}

	if ($admin_mode == 'group' && $submit && isset($_POST['group_delete']))
	{
		$group_id = get_var(POST_GROUPS_URL, 0);

		process_quota_settings($admin_mode, $group_id, QUOTA_UPLOAD_LIMIT, 0);
		process_quota_settings($admin_mode, $group_id, QUOTA_PM_LIMIT, 0);
	}
	else if ($admin_mode == 'group' && $submit)
	{
		$group_id = get_var(POST_GROUPS_URL, 0);

		// Get the contents
		$upload_quota = get_var('group_upload_quota', 0);
		$pm_quota = get_var('group_pm_quota', 0);

		process_quota_settings($admin_mode, $group_id, QUOTA_UPLOAD_LIMIT, $upload_quota);
		process_quota_settings($admin_mode, $group_id, QUOTA_PM_LIMIT, $pm_quota);
	}

}

/**
* Called from usercp_viewprofile, displays the User Upload Quota Box, Upload Stats and a Link to the User Attachment Control Panel
* Groups are able to be grabbed, but it's not used within the Attachment Mod. ;)
* (includes/usercp_viewprofile.php)
*/
function display_upload_attach_box_limits($user_id, $group_id = 0)
{
	global $attach_config, $bb_cfg, $lang, $template, $userdata, $profiledata;

	if (intval($attach_config['disable_mod']))
	{
		return;
	}

	if (!IS_ADMIN && $userdata['user_id'] != $user_id)
	{
		return;
	}

	if (!$user_id)
	{
		return;
	}

	// Return if the user is not within the to be listed Group
	if ($group_id)
	{
		if (!user_in_group($user_id, $group_id))
		{
			return;
		}
	}

	$user_id = (int) $user_id;
	$group_id = (int) $group_id;

	$attachments = new attach_posting();
	$attachments->page = 0;

	// Get the assigned Quota Limit. For Groups, we are directly getting the value, because this Quota can change from user to user.
	if ($group_id)
	{
		$sql = 'SELECT l.quota_limit
			FROM ' . BB_QUOTA . ' q, ' . BB_QUOTA_LIMITS . ' l
			WHERE q.group_id = ' . (int) $group_id . '
				AND q.quota_type = ' . QUOTA_UPLOAD_LIMIT . '
				AND q.quota_limit_id = l.quota_limit_id
			LIMIT 1';

		if ( !($result = DB()->sql_query($sql)) )
		{
			message_die(GENERAL_ERROR, 'Could not get Group Quota', '', __LINE__, __FILE__, $sql);
		}

		if (DB()->num_rows($result) > 0)
		{
			$row = DB()->sql_fetchrow($result);
			$attach_config['upload_filesize_limit'] = intval($row['quota_limit']);
			DB()->sql_freeresult($result);
		}
		else
		{
			DB()->sql_freeresult($result);

			// Set Default Quota Limit
			$quota_id = intval($attach_config['default_upload_quota']);

			if ($quota_id == 0)
			{
				$attach_config['upload_filesize_limit'] = $attach_config['attachment_quota'];
			}
			else
			{
				$sql = 'SELECT quota_limit
					FROM ' . BB_QUOTA_LIMITS . '
					WHERE quota_limit_id = ' . (int) $quota_id . '
					LIMIT 1';

				if ( !($result = DB()->sql_query($sql)) )
				{
					message_die(GENERAL_ERROR, 'Could not get Quota Limit', '', __LINE__, __FILE__, $sql);
				}

				if (DB()->num_rows($result) > 0)
				{
					$row = DB()->sql_fetchrow($result);
					$attach_config['upload_filesize_limit'] = $row['quota_limit'];
				}
				else
				{
					$attach_config['upload_filesize_limit'] = $attach_config['attachment_quota'];
				}
				DB()->sql_freeresult($result);
			}
		}
	}
	else
	{
		if (is_array($profiledata))
		{
			$attachments->get_quota_limits($profiledata, $user_id);
		}
		else
		{
			$attachments->get_quota_limits($userdata, $user_id);
		}
	}

	if (!$attach_config['upload_filesize_limit'])
	{
		$upload_filesize_limit = $attach_config['attachment_quota'];
	}
	else
	{
		$upload_filesize_limit = $attach_config['upload_filesize_limit'];
	}

	if ($upload_filesize_limit == 0)
	{
		$user_quota = $lang['UNLIMITED'];
	}
	else
	{
		$size_lang = ($upload_filesize_limit >= 1048576) ? $lang['MB'] : ( ($upload_filesize_limit >= 1024) ? $lang['KB'] : $lang['BYTES'] );

		if ($upload_filesize_limit >= 1048576)
		{
			$user_quota = (round($upload_filesize_limit / 1048576 * 100) / 100) . ' ' . $size_lang;
		}
		else if ($upload_filesize_limit >= 1024)
		{
			$user_quota = (round($upload_filesize_limit / 1024 * 100) / 100) . ' ' . $size_lang;
		}
		else
		{
			$user_quota = ($upload_filesize_limit) . ' ' . $size_lang;
		}
	}

	// Get all attach_id's the specific user posted, but only uploads to the board and not Private Messages
	$sql = 'SELECT attach_id
		FROM ' . BB_ATTACHMENTS . '
		WHERE user_id_1 = ' . (int) $user_id . '
		GROUP BY attach_id';

	if ( !($result = DB()->sql_query($sql)) )
	{
		message_die(GENERAL_ERROR, 'Couldn\'t query attachments', '', __LINE__, __FILE__, $sql);
	}

	$attach_ids = DB()->sql_fetchrowset($result);
	$num_attach_ids = DB()->num_rows($result);
	DB()->sql_freeresult($result);
	$attach_id = array();

	for ($j = 0; $j < $num_attach_ids; $j++)
	{
		$attach_id[] = intval($attach_ids[$j]['attach_id']);
	}

	$upload_filesize = (sizeof($attach_id) > 0) ? get_total_attach_filesize($attach_id) : 0;

	$size_lang = ($upload_filesize >= 1048576) ? $lang['MB'] : ( ($upload_filesize >= 1024) ? $lang['KB'] : $lang['BYTES'] );

	if ($upload_filesize >= 1048576)
	{
		$user_uploaded = (round($upload_filesize / 1048576 * 100) / 100) . ' ' . $size_lang;
	}
	else if ($upload_filesize >= 1024)
	{
		$user_uploaded = (round($upload_filesize / 1024 * 100) / 100) . ' ' . $size_lang;
	}
	else
	{
		$user_uploaded = ($upload_filesize) . ' ' . $size_lang;
	}

	$upload_limit_pct = ( $upload_filesize_limit > 0 ) ? round(( $upload_filesize / $upload_filesize_limit ) * 100) : 0;
	$upload_limit_img_length = ( $upload_filesize_limit > 0 ) ? round(( $upload_filesize / $upload_filesize_limit ) * $bb_cfg['privmsg_graphic_length']) : 0;
	if ($upload_limit_pct > 100)
	{
		$upload_limit_img_length = $bb_cfg['privmsg_graphic_length'];
	}
	$upload_limit_remain = ( $upload_filesize_limit > 0 ) ? $upload_filesize_limit - $upload_filesize : 100;

	$l_box_size_status = sprintf($lang['UPLOAD_PERCENT_PROFILE'], $upload_limit_pct);

	$template->assign_block_vars('switch_upload_limits', array());

	$template->assign_vars(array(
		'L_UACP'			=> $lang['UACP'],
		'U_UACP'			=> BB_ROOT ."profile.php?mode=attachcp&amp;u=$user_id&amp;sid={$userdata['session_id']}",
		'UPLOADED' 			=> sprintf($lang['USER_UPLOADED_PROFILE'], $user_uploaded),
		'QUOTA' 			=> sprintf($lang['USER_QUOTA_PROFILE'], $user_quota),
		'UPLOAD_LIMIT_IMG_WIDTH' 	=> $upload_limit_img_length,
		'UPLOAD_LIMIT_PERCENT' 		=> $upload_limit_pct,
		'PERCENT_FULL' 		=> $l_box_size_status,
	));
}

/**
* Prune Attachments (includes/prune.php)
*/
function prune_attachments($sql_post)
{
	delete_attachment($sql_post);
}