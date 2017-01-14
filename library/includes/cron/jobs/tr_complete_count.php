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

global $bb_cfg;

if ($bb_cfg['ocelot']['enabled']) {
    // Update TORRENT "completed" counters
    DB()->query("
		UPDATE
			" . BB_BT_TORRENTS . " tor,
			" . BB_BT_TRACKER_SNAP . " snap
		SET
			tor.complete_count = snap.complete
		WHERE
			tor.topic_id = snap.topic_id
	");
} else {
    // Get complete counts
    DB()->query("
		CREATE TEMPORARY TABLE tmp_complete_count
		SELECT
			topic_id, COUNT(*) AS compl_cnt
		FROM " . BB_BT_TORSTAT . "
		WHERE completed = 0
		GROUP BY topic_id
	");

    // Update USER "completed" counters
    DB()->query("UPDATE " . BB_BT_TORSTAT . " SET completed = 1");

    // Update TORRENT "completed" counters
    DB()->query("
		UPDATE
			" . BB_BT_TORRENTS . " tor,
			tmp_complete_count tmp
		SET
			tor.complete_count = tor.complete_count + tmp.compl_cnt
		WHERE
			tor.topic_id = tmp.topic_id
	");

    // Drop tmp table
    DB()->query("DROP TEMPORARY TABLE tmp_complete_count");
}
