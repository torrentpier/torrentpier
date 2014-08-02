<?php

if (!defined('BB_ROOT')) die(basename(__FILE__));

$users_per_cycle = 1000;

require_once(INC_DIR .'functions_admin.php');

$not_del_users = EXCLUDED_USERS_CSV;

while (true)
{
	@set_time_limit(600);

	$prune_users = $not_activated_users = $not_active_users = array();

	if ($not_activated_days = intval($bb_cfg['user_not_activated_days_keep']))
	{
		$sql = DB()->fetch_rowset("SELECT user_id FROM ". BB_USERS ."
			WHERE user_level      = 0
			AND user_lastvisit    = 0
			AND user_session_time = 0
			AND user_regdate      <= ". (TIMENOW - 86400 * $not_activated_days) ."
			AND user_id           NOT IN($not_del_users)
			LIMIT $users_per_cycle");

		foreach ($sql as $row)
		{
			$not_activated_users[] = $row['user_id'];
		}
	}

	if ($not_active_days = intval($bb_cfg['user_not_active_days_keep']))
	{
		$sql = DB()->fetch_rowset("SELECT user_id FROM ". BB_USERS ."
			WHERE user_level   = 0
			AND user_posts     = 0
			AND user_lastvisit <= ". (TIMENOW - 86400 * $not_active_days) ."
			AND user_id        NOT IN($not_del_users)
			LIMIT $users_per_cycle");

		foreach ($sql as $row)
		{
			$not_active_users[] = $row['user_id'];
		}
	}

	if ($prune_users = $not_activated_users + $not_active_users)
	{
		user_delete($prune_users);
	}

	if (count($prune_users) < $users_per_cycle)
	{
		break;
	}

	sleep(3);
}