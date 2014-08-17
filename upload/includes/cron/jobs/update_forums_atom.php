<?php

if (!defined('BB_ROOT')) die(basename(__FILE__));

global $bb_cfg;

require_once(INC_DIR .'functions_atom.php');

$timecheck = TIMENOW - 600;
$forums_data = DB()->fetch_rowset("SELECT forum_id, allow_reg_tracker, forum_name FROM ". BB_FORUMS);

if (file_exists($bb_cfg['atom']['path'] .'/f/0.atom') && filemtime($bb_cfg['atom']['path'] .'/f/0.atom') <= $timecheck)
{
	update_forum_feed(0, $forums_data);
}

foreach ($forums_data as $forum_data)
{
	if (file_exists($bb_cfg['atom']['path'] .'/f/'. $forum_data['forum_id'] .'.atom') && filemtime($bb_cfg['atom']['path'] .'/f/'. $forum_data['forum_id'] .'.atom') <= $timecheck)
	{
		update_forum_feed($forum_data['forum_id'], $forum_data);
	}
}