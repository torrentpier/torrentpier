<?php
/**
 * MIT License
 *
 * Copyright (c) 2005-2017 TorrentPier
 *
 * Permission is hereby granted, free of charge, to any person obtaining a copy
 * of this software and associated documentation files (the "Software"), to deal
 * in the Software without restriction, including without limitation the rights
 * to use, copy, modify, merge, publish, distribute, sublicense, and/or sell
 * copies of the Software, and to permit persons to whom the Software is
 * furnished to do so, subject to the following conditions:
 *
 * The above copyright notice and this permission notice shall be included in all
 * copies or substantial portions of the Software.
 *
 * THE SOFTWARE IS PROVIDED "AS IS", WITHOUT WARRANTY OF ANY KIND, EXPRESS OR
 * IMPLIED, INCLUDING BUT NOT LIMITED TO THE WARRANTIES OF MERCHANTABILITY,
 * FITNESS FOR A PARTICULAR PURPOSE AND NONINFRINGEMENT. IN NO EVENT SHALL THE
 * AUTHORS OR COPYRIGHT HOLDERS BE LIABLE FOR ANY CLAIM, DAMAGES OR OTHER
 * LIABILITY, WHETHER IN AN ACTION OF CONTRACT, TORT OR OTHERWISE, ARISING FROM,
 * OUT OF OR IN CONNECTION WITH THE SOFTWARE OR THE USE OR OTHER DEALINGS IN THE
 * SOFTWARE.
 */
use \TorrentPier\Di;

if (!defined('BB_ROOT')) {
    die(basename(__FILE__));
}

/** @var \TorrentPier\Di $di */
$di = \TorrentPier\Di::getInstance();

Di::getInstance()->db->expect_slow_query(600);

//
// Make tracker snapshot
//
if (!$di->config->get('ocelot.enabled')) {
    define('NEW_BB_BT_TRACKER_SNAP', 'new_tracker_snap');
    define('OLD_BB_BT_TRACKER_SNAP', 'old_tracker_snap');

    DB()->query("DROP TABLE IF EXISTS " . NEW_BB_BT_TRACKER_SNAP . ", " . OLD_BB_BT_TRACKER_SNAP);
    DB()->query("CREATE TABLE " . NEW_BB_BT_TRACKER_SNAP . " LIKE " . BB_BT_TRACKER_SNAP);
}

$per_cycle = 50000;
$row = DB()->fetch_row("SELECT MIN(topic_id) AS start_id, MAX(topic_id) AS finish_id FROM " . BB_BT_TRACKER);
$start_id = (int)$row['start_id'];
$finish_id = (int)$row['finish_id'];

while (true) {
    set_time_limit(600);
    $end_id = $start_id + $per_cycle - 1;

    $val = array();

    if (!$di->config->get('ocelot.enabled')) {
        $sql = "
			SELECT
				topic_id, SUM(seeder) AS seeders, (COUNT(*) - SUM(seeder)) AS leechers,
				SUM(speed_up) AS speed_up, SUM(speed_down) AS speed_down
			FROM " . BB_BT_TRACKER . "
			WHERE topic_id BETWEEN $start_id AND $end_id
			GROUP BY topic_id
		";
    } else {
        $sql = "
			SELECT
				topic_id, SUM(speed_up) AS speed_up, SUM(speed_down) AS speed_down
			FROM " . BB_BT_TRACKER . "
			WHERE topic_id BETWEEN $start_id AND $end_id
			GROUP BY topic_id
		";
    }

    foreach (DB()->fetch_rowset($sql) as $row) {
        $val[] = join(',', $row);
    }

    if ($val) {
        if (!$di->config->get('ocelot.enabled')) {
            DB()->query("
				REPLACE INTO " . NEW_BB_BT_TRACKER_SNAP . "
				(topic_id, seeders, leechers, speed_up, speed_down)
				VALUES(" . join('),(', $val) . ")
			");
        } else {
            DB()->query("
				INSERT INTO " . BB_BT_TRACKER_SNAP . "
				(topic_id, speed_up, speed_down)
				VALUES(" . join('),(', $val) . ")
				ON DUPLICATE KEY UPDATE speed_up = VALUES(speed_up), speed_down = VALUES(speed_down)
			");
        }
    }

    if ($end_id > $finish_id) {
        break;
    }

    if (!($start_id % ($per_cycle * 10))) {
        sleep(1);
    }

    $start_id += $per_cycle;
}

if (!$di->config->get('ocelot.enabled')) {
    DB()->query("
		RENAME TABLE
		" . BB_BT_TRACKER_SNAP . " TO " . OLD_BB_BT_TRACKER_SNAP . ",
		" . NEW_BB_BT_TRACKER_SNAP . " TO " . BB_BT_TRACKER_SNAP . "
	");

    DB()->query("DROP TABLE IF EXISTS " . NEW_BB_BT_TRACKER_SNAP . ", " . OLD_BB_BT_TRACKER_SNAP);
}

//
// Make dl-list snapshot
//
define('NEW_BB_BT_DLSTATUS_SNAP', 'new_dlstatus_snap');
define('OLD_BB_BT_DLSTATUS_SNAP', 'old_dlstatus_snap');

DB()->query("DROP TABLE IF EXISTS " . NEW_BB_BT_DLSTATUS_SNAP . ", " . OLD_BB_BT_DLSTATUS_SNAP);

DB()->query("CREATE TABLE " . NEW_BB_BT_DLSTATUS_SNAP . " LIKE " . BB_BT_DLSTATUS_SNAP);

if ($di->config->get('bt_show_dl_list') && $di->config->get('bt_dl_list_only_count')) {
    DB()->query("
		INSERT INTO " . NEW_BB_BT_DLSTATUS_SNAP . "
			(topic_id, dl_status, users_count)
		SELECT
			topic_id, user_status, COUNT(*)
		FROM " . BB_BT_DLSTATUS . "
		WHERE user_status != " . DL_STATUS_RELEASER . "
		GROUP BY topic_id, user_status
	");
}

DB()->query("
	RENAME TABLE
	" . BB_BT_DLSTATUS_SNAP . " TO " . OLD_BB_BT_DLSTATUS_SNAP . ",
	" . NEW_BB_BT_DLSTATUS_SNAP . " TO " . BB_BT_DLSTATUS_SNAP . "
");

DB()->query("DROP TABLE IF EXISTS " . NEW_BB_BT_DLSTATUS_SNAP . ", " . OLD_BB_BT_DLSTATUS_SNAP);

//
// TORHELP
//
if ($di->config->get('torhelp_enabled')) {
    $tor_min_seeders = 0;   // "<="
    $tor_min_leechers = 2;   // ">="
    $tor_min_completed = 10;  // ">="
    $tor_seed_last_seen_days = 3;   // "<="
    $tor_downloaded_days_ago = 60;  // ">="
    $user_last_seen_online = 15;  // minutes
    $users_limit = 3000;
    $dl_status_ary = array(DL_STATUS_COMPLETE);

    define('NEW_BB_BT_TORHELP', 'new_torhelp');
    define('OLD_BB_BT_TORHELP', 'old_torhelp');

    DB()->query("DROP TABLE IF EXISTS " . NEW_BB_BT_TORHELP . ", " . OLD_BB_BT_TORHELP);

    DB()->query("CREATE TABLE " . NEW_BB_BT_TORHELP . " LIKE " . BB_BT_TORHELP);

    // Select users
    $sql = "
		SELECT DISTINCT session_user_id AS uid
		FROM " . BB_SESSIONS . "
		WHERE session_time > (UNIX_TIMESTAMP() - $user_last_seen_online*60)
		  AND session_user_id != " . GUEST_UID . "
		ORDER BY session_time DESC
		LIMIT $users_limit
	";
    $online_users_ary = array();

    foreach (DB()->fetch_rowset($sql) as $row) {
        $online_users_ary[] = $row['uid'];
    }

    if ($online_users_csv = join(',', $online_users_ary)) {
        DB()->query("
			INSERT INTO " . NEW_BB_BT_TORHELP . " (user_id, topic_id_csv)
			SELECT
			  dl.user_id, GROUP_CONCAT(dl.topic_id)
			FROM       " . BB_BT_TRACKER_SNAP . " trsn
			INNER JOIN " . BB_BT_TORRENTS . " tor ON (tor.topic_id = trsn.topic_id)
			INNER JOIN " . BB_BT_DLSTATUS . " dl  ON (dl.topic_id = tor.topic_id)
			WHERE
			      trsn.seeders          <=  $tor_min_seeders
			  AND trsn.leechers         >=  $tor_min_leechers
			  AND tor.forum_id          !=  " . (int)$di->config->get('trash_forum_id') . "
			  AND tor.complete_count    >=  $tor_min_completed
			  AND tor.seeder_last_seen  <=  (UNIX_TIMESTAMP() - $tor_seed_last_seen_days*86400)
			  AND dl.user_id            IN($online_users_csv)
			  AND dl.user_status        IN(" . get_id_csv($dl_status_ary) . ")
			  AND dl.last_modified_dlstatus > DATE_SUB(NOW(), INTERVAL $tor_downloaded_days_ago DAY)
			GROUP BY dl.user_id
			LIMIT 10000
		");
    }

    DB()->query("
		RENAME TABLE
		" . BB_BT_TORHELP . " TO " . OLD_BB_BT_TORHELP . ",
		" . NEW_BB_BT_TORHELP . " TO " . BB_BT_TORHELP . "
	");

    DB()->query("DROP TABLE IF EXISTS " . NEW_BB_BT_TORHELP . ", " . OLD_BB_BT_TORHELP);
}

DB()->expect_slow_query(10);
