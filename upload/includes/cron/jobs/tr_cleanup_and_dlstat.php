<?php

if (!defined('BB_ROOT')) die(basename(__FILE__));

$releaser = DL_STATUS_RELEASER;

define('NEW_BB_BT_LAST_TORSTAT',  'new_bt_last_torstat');
define('OLD_BB_BT_LAST_TORSTAT',  'old_bt_last_torstat');
define('NEW_BB_BT_LAST_USERSTAT', 'new_bt_last_userstat');
define('OLD_BB_BT_LAST_USERSTAT', 'old_bt_last_userstat');

DB()->query("DROP TABLE IF EXISTS ". NEW_BB_BT_LAST_TORSTAT .", ". NEW_BB_BT_LAST_USERSTAT);
DB()->query("DROP TABLE IF EXISTS ". OLD_BB_BT_LAST_TORSTAT .", ". OLD_BB_BT_LAST_USERSTAT);

DB()->query("CREATE TABLE ". NEW_BB_BT_LAST_TORSTAT  ." LIKE ". BB_BT_LAST_TORSTAT);
DB()->query("CREATE TABLE ". NEW_BB_BT_LAST_USERSTAT ." LIKE ". BB_BT_LAST_USERSTAT);

DB()->expect_slow_query(600);

// Update dlstat (part 1)
if ($tr_cfg['update_dlstat'])
{
	// ############################ Tables LOCKED ################################
	DB()->lock(array(
		BB_BT_TRACKER,
		NEW_BB_BT_LAST_TORSTAT,
	));

	// Get PER TORRENT user's dlstat from tracker
	DB()->query("
		INSERT INTO ". NEW_BB_BT_LAST_TORSTAT ."
			(topic_id, user_id, dl_status, up_add, down_add, release_add, speed_up, speed_down)
		SELECT
			topic_id, user_id, IF(releaser, $releaser, seeder), SUM(up_add), SUM(down_add), IF(releaser, SUM(up_add), 0), SUM(speed_up), SUM(speed_down)
		FROM ". BB_BT_TRACKER ."
		WHERE (up_add != 0 OR down_add != 0)
		GROUP BY topic_id, user_id
	");

	// Reset up/down additions in tracker
	DB()->query("UPDATE ". BB_BT_TRACKER ." SET up_add = 0, down_add = 0");

	DB()->unlock();
	// ############################ Tables UNLOCKED ##############################
}

// Update last seeder info in BUF
DB()->query("
	REPLACE INTO ". BUF_LAST_SEEDER ."
		(topic_id, seeder_last_seen)
	SELECT
		topic_id, ". TIMENOW ."
	FROM ". BB_BT_TRACKER ."
	WHERE seeder = 1
	GROUP BY topic_id
");

// Clean peers table
if ($tr_cfg['autoclean'])
{
	$announce_interval = max(intval($bb_cfg['announce_interval']), 60);
	$expire_factor     = max(floatval($tr_cfg['expire_factor']), 1);
	$peer_expire_time  = TIMENOW - floor($announce_interval * $expire_factor);

	DB()->query("DELETE FROM ". BB_BT_TRACKER ." WHERE update_time < $peer_expire_time");
}

// Update dlstat (part 2)
if ($tr_cfg['update_dlstat'])
{
	// Set "only 1 seeder" bonus
	DB()->query("
		UPDATE
		  ". NEW_BB_BT_LAST_TORSTAT  ." tb,
		  ". BB_BT_TRACKER_SNAP      ." sn
		SET
		  tb.bonus_add = tb.up_add
		WHERE
		      tb.topic_id = sn.topic_id
		  AND sn.seeders = 1
		  AND tb.up_add != 0
		  AND tb.dl_status = ". DL_STATUS_COMPLETE ."
	");

	// Get SUMMARIZED user's dlstat
	DB()->query("
		INSERT INTO ". NEW_BB_BT_LAST_USERSTAT ."
			(user_id, up_add, down_add, release_add, bonus_add, speed_up, speed_down)
		SELECT
			user_id, SUM(up_add), SUM(down_add), SUM(release_add), SUM(bonus_add), SUM(speed_up), SUM(speed_down)
		FROM ". NEW_BB_BT_LAST_TORSTAT ."
		GROUP BY user_id
	");

	// Update TOTAL user's dlstat
	DB()->query("
		UPDATE
			". BB_BT_USERS             ." u,
			". NEW_BB_BT_LAST_USERSTAT ." ub
		SET
			u.u_up_total       = u.u_up_total       + ub.up_add,
			u.u_down_total     = u.u_down_total     + ub.down_add,
			u.u_up_release     = u.u_up_release     + ub.release_add,
			u.u_up_bonus       = u.u_up_bonus       + ub.bonus_add,
			u.up_today         = u.up_today         + ub.up_add,
			u.down_today       = u.down_today       + ub.down_add,
			u.up_release_today = u.up_release_today + ub.release_add,
			u.up_bonus_today   = u.up_bonus_today   + ub.bonus_add
		WHERE u.user_id = ub.user_id
	");

	// Delete from dl_list what exists in BUF but not exsits in NEW
	DB()->query("
		DELETE dl
		FROM ". BB_BT_DLSTATUS ." dl
		INNER JOIN ". NEW_BB_BT_LAST_TORSTAT ." buf USING(user_id, topic_id)
		WHERE buf.user_id IS NULL
			AND buf.topic_id IS NULL
	");

	// Update DL-Status
	DB()->query("
		REPLACE INTO ". BB_BT_DLSTATUS ."
			(user_id, topic_id, user_status)
		SELECT
			user_id, topic_id, dl_status
		FROM ". NEW_BB_BT_LAST_TORSTAT ."
	");

	// Update PER TORRENT DL-Status (for "completed" counter)
	DB()->query("
		INSERT IGNORE INTO ". BB_BT_TORSTAT ."
			(topic_id, user_id)
		SELECT
			topic_id, user_id
		FROM ". NEW_BB_BT_LAST_TORSTAT ."
		WHERE dl_status = ". DL_STATUS_COMPLETE ."
	");
}

DB()->query("
	RENAME TABLE
	". BB_BT_LAST_TORSTAT     ." TO ". OLD_BB_BT_LAST_TORSTAT .",
	". NEW_BB_BT_LAST_TORSTAT ." TO ". BB_BT_LAST_TORSTAT ."
");
DB()->query("DROP TABLE IF EXISTS ". NEW_BB_BT_LAST_TORSTAT .", ". OLD_BB_BT_LAST_TORSTAT);

DB()->query("
	RENAME TABLE
	". BB_BT_LAST_USERSTAT     ." TO ". OLD_BB_BT_LAST_USERSTAT .",
	". NEW_BB_BT_LAST_USERSTAT ." TO ". BB_BT_LAST_USERSTAT ."
");
DB()->query("DROP TABLE IF EXISTS ". NEW_BB_BT_LAST_USERSTAT .", ". OLD_BB_BT_LAST_USERSTAT);

DB()->expect_slow_query(10);

if($bb_cfg['seed_bonus_enabled'] && $bb_cfg['seed_bonus_points'] && $bb_cfg['seed_bonus_release'])
{
	DB()->query("
		CREATE TEMPORARY TABLE tmp_bonus (
			user_id       INT UNSIGNED NOT NULL DEFAULT '0',
			release_count INT UNSIGNED NOT NULL DEFAULT '0'
		) ENGINE = MEMORY
	");

    $tor_size = ($bb_cfg['seed_bonus_tor_size'] * 1073741824);

	DB()->query("INSERT INTO tmp_bonus
		SELECT bt.user_id, count(bt.seeder) AS release_count
			FROM ". BB_BT_TRACKER ." bt, ". BB_BT_TORRENTS ." tor
			WHERE tor.topic_id = bt.topic_id
				AND tor.size   > $tor_size
				AND bt.seeder  > 0
			GROUP BY user_id
	");

	$seed_bonus = unserialize($bb_cfg['seed_bonus_points']);
	$seed_release = unserialize($bb_cfg['seed_bonus_release']);

    foreach($seed_bonus as $i => $points)
    {
	    if(!$points || !$seed_release[$i]) continue;

        $user_points  = ($points / 4);
        $release      = $seed_release[$i];
        $user_regdate = (TIMENOW - $bb_cfg['seed_bonus_user_regdate'] * 86400);

        DB()->query("
			UPDATE ". BB_USERS ." u, ". BB_BT_USERS ." bu, tmp_bonus b
			SET
				u.user_points       = u.user_points + $user_points,
				bu.points_today     = bu.points_today + $user_points,
				b.user_id           = 0
			WHERE
			    b.user_id           =  u.user_id
				AND bu.user_id      =  u.user_id
				AND b.release_count <= $release
				AND u.user_regdate  <  $user_regdate
				AND u.user_active   =  1
				AND u.user_id       not IN(". EXCLUDED_USERS_CSV .")
		");
    }

	DB()->query("DROP TEMPORARY TABLE IF EXISTS tmp_bonus");
}