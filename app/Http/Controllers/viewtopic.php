<?php

/**
 * TorrentPier – Bull-powered BitTorrent tracker engine
 *
 * @copyright Copyright (c) 2005-2025 TorrentPier (https://torrentpier.com)
 * @link      https://github.com/torrentpier/torrentpier for the canonical source repository
 * @license   https://github.com/torrentpier/torrentpier/blob/master/LICENSE MIT License
 */

/*
 * ===========================================================================
 * Refactor to Modern Controller
 * ===========================================================================
 * Target: Convert to PSR-7 controller with constructor dependency injection
 *
 * Dependencies to inject:
 * - TorrentPier\Config (configuration access)
 * - TorrentPier\Database\Database (database operations)
 * - TorrentPier\Legacy\User (user session and permissions)
 * - TorrentPier\Http\Request (HTTP request handling)
 * - TorrentPier\Legacy\Templates (template rendering)
 * - TorrentPier\Legacy\BBCode (BBCode parsing)
 * - TorrentPier\Cache\UnifiedCacheSystem (caching)
 *
 * Target namespace: TorrentPier\Http\Controllers
 * Target class: ViewTopicController
 *
 * Key refactoring tasks:
 * 1. Extract procedural code into controller methods (show, vote, etc.)
 * 2. Replace global function calls with injected dependencies
 * 3. Implement PSR-7 request/response handling
 * 4. Extract business logic into TopicService
 * 5. Add proper pagination via PaginationService
 * 6. Add proper error handling with exceptions
 * ===========================================================================
 */

datastore()->enqueue([
    'ranks',
    'cat_forums',
]);

page_cfg('load_tpl_vars', [
    'post_buttons',
    'post_icons',
    'topic_icons',
]);

$newest = $next_topic_id = 0;
$start = request()->query->get('start') ? abs((int)request()->query->get('start')) : 0;
$topic_id = request()->query->get(POST_TOPIC_URL) ? (int)request()->query->get(POST_TOPIC_URL) : 0;
$post_id = (!$topic_id && request()->query->get(POST_POST_URL)) ? (int)request()->query->get(POST_POST_URL) : 0;

set_die_append_msg();

// Posts per page
$posts_per_page = config()->get('posts_per_page');
$select_ppp = '';

if (userdata('session_admin')) {
    $req_ppp = abs((int)(request()->get('ppp') ?? 0));
    if ($req_ppp && in_array($req_ppp, config()->get('forum.allowed_posts_per_page'))) {
        $posts_per_page = $req_ppp;
    }

    $select_ppp = [];
    foreach (config()->get('forum.allowed_posts_per_page') as $ppp) {
        $select_ppp[$ppp] = $ppp;
    }
}

if (request()->has('single')) {
    $posts_per_page = 1;
} else {
    $start = floor($start / $posts_per_page) * $posts_per_page;
}

if (!$topic_id && !$post_id) {
    bb_die(__('TOPIC_POST_NOT_EXIST'), 404);
}

// Find topic id if user requested a newer or older topic
if ($topic_id && request()->query->has('view') && (request()->query->get('view') == 'next' || request()->query->get('view') == 'previous')) {
    $sql_condition = (request()->query->get('view') == 'next') ? '>' : '<';
    $sql_ordering = (request()->query->get('view') == 'next') ? 'ASC' : 'DESC';

    $sql = 'SELECT t.topic_id
		FROM ' . BB_TOPICS . ' t, ' . BB_TOPICS . " t2
		WHERE t2.topic_id = {$topic_id}
			AND t.forum_id = t2.forum_id
			AND t.topic_moved_id = 0
			AND t.topic_last_post_id {$sql_condition} t2.topic_last_post_id
		ORDER BY t.topic_last_post_id {$sql_ordering}
		LIMIT 1";

    if ($row = DB()->fetch_row($sql)) {
        $next_topic_id = $topic_id = $row['topic_id'];
    } else {
        $message = (request()->query->get('view') == 'next') ? __('NO_NEWER_TOPICS') : __('NO_OLDER_TOPICS');
        bb_die($message);
    }
}

// Get forum/topic data
if ($topic_id) {
    $sql = 'SELECT t.*, f.cat_id, f.forum_name, f.forum_desc, f.forum_status, f.forum_order, f.forum_posts, f.forum_topics, f.forum_last_post_id, f.forum_tpl_id, f.prune_days, f.auth_view, f.auth_read, f.auth_post, f.auth_reply, f.auth_edit, f.auth_delete, f.auth_sticky, f.auth_announce, f.auth_vote, f.auth_pollcreate, f.auth_attachments, f.auth_download, f.allow_reg_tracker, f.allow_porno_topic, f.self_moderated, f.forum_parent, f.show_on_index, f.forum_display_sort, f.forum_display_order, tw.notify_status
		FROM ' . BB_TOPICS . ' t
		LEFT JOIN ' . BB_FORUMS . ' f ON t.forum_id = f.forum_id
		LEFT JOIN ' . BB_TOPICS_WATCH . ' tw ON(tw.topic_id = t.topic_id AND tw.user_id = ' . userdata('user_id') . ")
		WHERE t.topic_id = {$topic_id}
	";
} elseif ($post_id) {
    $sql = 'SELECT t.*, f.cat_id, f.forum_name, f.forum_desc, f.forum_status, f.forum_order, f.forum_posts, f.forum_topics, f.forum_last_post_id, f.forum_tpl_id, f.prune_days, f.auth_view, f.auth_read, f.auth_post, f.auth_reply, f.auth_edit, f.auth_delete, f.auth_sticky, f.auth_announce, f.auth_vote, f.auth_pollcreate, f.auth_attachments, f.auth_download, f.allow_reg_tracker, f.allow_porno_topic, f.self_moderated, f.forum_parent, f.show_on_index, f.forum_display_sort, f.forum_display_order, p.post_time, tw.notify_status
		FROM ' . BB_TOPICS . ' t
		LEFT JOIN ' . BB_FORUMS . ' f ON t.forum_id = f.forum_id
		LEFT JOIN ' . BB_POSTS . ' p ON t.topic_id = p.topic_id
		LEFT JOIN ' . BB_TOPICS_WATCH . ' tw ON(tw.topic_id = t.topic_id AND tw.user_id = ' . userdata('user_id') . ")
		WHERE p.post_id = {$post_id}
	";
} else {
    bb_die(__('TOPIC_POST_NOT_EXIST'), 404);
}

if (!$t_data = DB()->fetch_row($sql)) {
    meta_refresh('index.php', 10);
    bb_die(__('TOPIC_POST_NOT_EXIST'), 404);
}

$forum_topic_data = &$t_data;
$topic_id = $t_data['topic_id'];
$forum_id = $t_data['forum_id'];

if ($t_data['allow_porno_topic'] && bf(userdata('user_opt'), 'user_opt', 'user_porn_forums')) {
    bb_die(__('ERROR_PORNO_FORUM'));
}

if (userdata('session_admin') && request()->has('mod')) {
    if (IS_ADMIN) {
        datastore()->enqueue([
            'viewtopic_forum_select',
        ]);
    }
}

set_die_append_msg($forum_id);

// Find newest post
if (($next_topic_id || request()->query->get('view') === 'newest') && !IS_GUEST && $topic_id) {
    $post_time = 'post_time >= ' . get_last_read($topic_id, $forum_id);
    $post_id_altern = ($next_topic_id) ? '' : ' OR post_id = ' . $t_data['topic_last_post_id'];

    $sql = 'SELECT post_id, post_time
		FROM ' . BB_POSTS . "
		WHERE topic_id = {$topic_id}
			AND ({$post_time} {$post_id_altern})
		ORDER BY post_time ASC
		LIMIT 1";

    if ($row = DB()->fetch_row($sql)) {
        $post_id = $newest = $row['post_id'];
        $t_data['post_time'] = $row['post_time'];
    }
}

if ($post_id && !empty($t_data['post_time']) && ($t_data['topic_replies'] + 1) > $posts_per_page) {
    $sql = 'SELECT COUNT(post_id) AS prev_posts
		FROM ' . BB_POSTS . "
		WHERE topic_id = {$topic_id}
			AND post_time <= {$t_data['post_time']}";

    if ($row = DB()->fetch_row($sql)) {
        $t_data['prev_posts'] = $row['prev_posts'];
    }
}

// Auth check
$is_auth = auth(AUTH_ALL, $forum_id, userdata(), $t_data);

if (!$is_auth['auth_read']) {
    if (IS_GUEST) {
        $redirect = ($post_id) ? POST_URL . "{$post_id}#{$post_id}" : TOPIC_URL . $topic_id;
        $redirect .= ($start && !$post_id) ? "&start={$start}" : '';
        redirect(LOGIN_URL . "?redirect={$redirect}");
    }
    bb_die(__('TOPIC_POST_NOT_EXIST'), 404);
}

$forum_name = $t_data['forum_name'];
$parent_id = (is_numeric($t_data['forum_parent']) && $t_data['forum_parent'] > 0) ? $t_data['forum_parent'] : false;
$topic_title = $t_data['topic_title'];
$topic_id = $t_data['topic_id'];
$topic_time = $t_data['topic_time'];
$locked = ($t_data['forum_status'] == FORUM_LOCKED || $t_data['topic_status'] == TOPIC_LOCKED);

// Assert canonical URL for SEO-friendly routing
if (request()->attributes->get('semantic_route') && request()->attributes->get('semantic_route_type') === 'threads') {
    TorrentPier\Router\SemanticUrl\UrlBuilder::assertCanonical('threads', $topic_id, $topic_title);
}

$moderation = (request()->has('mod') && $is_auth['auth_mod']);

// Redirect to login page if not admin session
$mod_redirect_url = '';

if ($is_auth['auth_mod']) {
    $redirect = request()->post->get('redirect') ?? request()->server->get('REQUEST_URI');
    $redirect = url_arg($redirect, 'mod', 1, '&');
    $mod_redirect_url = LOGIN_URL . "?redirect={$redirect}&admin=1";

    if ($moderation && !userdata('session_admin')) {
        redirect($mod_redirect_url);
    }
}

if ($moderation) {
    if (IS_ADMIN) {
        if (!$forum_select = datastore()->get('viewtopic_forum_select')) {
            datastore()->update('viewtopic_forum_select');
            $forum_select = datastore()->get('viewtopic_forum_select');
        }
        $forum_select_html = $forum_select['viewtopic_forum_select'];
    } else {
        $not_auth_forums_csv = user()->get_not_auth_forums(AUTH_VIEW);
        $forum_select_html = get_forum_select(explode(',', $not_auth_forums_csv), 'new_forum_id');
    }
    template()->assign_vars(['S_FORUM_SELECT' => $forum_select_html]);
}

$forums = forum_tree();

template()->assign_vars([
    'CAT_TITLE' => $forums['cat_title_html'][$t_data['cat_id']],
    'U_VIEWCAT' => url()->category($t_data['cat_id'], $forums['c'][$t_data['cat_id']]['cat_title']),
    'PARENT_FORUM_HREF' => $parent_id ? url()->forum($parent_id, $forums['forum'][$parent_id]['forum_name'] ?? '') : '',
    'PARENT_FORUM_NAME' => $parent_id ? htmlCHR($forums['f'][$parent_id]['forum_name']) : '',
]);

// Make jumpbox
make_jumpbox();

// Allow robots indexing
if ($is_auth['auth_read']) {
    page_cfg('allow_robots', (bool)$t_data['topic_allow_robots']);
} else {
    page_cfg('allow_robots', false);
}

if ($post_id && !empty($t_data['prev_posts'])) {
    $start = floor(($t_data['prev_posts'] - 1) / $posts_per_page) * $posts_per_page;
}

// Redirect legacy ?p= requests to semantic URL with anchor
if ($post_id && !request()->attributes->get('semantic_route') && request()->isGet()) {
    $params = $start > 0 ? ['start' => $start] : [];
    $params['_fragment'] = $post_id;

    $redirectUrl = url()->topic($topic_id, $topic_title, $params);
    TorrentPier\Http\Response::permanentRedirect(make_url($redirectUrl))->send();
    exit;
}

// Is user watching this thread?
$can_watch_topic = $is_watching_topic = false;

if (config()->get('mail.notifications.topic_notify')) {
    if (!IS_GUEST) {
        $can_watch_topic = true;

        if ($t_data['notify_status'] == TOPIC_WATCH_NOTIFIED) {
            $is_watching_topic = true;
            if (request()->query->has('unwatch')) {
                if (request()->query->get('unwatch') == 'topic') {
                    DB()->query('DELETE FROM ' . BB_TOPICS_WATCH . " WHERE topic_id = {$topic_id} AND user_id = " . userdata('user_id'));
                }

                set_die_append_msg($forum_id, $topic_id);
                bb_die(__('NO_LONGER_WATCHING'));
            }
        } elseif ($t_data['notify_status'] == TOPIC_WATCH_UNNOTIFIED) {
            if (request()->query->has('watch')) {
                if (request()->query->get('watch') == 'topic') {
                    DB()->query('
						INSERT INTO ' . BB_TOPICS_WATCH . ' (user_id, topic_id, notify_status)
						VALUES (' . userdata('user_id') . ", {$topic_id}, " . TOPIC_WATCH_NOTIFIED . ')
					');
                }

                set_die_append_msg($forum_id, $topic_id);
                bb_die(__('YOU_ARE_WATCHING'));
            }
        }
    } else {
        if (request()->query->has('unwatch')) {
            if (request()->query->get('unwatch') == 'topic') {
                $unwatchUrl = url()->topic($topic_id, $topic_title, ['unwatch' => 'topic']);
                redirect(LOGIN_URL . '?redirect=' . urlencode($unwatchUrl));
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

if (request()->has('postdays')) {
    if ($post_days = (int)request()->get('postdays')) {
        if (request()->post->has('postdays')) {
            $start = 0;
        }
        $min_post_time = TIMENOW - ($post_days * 86400);

        $sql = 'SELECT COUNT(p.post_id) AS num_posts
			FROM ' . BB_TOPICS . ' t, ' . BB_POSTS . " p
			WHERE t.topic_id = {$topic_id}
				AND p.topic_id = t.topic_id
				AND p.post_time > {$min_post_time}";

        $total_replies = ($row = DB()->fetch_row($sql)) ? $row['num_posts'] : 0;
        $limit_posts_time = "AND p.post_time >= {$min_post_time} ";
    }
}

// Decide how to order the post display
$post_order = (request()->post->has('postorder') && request()->post->get('postorder') !== 'asc') ? 'desc' : 'asc';

//
// Go ahead and pull all data for this topic
//
// 1. Add first post of topic if it pinned and page of topic not first
$first_post = false;
if ($t_data['topic_show_first_post'] && $start) {
    $first_post = DB()->fetch_rowset('
		SELECT
			u.username, u.user_id, u.user_rank, u.user_posts, u.user_from,
			u.user_regdate, u.user_sig,
			u.avatar_ext_id,
			u.user_opt, u.user_gender, u.user_birthday,
			p.*, g.group_name, g.group_description, g.group_id, g.group_signature, g.avatar_ext_id as rg_avatar_id,
			u2.username as mc_username, u2.user_rank as mc_user_rank,
			h.post_html, IF(h.post_html IS NULL, pt.post_text, NULL) AS post_text
		FROM      ' . BB_POSTS . ' p
		LEFT JOIN ' . BB_USERS . ' u  ON(u.user_id = p.poster_id)
		LEFT JOIN ' . BB_POSTS_TEXT . ' pt ON(pt.post_id = p.post_id)
		LEFT JOIN ' . BB_POSTS_HTML . ' h  ON(h.post_id = p.post_id)
		LEFT JOIN ' . BB_USERS . ' u2 ON(u2.user_id = p.mc_user_id)
		LEFT JOIN ' . BB_GROUPS . " g ON(g.group_id = p.poster_rg_id)
		WHERE
			p.post_id = {$t_data['topic_first_post_id']}
		LIMIT 1
	");
}
// 2. All others posts
$sql = '
	SELECT
		u.username, u.user_id, u.user_rank, u.user_posts, u.user_from,
		u.user_regdate, u.user_sig,
		u.avatar_ext_id,
		u.user_opt, u.user_gender, u.user_birthday,
		p.*, g.group_name, g.group_description, g.group_id, g.group_signature, g.avatar_ext_id as rg_avatar_id,
		u2.username as mc_username, u2.user_rank as mc_user_rank,
		h.post_html, IF(h.post_html IS NULL, pt.post_text, NULL) AS post_text
	FROM      ' . BB_POSTS . ' p
	LEFT JOIN ' . BB_USERS . ' u  ON(u.user_id = p.poster_id)
	LEFT JOIN ' . BB_POSTS_TEXT . ' pt ON(pt.post_id = p.post_id)
	LEFT JOIN ' . BB_POSTS_HTML . ' h  ON(h.post_id = p.post_id)
	LEFT JOIN ' . BB_USERS . ' u2 ON(u2.user_id = p.mc_user_id)
	LEFT JOIN ' . BB_GROUPS . " g ON(g.group_id = p.poster_rg_id)
	WHERE p.topic_id = {$topic_id}
		{$limit_posts_time}
	GROUP BY p.post_id
	ORDER BY p.post_time {$post_order}
	LIMIT {$start}, {$posts_per_page}
";

if ($postrow = DB()->fetch_rowset($sql)) {
    if ($first_post) {
        $postrow = array_merge($first_post, $postrow);
    }
    $total_posts = count($postrow);
} else {
    bb_die(__('NO_POSTS_TOPIC'));
}

if (!$ranks = datastore()->get('ranks')) {
    datastore()->update('ranks');
    $ranks = datastore()->get('ranks');
}

// Censor topic title
$topic_title = censor()->censorString($topic_title);

// Post, reply and other URL generation for templating vars
$new_topic_url = POSTING_URL . '?mode=newtopic&amp;' . POST_FORUM_URL . "={$forum_id}";
$reply_topic_url = POSTING_URL . '?mode=reply&amp;' . POST_TOPIC_URL . "={$topic_id}";
$view_forum_url = url()->forum($forum_id, $t_data['forum_name']);
$view_prev_topic_url = url()->topic($topic_id, $topic_title, ['view' => 'previous', '_fragment' => 'newest']);
$view_next_topic_url = url()->topic($topic_id, $topic_title, ['view' => 'next', '_fragment' => 'newest']);

$reply_alt = $locked ? __('TOPIC_LOCKED_SHORT') : __('REPLY_TO_TOPIC');

// Set 'body' template for attach_mod
template()->set_filenames(['body' => 'viewtopic.twig']);

//
// User authorisation levels output
//
$s_auth_can = (($is_auth['auth_post']) ? __('RULES_POST_CAN') : __('RULES_POST_CANNOT')) . '<br />';
$s_auth_can .= (($is_auth['auth_reply']) ? __('RULES_REPLY_CAN') : __('RULES_REPLY_CANNOT')) . '<br />';
$s_auth_can .= (($is_auth['auth_edit']) ? __('RULES_EDIT_CAN') : __('RULES_EDIT_CANNOT')) . '<br />';
$s_auth_can .= (($is_auth['auth_delete']) ? __('RULES_DELETE_CAN') : __('RULES_DELETE_CANNOT')) . '<br />';
$s_auth_can .= (($is_auth['auth_vote']) ? __('RULES_VOTE_CAN') : __('RULES_VOTE_CANNOT')) . '<br />';
$s_auth_can .= (($is_auth['auth_attachments']) ? __('RULES_ATTACH_CAN') : __('RULES_ATTACH_CANNOT')) . '<br />';
$s_auth_can .= (($is_auth['auth_download']) ? __('RULES_DOWNLOAD_CAN') : __('RULES_DOWNLOAD_CANNOT')) . '<br />';

// Moderator output
$topic_mod = '';
if ($is_auth['auth_mod']) {
    $s_auth_can .= __('RULES_MODERATE');
    $topic_mod .= '<a href="modcp?' . POST_TOPIC_URL . "={$topic_id}&amp;mode=delete&amp;sid=" . userdata('session_id') . '"><img src="' . theme_images('topic_mod_delete') . '" alt="' . __('DELETE_TOPIC') . '" title="' . __('DELETE_TOPIC') . '" border="0" /></a>&nbsp;';
    $topic_mod .= '<a href="modcp?' . POST_TOPIC_URL . "={$topic_id}&amp;mode=move&amp;sid=" . userdata('session_id') . '"><img src="' . theme_images('topic_mod_move') . '" alt="' . __('MOVE_TOPIC') . '" title="' . __('MOVE_TOPIC') . '" border="0" /></a>&nbsp;';
    $topic_mod .= ($t_data['topic_status'] == TOPIC_UNLOCKED) ? '<a href="modcp?' . POST_TOPIC_URL . "={$topic_id}&amp;mode=lock&amp;sid=" . userdata('session_id') . '"><img src="' . theme_images('topic_mod_lock') . '" alt="' . __('LOCK_TOPIC') . '" title="' . __('LOCK_TOPIC') . '" border="0" /></a>&nbsp;' : '<a href="modcp?' . POST_TOPIC_URL . "={$topic_id}&amp;mode=unlock&amp;sid=" . userdata('session_id') . '"><img src="' . theme_images('topic_mod_unlock') . '" alt="' . __('UNLOCK_TOPIC') . '" title="' . __('UNLOCK_TOPIC') . '" border="0" /></a>&nbsp;';
    $topic_mod .= '<a href="modcp?' . POST_TOPIC_URL . "={$topic_id}&amp;mode=split&amp;sid=" . userdata('session_id') . '"><img src="' . theme_images('topic_mod_split') . '" alt="' . __('SPLIT_TOPIC') . '" title="' . __('SPLIT_TOPIC') . '" border="0" /></a>&nbsp;';

    if ($t_data['allow_reg_tracker'] || $t_data['topic_dl_type'] == TOPIC_DL_TYPE_DL || IS_ADMIN) {
        if ($t_data['topic_dl_type'] == TOPIC_DL_TYPE_DL) {
            $topic_mod .= '<a href="modcp?' . POST_TOPIC_URL . "={$topic_id}&amp;mode=unset_download&amp;sid=" . userdata('session_id') . '"><img src="' . theme_images('topic_normal') . '" alt="' . __('UNSET_DL_STATUS') . '" title="' . __('UNSET_DL_STATUS') . '" border="0" /></a>';
        } else {
            $topic_mod .= '<a href="modcp?' . POST_TOPIC_URL . "={$topic_id}&amp;mode=set_download&amp;sid=" . userdata('session_id') . '"><img src="' . theme_images('topic_dl') . '" alt="' . __('SET_DL_STATUS') . '" title="' . __('SET_DL_STATUS') . '" border="0" /></a>';
        }
    }
} elseif (!IS_GUEST && ($t_data['topic_poster'] == userdata('user_id')) && $t_data['self_moderated']) {
    $topic_mod .= '<a href="modcp?' . POST_TOPIC_URL . "={$topic_id}&amp;mode=move&amp;sid=" . userdata('session_id') . '"><img src="' . theme_images('topic_mod_move') . '" alt="' . __('MOVE_TOPIC') . '" title="' . __('MOVE_TOPIC') . '" border="0" /></a>&nbsp;';
}

// Topic watch information
$s_watching_topic = '';
if ($can_watch_topic) {
    $watchParams = ['start' => $start, 'sid' => userdata('session_id')];
    if ($is_watching_topic) {
        $watchParams['unwatch'] = 'topic';
        $s_watching_topic = '<a href="' . url()->topic($topic_id, $topic_title, $watchParams) . '">' . __('STOP_WATCHING_TOPIC') . '</a>';
    } else {
        $watchParams['watch'] = 'topic';
        $s_watching_topic = '<a href="' . url()->topic($topic_id, $topic_title, $watchParams) . '">' . __('START_WATCHING_TOPIC') . '</a>';
    }
}

// Build pagination URL with semantic URL base
$topicBaseUrl = url()->topic($topic_id, $topic_title);
$pg_params = [];
if ($post_days) {
    $pg_params['postdays'] = $post_days;
}
if ($post_order != 'asc') {
    $pg_params['postorder'] = $post_order;
}
if (request()->has('single')) {
    $pg_params['single'] = 1;
}
if ($moderation) {
    $pg_params['mod'] = 1;
}
if ($posts_per_page != config()->get('posts_per_page')) {
    $pg_params['ppp'] = $posts_per_page;
}
$pg_url = $topicBaseUrl . (!empty($pg_params) ? '?' . http_build_query($pg_params, '', '&amp;') : '');
$pg_url_sep = !empty($pg_params) ? '&amp;' : '?';

generate_pagination($pg_url, $total_replies, $posts_per_page, $start);

// Selects
$sel_previous_days = [
    0 => __('ALL_POSTS'),
    1 => __('1_DAY'),
    7 => __('7_DAYS'),
    14 => __('2_WEEKS'),
    30 => __('1_MONTH'),
    90 => __('3_MONTHS'),
    180 => __('6_MONTHS'),
    364 => __('1_YEAR'),
];

$sel_post_order_ary = [
    __('OLDEST_FIRST') => 'asc',
    __('NEWEST_FIRST') => 'desc',
];

$topic_has_poll = $t_data['topic_vote'];
$poll_time_expired = ($t_data['topic_time'] < TIMENOW - config()->get('forum.poll_max_days') * 86400);
$can_manage_poll = ($t_data['topic_poster'] == userdata('user_id') || $is_auth['auth_mod']);
$can_add_poll = ($can_manage_poll && $is_auth['auth_pollcreate'] && !$topic_has_poll && !$poll_time_expired && !$start);

$page_title = ((int)($start / $posts_per_page) === 0) ? $topic_title :
    $topic_title . ' - ' . __('SHORT_PAGE') . ' ' . (floor($start / $posts_per_page) + 1);

//
// Send vars to template
//
template()->assign_vars([
    'PAGE_URL' => $pg_url,
    'PAGE_URL_SEP' => $pg_url_sep,
    'PAGE_URL_PPP' => url_arg($pg_url, 'ppp', null),
    'PAGE_START' => $start,

    'FORUM_ID' => $forum_id,
    'FORUM_NAME' => htmlCHR($forum_name),
    'TOPIC_ID' => $topic_id,
    'PAGE_TITLE' => $page_title,
    'TOPIC_TITLE' => $topic_title,
    'CANONICAL_URL' => make_url(url()->topic($topic_id, $topic_title)),
    'PORNO_FORUM' => $t_data['allow_porno_topic'],
    'SHOW_BOT_NICK' => config()->get('forum.show_bot_nick'),
    'T_POST_REPLY' => $reply_alt,

    'HIDE_FROM' => user()->opt_js['h_from'],
    'HIDE_AVATAR' => user()->opt_js['h_av'],
    'HIDE_RANK_IMG' => (user()->opt_js['h_rnk_i'] && config()->get('forum.show_rank_image')),
    'HIDE_POST_IMG' => user()->opt_js['h_post_i'],
    'HIDE_SMILE' => user()->opt_js['h_smile'],
    'HIDE_SIGNATURE' => user()->opt_js['h_sig'],
    'SPOILER_OPENED' => user()->opt_js['sp_op'],
    'SHOW_IMG_AFTER_LOAD' => user()->opt_js['i_aft_l'],

    'HIDE_RANK_IMG_DIS' => !config()->get('forum.show_rank_image'),

    'PINNED_FIRST_POST' => $t_data['topic_show_first_post'],
    'PIN_HREF' => $t_data['topic_show_first_post'] ? 'modcp?' . POST_TOPIC_URL . "={$topic_id}&amp;mode=post_unpin" : 'modcp?' . POST_TOPIC_URL . "={$topic_id}&amp;mode=post_pin",
    'PIN_TITLE' => $t_data['topic_show_first_post'] ? __('POST_UNPIN') : __('POST_PIN'),

    'AUTH_MOD' => $is_auth['auth_mod'],
    'IN_MODERATION' => $moderation,
    'SELECT_PPP' => ($moderation && $select_ppp && $total_replies > $posts_per_page) ? build_select('ppp', $select_ppp, $posts_per_page, null, null, 'onchange="$(\'#ppp\').submit();"') : '',

    'S_SELECT_POST_DAYS' => build_select('postdays', array_flip($sel_previous_days), $post_days),
    'S_SELECT_POST_ORDER' => build_select('postorder', $sel_post_order_ary, $post_order),
    'S_POST_DAYS_ACTION' => url()->topic($topic_id, $topic_title, $start ? ['start' => $start] : []),
    'S_AUTH_LIST' => $s_auth_can,
    'S_TOPIC_ADMIN' => $topic_mod,
    'S_WATCH_TOPIC' => $s_watching_topic,
    'U_VIEW_TOPIC' => $topicBaseUrl,
    'U_VIEW_FORUM' => $view_forum_url,
    'U_VIEW_OLDER_TOPIC' => $view_prev_topic_url,
    'U_VIEW_NEWER_TOPIC' => $view_next_topic_url,
    'U_POST_NEW_TOPIC' => $new_topic_url,
    'U_POST_REPLY_TOPIC' => $reply_topic_url,
    'U_SEARCH_SELF' => FORUM_PATH . 'search?uid=' . userdata('user_id') . '&' . POST_TOPIC_URL . "={$topic_id}&dm=1",

    'TOPIC_HAS_POLL' => $topic_has_poll,
    'POLL_IS_EDITABLE' => !$poll_time_expired,
    'POLL_IS_FINISHED' => ($topic_has_poll == POLL_FINISHED),
    'CAN_MANAGE_POLL' => $can_manage_poll,
    'CAN_ADD_POLL' => $can_add_poll,
]);

// Does this topic contain DL-List?
template()->assign_vars([
    'SHOW_TOR_ACT' => false,
    'PEERS_FULL_LINK' => false,
    'DL_LIST_HREF' => url()->topic($topic_id, $topic_title, ['dl' => 'names', 'spmode' => 'full']),
]);
require INC_DIR . '/torrent_show_dl_list.php';

//
// Update the topic view counter
//
$sql = 'INSERT INTO ' . BUF_TOPIC_VIEW . " (topic_id,  topic_views) VALUES ({$topic_id}, 1) ON DUPLICATE KEY UPDATE topic_views = topic_views + 1";
if (!DB()->sql_query($sql)) {
    bb_die('Could not update topic views');
}

//
// Does this topic contain a poll?
//
if ($topic_has_poll) {
    $poll_votes_js = TorrentPier\Legacy\Poll::get_poll_data_items_js($topic_id);

    if (!$poll_votes_js) {
        template()->assign_vars(['TOPIC_HAS_POLL' => false]);
    } else {
        template()->assign_vars([
            'SHOW_VOTE_BTN' => TorrentPier\Legacy\Poll::pollIsActive($t_data) && $is_auth['auth_vote'],
            'POLL_ALREADY_VOTED' => TorrentPier\Legacy\Poll::userIsAlreadyVoted($topic_id, (int)userdata('user_id')),
            'POLL_VOTES_JS' => $poll_votes_js,
        ]);
    }
}

$prev_post_time = $max_post_time = 0;
$postrowList = [];

for ($i = 0; $i < $total_posts; $i++) {
    $poster_id = $postrow[$i]['user_id'];
    $poster_guest = ($poster_id == GUEST_UID);
    $poster_bot = ($poster_id == BOT_UID);
    $poster = $poster_guest ? __('GUEST') : $postrow[$i]['username'];

    $post_date = bb_date($postrow[$i]['post_time'], config()->get('localization.date_formats.post'));
    $max_post_time = max($max_post_time, $postrow[$i]['post_time']);
    $poster_posts = !$poster_guest ? $postrow[$i]['user_posts'] : '';
    $poster_from = ($postrow[$i]['user_from'] && !$poster_guest) ? $postrow[$i]['user_from'] : '';
    $poster_joined = !$poster_guest ? __('JOINED') . ': ' . bb_date($postrow[$i]['user_regdate'], 'Y-m-d H:i') : '';
    $poster_longevity = !$poster_guest ? humanTime($postrow[$i]['user_regdate']) : '';
    $poster_birthday = $postrow[$i]['user_birthday']->format('Y-m-d');
    $post_id = $postrow[$i]['post_id'];
    $mc_type = (int)$postrow[$i]['mc_type'];
    $mc_comment = $postrow[$i]['mc_comment'];
    $mc_user_id = profile_url(['username' => $postrow[$i]['mc_username'], 'user_id' => $postrow[$i]['mc_user_id'], 'user_rank' => $postrow[$i]['mc_user_rank']]);

    $rg_id = $postrow[$i]['poster_rg_id'] ?: 0;
    $rg_avatar = get_avatar(GROUP_AVATAR_MASK . $rg_id, $postrow[$i]['rg_avatar_id']);
    $rg_name = $postrow[$i]['group_name'] ? htmlCHR($postrow[$i]['group_name']) : '';
    $rg_desc = $postrow[$i]['group_description'] ? bbcode()->toHtml(htmlCHR($postrow[$i]['group_description'])) : '';
    $rg_signature = $postrow[$i]['group_signature'] ? bbcode()->toHtml(htmlCHR($postrow[$i]['group_signature'])) : '';

    $poster_avatar = '';
    if ((!user()->opt_js['h_av'] || $poster_bot) && !$poster_guest) {
        $poster_avatar = get_avatar($poster_id, $postrow[$i]['avatar_ext_id'], !bf($postrow[$i]['user_opt'], 'user_opt', 'dis_avatar'));
    }

    $poster_rank = $rank_image = '';
    $user_rank = $postrow[$i]['user_rank'];
    if (!user()->opt_js['h_rnk_i'] && isset($ranks[$user_rank])) {
        $rank_image = (config()->get('forum.show_rank_image') && $ranks[$user_rank]['rank_image']) ? '<img src="' . make_url($ranks[$user_rank]['rank_image']) . '" alt="" title="" border="0" />' : '';
        $poster_rank = config()->get('forum.show_rank_text') ? $ranks[$user_rank]['rank_title'] : '';
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
        $edit_btn = ((TorrentPier\Topic\Guard::isAuthor($poster_id) && $is_auth['auth_edit']) || $is_auth['auth_mod']);
        $ip_btn = ($is_auth['auth_mod'] || IS_MOD);
    }
    $delpost_btn = ($post_id != $t_data['topic_first_post_id'] && ($is_auth['auth_mod'] || (TorrentPier\Topic\Guard::isAuthor($poster_id) && $is_auth['auth_delete'] && $t_data['topic_last_post_id'] == $post_id && $postrow[$i]['post_time'] + 3600 * 3 > TIMENOW)));

    // Parse message and sig
    $message = bbcode()->getParsedPost($postrow[$i]);

    $user_sig = (config()->get('forum.user_signature.enabled') && !user()->opt_js['h_sig'] && $postrow[$i]['user_sig']) ? $postrow[$i]['user_sig'] : '';

    if (bf($postrow[$i]['user_opt'], 'user_opt', 'dis_sig')) {
        $user_sig = __('SIGNATURE_DISABLE');
    } elseif ($user_sig) {
        $user_sig = bbcode()->toHtml($user_sig);
    }

    // Replace naughty words
    if ($user_sig) {
        $user_sig = str_replace(
            '\"',
            '"',
            substr(
                preg_replace_callback('#(\>(((?>([^><]+|(?R)))*)\<))#s', function ($matches) {
                    return censor()->censorString(reset($matches));
                }, '>' . $user_sig . '<'),
                1,
                -1,
            ),
        );
    }

    $message = str_replace(
        '\"',
        '"',
        substr(
            preg_replace_callback('#(\>(((?>([^><]+|(?R)))*)\<))#s', function ($matches) {
                return censor()->censorString(reset($matches));
            }, '>' . $message . '<'),
            1,
            -1,
        ),
    );

    // Replace newlines (we use this rather than nl2br because till recently it wasn't XHTML compliant)
    if ($user_sig) {
        $user_sig = config()->get('forum.user_signature_start') . $user_sig . config()->get('forum.user_signature_end');
    }

    // Editing information
    if ($postrow[$i]['post_edit_count']) {
        $l_edit_time_total = ($postrow[$i]['post_edit_count'] == 1) ? __('EDITED_TIME_TOTAL') : __('EDITED_TIMES_TOTAL');
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
    foreach (__('MC_COMMENT') as $key => $value) {
        $mc_select_type[$key] = $value['type'];
    }

    $is_first_post = ($post_id == $t_data['topic_first_post_id']);

    // Set meta description
    if ($is_first_post || $i == 0) {
        $message_meta = preg_replace('#<br\s*/?>\s*#si', ' ', $message);
        $message_meta = str_replace('&#10;', '', $message_meta);
        page_cfg('meta_description', str_short(strip_tags($message_meta), 220));
    }

    $postrowItem = [
        'ROW_CLASS' => !($i % 2) ? 'row1' : 'row2',
        'POST_ID' => $post_id,
        'IS_NEWEST' => ($post_id == $newest),
        'POSTER_NAME' => profile_url(['username' => $poster, 'user_id' => $poster_id, 'user_rank' => $user_rank], no_link: true),
        'POSTER_NAME_JS' => addslashes($poster),
        'POSTER_RANK' => $poster_rank,
        'RANK_IMAGE' => $rank_image,
        'POSTER_JOINED' => config()->get('forum.show_poster_joined') ? $poster_longevity : '',

        'POSTER_JOINED_DATE' => $poster_joined,
        'POSTER_POSTS' => (config()->get('forum.show_poster_posts') && $poster_posts) ? '<a href="' . FORUM_PATH . 'search?search_author=1&amp;uid=' . $poster_id . '" target="_blank">' . $poster_posts . '</a>' : '',
        'POSTER_FROM' => config()->get('forum.show_poster_from') ? render_flag($poster_from, false) : '',
        'POSTER_BOT' => $poster_bot,
        'POSTER_GUEST' => $poster_guest,
        'POSTER_ID' => $poster_id,
        'POSTER_URL' => url()->member($poster_id, $poster),
        'POSTER_AUTHOR' => ($poster_id == $t_data['topic_poster']),
        'POSTER_GENDER' => !$poster_guest ? genderImage((int)$postrow[$i]['user_gender']) : '',
        'POSTED_AFTER' => $prev_post_time ? humanTime($postrow[$i]['post_time'], $prev_post_time) : '',
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

        'POSTER_BIRTHDAY' => user_birthday_icon($poster_birthday, $postrow[$i]['user_id']),

        'MC_COMMENT' => $mc_type ? bbcode()->toHtml($mc_comment) : '',
        'MC_BBCODE' => $mc_type ? $mc_comment : '',
        'MC_CLASS' => $mc_class,
        'MC_TITLE' => sprintf(__('MC_COMMENT')[$mc_type]['title'], $mc_user_id),
        'MC_SELECT_TYPE' => build_select("mc_type_{$post_id}", array_flip($mc_select_type), $mc_type),

        'RG_AVATAR' => $rg_avatar,
        'RG_NAME' => $rg_name,
        'RG_DESC' => $rg_desc,
        'RG_URL' => url()->group($rg_id, $postrow[$i]['group_name'] ?? ''),
        'RG_FIND_URL' => FORUM_PATH . 'tracker?srg=' . $rg_id,
        'RG_SIG' => $rg_signature,
        'RG_SIG_ATTACH' => $postrow[$i]['attach_rg_sig'],
        'IS_BANNED' => (bool)getBanInfo((int)$poster_id),
    ];

    $postrowList[] = $postrowItem;

    if ($is_first_post && $t_data['attach_ext_id'] == TORRENT_EXT_ID) {
        if (IS_GUEST && !$is_auth['auth_download']) {
            template()->assign_var('SHOW_GUEST_STUB', true);
        } elseif ($is_auth['auth_download']) {
            require_once INC_DIR . '/viewtopic_torrent.php';
            render_torrent_block($t_data, $poster_id, $is_auth, $post_id);
        }
    }

    if ($moderation && !defined('SPLIT_FORM_START') && ($start || $post_id == $t_data['topic_first_post_id'])) {
        define('SPLIT_FORM_START', true);
    }

    if (!$poster_bot) {
        $prev_post_time = $postrow[$i]['post_time'];
    }
}

$topics_tracking = &tracking_topics();
set_tracks(COOKIE_TOPIC, $topics_tracking, $topic_id, $max_post_time);

template()->assign_vars(['POSTROW' => $postrowList]);

if (defined('SPLIT_FORM_START')) {
    template()->assign_vars([
        'SPLIT_FORM' => true,
        'START' => $start,
        'S_SPLIT_ACTION' => FORUM_PATH . 'modcp',
        'POST_FORUM_URL' => POST_FORUM_URL,
        'POST_TOPIC_URL' => POST_TOPIC_URL,
    ]);
}

// Quick Reply
if (config()->get('forum.show_quick_reply')) {
    if ($is_auth['auth_reply'] && !$locked) {
        template()->assign_vars([
            'QUICK_REPLY' => true,
            'QR_POST_ACTION' => POSTING_URL,
            'QR_TOPIC_ID' => $topic_id,
            'CAPTCHA_HTML' => (IS_GUEST && !config()->get('forum.captcha.disabled')) ? bb_captcha('get') : '',
        ]);

        if (!IS_GUEST) {
            $notify_user = bf(userdata('user_opt'), 'user_opt', 'user_notify');

            template()->assign_vars(['QR_NOTIFY_CHECKED' => ($notify_user) ? ($notify_user && $is_watching_topic) : $is_watching_topic]);
        }
    }
}

foreach ($is_auth as $name => $is) {
    template()->assign_vars([strtoupper($name) => $is]);
}

template()->assign_vars(['PG_ROW_CLASS' => $pg_row_class ?? 'row1']);

if (IS_ADMIN) {
    template()->assign_vars(['U_LOGS' => FORUM_PATH . 'admin/admin_log.php?' . POST_TOPIC_URL . "={$topic_id}&amp;db=" . config()->get('logging.log_days_keep')]);
}

print_page('viewtopic.twig');
