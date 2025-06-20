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

// Delete staled dl-status records
$keeping_dlstat = [
    DL_STATUS_WILL => (int)config()->get('dl_will_days_keep'),
    DL_STATUS_DOWN => (int)config()->get('dl_down_days_keep'),
    DL_STATUS_COMPLETE => (int)config()->get('dl_complete_days_keep'),
    DL_STATUS_CANCEL => (int)config()->get('dl_cancel_days_keep')
];

$delete_dlstat_sql = [];

foreach ($keeping_dlstat as $dl_status => $days_to_keep) {
    if ($days_to_keep) {
        $delete_dlstat_sql[] = "
			user_status = $dl_status
			AND
			last_modified_dlstatus < DATE_SUB(NOW(), INTERVAL $days_to_keep DAY)
		";
    }
}

if ($delete_dlstat_sql = implode(') OR (', $delete_dlstat_sql)) {
    DB()->query("DELETE QUICK FROM " . BB_BT_DLSTATUS . " WHERE ($delete_dlstat_sql)");
}

// Delete orphans
DB()->query("
	DELETE QUICK dl
	FROM " . BB_BT_DLSTATUS . " dl
	LEFT JOIN " . BB_USERS . " u USING(user_id)
	WHERE u.user_id IS NULL
");

DB()->query("
	DELETE QUICK dl
	FROM " . BB_BT_DLSTATUS . " dl
	LEFT JOIN " . BB_TOPICS . " t USING(topic_id)
	WHERE t.topic_id IS NULL
");

// Tor-Stats cleanup
if ($torstat_days_keep = (int)config()->get('torstat_days_keep')) {
    DB()->query("DELETE QUICK FROM " . BB_BT_TORSTAT . " WHERE last_modified_torstat < DATE_SUB(NOW(), INTERVAL $torstat_days_keep DAY)");
}

DB()->query("
	DELETE QUICK tst
	FROM " . BB_BT_TORSTAT . " tst
	LEFT JOIN " . BB_BT_TORRENTS . " tor USING(topic_id)
	WHERE tor.topic_id IS NULL
");

DB()->query("
	UPDATE
		" . BB_BT_USERS . "
	SET
		up_yesterday         = up_today,
		down_yesterday       = down_today,
		up_release_yesterday = up_release_today,
		up_bonus_yesterday   = up_bonus_today,
		points_yesterday     = points_today
");

DB()->query("
	UPDATE
		" . BB_BT_USERS . "
	SET
		up_today             = 0,
		down_today           = 0,
		up_release_today     = 0,
		up_bonus_today       = 0,
		points_today         = 0
");
