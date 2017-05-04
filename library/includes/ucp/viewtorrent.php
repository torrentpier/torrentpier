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

if (!defined('IN_PROFILE')) {
    die(basename(__FILE__));
}

$releasing = $seeding = $leeching = array();
$releasing_count = $seeding_count = $leeching_count = 0;

// Auth
$excluded_forums_csv = $user->get_excluded_forums(AUTH_VIEW);
$not_auth_forums_sql = ($excluded_forums_csv) ? "
	AND f.forum_id NOT IN($excluded_forums_csv)
	AND f.forum_parent NOT IN($excluded_forums_csv)
" : '';

$sql = DB()->fetch_rowset("
	SELECT
		f.forum_id, f.forum_name, t.topic_title,
		tor.tor_type, tor.size,
		sn.seeders, sn.leechers, tr.*
	FROM " . BB_FORUMS . " f, " . BB_TOPICS . " t, " . BB_BT_TRACKER . " tr, " . BB_BT_TORRENTS . " tor, " . BB_BT_TRACKER_SNAP . " sn
	WHERE tr.user_id = {$profiledata['user_id']}
		AND tr.topic_id = tor.topic_id
		AND sn.topic_id = tor.topic_id
		AND tor.topic_id = t.topic_id
		AND t.forum_id = f.forum_id
			$not_auth_forums_sql
	GROUP BY tr.topic_id, tr.peer_hash
	ORDER BY f.forum_name, t.topic_title
");

foreach ($sql as $rowset) {
    if ($rowset['releaser']) {
        $releasing[] = $rowset;
    } elseif ($rowset['seeder']) {
        $seeding[] = $rowset;
    } else {
        $leeching[] = $rowset;
    }
}

if ($releasing) {
    foreach ($releasing as $i => $row) {
        $topic_title = wbr($row['topic_title']);

        $template->assign_block_vars('released', array(
            'ROW_CLASS' => !($i % 2) ? 'row1' : 'row2',
            'FORUM_NAME' => htmlCHR($row['forum_name']),
            'TOPIC_TITLE' => ($row['update_time']) ? $topic_title : "<s>$topic_title</s>",
            'U_VIEW_FORUM' => FORUM_URL . $row['forum_id'],
            'U_VIEW_TOPIC' => TOPIC_URL . $row['topic_id'],
            'TOR_TYPE' => is_gold($row['tor_type']),
            'TOPIC_SEEDERS' => ($row['seeders']) ?: 0,
            'TOPIC_LEECHERS' => ($row['leechers']) ?: 0,
            'SPEED_UP' => ($row['speed_up']) ? humn_size($row['speed_up'], 0, 'KB') . '/s' : '-',
        ));

        $releasing_count++;
    }
}

if ($seeding) {
    foreach ($seeding as $i => $row) {
        $topic_title = wbr($row['topic_title']);

        $template->assign_block_vars('seed', array(
            'ROW_CLASS' => !($i % 2) ? 'row1' : 'row2',
            'FORUM_NAME' => htmlCHR($row['forum_name']),
            'TOPIC_TITLE' => ($row['update_time']) ? $topic_title : "<s>$topic_title</s>",
            'U_VIEW_FORUM' => FORUM_URL . $row['forum_id'],
            'U_VIEW_TOPIC' => TOPIC_URL . $row['topic_id'],
            'TOR_TYPE' => is_gold($row['tor_type']),
            'TOPIC_SEEDERS' => ($row['seeders']) ?: 0,
            'TOPIC_LEECHERS' => ($row['leechers']) ?: 0,
            'SPEED_UP' => ($row['speed_up']) ? humn_size($row['speed_up'], 0, 'KB') . '/s' : '-',
        ));

        $seeding_count++;
    }
}

if ($leeching) {
    foreach ($leeching as $i => $row) {
        $compl_size = ($row['remain'] && $row['size'] && $row['size'] > $row['remain']) ? ($row['size'] - $row['remain']) : 0;
        $compl_perc = ($compl_size) ? floor($compl_size * 100 / $row['size']) : 0;
        $topic_title = wbr($row['topic_title']);

        $template->assign_block_vars('leech', array(
            'ROW_CLASS' => !($i % 2) ? 'row1' : 'row2',
            'FORUM_NAME' => htmlCHR($row['forum_name']),
            'TOPIC_TITLE' => ($row['update_time']) ? $topic_title : "<s>$topic_title</s>",
            'U_VIEW_FORUM' => FORUM_URL . $row['forum_id'],
            'U_VIEW_TOPIC' => TOPIC_URL . $row['topic_id'],
            'COMPL_PERC' => $compl_perc,
            'TOR_TYPE' => is_gold($row['tor_type']),
            'TOPIC_SEEDERS' => ($row['seeders']) ?: 0,
            'TOPIC_LEECHERS' => ($row['leechers']) ?: 0,
            'SPEED_DOWN' => ($row['speed_down']) ? humn_size($row['speed_down'], 0, 'KB') . '/s' : '-',
        ));

        $leeching_count++;
    }
}

$template->assign_vars(array(
    'SHOW_SEARCH_DL' => IS_AM || $profile_user_id,
    'USERNAME' => $profiledata['username'],
    'L_RELEASINGS' => "{$lang['RELEASING']}: " . (($releasing_count) ? "<b>$releasing_count</b>" : '0'),
    'L_SEEDINGS' => "{$lang['SEEDING']}: " . (($seeding_count) ? "<b>$seeding_count</b>" : '0'),
    'L_LEECHINGS' => "{$lang['LEECHING']}: " . (($leeching_count) ? "<b>$leeching_count</b>" : '0'),
    'USER_DLS' => $releasing_count || $seeding_count || $leeching_count,
));
