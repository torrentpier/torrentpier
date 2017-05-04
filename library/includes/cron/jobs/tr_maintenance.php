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

if (empty($bb_cfg['seeder_last_seen_days_keep']) || empty($bb_cfg['seeder_never_seen_days_keep'])) {
    return;
}

$last_seen_time = TIMENOW - 86400 * $bb_cfg['seeder_last_seen_days_keep'];
$never_seen_time = TIMENOW - 86400 * $bb_cfg['seeder_never_seen_days_keep'];
$limit_sql = 3000;

$topics_sql = $attach_sql = array();

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
