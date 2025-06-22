<?php
/**
 * TorrentPier – Bull-powered BitTorrent tracker engine
 *
 * @copyright Copyright (c) 2005-2025 TorrentPier (https://torrentpier.com)
 * @link      https://github.com/torrentpier/torrentpier for the canonical source repository
 * @license   https://github.com/torrentpier/torrentpier/blob/master/LICENSE MIT License
 */

if (!defined('BB_ROOT')) {
    die(basename(__FILE__));
}

if (empty(tp_config()->get('seeder_last_seen_days_keep')) || empty(tp_config()->get('seeder_never_seen_days_keep'))) {
    return;
}

$last_seen_time = TIMENOW - 86400 * tp_config()->get('seeder_last_seen_days_keep');
$never_seen_time = TIMENOW - 86400 * tp_config()->get('seeder_never_seen_days_keep');
$limit_sql = 3000;

$topics_sql = $attach_sql = [];

$sql = "SELECT topic_id, attach_id
	FROM " . BB_BT_TORRENTS . "
	WHERE reg_time < $never_seen_time
		AND seeder_last_seen < $last_seen_time
	LIMIT $limit_sql";

foreach (DB()->fetch_rowset($sql) as $row) {
    $topics_sql[] = $row['topic_id'];
    $attach_sql[] = $row['attach_id'];
}
$dead_tor_sql = implode(',', $topics_sql);
$attach_sql = implode(',', $attach_sql);

if ($dead_tor_sql && $attach_sql) {
    // Delete torstat
    DB()->query("
		DELETE FROM " . BB_BT_TORSTAT . "
		WHERE topic_id IN($dead_tor_sql)
	");

    // Update attach
    DB()->query("
		UPDATE
			" . BB_ATTACHMENTS_DESC . " a,
			" . BB_BT_TORRENTS . " tor
		SET
			a.tracker_status = 0,
			a.download_count = tor.complete_count
		WHERE
			    a.attach_id = tor.attach_id
			AND tor.attach_id IN($attach_sql)
	");

    // Remove torrents
    DB()->query("
		DELETE FROM " . BB_BT_TORRENTS . "
		WHERE topic_id IN($dead_tor_sql)
	");
}
