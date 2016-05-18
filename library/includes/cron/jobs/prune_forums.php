<?php

if (!defined('BB_ROOT')) die(basename(__FILE__));

/** @var \TorrentPier\Di $di */
$di = \TorrentPier\Di::getInstance();

require_once(INC_DIR .'functions_admin.php');

if ($di->config->get('prune_enable'))
{
	$sql = "SELECT forum_id, prune_days FROM ". BB_FORUMS ." WHERE prune_days != 0";

	foreach (DB()->fetch_rowset($sql) as $row)
	{
		topic_delete('prune', $row['forum_id'], (TIMENOW - 86400*$row['prune_days']));
	}
}