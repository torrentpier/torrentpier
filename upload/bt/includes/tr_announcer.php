<?php

if (!defined('IN_TRACKER')) die(basename(__FILE__));

db_init();

$seeder  = ($left == 0) ? 1 : 0;
$stopped = ($event === 'stopped');

// Stopped event
if ($stopped)
{
	CACHE('tr_cache')->rm(PEER_HASH_PREFIX . $peer_hash);
	if (DBG_LOG) dbg_log(' ', 'stopped');
}

// Get last peer info from DB
if (!CACHE('tr_cache')->used && !$lp_info)
{
	$lp_info = DB()->fetch_row("
		SELECT * FROM ". BB_BT_TRACKER ." WHERE peer_hash = '$peer_hash' LIMIT 1
	");

	if (DBG_LOG) dbg_log(' ', '$lp_info-get_from-DB-'. ($lp_info ? 'hit' : 'miss'));
}

if ($lp_info)
{
	if (!$stopped)
	{
		drop_fast_announce($lp_info);
	}

	$user_id  = $lp_info['user_id'];
	$topic_id = $lp_info['topic_id'];
	$releaser = $lp_info['releaser'];
	$tor_type = $lp_info['tor_type'];
}
else
{
	// Verify if torrent registered on tracker and user authorized
	$info_hash_sql = rtrim(DB()->escape($info_hash), ' ');
	$passkey_sql   = DB()->escape($passkey);

	$sql = "
		SELECT tor.topic_id, tor.poster_id, tor.tor_type, u.*
		FROM ". BB_BT_TORRENTS ." tor
		LEFT JOIN ". BB_BT_USERS ." u ON u.auth_key = '$passkey_sql'
		WHERE tor.info_hash = '$info_hash_sql'
		LIMIT 1
	";

	$row = DB()->fetch_row($sql);

	if (empty($row['topic_id']))
	{
		msg_die('Torrent not registered, info_hash = ' . bin2hex($info_hash_sql));
	}
	if (empty($row['user_id']))
	{
		msg_die('Please LOG IN and REDOWNLOAD this torrent (user not found)');
	}

	$user_id  = $row['user_id'];
	$topic_id = $row['topic_id'];
	$releaser = (int) ($user_id == $row['poster_id']);
	$tor_type = $row['tor_type'];

	// Ratio limits
	if ((TR_RATING_LIMITS || $tr_cfg['limit_concurrent_ips']) && !$stopped)
	{
		$user_ratio = ($row['u_down_total'] && $row['u_down_total'] > MIN_DL_FOR_RATIO) ? ($row['u_up_total'] + $row['u_up_release'] + $row['u_up_bonus']) / $row['u_down_total'] : 1;
		require(TR_ROOT .'includes/tr_ratio.php');
	}
}

// Up/Down speed
$speed_up = $speed_down = 0;

if ($lp_info && $lp_info['update_time'] < TIMENOW)
{
	if ($uploaded > $lp_info['uploaded'])
	{
		$speed_up = ceil(($uploaded - $lp_info['uploaded']) / (TIMENOW - $lp_info['update_time']));
	}
	if ($downloaded > $lp_info['downloaded'])
	{
		$speed_down = ceil(($downloaded - $lp_info['downloaded']) / (TIMENOW - $lp_info['update_time']));
	}
}

// Up/Down addition
$up_add = ($lp_info && $uploaded > $lp_info['uploaded']) ? $uploaded - $lp_info['uploaded'] : 0;
$down_add = ($lp_info && $downloaded > $lp_info['downloaded']) ? $downloaded - $lp_info['downloaded'] : 0;

// Gold/Silver releases
if ($bb_cfg['gold_silver_enabled'] && $down_add)
{
	if ($tor_type == TOR_TYPE_GOLD)
	{
		$down_add = 0;
	}
	// Silver releases
	else if ($tor_type == TOR_TYPE_SILVER)
	{
		$down_add = ceil($down_add/2);
	}
}

// Insert/update peer info
$peer_info_updated = false;
$update_time = ($stopped) ? 0 : TIMENOW;

if ($lp_info)
{
	$sql  = "UPDATE ". BB_BT_TRACKER ." SET update_time = $update_time";

	$sql .= ", seeder = $seeder";
	$sql .= ($releaser != $lp_info['releaser']) ? ", releaser = $releaser" : '';

	$sql .= ($tor_type != $lp_info['tor_type']) ? ", tor_type = $tor_type" : '';

	$sql .= ($uploaded != $lp_info['uploaded']) ? ", uploaded = $uploaded" : '';
	$sql .= ($downloaded != $lp_info['downloaded']) ? ", downloaded = $downloaded" : '';
	$sql .= ", remain = $left";

	$sql .= ($up_add) ? ", up_add = up_add + $up_add" : '';
	$sql .= ($down_add) ? ", down_add = down_add + $down_add" : '';

	$sql .= ", speed_up = $speed_up";
	$sql .= ", speed_down = $speed_down";

	$sql .= " WHERE peer_hash = '$peer_hash'";
	$sql .= " LIMIT 1";

	DB()->query($sql);

	$peer_info_updated = DB()->affected_rows();

	if (DBG_LOG) dbg_log(' ', 'this_peer-update'. ($peer_info_updated ? '' : '-FAIL'));
}

if (!$lp_info || !$peer_info_updated)
{
	$columns = 'peer_hash,    topic_id,  user_id,   ip,       port,  seeder,  releaser, tor_type,  uploaded,  downloaded, remain, speed_up,  speed_down,  up_add,  down_add,  update_time';
	$values = "'$peer_hash', $topic_id, $user_id, '$ip_sql', $port, $seeder, $releaser, $tor_type, $uploaded, $downloaded, $left, $speed_up, $speed_down, $up_add, $down_add, $update_time";

	DB()->query("REPLACE INTO ". BB_BT_TRACKER ." ($columns) VALUES ($values)");

	if (DBG_LOG) dbg_log(' ', 'this_peer-insert');
}

// Exit if stopped
if ($stopped)
{
	silent_exit();
}

// Store peer info in cache
$lp_info = array(
	'downloaded'  => (float) $downloaded,
	'releaser'    => (int)   $releaser,
	'seeder'      => (int)   $seeder,
	'topic_id'    => (int)   $topic_id,
	'update_time' => (int)   TIMENOW,
	'uploaded'    => (float) $uploaded,
	'user_id'     => (int)   $user_id,
	'tor_type'    => (int)   $tor_type,
);

$lp_info_cached = CACHE('tr_cache')->set(PEER_HASH_PREFIX . $peer_hash, $lp_info, PEER_HASH_EXPIRE);

if (DBG_LOG && !$lp_info_cached) dbg_log(' ', '$lp_info-caching-FAIL');

// Get cached output
$output = CACHE('tr_cache')->get(PEERS_LIST_PREFIX . $topic_id);

if (DBG_LOG) dbg_log(' ', '$output-get_from-CACHE-'. ($output !== false ? 'hit' : 'miss'));

if (!$output)
{
	// Retrieve peers
	$numwant      = (int) $tr_cfg['numwant'];
	$compact_mode = ($tr_cfg['compact_mode'] || !empty($compact));

	$rowset = DB()->fetch_rowset("
		SELECT ip, port
		FROM ". BB_BT_TRACKER ."
		WHERE topic_id = $topic_id
		ORDER BY RAND()
		LIMIT $numwant
	");

	if ($compact_mode)
	{
		$peers = '';

		foreach ($rowset as $peer)
		{
			$peers .= pack('Nn', ip2long(decode_ip($peer['ip'])), $peer['port']);
		}
	}
	else
	{
		$peers = array();

		foreach ($rowset as $peer)
		{
			$peers[] = array(
				'ip'   => decode_ip($peer['ip']),
				'port' => intval($peer['port']),
			);
		}
	}

	$seeders  = 0;
	$leechers = 0;

	if ($tr_cfg['scrape'])
	{
		$row = DB()->fetch_row("
			SELECT seeders, leechers
			FROM ". BB_BT_TRACKER_SNAP ."
			WHERE topic_id = $topic_id
			LIMIT 1
		");

		$seeders  = $row['seeders'];
		$leechers = $row['leechers'];
	}

	$output = array(
		'interval'     => (int) $announce_interval,
		'min interval' => (int) $announce_interval,
		'peers'        => $peers,
		'complete'     => (int) $seeders,
		'incomplete'   => (int) $leechers,
	);

	$peers_list_cached = CACHE('tr_cache')->set(PEERS_LIST_PREFIX . $topic_id, $output, PEERS_LIST_EXPIRE);

	if (DBG_LOG && !$peers_list_cached) dbg_log(' ', '$output-caching-FAIL');
}

// Return data to client
echo bencode($output);

tracker_exit();
