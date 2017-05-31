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

$data = array();

// usercount
$row = DB()->fetch_row("SELECT COUNT(*) AS usercount FROM " . BB_USERS . " WHERE user_id NOT IN(" . EXCLUDED_USERS . ")");
$data['usercount'] = number_format($row['usercount']);

// newestuser
$row = DB()->fetch_row("SELECT user_id, username, user_rank FROM " . BB_USERS . " WHERE user_active = 1 ORDER BY user_id DESC LIMIT 1");
$data['newestuser'] = $row;

// post/topic count
$row = DB()->fetch_row("SELECT SUM(forum_topics) AS topiccount, SUM(forum_posts) AS postcount FROM " . BB_FORUMS);
$data['postcount'] = number_format($row['postcount']);
$data['topiccount'] = number_format($row['topiccount']);

// Tracker stats
if ($bb_cfg['tor_stats']) {
    // torrents stat
    $row = DB()->fetch_row("SELECT COUNT(topic_id) AS torrentcount, SUM(size) AS size FROM " . BB_BT_TORRENTS);
    $data['torrentcount'] = number_format($row['torrentcount']);
    $data['size'] = $row['size'];

    // peers stat
    $row = DB()->fetch_row("SELECT SUM(seeders) AS seeders, SUM(leechers) AS leechers, ((SUM(speed_up) + SUM(speed_down))/2) AS speed FROM " . BB_BT_TRACKER_SNAP);
    $data['seeders'] = number_format($row['seeders']);
    $data['leechers'] = number_format($row['leechers']);
    $data['peers'] = number_format($row['seeders'] + $row['leechers']);
    $data['speed'] = $row['speed'];
}

// gender stat
if ($bb_cfg['gender']) {
    $male = DB()->fetch_row("SELECT COUNT(user_id) AS male FROM " . BB_USERS . " WHERE user_gender = " . MALE . " AND user_id NOT IN(" . EXCLUDED_USERS . ")");
    $female = DB()->fetch_row("SELECT COUNT(user_id) AS female FROM " . BB_USERS . " WHERE user_gender = " . FEMALE . " AND user_id NOT IN(" . EXCLUDED_USERS . ")");
    $unselect = DB()->fetch_row("SELECT COUNT(user_id) AS unselect FROM " . BB_USERS . " WHERE user_gender = 0 AND user_id NOT IN(" . EXCLUDED_USERS . ")");

    $data['male'] = $male['male'];
    $data['female'] = $female['female'];
    $data['unselect'] = $unselect['unselect'];
}

// birthday stat
if ($bb_cfg['birthday_check_day'] && $bb_cfg['birthday_enabled']) {
    $sql = DB()->fetch_rowset("SELECT user_id, username, user_rank , user_birthday
		FROM " . BB_USERS . "
		WHERE user_id NOT IN(" . EXCLUDED_USERS . ")
			AND user_birthday != '0000-00-00'
			AND user_active = 1
		ORDER BY user_level DESC, username
	");

    $date_today = bb_date(TIMENOW, 'md', false);
    $date_forward = bb_date(TIMENOW + ($bb_cfg['birthday_check_day'] * 86400), 'md', false);

    $birthday_today_list = $birthday_week_list = array();

    foreach ($sql as $row) {
        $user_birthday = date('md', strtotime($row['user_birthday']));

        if ($user_birthday > $date_today && $user_birthday <= $date_forward) {
            // user are having birthday within the next days
            $birthday_week_list[] = array(
                'user_id' => $row['user_id'],
                'username' => $row['username'],
                'user_rank' => $row['user_rank'],
                'user_birthday' => $row['user_birthday'],
            );
        } elseif ($user_birthday == $date_today) {
            //user have birthday today
            $birthday_today_list[] = array(
                'user_id' => $row['user_id'],
                'username' => $row['username'],
                'user_rank' => $row['user_rank'],
                'user_birthday' => $row['user_birthday'],
            );
        }
    }

    $data['birthday_today_list'] = $birthday_today_list;
    $data['birthday_week_list'] = $birthday_week_list;
}

$this->store('stats', $data);
