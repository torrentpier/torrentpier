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

define('BB_SCRIPT', 'forum');
define('BB_ROOT', './');
require __DIR__ . '/common.php';

$page_cfg['include_bbcode_js'] = true;

$show_last_topic = true;
$last_topic_max_len = 40;
$title_match_key = 'nm';
$title_match_max_len = 60;

$page_cfg['load_tpl_vars'] = array(
    'post_icons',
    'topic_icons',
);

// Init request vars
$forum_id = (int)request_var('f', '');
$start = abs((int)request_var('start', ''));
$mark_read = (request_var('mark', '') === 'topics');

$anon = GUEST_UID;

// Start session
$user->session_start();

$lastvisit = (IS_GUEST) ? TIMENOW : $userdata['user_lastvisit'];

// Caching output
$req_page = "forum_f{$forum_id}";
$req_page .= ($start) ? "_start{$start}" : '';

define('REQUESTED_PAGE', $req_page);
caching_output(IS_GUEST, 'send', REQUESTED_PAGE . '_guest');

set_die_append_msg();
if (!$forums = $datastore->get('cat_forums')) {
    $datastore->update('cat_forums');
    $forums = $datastore->get('cat_forums');
}
if (!$forum_id or !$forum_data = @$forums['forum'][$forum_id]) {
    bb_die($lang['FORUM_NOT_EXIST']);
}

// Only new
$only_new = $user->opt_js['only_new'];
$only_new_sql = '';
if ($only_new == ONLY_NEW_POSTS) {
    $only_new_sql = "AND t.topic_last_post_time > $lastvisit";
} elseif ($only_new == ONLY_NEW_TOPICS) {
    $only_new_sql = "AND t.topic_time > $lastvisit";
}

// Auth
$is_auth = auth(AUTH_ALL, $forum_id, $userdata, $forum_data);

$moderation = (!empty($_REQUEST['mod']) && $is_auth['auth_mod']);

if (!$is_auth['auth_view']) {
    if (IS_GUEST) {
        $redirect = ($start) ? "&start=$start" : '';
        redirect(LOGIN_URL . "?redirect=" . FORUM_URL . $forum_id . $redirect);
    }
    // The user is not authed to read this forum ...
    $message = sprintf($lang['SORRY_AUTH_VIEW'], $is_auth['auth_view_type']);
    bb_die($message);
}

// Redirect to login page if not admin session
$mod_redirect_url = '';

// Filter by torrent status
$tor_status = -1;  //  all by default

if ($is_auth['auth_mod']) {
    $redirect = isset($_POST['redirect']) ? $_POST['redirect'] : $_SERVER['REQUEST_URI'];
    $redirect = url_arg($redirect, 'mod', 1, '&');
    $mod_redirect_url = LOGIN_URL . "?redirect=$redirect&admin=1";

    if ($moderation && !$userdata['session_admin']) {
        redirect($mod_redirect_url);
    }
    if (isset($_REQUEST['tst']) && $_REQUEST['tst'] != -1) {
        $tor_status = (int)$_REQUEST['tst'];
        // reset other req values
        unset($_REQUEST['sort'], $_REQUEST['order'], $_REQUEST[$title_match_key]);
        $show_type_separator = false;
    }
    $select_tst = array_merge(array($lang['TOR_STATUS_SELECT_ALL'] => -1), array_flip($lang['TOR_STATUS_NAME']));
    $template->assign_vars(array(
        'SELECT_TST' => build_select('tst', $select_tst, $tor_status),
    ));
    $select_st = array_merge(array($lang['TOR_STATUS_SELECT_ACTION'] => -1), array_flip($lang['TOR_STATUS_NAME']));
    $template->assign_vars(array(
        'SELECT_ST' => build_select('st', $select_st, -1),
    ));
}

// Topics read tracks
$tracking_topics = get_tracks('topic');
$tracking_forums = get_tracks('forum');

if ($mark_read && !IS_GUEST) {
    set_tracks(COOKIE_FORUM, $tracking_forums, $forum_id);

    set_die_append_msg($forum_id);
    bb_die($lang['TOPICS_MARKED_READ']);
}

// Subforums
$show_subforums = $bb_cfg['sf_on_first_page_only'] ? !$start : true;

if (!$forums = $datastore->get('cat_forums')) {
    $datastore->update('cat_forums');
    $forums = $datastore->get('cat_forums');
}

if ($forums['forum'][$forum_id]['allow_porno_topic'] && bf($userdata['user_opt'], 'user_opt', 'user_porn_forums')) {
    bb_die($lang['ERROR_PORNO_FORUM']);
}

if (!$forum_data['forum_parent'] && isset($forums['f'][$forum_id]['subforums']) && $show_subforums) {
    $not_auth_forums = $user->get_excluded_forums(AUTH_VIEW);
    $ignore_forum_sql = ($not_auth_forums) ? "AND f.forum_id NOT IN($not_auth_forums)" : '';

    $sql = "
		SELECT
			f.forum_id, f.forum_status, f.forum_last_post_id, f.forum_posts, f.forum_topics,
			t.topic_last_post_time, t.topic_id AS last_topic_id, t.topic_title AS last_topic_title,
			p.poster_id AS sf_last_user_id, IF(p.poster_id = $anon, p.post_username, u.username) AS sf_last_username, u.user_rank
		FROM      " . BB_FORUMS . " f
		LEFT JOIN " . BB_TOPICS . " t ON(f.forum_last_post_id = t.topic_last_post_id)
		LEFT JOIN " . BB_POSTS . " p ON(f.forum_last_post_id = p.post_id)
		LEFT JOIN " . BB_USERS . " u ON(p.poster_id = u.user_id)
		WHERE f.forum_parent = $forum_id
			$only_new_sql
			$ignore_forum_sql
		ORDER BY f.forum_order
	";

    if ($rowset = DB()->fetch_rowset($sql)) {
        $template->assign_vars(array(
            'SHOW_SUBFORUMS' => true,
            'FORUM_IMG' => $images['forum'],
            'FORUM_NEW_IMG' => $images['forum_new'],
            'FORUM_LOCKED_IMG' => $images['forum_locked'],
        ));
    }
    foreach ($rowset as $sf_data) {
        $sf_forum_id = $sf_data['forum_id'];
        $sf_last_tid = $sf_data['last_topic_id'];
        $folder_image = $images['forum'];
        $last_post = $lang['NO_POSTS'];

        if (!$fname_html =& $forums['forum_name_html'][$sf_forum_id]) {
            continue;
        }

        if ($sf_data['forum_status'] == FORUM_LOCKED) {
            $folder_image = $images['forum_locked'];
        } elseif (is_unread($sf_data['topic_last_post_time'], $sf_last_tid, $sf_forum_id)) {
            $folder_image = $images['forum_new'];
        }

        $last_post_user = profile_url(array('username' => $sf_data['sf_last_username'], 'user_id' => $sf_data['sf_last_user_id'], 'user_rank' => $sf_data['user_rank']));

        if ($sf_data['forum_last_post_id']) {
            $last_post = bb_date($sf_data['topic_last_post_time'], $bb_cfg['last_post_date_format']);
            $last_post .= "<br />$last_post_user";
            $last_post .= '<a href="' . POST_URL . $sf_data['forum_last_post_id'] . '#' . $sf_data['forum_last_post_id'] . '"><img src="' . $images['icon_latest_reply'] . '" class="icon2" alt="latest" title="' . $lang['VIEW_LATEST_POST'] . '" /></a>';
        }

        $template->assign_block_vars('f', array(
            'FORUM_FOLDER_IMG' => $folder_image,

            'FORUM_NAME' => $fname_html,
            'FORUM_DESC' => $forums['f'][$sf_forum_id]['forum_desc'],
            'U_VIEWFORUM' => FORUM_URL . $sf_forum_id,
            'TOPICS' => commify($sf_data['forum_topics']),
            'POSTS' => commify($sf_data['forum_posts']),
            'LAST_POST' => $last_post,
        ));

        if ($sf_data['forum_last_post_id']) {
            $template->assign_block_vars('f.last', array(
                'FORUM_LAST_POST' => true,
                'SHOW_LAST_TOPIC' => $show_last_topic,
                'LAST_TOPIC_ID' => $sf_data['last_topic_id'],
                'LAST_TOPIC_TIP' => $sf_data['last_topic_title'],
                'LAST_TOPIC_TITLE' => str_short($sf_data['last_topic_title'], $last_topic_max_len),
                'LAST_POST_TIME' => bb_date($sf_data['topic_last_post_time'], $bb_cfg['last_post_date_format']),
                'LAST_POST_ID' => $sf_data['forum_last_post_id'],
                'LAST_POST_USER' => $last_post_user,
                'ICON_LATEST_REPLY' => $images['icon_latest_reply'],
            ));
        } else {
            $template->assign_block_vars('f.last', array('FORUM_LAST_POST' => false));
        }
    }
}
unset($rowset);
$datastore->rm('cat_forums');

// Topics per page
$topics_per_page = $bb_cfg['topics_per_page'];
$select_tpp = '';

if ($is_auth['auth_mod']) {
    if ($req_tpp = abs((int)(@$_REQUEST['tpp'])) and in_array($req_tpp, $bb_cfg['allowed_topics_per_page'])) {
        $topics_per_page = $req_tpp;
    }

    $select_tpp = array();
    foreach ($bb_cfg['allowed_topics_per_page'] as $tpp) {
        $select_tpp[$tpp] = $tpp;
    }
}

// Generate a 'Show topics in previous x days' select box.
$topic_days = 0; // all the time
$forum_topics = $forum_data['forum_topics'];

$sel_previous_days = array(
    0 => $lang['ALL_POSTS'],
    1 => $lang['1_DAY'],
    7 => $lang['7_DAYS'],
    14 => $lang['2_WEEKS'],
    30 => $lang['1_MONTH'],
    90 => $lang['3_MONTHS'],
    180 => $lang['6_MONTHS'],
    364 => $lang['1_YEAR'],
);

if (!empty($_REQUEST['topicdays'])) {
    if ($req_topic_days = abs((int)$_REQUEST['topicdays']) and isset($sel_previous_days[$req_topic_days])) {
        $sql = "
			SELECT COUNT(*) AS forum_topics
			FROM " . BB_TOPICS . "
			WHERE forum_id = $forum_id
				AND topic_last_post_time > " . (TIMENOW - 86400 * $req_topic_days) . "
		";

        if ($row = DB()->fetch_row($sql)) {
            $topic_days = $req_topic_days;
            $forum_topics = $row['forum_topics'];
        }
    }
}
// Correct $start value
if ($start > $forum_topics) {
    redirect(FORUM_URL . $forum_id);
}

// Generate SORT and ORDER selects
$sort_value = isset($_REQUEST['sort']) ? (int)$_REQUEST['sort'] : $forum_data['forum_display_sort'];
$order_value = isset($_REQUEST['order']) ? (int)$_REQUEST['order'] : $forum_data['forum_display_order'];
$sort_list = '<select name="sort">' . get_forum_display_sort_option($sort_value, 'list', 'sort') . '</select>';
$order_list = '<select name="order">' . get_forum_display_sort_option($order_value, 'list', 'order') . '</select>';
$s_display_order = '&nbsp;' . $lang['SORT_BY'] . ':&nbsp;' . $sort_list . '&nbsp;' . $order_list . '&nbsp;';

// Selected SORT and ORDER methods
$sort_method = get_forum_display_sort_option($sort_value, 'field', 'sort');
$order_method = get_forum_display_sort_option($order_value, 'field', 'order');

$order_sql = "ORDER BY t.topic_type DESC, $sort_method $order_method";

$limit_topics_time_sql = ($topic_days) ? "AND t.topic_last_post_time > " . (TIMENOW - 86400 * $topic_days) : '';

$select_tor_sql = $join_tor_sql = '';
$join_dl = ($bb_cfg['show_dl_status_in_forum'] && !IS_GUEST);

$where_tor_sql = '';
if ($forum_data['allow_reg_tracker']) {
    if ($tor_status != -1) {
        $where_tor_sql = "AND tor.tor_status = $tor_status";
    }

    $select_tor_sql = ',
		bt.auth_key, tor.info_hash, tor.size AS tor_size, tor.reg_time, tor.complete_count, tor.seeder_last_seen, tor.attach_id, tor.tor_status, tor.tor_type,
		sn.seeders, sn.leechers
	';
    $select_tor_sql .= ($join_dl) ? ', dl.user_status AS dl_status' : '';

    $join_tor_sql = "
		LEFT JOIN " . BB_BT_TORRENTS . " tor ON(t.topic_id = tor.topic_id)
		LEFT JOIN " . BB_BT_USERS . " bt  ON(bt.user_id = {$userdata['user_id']})
		LEFT JOIN " . BB_BT_TRACKER_SNAP . " sn  ON(tor.topic_id = sn.topic_id)
	";
    $join_tor_sql .= ($join_dl) ? " LEFT JOIN " . BB_BT_DLSTATUS . " dl ON(dl.user_id = {$userdata['user_id']} AND dl.topic_id = t.topic_id)" : '';
}

// Title match
$title_match_sql = '';

if ($title_match =& $_REQUEST[$title_match_key]) {
    if ($tmp = mb_substr(trim($title_match), 0, $title_match_max_len)) {
        $title_match_val = clean_text_match($tmp, true, false);
        $title_match_topics = get_title_match_topics($title_match_val, array(0 => $forum_id));

        if ($search_match_topics_csv = implode(',', $title_match_topics)) {
            $title_match_sql = "AND t.topic_id IN($search_match_topics_csv)";
        }
    }
}

// Get topics
$topic_ids = $topic_rowset = array();

// IDs
$sql = "
	SELECT t.topic_id
	FROM " . BB_TOPICS . " t
	WHERE t.forum_id = $forum_id
		$only_new_sql
		$title_match_sql
		$limit_topics_time_sql
	$order_sql
	LIMIT $start, $topics_per_page
";
foreach (DB()->fetch_rowset($sql) as $row) {
    $topic_ids[] = $row['topic_id'];
}

// Titles, posters etc.
if ($topics_csv = implode(',', $topic_ids)) {
    $topic_rowset = DB()->fetch_rowset("
		SELECT
			t.*, t.topic_poster AS first_user_id, u1.user_rank as first_user_rank,
			IF(t.topic_poster = $anon, p1.post_username, u1.username) AS first_username,
			p2.poster_id AS last_user_id, u2.user_rank as last_user_rank,
			IF(p2.poster_id = $anon, p2.post_username, u2.username) AS last_username
				$select_tor_sql
		FROM      " . BB_TOPICS . " t
		LEFT JOIN " . BB_POSTS . " p1 ON(t.topic_first_post_id = p1.post_id)
		LEFT JOIN " . BB_USERS . " u1 ON(t.topic_poster = u1.user_id)
		LEFT JOIN " . BB_POSTS . " p2 ON(t.topic_last_post_id = p2.post_id)
		LEFT JOIN " . BB_USERS . " u2 ON(p2.poster_id = u2.user_id)
			$join_tor_sql
		WHERE t.topic_id IN($topics_csv)
		    $where_tor_sql
		GROUP BY t.topic_id
		$order_sql
	");
}

// Define censored word matches
$orig_word = $replacement_word = array();
obtain_word_list($orig_word, $replacement_word);

if ($forum_data['allow_reg_tracker']) {
    $post_new_topic_url = POSTING_URL . "?mode=new_rel&amp;f=$forum_id";
    $post_img = $images['release_new'];
    $post_new_topic = $lang['POST_NEW_RELEASE'];
} else {
    $post_new_topic_url = POSTING_URL . "?mode=newtopic&amp;f=$forum_id";
    $post_img = $images['post_new'];
    $post_new_topic = $lang['POST_NEW_TOPIC'];
}

// Post URL generation for templating vars
$template->assign_vars(array(
    'U_POST_NEW_TOPIC' => $post_new_topic_url,
    'S_SELECT_TOPIC_DAYS' => build_select('topicdays', array_flip($sel_previous_days), $topic_days),
    'S_POST_DAYS_ACTION' => "viewforum.php?f=$forum_id&amp;start=$start",
    'S_DISPLAY_ORDER' => $s_display_order,
));

// User authorisation levels output
$u_auth = array();
$u_auth[] = ($is_auth['auth_post']) ? $lang['RULES_POST_CAN'] : $lang['RULES_POST_CANNOT'];
$u_auth[] = ($is_auth['auth_reply']) ? $lang['RULES_REPLY_CAN'] : $lang['RULES_REPLY_CANNOT'];
$u_auth[] = ($is_auth['auth_edit']) ? $lang['RULES_EDIT_CAN'] : $lang['RULES_EDIT_CANNOT'];
$u_auth[] = ($is_auth['auth_delete']) ? $lang['RULES_DELETE_CAN'] : $lang['RULES_DELETE_CANNOT'];
$u_auth[] = ($is_auth['auth_vote']) ? $lang['RULES_VOTE_CAN'] : $lang['RULES_VOTE_CANNOT'];
$u_auth[] = ($is_auth['auth_attachments']) ? $lang['RULES_ATTACH_CAN'] : $lang['RULES_ATTACH_CANNOT'];
$u_auth[] = ($is_auth['auth_download']) ? $lang['RULES_DOWNLOAD_CAN'] : $lang['RULES_DOWNLOAD_CANNOT'];
$u_auth[] = ($is_auth['auth_mod']) ? $lang['RULES_MODERATE'] : '';
$u_auth = implode("<br />\n", $u_auth);

$template->assign_vars(array(
    'SHOW_JUMPBOX' => true,
    'PAGE_TITLE' => htmlCHR($forum_data['forum_name']),
    'FORUM_ID' => $forum_id,
    'FORUM_NAME' => htmlCHR($forum_data['forum_name']),
    'TORRENTS' => $forum_data['allow_reg_tracker'],
    'POST_IMG' => ($forum_data['forum_status'] == FORUM_LOCKED) ? $images['post_locked'] : $post_img,

    'FOLDER_IMG' => $images['folder'],
    'FOLDER_NEW_IMG' => $images['folder_new'],
    'FOLDER_LOCKED_IMG' => $images['folder_locked'],
    'FOLDER_STICKY_IMG' => $images['folder_sticky'],
    'FOLDER_ANNOUNCE_IMG' => $images['folder_announce'],
    'FOLDER_DOWNLOAD_IMG' => $images['folder_dl'],

    'SHOW_ONLY_NEW_MENU' => true,
    'ONLY_NEW_POSTS_ON' => ($only_new == ONLY_NEW_POSTS),
    'ONLY_NEW_TOPICS_ON' => ($only_new == ONLY_NEW_TOPICS),

    'TITLE_MATCH' => htmlCHR($title_match),
    'SELECT_TPP' => ($select_tpp) ? build_select('tpp', $select_tpp, $topics_per_page, null, null, 'onchange="$(\'#tpp\').submit();"') : '',
    'T_POST_NEW_TOPIC' => ($forum_data['forum_status'] == FORUM_LOCKED) ? $lang['FORUM_LOCKED'] : $post_new_topic,
    'S_AUTH_LIST' => $u_auth,
    'U_VIEW_FORUM' => FORUM_URL . $forum_id,
    'U_MARK_READ' => FORUM_URL . $forum_id . "&amp;mark=topics",
    'U_SEARCH_SELF' => "search.php?uid={$userdata['user_id']}&f=$forum_id",
));

// Okay, lets dump out the page ...
$found_topics = 0;
foreach ($topic_rowset as $topic) {
    $topic_id = $topic['topic_id'];
    $moved = ($topic['topic_status'] == TOPIC_MOVED);
    $replies = $topic['topic_replies'];
    $t_hot = ($replies >= $bb_cfg['hot_threshold']);
    $t_type = $topic['topic_type'];
    $separator = '';
    $is_unread = is_unread($topic['topic_last_post_time'], $topic_id, $forum_id);

    if ($t_type == POST_ANNOUNCE && !defined('ANNOUNCE_SEP')) {
        define('ANNOUNCE_SEP', true);
        $separator = $lang['TOPICS_ANNOUNCEMENT'];
    } elseif ($t_type == POST_STICKY && !defined('STICKY_SEP')) {
        define('STICKY_SEP', true);
        $separator = $lang['TOPICS_STICKY'];
    } elseif ($t_type == POST_NORMAL && !defined('NORMAL_SEP')) {
        if (defined('ANNOUNCE_SEP') || defined('STICKY_SEP')) {
            define('NORMAL_SEP', true);
            $separator = $lang['TOPICS_NORMAL'];
        }
    }

    $template->assign_block_vars('t', array(
        'FORUM_ID' => $forum_id,
        'TOPIC_ID' => $topic_id,
        'HREF_TOPIC_ID' => ($moved) ? $topic['topic_moved_id'] : $topic['topic_id'],
        'TOPIC_TITLE' => wbr($topic['topic_title']),
        'TOPICS_SEPARATOR' => $separator,
        'IS_UNREAD' => $is_unread,
        'TOPIC_ICON' => get_topic_icon($topic, $is_unread),
        'PAGINATION' => ($moved) ? '' : build_topic_pagination(TOPIC_URL . $topic_id, $replies, $bb_cfg['posts_per_page']),
        'REPLIES' => $replies,
        'VIEWS' => $topic['topic_views'],
        'TOR_STALED' => ($forum_data['allow_reg_tracker'] && !($t_type == POST_ANNOUNCE || $t_type == POST_STICKY || $topic['tor_size'])),
        'TOR_FROZEN' => isset($topic['tor_status']) ? ((!IS_AM) ? isset($bb_cfg['tor_frozen'][$topic['tor_status']]) : '') : '',
        'TOR_TYPE' => isset($topic['tor_type']) ? is_gold($topic['tor_type']) : '',

        'TOR_STATUS_ICON' => isset($topic['tor_status']) ? $bb_cfg['tor_icons'][$topic['tor_status']] : '',
        'TOR_STATUS_TEXT' => isset($topic['tor_status']) ? $lang['TOR_STATUS_NAME'][$topic['tor_status']] : '',

        'ATTACH' => isset($topic['topic_attachment']) ? $topic['topic_attachment'] : false,
        'STATUS' => $topic['topic_status'],
        'TYPE' => $topic['topic_type'],
        'DL' => ($topic['topic_dl_type'] == TOPIC_DL_TYPE_DL && !$forum_data['allow_reg_tracker']),
        'POLL' => $topic['topic_vote'],
        'DL_CLASS' => isset($topic['dl_status']) ? $dl_link_css[$topic['dl_status']] : '',

        'TOPIC_AUTHOR' => profile_url(array('username' => str_short($topic['first_username'], 15), 'user_id' => $topic['first_user_id'], 'user_rank' => $topic['first_user_rank'])),
        'LAST_POSTER' => profile_url(array('username' => str_short($topic['last_username'], 15), 'user_id' => $topic['last_user_id'], 'user_rank' => $topic['last_user_rank'])),
        'LAST_POST_TIME' => bb_date($topic['topic_last_post_time']),
        'LAST_POST_ID' => $topic['topic_last_post_id'],
    ));

    if (isset($topic['tor_size'])) {
        $tor_magnet = create_magnet($topic['info_hash'], $topic['auth_key'], $userdata['session_logged_in']);

        $template->assign_block_vars('t.tor', array(
            'SEEDERS' => (int)$topic['seeders'],
            'LEECHERS' => (int)$topic['leechers'],
            'TOR_SIZE' => humn_size($topic['tor_size']),
            'COMPL_CNT' => (int)$topic['complete_count'],
            'ATTACH_ID' => $topic['attach_id'],
            'MAGNET' => $tor_magnet,
        ));
    }
    $found_topics++;
}
unset($topic_rowset);

$pg_url = FORUM_URL . $forum_id;
$pg_url .= ($sort_value) ? "&sort=$sort_value" : '';
$pg_url .= ($order_value) ? "&order=$order_value" : '';
$template->assign_var('MOD_URL', $pg_url);
$pg_url = FORUM_URL . $forum_id;
$pg_url .= ($topic_days) ? "&amp;topicdays=$topic_days" : '';
$pg_url .= ($sort_value) ? "&amp;sort=$sort_value" : '';
$pg_url .= ($order_value) ? "&amp;order=$order_value" : '';
$pg_url .= ($moderation) ? "&amp;mod=1" : '';
$pg_url .= ($topics_per_page != $bb_cfg['topics_per_page']) ? "&amp;tpp=$topics_per_page" : '';

if ($found_topics) {
    generate_pagination($pg_url, $forum_topics, $topics_per_page, $start);
} else {
    if ($only_new) {
        $no_topics_msg = $lang['NO_NEW_POSTS'];
    } else {
        $no_topics_msg = ($topic_days || $title_match) ? $lang['NO_SEARCH_MATCH'] : $lang['NO_TOPICS_POST_ONE'];
    }
    $template->assign_vars(array(
        'NO_TOPICS' => $no_topics_msg,
    ));
}

$template->assign_vars(array(
    'PAGE_URL' => $pg_url,
    'PAGE_URL_TPP' => url_arg($pg_url, 'tpp', null),
    'FOUND_TOPICS' => $found_topics,

    'AUTH_MOD' => $is_auth['auth_mod'],
    'SESSION_ADMIN' => $userdata['session_admin'],
    'MOD_REDIRECT_URL' => $mod_redirect_url,
    'MODERATION_ON' => $moderation,
    'PRUNE_DAYS' => $forum_data['prune_days'],

    'CAT_ID' => $forum_data['cat_id'],
    'CAT_TITLE' => $forums['cat_title_html'][$forum_data['cat_id']],
    'U_VIEWCAT' => CAT_URL . $forum_data['cat_id'],
    'PARENT_FORUM_HREF' => ($parent_id = $forum_data['forum_parent']) ? FORUM_URL . $forum_data['forum_parent'] : '',
    'PARENT_FORUM_NAME' => ($parent_id = $forum_data['forum_parent']) ? $forums['forum_name_html'][$parent_id] : '',
));

print_page('viewforum.tpl');
