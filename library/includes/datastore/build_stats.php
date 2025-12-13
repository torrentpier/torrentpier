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
$excludedUsers = array_map('intval', explode(',', EXCLUDED_USERS));

// usercount
$data['usercount'] = DB()->table(BB_USERS)
    ->where('user_id NOT IN ?', $excludedUsers)
    ->count('*');

// newestuser
$data['newestuser'] = DB()->table(BB_USERS)
    ->select('user_id, username, user_rank')
    ->where('user_active', 1)
    ->where('user_id NOT IN ?', $excludedUsers)
    ->order('user_id DESC')
    ->limit(1)
    ->fetch()
    ?->toArray();

// post/topic count
$row = DB()->table(BB_FORUMS)
    ->select('SUM(forum_topics) AS topiccount, SUM(forum_posts) AS postcount')
    ->fetch();
$data['postcount'] = (int)$row?->postcount;
$data['topiccount'] = (int)$row?->topiccount;

// Tracker stats
if (config()->get('tor_stats')) {
    $row = DB()->table(BB_BT_TORRENTS)
        ->select('COUNT(topic_id) AS torrentcount, SUM(size) AS size')
        ->fetch();
    $data['torrentcount'] = commify($row?->torrentcount);
    $data['size'] = $row?->size;

    $row = DB()->table(BB_BT_TRACKER_SNAP)
        ->select('SUM(seeders) AS seeders, SUM(leechers) AS leechers, ((SUM(speed_up) + SUM(speed_down))/2) AS speed')
        ->fetch();
    $seeders = (int)$row?->seeders;
    $leechers = (int)$row?->leechers;
    $data['seeders'] = commify($seeders);
    $data['leechers'] = commify($leechers);
    $data['peers'] = commify($seeders + $leechers);
    $data['speed'] = $row?->speed;
}

// gender stat
if (config()->get('gender')) {
    $data['male'] = DB()->table(BB_USERS)
        ->where('user_gender', MALE)
        ->where('user_id NOT IN ?', $excludedUsers)
        ->count('*');

    $data['female'] = DB()->table(BB_USERS)
        ->where('user_gender', FEMALE)
        ->where('user_id NOT IN ?', $excludedUsers)
        ->count('*');

    $data['unselect'] = DB()->table(BB_USERS)
        ->where('user_gender', 0)
        ->where('user_id NOT IN ?', $excludedUsers)
        ->count('*');
}

// birthday stat
if (config()->get('birthday_check_day') && config()->get('birthday_enabled')) {
    $checkDays = (int)config()->get('birthday_check_day');

    // Use numeric MMDD format with birthday_md generated column
    $dateToday = (int)date('n') * 100 + (int)date('j'); // e.g., 1207 for Dec 7
    $dateForward = (int)date('n', strtotime("+{$checkDays} days")) * 100 + (int)date('j', strtotime("+{$checkDays} days"));

    // Helper to convert ActiveRow objects to arrays
    $toArrays = static fn (array $rows): array => array_map(static fn ($row) => $row->toArray(), $rows);

    // Birthday today - using the birthday_md indexed column
    $data['birthday_today_list'] = $toArrays(DB()->table(BB_USERS)
        ->select('user_id, username, user_rank, user_birthday')
        ->where('user_id NOT IN ?', $excludedUsers)
        ->where('user_birthday !=', '1900-01-01')
        ->where('user_active', 1)
        ->where('birthday_md', $dateToday)
        ->order('user_level DESC, username')
        ->fetchAll());

    // Birthday in upcoming days - using birthday_md indexed column
    // Handle year wrap-around (e.g., Dec 28 + 7 days = Jan 4)
    if ($dateForward < $dateToday) {
        $data['birthday_week_list'] = $toArrays(DB()->table(BB_USERS)
            ->select('user_id, username, user_rank, user_birthday')
            ->where('user_id NOT IN ?', $excludedUsers)
            ->where('user_birthday !=', '1900-01-01')
            ->where('user_active', 1)
            ->where('(birthday_md > ? OR birthday_md <= ?)', $dateToday, $dateForward)
            ->order('user_level DESC, username')
            ->fetchAll());
    } else {
        $data['birthday_week_list'] = $toArrays(DB()->table(BB_USERS)
            ->select('user_id, username, user_rank, user_birthday')
            ->where('user_id NOT IN ?', $excludedUsers)
            ->where('user_birthday !=', '1900-01-01')
            ->where('user_active', 1)
            ->where('birthday_md > ?', $dateToday)
            ->where('birthday_md <= ?', $dateForward)
            ->order('user_level DESC, username')
            ->fetchAll());
    }
}

$this->store('stats', $data);
