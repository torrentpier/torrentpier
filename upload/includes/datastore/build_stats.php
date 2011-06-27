<?php

if (!defined('BB_ROOT')) die(basename(__FILE__));

$data = array();

// usercount
$row = DB()->fetch_row("SELECT COUNT(*) AS usercount FROM ". BB_USERS ." WHERE user_id NOT IN(". EXCLUDED_USERS_CSV .")");
$data['usercount'] = number_format($row['usercount']);

// newestuser
$row = DB()->fetch_row("SELECT user_id, username FROM ". BB_USERS ." ORDER BY user_id DESC LIMIT 1");
$data['newestuser'] = $row;

// post/topic count
$row = DB()->fetch_row("SELECT SUM(forum_topics) AS topiccount, SUM(forum_posts) AS postcount FROM ". BB_FORUMS);
$data['postcount'] = number_format($row['postcount']);
$data['topiccount'] = number_format($row['topiccount']);

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

$this->store('stats', $data);
