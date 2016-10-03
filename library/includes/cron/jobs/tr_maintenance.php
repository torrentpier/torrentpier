<?php

if (!defined('BB_ROOT')) {
    die(basename(__FILE__));
}

/** @var \TorrentPier\Di $di */
$di = \TorrentPier\Di::getInstance();

if (empty($di->config->get('seeder_last_seen_days_keep')) || empty($di->config->get('seeder_never_seen_days_keep'))) {
    return;
}

$last_seen_time = TIMENOW - 86400 * $di->config->get('seeder_last_seen_days_keep');
$never_seen_time = TIMENOW - 86400 * $di->config->get('seeder_never_seen_days_keep');
$limit_sql = 3000;

$topics_sql = [];

$sql = "SELECT topic_id
	FROM " . BB_BT_TORRENTS . "
	WHERE reg_time < $never_seen_time
		AND seeder_last_seen < $last_seen_time
	LIMIT $limit_sql";

foreach (DB()->fetch_rowset($sql) as $row) {
    $topics_sql[] = $row['topic_id'];
}
$dead_tor_sql = join(',', $topics_sql);

if ($dead_tor_sql) {
    // Delete torstat
    DB()->query("
		DELETE FROM " . BB_BT_TORSTAT . "
		WHERE topic_id IN($dead_tor_sql)
	");

    // Remove torrents
    DB()->query("
		DELETE FROM " . BB_BT_TORRENTS . "
		WHERE topic_id IN($dead_tor_sql)
	");
}
