<?php

/**
 * TorrentPier – Bull-powered BitTorrent tracker engine
 *
 * @copyright Copyright (c) 2005-2025 TorrentPier (https://torrentpier.com)
 * @link      https://github.com/torrentpier/torrentpier for the canonical source repository
 * @license   https://github.com/torrentpier/torrentpier/blob/master/LICENSE MIT License
 */

$pm_days_keep = (int)config()->get('forum.pm_days_keep');

if ($pm_days_keep !== 0) {
    $per_cycle = 20000;
    $row = DB()->fetch_row('SELECT MIN(privmsgs_id) AS start_id, MAX(privmsgs_id) AS finish_id FROM ' . BB_PRIVMSGS);
    $start_id = (int)$row['start_id'];
    $finish_id = (int)$row['finish_id'];

    while (true) {
        set_time_limit(600);
        $end_id = $start_id + $per_cycle - 1;

        DB()->query('
			DELETE pm, pmt
			FROM ' . BB_PRIVMSGS . ' pm
			LEFT JOIN ' . BB_PRIVMSGS_TEXT . " pmt ON(pmt.privmsgs_text_id = pm.privmsgs_id)
			WHERE pm.privmsgs_id BETWEEN {$start_id} AND {$end_id}
				AND pm.privmsgs_date < " . (TIMENOW - 86400 * $pm_days_keep) . '
		');

        if ($end_id > $finish_id) {
            break;
        }

        $start_id += $per_cycle;
    }
}
