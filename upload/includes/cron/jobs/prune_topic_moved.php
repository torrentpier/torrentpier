<?php

if (!defined('BB_ROOT')) die(basename(__FILE__));

if ($bb_cfg['topic_moved_days_keep'])
{
	$prune_time = TIMENOW - 86400*$bb_cfg['topic_moved_days_keep'];

	DB()->query("
		DELETE FROM ". BB_TOPICS ."
		WHERE topic_status = ". TOPIC_MOVED ."
			AND topic_time < $prune_time
	");
}