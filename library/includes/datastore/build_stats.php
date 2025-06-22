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

$data = [];

// usercount
$row = DB()->fetch_row("SELECT COUNT(*) AS usercount FROM " . BB_USERS . " WHERE user_id NOT IN(" . EXCLUDED_USERS . ")");
$data['usercount'] = commify($row['usercount']);

// newestuser
$row = DB()->fetch_row("SELECT user_id, username, user_rank FROM " . BB_USERS . " WHERE user_active = 1 AND user_id NOT IN(" . EXCLUDED_USERS . ") ORDER BY user_id DESC LIMIT 1");
$data['newestuser'] = $row;

// post/topic count
$row = DB()->fetch_row("SELECT SUM(forum_topics) AS topiccount, SUM(forum_posts) AS postcount FROM " . BB_FORUMS);
$data['postcount'] = commify($row['postcount']);
$data['topiccount'] = commify($row['topiccount']);

// Tracker stats
if (tp_config()->get('tor_stats')) {
    // torrents stat
    $row = DB()->fetch_row("SELECT COUNT(topic_id) AS torrentcount, SUM(size) AS size FROM " . BB_BT_TORRENTS);
    $data['torrentcount'] = commify($row['torrentcount']);
    $data['size'] = $row['size'];

    // peers stat
    $row = DB()->fetch_row("SELECT SUM(seeders) AS seeders, SUM(leechers) AS leechers, ((SUM(speed_up) + SUM(speed_down))/2) AS speed FROM " . BB_BT_TRACKER_SNAP);
    $data['seeders'] = commify($row['seeders']);
    $data['leechers'] = commify($row['leechers']);
    $data['peers'] = commify($row['seeders'] + $row['leechers']);
    $data['speed'] = $row['speed'];
}

// gender stat
if (tp_config()->get('gender')) {
    $male = DB()->fetch_row("SELECT COUNT(user_id) AS male FROM " . BB_USERS . " WHERE user_gender = " . MALE . " AND user_id NOT IN(" . EXCLUDED_USERS . ")");
    $female = DB()->fetch_row("SELECT COUNT(user_id) AS female FROM " . BB_USERS . " WHERE user_gender = " . FEMALE . " AND user_id NOT IN(" . EXCLUDED_USERS . ")");
    $unselect = DB()->fetch_row("SELECT COUNT(user_id) AS unselect FROM " . BB_USERS . " WHERE user_gender = 0 AND user_id NOT IN(" . EXCLUDED_USERS . ")");

    $data['male'] = $male['male'];
    $data['female'] = $female['female'];
    $data['unselect'] = $unselect['unselect'];
}

// birthday stat
if (tp_config()->get('birthday_check_day') && tp_config()->get('birthday_enabled')) {
    $sql = DB()->fetch_rowset("SELECT user_id, username, user_rank , user_birthday
		FROM " . BB_USERS . "
		WHERE user_id NOT IN(" . EXCLUDED_USERS . ")
			AND user_birthday != '1900-01-01'
			AND user_birthday IS NOT NULL
			AND user_active = 1
		ORDER BY user_level DESC, username
	");

    $date_today = bb_date(TIMENOW, 'md', false);
    $date_forward = bb_date(TIMENOW + (tp_config()->get('birthday_check_day') * 86400), 'md', false);

    $birthday_today_list = $birthday_week_list = [];

    foreach ($sql as $row) {
        $user_birthday = bb_date(strtotime($row['user_birthday']), 'md', false);

        if ($user_birthday > $date_today && $user_birthday <= $date_forward) {
            // user are having birthday within the next days
            $birthday_week_list[] = [
                'user_id' => $row['user_id'],
                'username' => $row['username'],
                'user_rank' => $row['user_rank'],
                'user_birthday' => $row['user_birthday']
            ];
        } elseif ($user_birthday == $date_today) {
            //user have birthday today
            $birthday_today_list[] = [
                'user_id' => $row['user_id'],
                'username' => $row['username'],
                'user_rank' => $row['user_rank'],
                'user_birthday' => $row['user_birthday']
            ];
        }
    }

    $data['birthday_today_list'] = $birthday_today_list;
    $data['birthday_week_list'] = $birthday_week_list;
}

$this->store('stats', $data);
