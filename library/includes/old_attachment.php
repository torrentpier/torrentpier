<?php

function get_attachments_from_post($post_id_array)
{
	global $attach_config;

	$attachments = array();

	if (!is_array($post_id_array))
	{
		if (empty($post_id_array))
		{
			return $attachments;
		}

		$post_id = intval($post_id_array);

		$post_id_array = array();
		$post_id_array[] = $post_id;
	}

	$post_id_array = implode(', ', array_map('intval', $post_id_array));

	if ($post_id_array == '')
	{
		return $attachments;
	}

	$display_order = (intval($attach_config['display_order']) == 0) ? 'DESC' : 'ASC';

	$sql = 'SELECT a.post_id, d.*
		FROM ' . BB_ATTACHMENTS . ' a, ' . BB_ATTACHMENTS_DESC . " d
		WHERE a.post_id IN ($post_id_array)
			AND a.attach_id = d.attach_id
		ORDER BY d.filetime $display_order";

	if (!($result = DB()->sql_query($sql)))
	{
		bb_die('Could not get attachment informations for post number ' . $post_id_array);
	}

	$num_rows = DB()->num_rows($result);
	$attachments = DB()->sql_fetchrowset($result);
	DB()->sql_freeresult($result);

	if ($num_rows == 0)
	{
		return array();
	}

	return $attachments;
}


/**
* Get attachment mod configuration
*/
function get_config()
{
	global $bb_cfg;

	$attach_config = array();

	$sql = 'SELECT * FROM ' . BB_ATTACH_CONFIG;

	if (!($result = DB()->sql_query($sql)))
	{
		bb_die('Could not query attachment information');
	}

	while ($row = DB()->sql_fetchrow($result))
	{
		$attach_config[$row['config_name']] = trim($row['config_value']);
	}

	// We assign the original default board language here, because it gets overwritten later with the users default language
	$attach_config['board_lang'] = trim($bb_cfg['default_lang']);

	return $attach_config;
}

// Get Attachment Config
$attach_config = array();

if (!$attach_config = CACHE('bb_cache')->get('attach_config'))
{
	$attach_config = get_config();
	CACHE('bb_cache')->set('attach_config', $attach_config, 86400);
}

/**
 * Writing Data into plain Template Vars
 *
 * @param        $template_var
 * @param        $replacement
 * @param string $filename
 */
function init_display_template($template_var, $replacement, $filename = 'viewtopic_attach.tpl')
{
	global $template;

	// This function is adapted from the old template class
	// I wish i had the functions from the 3.x one. :D (This class rocks, can't await to use it in Mods)

	// Handle Attachment Informations
	if (!isset($template->uncompiled_code[$template_var]) && empty($template->uncompiled_code[$template_var]))
	{
		// If we don't have a file assigned to this handle, die.
		if (!isset($template->files[$template_var]))
		{
			die("Template->loadfile(): No file specified for handle $template_var");
		}

		$filename_2 = $template->files[$template_var];

		$str = implode('', @file($filename_2));
		if (empty($str))
		{
			die("Template->loadfile(): File $filename_2 for handle $template_var is empty");
		}

		$template->uncompiled_code[$template_var] = $str;
	}

	$complete_filename = $filename;
	if (substr($complete_filename, 0, 1) != '/')
	{
		$complete_filename = $template->root . '/' . $complete_filename;
	}

	if (!file_exists($complete_filename))
	{
		die("Template->make_filename(): Error - file $complete_filename does not exist");
	}

	$content = implode('', file($complete_filename));
	if (empty($content))
	{
		die('Template->loadfile(): File ' . $complete_filename . ' is empty');
	}

	// replace $replacement with uncompiled code in $filename
	$template->uncompiled_code[$template_var] = str_replace($replacement, $content, $template->uncompiled_code[$template_var]);
}

/**
 * Display Attachments in Posts
 *
 * @param $post_id
 * @param $switch_attachment
 */
function display_post_attachments($post_id, $switch_attachment)
{
	global $attach_config, $is_auth;

	if (intval($switch_attachment) == 0 || intval($attach_config['disable_mod']))
	{
		return;
	}

	if ($is_auth['auth_download'] && $is_auth['auth_view'])
	{
		display_attachments($post_id);
	}
}

/**
 * Initializes some templating variables for displaying Attachments in Posts
 *
 * @param $switch_attachment
 */
function init_display_post_attachments($switch_attachment)
{
	global $attach_config, $is_auth, $template, $lang, $postrow, $total_posts, $attachments, $forum_row, $t_data;

	if (empty($t_data) && !empty($forum_row))
	{
		$switch_attachment = $forum_row['topic_attachment'];
	}

	if (intval($switch_attachment) == 0 || intval($attach_config['disable_mod']) || (!($is_auth['auth_download'] && $is_auth['auth_view'])))
	{
		init_display_template('body', '{postrow.ATTACHMENTS}', 'viewtopic_attach_guest.tpl');
		return;
	}

	$post_id_array = array();

	for ($i = 0; $i < $total_posts; $i++)
	{
		if ($postrow[$i]['post_attachment'] == 1)
		{
			$post_id_array[] = (int) $postrow[$i]['post_id'];
		}
	}

	if (sizeof($post_id_array) == 0)
	{
		return;
	}

	$rows = get_attachments_from_post($post_id_array);
	$num_rows = sizeof($rows);

	if ($num_rows == 0)
	{
		return;
	}

	@reset($attachments);

	for ($i = 0; $i < $num_rows; $i++)
	{
		$attachments['_' . $rows[$i]['post_id']][] = $rows[$i];
		//bt
		if ($rows[$i]['tracker_status'])
		{
			if (defined('TORRENT_POST'))
			{
				bb_die('Multiple registered torrents in one topic<br /><br />first torrent found in post_id = '. TORRENT_POST .'<br />current post_id = '. $rows[$i]['post_id'] .'<br /><br />attachments info:<br /><pre style="text-align: left;">'. print_r($rows, TRUE) .'</pre>');
			}
			define('TORRENT_POST', $rows[$i]['post_id']);
		}
		//bt end
	}

	init_display_template('body', '{postrow.ATTACHMENTS}');

}

function display_attachments($post_id)
{
	global $template, $upload_dir, $userdata, $allowed_extensions, $display_categories, $download_modes, $lang, $attachments, $upload_icons, $attach_config;

	$num_attachments = @sizeof($attachments['_' . $post_id]);

	if ($num_attachments == 0)
	{
		return;
	}

	$template->assign_block_vars('postrow.attach', array());

	for ($i = 0; $i < $num_attachments; $i++)
	{
		$upload_image = '';

		if ($attach_config['upload_img'] && empty($upload_icons[$attachments['_' . $post_id][$i]['extension']]))
		{
			$upload_image = '<img src="' . $attach_config['upload_img'] . '" alt="" border="0" />';
		}
		else if (trim($upload_icons[$attachments['_' . $post_id][$i]['extension']]) != '')
		{
			$upload_image = '<img src="' . $upload_icons[$attachments['_' . $post_id][$i]['extension']] . '" alt="" border="0" />';
		}

		$filesize = humn_size($attachments['_' . $post_id][$i]['filesize']);

		$display_name = htmlspecialchars($attachments['_' . $post_id][$i]['real_filename']);
		$comment = htmlspecialchars($attachments['_' . $post_id][$i]['comment']);
		$comment = str_replace("\n", '<br />', $comment);

		$denied = false;

		if (!$denied || IS_ADMIN)
		{
			$target_blank = ( (@intval($display_categories[$attachments['_' . $post_id][$i]['extension']]) == IMAGE_CAT) ) ? 'target="_blank"' : '';

			// display attachment
			$template->assign_block_vars('postrow.attach.attachrow', array(
				'U_DOWNLOAD_LINK' => BB_ROOT . DOWNLOAD_URL . $attachments['_' . $post_id][$i]['attach_id'],
				'S_UPLOAD_IMAGE'  => $upload_image,
				'DOWNLOAD_NAME'   => $display_name,
				'FILESIZE'        => $filesize,
				'COMMENT'         => $comment,
				'TARGET_BLANK'    => $target_blank,
				'DOWNLOAD_COUNT'  => sprintf($lang['DOWNLOAD_NUMBER'], $attachments['_' . $post_id][$i]['download_count']),
			));
		}
	}
}

$upload_dir = $attach_config['upload_dir'];