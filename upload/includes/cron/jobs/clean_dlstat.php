<?php

if (!defined('BB_ROOT')) die(basename(__FILE__));

if($bb_cfg['announce_type'] != 'xbt')
{
	define('BUF_DLSTATUS_TABLE', 'tmp_buf_dlstatus');

	// Move new dl-status records to main table
	DB()->query("
		CREATE TEMPORARY TABLE ". BUF_DLSTATUS_TABLE ." (
			user_id      mediumint(9)          NOT NULL default '0',
			topic_id     mediumint(8) unsigned NOT NULL default '0',
			user_status  tinyint(1)            NOT NULL default '0',
			PRIMARY KEY (user_id, topic_id)
		) ENGINE = MyISAM
	");

	DB()->query("
		INSERT INTO ". BUF_DLSTATUS_TABLE ."
			(user_id, topic_id, user_status)
		SELECT
			user_id, topic_id, user_status
		FROM
			". BB_BT_DLSTATUS_NEW ."
		WHERE
			last_modified_dlstatus < DATE_SUB(NOW(), INTERVAL 1 DAY)
	");

	DB()->query("
		REPLACE INTO ". BB_BT_DLSTATUS_MAIN ."
			(user_id, topic_id, user_status)
		SELECT
			user_id, topic_id, user_status
		FROM ". BUF_DLSTATUS_TABLE ."
	");

	DB()->query("
		DELETE new
		FROM ". BUF_DLSTATUS_TABLE ." buf
		INNER JOIN ". BB_BT_DLSTATUS_NEW ." new USING(user_id, topic_id)
	");

	DB()->query("DROP TEMPORARY TABLE ". BUF_DLSTATUS_TABLE);
}

// Delete staled dl-status records
$keeping_dlstat = array(
	DL_STATUS_WILL     => (int) $bb_cfg['dl_will_days_keep'],
	DL_STATUS_DOWN     => (int) $bb_cfg['dl_down_days_keep'],
	DL_STATUS_COMPLETE => (int) $bb_cfg['dl_complete_days_keep'],
	DL_STATUS_CANCEL   => (int) $bb_cfg['dl_cancel_days_keep'],
);

$delete_dlstat_sql = array();

foreach ($keeping_dlstat as $dl_status => $days_to_keep)
{
	if ($days_to_keep)
	{
		$delete_dlstat_sql[] = "
			user_status = $dl_status
			AND
			last_modified_dlstatus < DATE_SUB(NOW(), INTERVAL $days_to_keep DAY)
		";
	}
}

if ($delete_dlstat_sql = join(') OR (', $delete_dlstat_sql))
{
	DB()->query("DELETE QUICK FROM ". BB_BT_DLSTATUS ." WHERE ($delete_dlstat_sql)");
}

// Delete orphans
DB()->query("
	DELETE QUICK dl
	FROM ". BB_BT_DLSTATUS ." dl
	LEFT JOIN ". BB_USERS ." u USING(user_id)
	WHERE u.user_id IS NULL
");

DB()->query("
	DELETE QUICK dl
	FROM ". BB_BT_DLSTATUS ." dl
	LEFT JOIN ". BB_TOPICS ." t USING(topic_id)
	WHERE t.topic_id IS NULL
");

// Tor-Stats cleanup
if ($torstat_days_keep = intval($bb_cfg['torstat_days_keep']))
{
	DB()->query("DELETE QUICK FROM ". BB_BT_TORSTAT ." WHERE last_modified_torstat < DATE_SUB(NOW(), INTERVAL $torstat_days_keep DAY)");
}

DB()->query("
	DELETE QUICK tst
	FROM ". BB_BT_TORSTAT ." tst
	LEFT JOIN ". BB_BT_TORRENTS ." tor USING(topic_id)
	WHERE tor.topic_id IS NULL
");