<?php

if (!defined('BB_ROOT')) die(basename(__FILE__));

if (empty($bb_cfg['seeder_last_seen_days_keep']) || empty($bb_cfg['seeder_never_seen_days_keep']))
{
	return;
}

$last_seen_time = TIMENOW - 86400*$bb_cfg['seeder_last_seen_days_keep'];
$never_seen_time = TIMENOW - 86400*$bb_cfg['seeder_never_seen_days_keep'];
$limit_sql = 3000;

$topics_sql = $attach_sql = array();

$sql = "SELECT topic_id, attach_id
	FROM ". BB_BT_TORRENTS ."
	WHERE reg_time < $never_seen_time
		AND seeder_last_seen < $last_seen_time
	LIMIT $limit_sql";

foreach (DB()->fetch_rowset($sql) as $row)
{
	$topics_sql[] = $row['topic_id'];
	$attach_sql[] = $row['attach_id'];
}
$dead_tor_sql = join(',', $topics_sql);
$attach_sql = join(',', $attach_sql);

if ($dead_tor_sql && $attach_sql)
{
	// Delete torstat
	DB()->query("
		DELETE FROM ". BB_BT_TORSTAT ."
		WHERE topic_id IN($dead_tor_sql)
	");

	// Update attach
	DB()->query("
		UPDATE
			". BB_ATTACHMENTS_DESC ." a,
			". BB_BT_TORRENTS ." tor
		SET
			a.tracker_status = 0,
			a.download_count = tor.complete_count
		WHERE
			    a.attach_id = tor.attach_id
			AND tor.attach_id IN($attach_sql)
	");

	// Remove torrents
	DB()->query("
		DELETE FROM ". BB_BT_TORRENTS ."
		WHERE topic_id IN($dead_tor_sql)
	");
}