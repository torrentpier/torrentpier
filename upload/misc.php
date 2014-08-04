<?php

define('IN_FORUM', true);
define('BB_SCRIPT', 'misc');
define('BB_ROOT', './');
require(BB_ROOT .'common.php');

// Start Session Management
$user->session_start();

$do = request_var('do', '');

if ($do == 'attach_rules')
{
	if (!$forum_id = @intval(request_var('f', '')) OR !forum_exists($forum_id))
	{
		bb_die('invalid forum_id');
	}
	require(ATTACH_DIR .'attachment_mod.php');
	// Display the allowed Extension Groups and Upload Size
	$auth = auth(AUTH_ALL, $forum_id, $userdata);
	$_max_filesize = $attach_config['max_filesize'];

	if (!$auth['auth_attachments'] || !$auth['auth_view'])
	{
		bb_die('You are not allowed to call this file');
	}

	$sql = 'SELECT group_id, group_name, max_filesize, forum_permissions
		FROM ' . BB_EXTENSION_GROUPS . '
		WHERE allow_group = 1
		ORDER BY group_name ASC';

	if (!($result = DB()->sql_query($sql)))
	{
		bb_die('Could not query extension groups');
	}

	$allowed_filesize = array();
	$rows = DB()->sql_fetchrowset($result);
	$num_rows = DB()->num_rows($result);
	DB()->sql_freeresult($result);

	// Ok, only process those Groups allowed within this forum
	$nothing = true;
	for ($i = 0; $i < $num_rows; $i++)
	{
		$auth_cache = trim($rows[$i]['forum_permissions']);

		$permit = ((is_forum_authed($auth_cache, $forum_id)) || trim($rows[$i]['forum_permissions']) == '');

		if ($permit)
		{
			$nothing = false;
			$group_name = $rows[$i]['group_name'];
			$f_size = intval(trim($rows[$i]['max_filesize']));
			$det_filesize = (!$f_size) ? $_max_filesize : $f_size;

			$max_filesize = (!$det_filesize) ? $lang['UNLIMITED'] : humn_size($det_filesize);

			$template->assign_block_vars('group_row', array(
				'GROUP_RULE_HEADER' => sprintf($lang['GROUP_RULE_HEADER'], $group_name, $max_filesize),
			));

			$sql = 'SELECT extension
				FROM ' . BB_EXTENSIONS . "
				WHERE group_id = " . (int) $rows[$i]['group_id'] . "
				ORDER BY extension ASC";

			if (!($result = DB()->sql_query($sql)))
			{
				bb_die('Could not query extensions');
			}

			$e_rows = DB()->sql_fetchrowset($result);
			$e_num_rows = DB()->num_rows($result);
			DB()->sql_freeresult($result);

			for ($j = 0; $j < $e_num_rows; $j++)
			{
				$template->assign_block_vars('group_row.extension_row', array(
					'EXTENSION' => $e_rows[$j]['extension'],
				));
			}
		}
	}

	$template->assign_vars(array(
		'PAGE_TITLE' => $lang['ATTACH_RULES_TITLE'],
	));

	if ($nothing)
	{
		$template->assign_block_vars('switch_nothing', array());
	}

	print_page('attach_rules.tpl', 'simple');
}
elseif ($do == 'info')
{
	$req_mode = (string) request_var('show', 'not_found');
	if(preg_match('/\//i', $req_mode))
	{
		die('Include detected!');
	}
	if(preg_match('/</i', $req_mode))
	{
		die('XSS detected!');
	}
	$req_mode = clean_filename(basename($req_mode));

	$html_dir = LANG_DIR . 'html/';
	$require = file_exists($html_dir . $req_mode .'.html') ? $html_dir . $req_mode .'.html' : $html_dir . 'not_found.html';

	$in_info = true;

	?><!DOCTYPE html>
	<html dir="ltr">
	<head>
		<meta http-equiv="Content-Type" content="text/html; charset=UTF-8" />
		<meta http-equiv="Content-Style-Type" content="text/css" />

		<link rel="stylesheet" href="./templates/default/css/main.css" type="text/css">
	</head>
	<body>

	<style type="text/css">
	#infobox-wrap { width: 760px; }
	#infobox-body {
		background: #FFFFFF; color: #000000; padding: 1em;
		height: 400px; overflow: auto; border: 1px inset #000000;
	}
	</style>

	<br />
	<?php require($require) ?>
	</body>
	</html>
	<?php
}
else
{
	bb_die('Invalid mode');
}