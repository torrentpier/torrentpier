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

global $bf;

/** @var \TorrentPier\Di $di */
$di = \TorrentPier\Di::getInstance();

//
// cat_forums
//
$data = array(
    'not_auth_forums' => array(
        'guest_view' => array(),
        'guest_read' => array(),
        'user_view' => array(),
        'user_read' => array(),
    ),
    'tracker_forums' => array(),
    'cat_title_html' => array(),
    'forum_name_html' => array(),
    'c' => array(),                // also has $data['c']['cat_id']['forums'] key
    'f' => array(),                // also has $data['f']['forum_id']['subforums'] key
);

// Store only these fields from BB_FORUMS in $data['f']
$forum_store_fields = array_flip(array_keys($bf['forum_perm']));
$forum_store_fields += array_flip(array(
    'forum_id',
    'cat_id',
    'forum_name',
    'forum_desc',
    'forum_status',
    'forum_posts',
    'forum_topics',
    'forum_parent',
));

// Categories
$sql = "SELECT * FROM " . BB_CATEGORIES . " ORDER BY cat_order";

foreach (DB()->fetch_rowset($sql) as $row) {
    $data['c'][$row['cat_id']] = $row;
    $data['cat_title_html'][$row['cat_id']] = htmlCHR($row['cat_title']);
}

$sql = "
	SELECT f.*
	FROM " . BB_FORUMS . " f, " . BB_CATEGORIES . " c
	WHERE f.cat_id = c.cat_id
	ORDER BY c.cat_order, f.forum_order
";

foreach (DB()->fetch_rowset($sql) as $row) {
    $fid = $row['forum_id'];
    $not_auth =& $data['not_auth_forums'];

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
        $parent =& $data['f'][$parent_id];

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
    $data['not_auth_forums'][$key] = join(',', $val);
}
$data['tracker_forums'] = join(',', $data['tracker_forums']);

$this->store('cat_forums', $data);

//
// jumpbox
//
$data = array(
    'guest' => get_forum_select('guest', 'f', null, null, null, 'id="jumpbox" onchange="window.location.href=\'viewforum.php?f=\'+this.value;"'),
    'user' => get_forum_select('user', 'f', null, null, null, 'id="jumpbox" onchange="window.location.href=\'viewforum.php?f=\'+this.value;"'),
);

$this->store('jumpbox', $data);

file_write($data['guest'], AJAX_HTML_DIR . 'jumpbox_guest.html', false, true, true);
file_write($data['user'], AJAX_HTML_DIR . 'jumpbox_user.html', false, true, true);

//
// viewtopic_forum_select
//
$data = array(
    'viewtopic_forum_select' => get_forum_select('admin', 'new_forum_id'),
);

$this->store('viewtopic_forum_select', $data);

//
// latest_news
//
if ($di->config->get('show_latest_news') && ($news_forum_ids = $di->config->get('latest_news_forum_id'))) {
    $news_count = max($di->config->get('latest_news_count'), 1);

    $data = DB()->fetch_rowset("
		SELECT topic_id, topic_time, topic_title, forum_id
		FROM " . BB_TOPICS . "
		WHERE forum_id IN ($news_forum_ids)
			AND topic_moved_id = 0
		ORDER BY topic_time DESC
		LIMIT $news_count
	");

    $this->store('latest_news', $data);
}

//
// Network_news
//
if ($di->config->get('show_network_news') && ($net_forum_ids = $di->config->get('network_news_forum_id'))) {
    $net_count = max($di->config->get('network_news_count'), 1);

    $data = DB()->fetch_rowset("
		SELECT topic_id, topic_time, topic_title, forum_id
		FROM " . BB_TOPICS . "
		WHERE forum_id IN ($net_forum_ids)
			AND topic_moved_id = 0
		ORDER BY topic_time DESC
		LIMIT $net_count
	");

    $this->store('network_news', $data);
}
