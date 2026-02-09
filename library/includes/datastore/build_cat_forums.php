<?php

/**
 * TorrentPier – Bull-powered BitTorrent tracker engine
 *
 * @copyright Copyright (c) 2005-2025 TorrentPier (https://torrentpier.com)
 * @link      https://github.com/torrentpier/torrentpier for the canonical source repository
 * @license   https://github.com/torrentpier/torrentpier/blob/master/LICENSE MIT License
 */

//
// cat_forums
//
$data = [
    'not_auth_forums' => [
        'guest_view' => [],
        'guest_read' => [],
        'user_view' => [],
        'user_read' => [],
    ],
    'tracker_forums' => [],
    'cat_title_html' => [],
    'forum_name_html' => [],
    'c' => [], // also has $data['c']['cat_id']['forums'] key
    'f' => [], // also has $data['f']['forum_id']['subforums'] key
];

// Store only these fields from BB_FORUMS in $data['f']
$forum_store_fields = array_flip(array_keys(bitfields('forum_perm')));
$forum_store_fields += array_flip([
    'forum_id',
    'cat_id',
    'forum_name',
    'forum_desc',
    'forum_status',
    'forum_posts',
    'forum_topics',
    'forum_parent',
]);

// Categories
$sql = 'SELECT * FROM ' . BB_CATEGORIES . ' ORDER BY cat_order';

foreach (DB()->fetch_rowset($sql) as $row) {
    $data['c'][$row['cat_id']] = $row;
    $data['cat_title_html'][$row['cat_id']] = htmlCHR($row['cat_title']);
}

$sql = '
	SELECT f.*
	FROM ' . BB_FORUMS . ' f, ' . BB_CATEGORIES . ' c
	WHERE f.cat_id = c.cat_id
	ORDER BY c.cat_order, f.forum_order
';

foreach (DB()->fetch_rowset($sql) as $row) {
    $fid = $row['forum_id'];
    $not_auth = &$data['not_auth_forums'];

    // Find not auth forums
    if ($row['auth_view'] != AUTH_ALL) {
        $not_auth['guest_view'][] = $fid;
    }
    if ($row['auth_view'] != AUTH_ALL && $row['auth_view'] != AUTH_REG) {
        $not_auth['user_view'][] = $fid;
    }
    if ($row['auth_read'] != AUTH_ALL) {
        $not_auth['guest_read'][] = $fid;
    }
    if ($row['auth_read'] != AUTH_ALL && $row['auth_read'] != AUTH_REG) {
        $not_auth['user_read'][] = $fid;
    }

    $data['forum'][$fid] = $row;

    // Store forums data
    if ($parent_id = $row['forum_parent']) {
        $parent = &$data['f'][$parent_id];

        $parent['subforums'][] = $fid;
        $parent['forum_posts'] += $row['forum_posts'];
        $parent['forum_topics'] += $row['forum_topics'];
    }
    if ($row['allow_reg_tracker']) {
        $data['tracker_forums'][] = $fid;
    }

    $data['f'][$fid] = array_intersect_key($row, $forum_store_fields);
    $data['forum_name_html'][$fid] = htmlCHR($row['forum_name']);

    // Forum ids in cat
    $data['c'][$row['cat_id']]['forums'][] = $fid;
}
foreach ($data['not_auth_forums'] as $key => $val) {
    $data['not_auth_forums'][$key] = implode(',', $val);
}
$data['tracker_forums'] = implode(',', $data['tracker_forums']);

$this->store('cat_forums', $data);

//
// jumpbox
//
if (config()->get('forum.show_jumpbox')) {
    $data = [
        'guest' => get_forum_select('guest', 'f', null, null, null, 'id="jumpbox" onchange="window.location.href=\'' . FORUM_URL . '\'+this.value;"'),
        'user' => get_forum_select('user', 'f', null, null, null, 'id="jumpbox" onchange="window.location.href=\'' . FORUM_URL . '\'+this.value;"'),
    ];

    $this->store('jumpbox', $data);
}

//
// viewtopic_forum_select
//
$data = ['viewtopic_forum_select' => get_forum_select('admin', 'new_forum_id')];

$this->store('viewtopic_forum_select', $data);

//
// latest_news
//
if (config()->get('show_latest_news') && $news_forum_ids = config()->get('latest_news_forum_id')) {
    $news_count = max(config()->get('latest_news_count'), 1);

    $data = DB()->fetch_rowset('
		SELECT topic_id, topic_time, topic_title, forum_id
		FROM ' . BB_TOPICS . "
		WHERE forum_id IN ({$news_forum_ids})
			AND topic_moved_id = 0
		ORDER BY topic_time DESC
		LIMIT {$news_count}
	");

    $this->store('latest_news', $data);
}

//
// Network_news
//
if (config()->get('show_network_news') && $net_forum_ids = config()->get('network_news_forum_id')) {
    $net_count = max(config()->get('network_news_count'), 1);

    $data = DB()->fetch_rowset('
		SELECT topic_id, topic_time, topic_title, forum_id
		FROM ' . BB_TOPICS . "
		WHERE forum_id IN ({$net_forum_ids})
			AND topic_moved_id = 0
		ORDER BY topic_time DESC
		LIMIT {$net_count}
	");

    $this->store('network_news', $data);
}
