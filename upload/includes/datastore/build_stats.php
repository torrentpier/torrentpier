<?php

if (!defined('BB_ROOT')) die(basename(__FILE__));

global $bb_cfg;

$data = array();

// usercount
$row = DB()->fetch_row("SELECT COUNT(*) AS usercount FROM ". BB_USERS ." WHERE user_id NOT IN(". EXCLUDED_USERS_CSV .")");
$data['usercount'] = number_format($row['usercount']);

// newestuser
$row = DB()->fetch_row("SELECT user_id, username, user_rank FROM ". BB_USERS ." ORDER BY user_id DESC LIMIT 1");
$data['newestuser'] = $row;

// post/topic count
$row = DB()->fetch_row("SELECT SUM(forum_topics) AS topiccount, SUM(forum_posts) AS postcount FROM ". BB_FORUMS);
$data['postcount'] = number_format($row['postcount']);
$data['topiccount'] = number_format($row['topiccount']);

// Tracker stats
if ($bb_cfg['tor_stats'])
{
    // torrents stat
    $row = DB()->fetch_row("SELECT COUNT(topic_id) AS torrentcount, SUM(size) AS size FROM ". BB_BT_TORRENTS);
    $data['torrentcount'] = number_format($row['torrentcount']);
    $data['size'] = $row['size'];

    // peers stat
    $row = DB()->fetch_row("SELECT SUM(seeders) AS seeders, SUM(leechers) AS leechers, ((SUM(speed_up) + SUM(speed_down))/2) AS speed FROM ". BB_BT_TRACKER_SNAP);
    $data['seeders']  = number_format($row['seeders']);
    $data['leechers'] = number_format($row['leechers']);
    $data['peers']    = number_format($row['seeders'] + $row['leechers']);
    $data['speed']    = $row['speed'];
}

// gender stat
if ($bb_cfg['gender'])
{
    $sql = DB()->fetch_rowset("SELECT user_gender FROM ". BB_USERS ." WHERE user_id NOT IN(". EXCLUDED_USERS_CSV .")");
    $data['male'] = $data['female'] = $data['unselect'] = 0;

	foreach($sql as $row)
    {
	    if($row['user_gender'] == MALE) $data['male']++;
	    if($row['user_gender'] == FEMALE) $data['female']++;
	    if(!$row['user_gender']) $data['unselect']++;
    }
}

// birthday stat
if ($bb_cfg['birthday_check_day'] && $bb_cfg['birthday_enabled'])
{
	$sql = DB()->fetch_rowset("SELECT user_id, username, user_birthday, user_birthday, user_rank FROM ". BB_USERS ." WHERE user_id NOT IN(". EXCLUDED_USERS_CSV .") AND user_birthday != 0 ORDER BY user_level DESC, username");
	$this_year = bb_date(TIMENOW, 'Y', 'false');
	$date_today = bb_date(TIMENOW, 'Ymd', 'false');
	$date_forward = bb_date(TIMENOW + ($bb_cfg['birthday_check_day']*86400), 'Ymd', 'false');

	$birthday_today_list = $birthday_week_list = array();

	foreach ($sql as $row)
	{
		$user_birthday = realdate($row['user_birthday'], 'md');
		$user_birthday2 = $this_year . $user_birthday;

		if ($user_birthday2 < $date_today) $user_birthday2 += 10000;

		if ($user_birthday2 > $date_today  && $user_birthday2 <= $date_forward)
		{
			// user are having birthday within the next days
			$birthday_week_list[] = array(
				'user_id'   => $row['user_id'],
				'username'  => $row['username'],
				'user_rank' => $row['user_rank'],
				'age'       => $row['user_birthday'],
			);
		}
		elseif ($user_birthday2 == $date_today)
		{
			//user have birthday today
			$birthday_today_list[] = array(
				'user_id'   => $row['user_id'],
				'username'  => $row['username'],
				'user_rank' => $row['user_rank'],
				'age'       => $row['user_birthday'],
			);
		}
	}

	$data['birthday_today_list'] = $birthday_today_list;
    $data['birthday_week_list']  = $birthday_week_list;
}

$this->store('stats', $data);
