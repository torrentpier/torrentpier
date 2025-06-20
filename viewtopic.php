<?php
/**
 * TorrentPier – Bull-powered BitTorrent tracker engine
 *
 * @copyright Copyright (c) 2005-2025 TorrentPier (https://torrentpier.com)
 * @link      https://github.com/torrentpier/torrentpier for the canonical source repository
 * @license   https://github.com/torrentpier/torrentpier/blob/master/LICENSE MIT License
 */

define('BB_SCRIPT', 'topic');

require __DIR__ . '/common.php';
require INC_DIR . '/bbcode.php';

$datastore->enqueue([
    'ranks',
    'cat_forums'
]);

$page_cfg['load_tpl_vars'] = [
    'post_buttons',
    'post_icons',
    'topic_icons'
];

$newest = $next_topic_id = 0;
$start = isset($_GET['start']) ? abs((int)$_GET['start']) : 0;
$topic_id = isset($_GET[POST_TOPIC_URL]) ? (int)$_GET[POST_TOPIC_URL] : 0;
$post_id = (!$topic_id && isset($_GET[POST_POST_URL])) ? (int)$_GET[POST_POST_URL] : 0;

// Start session
$user->session_start();

set_die_append_msg();

// Posts per page
$posts_per_page = config()->get('posts_per_page');
$select_ppp = '';

if ($userdata['session_admin']) {
    if ($req_ppp = abs((int)(@$_REQUEST['ppp'])) and in_array($req_ppp, config()->get('allowed_posts_per_page'))) {
        $posts_per_page = $req_ppp;
    }

    $select_ppp = [];
    foreach (config()->get('allowed_posts_per_page') as $ppp) {
        $select_ppp[$ppp] = $ppp;
    }
}

if (isset($_REQUEST['single'])) {
    $posts_per_page = 1;
} else {
    $start = floor($start / $posts_per_page) * $posts_per_page;
}

if (!$topic_id && !$post_id) {
    bb_die($lang['TOPIC_POST_NOT_EXIST'], 404);
}

$tracking_topics = get_tracks('topic');
$tracking_forums = get_tracks('forum');

// Find topic id if user requested a newer or older topic
if ($topic_id && isset($_GET['view']) && ($_GET['view'] == 'next' || $_GET['view'] == 'previous')) {
    $sql_condition = ($_GET['view'] == 'next') ? '>' : '<';
    $sql_ordering = ($_GET['view'] == 'next') ? 'ASC' : 'DESC';

    $sql = "SELECT t.topic_id
		FROM " . BB_TOPICS . " t, " . BB_TOPICS . " t2
		WHERE t2.topic_id = $topic_id
			AND t.forum_id = t2.forum_id
			AND t.topic_moved_id = 0
			AND t.topic_last_post_id $sql_condition t2.topic_last_post_id
		ORDER BY t.topic_last_post_id $sql_ordering
		LIMIT 1";

    if ($row = DB()->fetch_row($sql)) {
        $next_topic_id = $topic_id = $row['topic_id'];
    } else {
        $message = ($_GET['view'] == 'next') ? $lang['NO_NEWER_TOPICS'] : $lang['NO_OLDER_TOPICS'];
        bb_die($message);
    }
}

// Get forum/topic data
if ($topic_id) {
    $sql = "SELECT t.*, f.cat_id, f.forum_name, f.forum_desc, f.forum_status, f.forum_order, f.forum_posts, f.forum_topics, f.forum_last_post_id, f.forum_tpl_id, f.prune_days, f.auth_view, f.auth_read, f.auth_post, f.auth_reply, f.auth_edit, f.auth_delete, f.auth_sticky, f.auth_announce, f.auth_vote, f.auth_pollcreate, f.auth_attachments, f.auth_download, f.allow_reg_tracker, f.allow_porno_topic, f.self_moderated, f.forum_parent, f.show_on_index, f.forum_display_sort, f.forum_display_order, tw.notify_status
		FROM " . BB_TOPICS . " t
		LEFT JOIN " . BB_FORUMS . " f ON t.forum_id = f.forum_id
		LEFT JOIN " . BB_TOPICS_WATCH . " tw ON(tw.topic_id = t.topic_id AND tw.user_id = {$userdata['user_id']})
		WHERE t.topic_id = $topic_id
	";
} elseif ($post_id) {
    $sql = "SELECT t.*, f.cat_id, f.forum_name, f.forum_desc, f.forum_status, f.forum_order, f.forum_posts, f.forum_topics, f.forum_last_post_id, f.forum_tpl_id, f.prune_days, f.auth_view, f.auth_read, f.auth_post, f.auth_reply, f.auth_edit, f.auth_delete, f.auth_sticky, f.auth_announce, f.auth_vote, f.auth_pollcreate, f.auth_attachments, f.auth_download, f.allow_reg_tracker, f.allow_porno_topic, f.self_moderated, f.forum_parent, f.show_on_index, f.forum_display_sort, f.forum_display_order, p.post_time, tw.notify_status
		FROM " . BB_TOPICS . " t
		LEFT JOIN " . BB_FORUMS . " f ON t.forum_id = f.forum_id
		LEFT JOIN " . BB_POSTS . " p ON t.topic_id = p.topic_id
		LEFT JOIN " . BB_TOPICS_WATCH . " tw ON(tw.topic_id = t.topic_id AND tw.user_id = {$userdata['user_id']})
		WHERE p.post_id = $post_id
	";
} else {
    bb_die($lang['TOPIC_POST_NOT_EXIST'], 404);
}

if (!$t_data = DB()->fetch_row($sql)) {
    meta_refresh('index.php', 10);
    bb_die($lang['TOPIC_POST_NOT_EXIST'], 404);
}

$forum_topic_data =& $t_data;
$topic_id = $t_data['topic_id'];
$forum_id = $t_data['forum_id'];
$topic_attachment = isset($t_data['topic_attachment']) ? (int)$t_data['topic_attachment'] : null;

// Allow robots indexing
$page_cfg['allow_robots'] = (bool)$t_data['topic_allow_robots'];

if ($t_data['allow_porno_topic'] && bf($userdata['user_opt'], 'user_opt', 'user_porn_forums')) {
    bb_die($lang['ERROR_PORNO_FORUM']);
}

if ($userdata['session_admin'] && !empty($_REQUEST['mod'])) {
    if (IS_ADMIN) {
        $datastore->enqueue([
            'viewtopic_forum_select'
        ]);
    }
}
if ($topic_attachment) {
    $datastore->enqueue([
        'attach_extensions'
    ]);
}

set_die_append_msg($forum_id);

// Find newest post
if (($next_topic_id || @$_GET['view'] === 'newest') && !IS_GUEST && $topic_id) {
    $post_time = 'post_time >= ' . get_last_read($topic_id, $forum_id);
    $post_id_altern = ($next_topic_id) ? '' : ' OR post_id = ' . $t_data['topic_last_post_id'];

    $sql = "SELECT post_id, post_time
		FROM " . BB_POSTS . "
		WHERE topic_id = $topic_id
			AND ($post_time $post_id_altern)
		ORDER BY post_time ASC
		LIMIT 1";

    if ($row = DB()->fetch_row($sql)) {
        $post_id = $newest = $row['post_id'];
        $t_data['post_time'] = $row['post_time'];
    }
}

if ($post_id && !empty($t_data['post_time']) && ($t_data['topic_replies'] + 1) > $posts_per_page) {
    $sql = "SELECT COUNT(post_id) AS prev_posts
		FROM " . BB_POSTS . "
		WHERE topic_id = $topic_id
			AND post_time <= {$t_data['post_time']}";

    if ($row = DB()->fetch_row($sql)) {
        $t_data['prev_posts'] = $row['prev_posts'];
    }
}

// Auth check
$is_auth = auth(AUTH_ALL, $forum_id, $userdata, $t_data);

if (!$is_auth['auth_read']) {
    if (IS_GUEST) {
        $redirect = ($post_id) ? POST_URL . "$post_id#$post_id" : TOPIC_URL . $topic_id;
        $redirect .= ($start && !$post_id) ? "&start=$start" : '';
        redirect(LOGIN_URL . "?redirect=$redirect");
    }
    bb_die($lang['TOPIC_POST_NOT_EXIST'], 404);
}

$forum_name = $t_data['forum_name'];
$parent_id = (is_numeric($t_data['forum_parent']) && $t_data['forum_parent'] > 0) ? $t_data['forum_parent'] : false;
$topic_title = $t_data['topic_title'];
$topic_id = $t_data['topic_id'];
$topic_time = $t_data['topic_time'];
$locked = ($t_data['forum_status'] == FORUM_LOCKED || $t_data['topic_status'] == TOPIC_LOCKED);

$moderation = (!empty($_REQUEST['mod']) && $is_auth['auth_mod']);

// Redirect to login page if not admin session
$mod_redirect_url = '';

if ($is_auth['auth_mod']) {
    $redirect = $_POST['redirect'] ?? @$_SERVER['REQUEST_URI'];
    $redirect = url_arg($redirect, 'mod', 1, '&');
    $mod_redirect_url = LOGIN_URL . "?redirect=$redirect&admin=1";

    if ($moderation && !$userdata['session_admin']) {
        redirect($mod_redirect_url);
    }
}

if ($moderation) {
    if (IS_ADMIN) {
        if (!$forum_select = $datastore->get('viewtopic_forum_select')) {
            $datastore->update('viewtopic_forum_select');
            $forum_select = $datastore->get('viewtopic_forum_select');
        }
        $forum_select_html = $forum_select['viewtopic_forum_select'];
    } else {
        $not_auth_forums_csv = $user->get_not_auth_forums(AUTH_VIEW);
        $forum_select_html = get_forum_select(explode(',', $not_auth_forums_csv), 'new_forum_id');
    }
    $template->assign_vars(['S_FORUM_SELECT' => $forum_select_html]);
}

if (!$forums = $datastore->get('cat_forums')) {
    $datastore->update('cat_forums');
    $forums = $datastore->get('cat_forums');
}

$template->assign_vars([
    'CAT_TITLE' => $forums['cat_title_html'][$t_data['cat_id']],
    'U_VIEWCAT' => CAT_URL . $t_data['cat_id'],
    'PARENT_FORUM_HREF' => $parent_id ? FORUM_URL . $parent_id : '',
    'PARENT_FORUM_NAME' => $parent_id ? htmlCHR($forums['f'][$parent_id]['forum_name']) : '',
]);
unset($forums);
$datastore->rm('cat_forums');

// Make jumpbox
make_jumpbox();

if ($post_id && !empty($t_data['prev_posts'])) {
    $start = floor(($t_data['prev_posts'] - 1) / $posts_per_page) * $posts_per_page;
}

// Is user watching this thread?
$can_watch_topic = $is_watching_topic = false;

if (config()->get('topic_notify_enabled')) {
    if (!IS_GUEST) {
        $can_watch_topic = true;

        if ($t_data['notify_status'] == TOPIC_WATCH_NOTIFIED) {
            $is_watching_topic = true;
            if (isset($_GET['unwatch'])) {
                if ($_GET['unwatch'] == 'topic') {
                    DB()->query("DELETE FROM " . BB_TOPICS_WATCH . " WHERE topic_id = $topic_id AND user_id = {$userdata['user_id']}");
                }

                set_die_append_msg($forum_id, $topic_id);
                bb_die($lang['NO_LONGER_WATCHING']);
            }
        } elseif ($t_data['notify_status'] == TOPIC_WATCH_UNNOTIFIED) {
            if (isset($_GET['watch'])) {
                if ($_GET['watch'] == 'topic') {
                    DB()->query("
						INSERT INTO " . BB_TOPICS_WATCH . " (user_id, topic_id, notify_status)
						VALUES (" . $userdata['user_id'] . ", $topic_id, " . TOPIC_WATCH_NOTIFIED . ")
					");
                }

                set_die_append_msg($forum_id, $topic_id);
                bb_die($lang['YOU_ARE_WATCHING']);
            }
        }
    } else {
        if (isset($_GET['unwatch'])) {
            if ($_GET['unwatch'] == 'topic') {
                redirect(LOGIN_URL . "?redirect=" . TOPIC_URL . "$topic_id&unwatch=topic");
            }
        }
    }
}

// Generate a 'Show posts in previous x days' select box. If the postdays var is POSTed
// then get it's value, find the number of topics with dates newer than it (to properly
// handle pagination) and alter the main query
$post_days = 0;
$limit_posts_time = '';
$total_replies = $t_data['topic_replies'] + 1;

if (!empty($_REQUEST['postdays'])) {
    if ($post_days = (int)$_REQUEST['postdays']) {
        if (!empty($_POST['postdays'])) {
            $start = 0;
        }
        $min_post_time = TIMENOW - ($post_days * 86400);

        $sql = "SELECT COUNT(p.post_id) AS num_posts
			FROM " . BB_TOPICS . " t, " . BB_POSTS . " p
			WHERE t.topic_id = $topic_id
				AND p.topic_id = t.topic_id
				AND p.post_time > $min_post_time";

        $total_replies = ($row = DB()->fetch_row($sql)) ? $row['num_posts'] : 0;
        $limit_posts_time = "AND p.post_time >= $min_post_time ";
    }
}

// Decide how to order the post display
$post_order = (isset($_POST['postorder']) && $_POST['postorder'] !== 'asc') ? 'desc' : 'asc';

//
// Go ahead and pull all data for this topic
//
// 1. Add first post of topic if it pinned and page of topic not first
$first_post = false;
if ($t_data['topic_show_first_post'] && $start) {
    $first_post = DB()->fetch_rowset("
		SELECT
			u.username, u.user_id, u.user_rank, u.user_posts, u.user_from,
			u.user_regdate, u.user_sig,
			u.avatar_ext_id,
			u.user_opt, u.user_gender, u.user_birthday,
			p.*, g.group_name, g.group_description, g.group_id, g.group_signature, g.avatar_ext_id as rg_avatar_id,
			u2.username as mc_username, u2.user_rank as mc_user_rank,
			h.post_html, IF(h.post_html IS NULL, pt.post_text, NULL) AS post_text
		FROM      " . BB_POSTS . " p
		LEFT JOIN " . BB_USERS . " u  ON(u.user_id = p.poster_id)
		LEFT JOIN " . BB_POSTS_TEXT . " pt ON(pt.post_id = p.post_id)
		LEFT JOIN " . BB_POSTS_HTML . " h  ON(h.post_id = p.post_id)
		LEFT JOIN " . BB_USERS . " u2 ON(u2.user_id = p.mc_user_id)
		LEFT JOIN " . BB_GROUPS . " g ON(g.group_id = p.poster_rg_id)
		WHERE
			p.post_id = {$t_data['topic_first_post_id']}
		LIMIT 1
	");
}
// 2. All others posts
$sql = "
	SELECT
		u.username, u.user_id, u.user_rank, u.user_posts, u.user_from,
		u.user_regdate, u.user_sig,
		u.avatar_ext_id,
		u.user_opt, u.user_gender, u.user_birthday,
		p.*, g.group_name, g.group_description, g.group_id, g.group_signature, g.avatar_ext_id as rg_avatar_id,
		u2.username as mc_username, u2.user_rank as mc_user_rank,
		h.post_html, IF(h.post_html IS NULL, pt.post_text, NULL) AS post_text
	FROM      " . BB_POSTS . " p
	LEFT JOIN " . BB_USERS . " u  ON(u.user_id = p.poster_id)
	LEFT JOIN " . BB_POSTS_TEXT . " pt ON(pt.post_id = p.post_id)
	LEFT JOIN " . BB_POSTS_HTML . " h  ON(h.post_id = p.post_id)
	LEFT JOIN " . BB_USERS . " u2 ON(u2.user_id = p.mc_user_id)
	LEFT JOIN " . BB_GROUPS . " g ON(g.group_id = p.poster_rg_id)
	WHERE p.topic_id = $topic_id
		$limit_posts_time
	GROUP BY p.post_id
	ORDER BY p.post_time $post_order
	LIMIT $start, $posts_per_page
";

if ($postrow = DB()->fetch_rowset($sql)) {
    if ($first_post) {
        $postrow = array_merge($first_post, $postrow);
    }
    $total_posts = count($postrow);
} else {
    bb_die($lang['NO_POSTS_TOPIC']);
}

if (!$ranks = $datastore->get('ranks')) {
    $datastore->update('ranks');
    $ranks = $datastore->get('ranks');
}

// Censor topic title
$topic_title = censor()->censorString($topic_title);

// Post, reply and other URL generation for templating vars
$new_topic_url = POSTING_URL . "?mode=newtopic&amp;" . POST_FORUM_URL . "=$forum_id";
$reply_topic_url = POSTING_URL . "?mode=reply&amp;" . POST_TOPIC_URL . "=$topic_id";
$view_forum_url = FORUM_URL . $forum_id;
$view_prev_topic_url = TOPIC_URL . $topic_id . "&amp;view=previous#newest";
$view_next_topic_url = TOPIC_URL . $topic_id . "&amp;view=next#newest";

$reply_img = $locked ? $images['reply_locked'] : $images['reply_new'];
$reply_alt = $locked ? $lang['TOPIC_LOCKED_SHORT'] : $lang['REPLY_TO_TOPIC'];

// Set 'body' template for attach_mod
$template->set_filenames(['body' => 'viewtopic.tpl']);

//
// User authorisation levels output
//
$s_auth_can = (($is_auth['auth_post']) ? $lang['RULES_POST_CAN'] : $lang['RULES_POST_CANNOT']) . '<br />';
$s_auth_can .= (($is_auth['auth_reply']) ? $lang['RULES_REPLY_CAN'] : $lang['RULES_REPLY_CANNOT']) . '<br />';
$s_auth_can .= (($is_auth['auth_edit']) ? $lang['RULES_EDIT_CAN'] : $lang['RULES_EDIT_CANNOT']) . '<br />';
$s_auth_can .= (($is_auth['auth_delete']) ? $lang['RULES_DELETE_CAN'] : $lang['RULES_DELETE_CANNOT']) . '<br />';
$s_auth_can .= (($is_auth['auth_vote']) ? $lang['RULES_VOTE_CAN'] : $lang['RULES_VOTE_CANNOT']) . '<br />';
$s_auth_can .= (($is_auth['auth_attachments']) ? $lang['RULES_ATTACH_CAN'] : $lang['RULES_ATTACH_CANNOT']) . '<br />';
$s_auth_can .= (($is_auth['auth_download']) ? $lang['RULES_DOWNLOAD_CAN'] : $lang['RULES_DOWNLOAD_CANNOT']) . '<br />';

// Moderator output
$topic_mod = '';
if ($is_auth['auth_mod']) {
    $s_auth_can .= $lang['RULES_MODERATE'];
    $topic_mod .= "<a href=\"modcp.php?" . POST_TOPIC_URL . "=$topic_id&amp;mode=delete&amp;sid=" . $userdata['session_id'] . '"><img src="' . $images['topic_mod_delete'] . '" alt="' . $lang['DELETE_TOPIC'] . '" title="' . $lang['DELETE_TOPIC'] . '" border="0" /></a>&nbsp;';
    $topic_mod .= "<a href=\"modcp.php?" . POST_TOPIC_URL . "=$topic_id&amp;mode=move&amp;sid=" . $userdata['session_id'] . '"><img src="' . $images['topic_mod_move'] . '" alt="' . $lang['MOVE_TOPIC'] . '" title="' . $lang['MOVE_TOPIC'] . '" border="0" /></a>&nbsp;';
    $topic_mod .= ($t_data['topic_status'] == TOPIC_UNLOCKED) ? "<a href=\"modcp.php?" . POST_TOPIC_URL . "=$topic_id&amp;mode=lock&amp;sid=" . $userdata['session_id'] . '"><img src="' . $images['topic_mod_lock'] . '" alt="' . $lang['LOCK_TOPIC'] . '" title="' . $lang['LOCK_TOPIC'] . '" border="0" /></a>&nbsp;' : "<a href=\"modcp.php?" . POST_TOPIC_URL . "=$topic_id&amp;mode=unlock&amp;sid=" . $userdata['session_id'] . '"><img src="' . $images['topic_mod_unlock'] . '" alt="' . $lang['UNLOCK_TOPIC'] . '" title="' . $lang['UNLOCK_TOPIC'] . '" border="0" /></a>&nbsp;';
    $topic_mod .= "<a href=\"modcp.php?" . POST_TOPIC_URL . "=$topic_id&amp;mode=split&amp;sid=" . $userdata['session_id'] . '"><img src="' . $images['topic_mod_split'] . '" alt="' . $lang['SPLIT_TOPIC'] . '" title="' . $lang['SPLIT_TOPIC'] . '" border="0" /></a>&nbsp;';

    if ($t_data['allow_reg_tracker'] || $t_data['topic_dl_type'] == TOPIC_DL_TYPE_DL || IS_ADMIN) {
        if ($t_data['topic_dl_type'] == TOPIC_DL_TYPE_DL) {
            $topic_mod .= "<a href=\"modcp.php?" . POST_TOPIC_URL . "=$topic_id&amp;mode=unset_download&amp;sid=" . $userdata['session_id'] . '"><img src="' . $images['topic_normal'] . '" alt="' . $lang['UNSET_DL_STATUS'] . '" title="' . $lang['UNSET_DL_STATUS'] . '" border="0" /></a>';
        } else {
            $topic_mod .= "<a href=\"modcp.php?" . POST_TOPIC_URL . "=$topic_id&amp;mode=set_download&amp;sid=" . $userdata['session_id'] . '"><img src="' . $images['topic_dl'] . '" alt="' . $lang['SET_DL_STATUS'] . '" title="' . $lang['SET_DL_STATUS'] . '" border="0" /></a>';
        }
    }
} elseif (!IS_GUEST && ($t_data['topic_poster'] == $userdata['user_id']) && $t_data['self_moderated']) {
    $topic_mod .= "<a href=\"modcp.php?" . POST_TOPIC_URL . "=$topic_id&amp;mode=move&amp;sid=" . $userdata['session_id'] . '"><img src="' . $images['topic_mod_move'] . '" alt="' . $lang['MOVE_TOPIC'] . '" title="' . $lang['MOVE_TOPIC'] . '" border="0" /></a>&nbsp;';
}

// Topic watch information
$s_watching_topic = '';
if ($can_watch_topic) {
    if ($is_watching_topic) {
        $s_watching_topic = "<a href=\"" . TOPIC_URL . $topic_id . "&amp;unwatch=topic&amp;start=$start&amp;sid=" . $userdata['session_id'] . '">' . $lang['STOP_WATCHING_TOPIC'] . '</a>';
    } else {
        $s_watching_topic = "<a href=\"" . TOPIC_URL . $topic_id . "&amp;watch=topic&amp;start=$start&amp;sid=" . $userdata['session_id'] . '">' . $lang['START_WATCHING_TOPIC'] . '</a>';
    }
}

// If we've got a highlight set pass it on to pagination,
$pg_url = TOPIC_URL . $topic_id;
$pg_url .= $post_days ? "&amp;postdays=$post_days" : '';
$pg_url .= ($post_order != 'asc') ? "&amp;postorder=$post_order" : '';
$pg_url .= isset($_REQUEST['single']) ? "&amp;single=1" : '';
$pg_url .= $moderation ? "&amp;mod=1" : '';
$pg_url .= ($posts_per_page != config()->get('posts_per_page')) ? "&amp;ppp=$posts_per_page" : '';

generate_pagination($pg_url, $total_replies, $posts_per_page, $start);

// Selects
$sel_previous_days = [
    0 => $lang['ALL_POSTS'],
    1 => $lang['1_DAY'],
    7 => $lang['7_DAYS'],
    14 => $lang['2_WEEKS'],
    30 => $lang['1_MONTH'],
    90 => $lang['3_MONTHS'],
    180 => $lang['6_MONTHS'],
    364 => $lang['1_YEAR']
];

$sel_post_order_ary = [
    $lang['OLDEST_FIRST'] => 'asc',
    $lang['NEWEST_FIRST'] => 'desc'
];

$topic_has_poll = $t_data['topic_vote'];
$poll_time_expired = ($t_data['topic_time'] < TIMENOW - config()->get('poll_max_days') * 86400);
$can_manage_poll = ($t_data['topic_poster'] == $userdata['user_id'] || $is_auth['auth_mod']);
$can_add_poll = ($can_manage_poll && !$topic_has_poll && !$poll_time_expired && !$start);

$page_title = ((int)($start / $posts_per_page) === 0) ? $topic_title :
    $topic_title . ' - ' . $lang['SHORT_PAGE'] . ' ' . (floor($start / $posts_per_page) + 1);

//
// Send vars to template
//
$template->assign_vars([
    'PAGE_URL' => $pg_url,
    'PAGE_URL_PPP' => url_arg($pg_url, 'ppp', null),
    'PAGE_START' => $start,

    'FORUM_ID' => $forum_id,
    'FORUM_NAME' => htmlCHR($forum_name),
    'TOPIC_ID' => $topic_id,
    'PAGE_TITLE' => $page_title,
    'TOPIC_TITLE' => $topic_title,
    'PORNO_FORUM' => $t_data['allow_porno_topic'],
    'REPLY_IMG' => $reply_img,
    'SHOW_BOT_NICK' => config()->get('show_bot_nick'),
    'T_POST_REPLY' => $reply_alt,

    'HIDE_FROM' => $user->opt_js['h_from'],
    'HIDE_AVATAR' => $user->opt_js['h_av'],
    'HIDE_RANK_IMG' => ($user->opt_js['h_rnk_i'] && config()->get('show_rank_image')),
    'HIDE_POST_IMG' => $user->opt_js['h_post_i'],
    'HIDE_SMILE' => $user->opt_js['h_smile'],
    'HIDE_SIGNATURE' => $user->opt_js['h_sig'],
    'SPOILER_OPENED' => $user->opt_js['sp_op'],
    'SHOW_IMG_AFTER_LOAD' => $user->opt_js['i_aft_l'],

    'HIDE_RANK_IMG_DIS' => !config()->get('show_rank_image'),

    'PINNED_FIRST_POST' => $t_data['topic_show_first_post'],
    'PIN_HREF' => $t_data['topic_show_first_post'] ? "modcp.php?" . POST_TOPIC_URL . "=$topic_id&amp;mode=post_unpin" : "modcp.php?" . POST_TOPIC_URL . "=$topic_id&amp;mode=post_pin",
    'PIN_TITLE' => $t_data['topic_show_first_post'] ? $lang['POST_UNPIN'] : $lang['POST_PIN'],

    'AUTH_MOD' => $is_auth['auth_mod'],
    'IN_MODERATION' => $moderation,
    'SELECT_PPP' => ($moderation && $select_ppp && $total_replies > $posts_per_page) ? build_select('ppp', $select_ppp, $posts_per_page, null, null, 'onchange="$(\'#ppp\').submit();"') : '',

    'S_SELECT_POST_DAYS' => build_select('postdays', array_flip($sel_previous_days), $post_days),
    'S_SELECT_POST_ORDER' => build_select('postorder', $sel_post_order_ary, $post_order),
    'S_POST_DAYS_ACTION' => TOPIC_URL . $topic_id . "&amp;start=$start",
    'S_AUTH_LIST' => $s_auth_can,
    'S_TOPIC_ADMIN' => $topic_mod,
    'S_WATCH_TOPIC' => $s_watching_topic,
    'U_VIEW_TOPIC' => TOPIC_URL . $topic_id,
    'U_VIEW_FORUM' => $view_forum_url,
    'U_VIEW_OLDER_TOPIC' => $view_prev_topic_url,
    'U_VIEW_NEWER_TOPIC' => $view_next_topic_url,
    'U_POST_NEW_TOPIC' => $new_topic_url,
    'U_POST_REPLY_TOPIC' => $reply_topic_url,
    'U_SEARCH_SELF' => "search.php?uid={$userdata['user_id']}&" . POST_TOPIC_URL . "=$topic_id&dm=1",

    'TOPIC_HAS_POLL' => $topic_has_poll,
    'POLL_IS_EDITABLE' => !$poll_time_expired,
    'POLL_IS_FINISHED' => ($topic_has_poll == POLL_FINISHED),
    'CAN_MANAGE_POLL' => $can_manage_poll,
    'CAN_ADD_POLL' => $can_add_poll
]);

// Does this topic contain DL-List?
$template->assign_vars([
    'SHOW_TOR_ACT' => false,
    'PEERS_FULL_LINK' => false,
    'DL_LIST_HREF' => TOPIC_URL . "$topic_id&amp;dl=names&amp;spmode=full",
]);
require INC_DIR . '/torrent_show_dl_list.php';

if ($topic_attachment) {
    require ATTACH_DIR . '/attachment_mod.php';
    init_display_post_attachments($t_data['topic_attachment']);
}

//
// Update the topic view counter
//
$sql = "INSERT INTO " . BUF_TOPIC_VIEW . " (topic_id,  topic_views) VALUES ($topic_id, 1) ON DUPLICATE KEY UPDATE topic_views = topic_views + 1";
if (!DB()->sql_query($sql)) {
    bb_die('Could not update topic views');
}

//
// Does this topic contain a poll?
//
if ($topic_has_poll) {
    $poll_votes_js = \TorrentPier\Legacy\Poll::get_poll_data_items_js($topic_id);

    if (!$poll_votes_js) {
        $template->assign_vars(['TOPIC_HAS_POLL' => false]);
    } else {
        $template->assign_vars([
            'SHOW_VOTE_BTN' => \TorrentPier\Legacy\Poll::pollIsActive($t_data),
            'POLL_ALREADY_VOTED' => \TorrentPier\Legacy\Poll::userIsAlreadyVoted($topic_id, (int)$userdata['user_id']),
            'POLL_VOTES_JS' => $poll_votes_js
        ]);
    }
}

$prev_post_time = $max_post_time = 0;

for ($i = 0; $i < $total_posts; $i++) {
    $poster_id = $postrow[$i]['user_id'];
    $poster_guest = ($poster_id == GUEST_UID);
    $poster_bot = ($poster_id == BOT_UID);
    $poster = $poster_guest ? $lang['GUEST'] : $postrow[$i]['username'];

    $post_date = bb_date($postrow[$i]['post_time'], config()->get('post_date_format'));
    $max_post_time = max($max_post_time, $postrow[$i]['post_time']);
    $poster_posts = !$poster_guest ? $postrow[$i]['user_posts'] : '';
    $poster_from = ($postrow[$i]['user_from'] && !$poster_guest) ? $postrow[$i]['user_from'] : '';
    $poster_joined = !$poster_guest ? $lang['JOINED'] . ': ' . bb_date($postrow[$i]['user_regdate'], 'Y-m-d H:i') : '';
    $poster_longevity = !$poster_guest ? delta_time($postrow[$i]['user_regdate']) : '';
    $post_id = $postrow[$i]['post_id'];
    $mc_type = (int)$postrow[$i]['mc_type'];
    $mc_comment = $postrow[$i]['mc_comment'];
    $mc_user_id = profile_url(['username' => $postrow[$i]['mc_username'], 'user_id' => $postrow[$i]['mc_user_id'], 'user_rank' => $postrow[$i]['mc_user_rank']]);

    $rg_id = $postrow[$i]['poster_rg_id'] ?: 0;
    $rg_avatar = get_avatar(GROUP_AVATAR_MASK . $rg_id, $postrow[$i]['rg_avatar_id']);
    $rg_name = $postrow[$i]['group_name'] ? htmlCHR($postrow[$i]['group_name']) : '';
    $rg_desc = $postrow[$i]['group_description'] ? bbcode2html(htmlCHR($postrow[$i]['group_description'])) : '';
    $rg_signature = $postrow[$i]['group_signature'] ? bbcode2html(htmlCHR($postrow[$i]['group_signature'])) : '';

    $poster_avatar = '';
    if ((!$user->opt_js['h_av'] || $poster_bot) && !$poster_guest) {
        $poster_avatar = get_avatar($poster_id, $postrow[$i]['avatar_ext_id'], !bf($postrow[$i]['user_opt'], 'user_opt', 'dis_avatar'));
    }

    $poster_rank = $rank_image = '';
    $user_rank = $postrow[$i]['user_rank'];
    if (!$user->opt_js['h_rnk_i'] and isset($ranks[$user_rank])) {
        $rank_image = (config()->get('show_rank_image') && $ranks[$user_rank]['rank_image']) ? '<img src="' . $ranks[$user_rank]['rank_image'] . '" alt="" title="" border="0" />' : '';
        $poster_rank = config()->get('show_rank_text') ? $ranks[$user_rank]['rank_title'] : '';
    }

    // Handle anon users posting with usernames
    if ($poster_guest && !empty($postrow[$i]['post_username'])) {
        $poster = $postrow[$i]['post_username'];
    }

    // Buttons
    $pm_btn = $profile_btn = $delpost_btn = $edit_btn = $ip_btn = $quote_btn = '';

    if (!$poster_guest) {
        $profile_btn = true;
        $pm_btn = true;
    }

    if (!$poster_bot) {
        $quote_btn = ($is_auth['auth_reply'] && !$locked);
        $edit_btn = (($userdata['user_id'] == $poster_id && $is_auth['auth_edit']) || $is_auth['auth_mod']);
        $ip_btn = ($is_auth['auth_mod'] || IS_MOD);
    }
    $delpost_btn = ($post_id != $t_data['topic_first_post_id'] && ($is_auth['auth_mod'] || ($userdata['user_id'] == $poster_id && $is_auth['auth_delete'] && $t_data['topic_last_post_id'] == $post_id && $postrow[$i]['post_time'] + 3600 * 3 > TIMENOW)));

    // Parse message and sig
    $message = get_parsed_post($postrow[$i]);

    $user_sig = (config()->get('allow_sig') && !$user->opt_js['h_sig'] && $postrow[$i]['user_sig']) ? $postrow[$i]['user_sig'] : '';

    if (bf($postrow[$i]['user_opt'], 'user_opt', 'dis_sig')) {
        $user_sig = $lang['SIGNATURE_DISABLE'];
    } elseif ($user_sig) {
        $user_sig = bbcode2html($user_sig);
    }

    // Replace naughty words
    if ($user_sig) {
        $user_sig = str_replace(
            '\"', '"',
            substr(
                preg_replace_callback('#(\>(((?>([^><]+|(?R)))*)\<))#s', function ($matches) {
                    return censor()->censorString(reset($matches));
                }, '>' . $user_sig . '<'), 1, -1
            )
        );
    }

    $message = str_replace(
        '\"', '"',
        substr(
            preg_replace_callback('#(\>(((?>([^><]+|(?R)))*)\<))#s', function ($matches) {
                return censor()->censorString(reset($matches));
            }, '>' . $message . '<'), 1, -1
        )
    );

    // Replace newlines (we use this rather than nl2br because till recently it wasn't XHTML compliant)
    if ($user_sig) {
        $user_sig = config()->get('user_signature_start') . $user_sig . config()->get('user_signature_end');
    }

    // Editing information
    if ($postrow[$i]['post_edit_count']) {
        $l_edit_time_total = ($postrow[$i]['post_edit_count'] == 1) ? $lang['EDITED_TIME_TOTAL'] : $lang['EDITED_TIMES_TOTAL'];
        $l_edited_by = '<br /><br />' . sprintf($l_edit_time_total, profile_url(['username' => $poster, 'user_id' => $poster_id, 'user_rank' => $user_rank]), bb_date($postrow[$i]['post_edit_time']), $postrow[$i]['post_edit_count']);
    } else {
        $l_edited_by = '';
    }

    // Again this will be handled by the templating code at some point
    $pg_row_class = !($i % 2) ? 'row2' : 'row1';

    // Mod comment
    $mc_class = match ($mc_type) {
        1 => 'success',
        2 => 'info',
        3 => 'warning',
        4 => 'danger',
        default => '',
    };
    $mc_select_type = [];
    foreach ($lang['MC_COMMENT'] as $key => $value) {
        $mc_select_type[$key] = $value['type'];
    }

    $is_first_post = ($post_id == $t_data['topic_first_post_id']);

    // Set meta description
    if ($is_first_post || $i == 0) {
        $message_meta = preg_replace('#<br\s*/?>\s*#si', ' ', $message);
        $message_meta = str_replace('&#10;', '', $message_meta);
        $page_cfg['meta_description'] = str_short(strip_tags($message_meta), 220);
    }

    $template->assign_block_vars('postrow', [
        'ROW_CLASS' => !($i % 2) ? 'row1' : 'row2',
        'POST_ID' => $post_id,
        'IS_NEWEST' => ($post_id == $newest),
        'POSTER_NAME' => profile_url(['username' => $poster, 'user_id' => $poster_id, 'user_rank' => $user_rank], no_link: true),
        'POSTER_NAME_JS' => addslashes($poster),
        'POSTER_RANK' => $poster_rank,
        'RANK_IMAGE' => $rank_image,
        'POSTER_JOINED' => config()->get('show_poster_joined') ? $poster_longevity : '',

        'POSTER_JOINED_DATE' => $poster_joined,
        'POSTER_POSTS' => (config()->get('show_poster_posts') && $poster_posts) ? '<a href="search.php?search_author=1&amp;uid=' . $poster_id . '" target="_blank">' . $poster_posts . '</a>' : '',
        'POSTER_FROM' => config()->get('show_poster_from') ? render_flag($poster_from, false) : '',
        'POSTER_BOT' => $poster_bot,
        'POSTER_GUEST' => $poster_guest,
        'POSTER_ID' => $poster_id,
        'POSTER_AUTHOR' => ($poster_id == $t_data['topic_poster']),
        'POSTER_GENDER' => !$poster_guest ? genderImage((int)$postrow[$i]['user_gender']) : '',
        'POSTED_AFTER' => $prev_post_time ? delta_time($postrow[$i]['post_time'], $prev_post_time) : '',
        'IS_UNREAD' => is_unread($postrow[$i]['post_time'], $topic_id, $forum_id),
        'IS_FIRST_POST' => (!$start && $is_first_post),
        'MOD_CHECKBOX' => ($moderation && ($start || defined('SPLIT_FORM_START'))),
        'POSTER_AVATAR' => $poster_avatar,
        'POST_NUMBER' => ($i + $start + 1),
        'POST_DATE' => $post_date,
        'MESSAGE' => $message,
        'SIGNATURE' => $user_sig,
        'EDITED_MESSAGE' => $l_edited_by,

        'PM' => $pm_btn,
        'PROFILE' => $profile_btn,

        'QUOTE' => $quote_btn,
        'EDIT' => $edit_btn,
        'DELETE' => $delpost_btn,
        'IP' => $ip_btn,

        'POSTER_BIRTHDAY' => user_birthday_icon($postrow[$i]['user_birthday'], $postrow[$i]['user_id']),

        'MC_COMMENT' => $mc_type ? bbcode2html($mc_comment) : '',
        'MC_BBCODE' => $mc_type ? $mc_comment : '',
        'MC_CLASS' => $mc_class,
        'MC_TITLE' => sprintf($lang['MC_COMMENT'][$mc_type]['title'], $mc_user_id),
        'MC_SELECT_TYPE' => build_select("mc_type_$post_id", array_flip($mc_select_type), $mc_type),

        'RG_AVATAR' => $rg_avatar,
        'RG_NAME' => $rg_name,
        'RG_DESC' => $rg_desc,
        'RG_URL' => GROUP_URL . $rg_id,
        'RG_FIND_URL' => 'tracker.php?srg=' . $rg_id,
        'RG_SIG' => $rg_signature,
        'RG_SIG_ATTACH' => $postrow[$i]['attach_rg_sig']
    ]);

    // Ban information
    if ($banInfo = getBanInfo((int)$poster_id)) {
        $template->assign_block_vars('postrow.ban', [
            'IS_BANNED' => true,
            'BAN_REASON' => $banInfo['ban_reason']
        ]);
    }

    if (isset($postrow[$i]['post_attachment']) && $is_auth['auth_download'] && function_exists('display_post_attachments')) {
        display_post_attachments($post_id, $postrow[$i]['post_attachment']);
    }

    if ($moderation && !defined('SPLIT_FORM_START') && ($start || $post_id == $t_data['topic_first_post_id'])) {
        define('SPLIT_FORM_START', true);
    }

    if (!$poster_bot) {
        $prev_post_time = $postrow[$i]['post_time'];
    }
}

set_tracks(COOKIE_TOPIC, $tracking_topics, $topic_id, $max_post_time);

if (defined('SPLIT_FORM_START')) {
    $template->assign_vars([
        'SPLIT_FORM' => true,
        'START' => $start,
        'S_SPLIT_ACTION' => 'modcp.php'
    ]);
}

// Quick Reply
if (config()->get('show_quick_reply')) {
    if ($is_auth['auth_reply'] && !$locked) {
        $template->assign_vars([
            'QUICK_REPLY' => true,
            'QR_POST_ACTION' => POSTING_URL,
            'QR_TOPIC_ID' => $topic_id,
            'CAPTCHA_HTML' => (IS_GUEST && !config()->get('captcha.disabled')) ? bb_captcha('get') : ''
        ]);

        if (!IS_GUEST) {
            $notify_user = bf($userdata['user_opt'], 'user_opt', 'user_notify');

            $template->assign_vars(['QR_NOTIFY_CHECKED' => ($notify_user) ? ($notify_user && $is_watching_topic) : $is_watching_topic]);
        }
    }
}

foreach ($is_auth as $name => $is) {
    $template->assign_vars([strtoupper($name) => $is]);
}

$template->assign_vars(['PG_ROW_CLASS' => $pg_row_class ?? 'row1']);

if (IS_ADMIN) {
    $template->assign_vars(['U_LOGS' => "admin/admin_log.php?" . POST_TOPIC_URL . "=$topic_id&amp;db=" . config()->get('log_days_keep')]);
}

print_page('viewtopic.tpl');
