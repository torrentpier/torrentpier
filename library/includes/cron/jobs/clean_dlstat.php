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

if (!defined('BB_ROOT')) {
    die(basename(__FILE__));
}

// Delete staled dl-status records
$keeping_dlstat = array(
    DL_STATUS_WILL => (int)$bb_cfg['dl_will_days_keep'],
    DL_STATUS_DOWN => (int)$bb_cfg['dl_down_days_keep'],
    DL_STATUS_COMPLETE => (int)$bb_cfg['dl_complete_days_keep'],
    DL_STATUS_CANCEL => (int)$bb_cfg['dl_cancel_days_keep'],
);

$delete_dlstat_sql = array();

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
if ($torstat_days_keep = (int)$bb_cfg['torstat_days_keep']) {
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
