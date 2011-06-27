<?php

if (!defined('IN_TRACKER')) die(basename(__FILE__));

$rating_msg = '';

if (!$seeder)
{
	foreach ($rating_limits as $ratio => $limit)
	{
		if ($user_ratio < $ratio)
		{
			$tr_cfg['limit_active_tor'] = 1;
			$tr_cfg['limit_leech_count'] = $limit;
			$rating_msg = " (ratio < $ratio)";
			break;
		}
	}
}

// Limit active torrents
if (!isset($bb_cfg['unlimited_users'][$user_id]) && $tr_cfg['limit_active_tor'] && (($tr_cfg['limit_seed_count'] && $seeder) || ($tr_cfg['limit_leech_count'] && !$seeder)))
{
	$sql = "SELECT COUNT(DISTINCT topic_id) AS active_torrents
		FROM ". BB_BT_TRACKER ."
		WHERE user_id = $user_id
			AND seeder = $seeder
			AND topic_id != $topic_id";

	if (!$seeder && $tr_cfg['leech_expire_factor'] && $user_ratio < 0.5)
	{
		$sql .= " AND update_time > ". (TIMENOW - 60*$tr_cfg['leech_expire_factor']);
	}
	$sql .= "	GROUP BY user_id";

	if ($row = DB()->fetch_row($sql))
	{
		if ($seeder && $tr_cfg['limit_seed_count'] && $row['active_torrents'] >= $tr_cfg['limit_seed_count'])
		{
			msg_die('Only '. $tr_cfg['limit_seed_count'] .' torrent(s) allowed for seeding');
		}
		else if (!$seeder && $tr_cfg['limit_leech_count'] && $row['active_torrents'] >= $tr_cfg['limit_leech_count'])
		{
			msg_die('Only '. $tr_cfg['limit_leech_count'] .' torrent(s) allowed for leeching'. $rating_msg);
		}
	}
}

// Limit concurrent IPs
if ($tr_cfg['limit_concurrent_ips'] && (($tr_cfg['limit_seed_ips'] && $seeder) || ($tr_cfg['limit_leech_ips'] && !$seeder)))
{
	$sql = "SELECT COUNT(DISTINCT ip) AS ips
		FROM ". BB_BT_TRACKER ."
		WHERE topic_id = $topic_id
			AND user_id = $user_id
			AND seeder = $seeder
			AND ip != '$ip_sql'";

	if (!$seeder && $tr_cfg['leech_expire_factor'])
	{
		$sql .= " AND update_time > ". (TIMENOW - 60*$tr_cfg['leech_expire_factor']);
	}
	$sql .= "	GROUP BY topic_id";

	if ($row = DB()->fetch_row($sql))
	{
		if ($seeder && $tr_cfg['limit_seed_ips'] && $row['ips'] >= $tr_cfg['limit_seed_ips'])
		{
			msg_die('You can seed only from '. $tr_cfg['limit_seed_ips'] ." IP's");
		}
		else if (!$seeder && $tr_cfg['limit_leech_ips'] && $row['ips'] >= $tr_cfg['limit_leech_ips'])
		{
			msg_die('You can leech only from '. $tr_cfg['limit_leech_ips'] ." IP's");
		}
	}
}