<?php

/**
 * TorrentPier – Bull-powered BitTorrent tracker engine
 *
 * @copyright Copyright (c) 2005-2025 TorrentPier (https://torrentpier.com)
 * @link      https://github.com/torrentpier/torrentpier for the canonical source repository
 * @license   https://github.com/torrentpier/torrentpier/blob/master/LICENSE MIT License
 */

if (!config()->get('mail.notifications.topic_notify')) {
    bb_die(__('DISABLED'));
}

// Page config
page_cfg('use_tablesorter', true);
page_cfg('include_bbcode_js', true);

$user_id = userdata('user_id');
if (request()->query->has(POST_USERS_URL)) {
    if (get_username(request()->query->get(POST_USERS_URL))) {
        if (request()->query->get(POST_USERS_URL) == userdata('user_id') || IS_ADMIN) {
            $user_id = DB()->escape(request()->query->get(POST_USERS_URL));
        } else {
            bb_die(__('NOT_AUTHORISED'));
        }
    } else {
        bb_die(__('USER_NOT_EXIST'));
    }
}
$start = abs(request()->query->getInt('start'));
$per_page = config()->get('topics_per_page');

if (request()->post->has('topic_id_list')) {
    $topic_ids = implode(',', array_map('intval', request()->post->get('topic_id_list')));
    $sql = 'DELETE FROM ' . BB_TOPICS_WATCH . '  WHERE topic_id IN(' . $topic_ids . ") AND user_id = {$user_id}";
    if (!($result = DB()->sql_query($sql))) {
        bb_die('Could not delete topic watch information #1');
    }
}

$sql = 'SELECT COUNT(topic_id) as watch_count FROM ' . BB_TOPICS_WATCH . " WHERE user_id = {$user_id}";
if (!($result = DB()->sql_query($sql))) {
    bb_die('Could not obtain watch topic information #2');
}
$row = DB()->sql_fetchrow($result);
$watch_count = ($row['watch_count']) ?: 0;
DB()->sql_freeresult($result);

if ($watch_count > 0) {
    $sql = 'SELECT w.*, t.*, f.*, u.*, u2.username as last_username, u2.user_id as last_user_id,
		u2.user_level as last_user_level, u2.user_rank as last_user_rank,
		p_first.post_anonymous as first_post_anonymous,
		p.post_anonymous as last_post_anonymous
	FROM ' . BB_TOPICS_WATCH . ' w, ' . BB_TOPICS . ' t, ' . BB_USERS . ' u, ' . BB_FORUMS . ' f, ' . BB_POSTS . ' p, ' . BB_USERS . ' u2, ' . BB_POSTS . " p_first
	WHERE w.topic_id = t.topic_id
		AND t.forum_id = f.forum_id
		AND p.post_id = t.topic_last_post_id
		AND p.poster_id = u2.user_id
		AND p_first.post_id = t.topic_first_post_id
		AND t.topic_poster = u.user_id
		AND w.user_id = {$user_id}
	ORDER BY t.topic_last_post_time DESC
	LIMIT {$start}, {$per_page}";
    if (!($result = DB()->sql_query($sql))) {
        bb_die('Could not obtain watch topic information #3');
    }
    $watch = DB()->sql_fetchrowset($result);

    $watchList = [];
    $matches = '';
    $pagination = '';
    $pageNumber = '';

    if ($watch) {
        for ($i = 0, $iMax = count($watch); $i < $iMax; $i++) {
            $is_unread = is_unread($watch[$i]['topic_last_post_time'], $watch[$i]['topic_id'], $watch[$i]['forum_id']);

            $topicId = $watch[$i]['topic_id'];
            $topicTitle = $watch[$i]['topic_title'];
            $topicUrl = url()->topic($topicId, $topicTitle);

            // Anonymous posting: hide author/last poster identity from non-staff
            $authorAnonymous = !empty($watch[$i]['first_post_anonymous']) && $watch[$i]['user_id'] != GUEST_UID;
            if ($authorAnonymous && !IS_AM) {
                $authorDisplay = htmlCHR(__('ANONYMOUS'));
            } else {
                $authorDisplay = profile_url($watch[$i]);
            }

            $lastPosterAnonymous = !empty($watch[$i]['last_post_anonymous']) && $watch[$i]['last_user_id'] != GUEST_UID;
            if ($lastPosterAnonymous && !IS_AM) {
                $lastPosterDisplay = htmlCHR(__('ANONYMOUS'));
            } else {
                $lastPosterDisplay = profile_url(['user_id' => $watch[$i]['last_user_id'], 'username' => $watch[$i]['last_username'], 'user_rank' => $watch[$i]['last_user_rank']]);
            }

            $watchList[] = [
                'ROW_CLASS' => (!($i % 2)) ? 'row1' : 'row2',
                'POST_ID' => $watch[$i]['topic_first_post_id'],
                'TOPIC_ID' => $topicId,
                'TOPIC_TITLE' => str_short(censor()->censorString($topicTitle), 70),
                'FULL_TOPIC_TITLE' => $topicTitle,
                'U_TOPIC' => $topicUrl,
                'U_TOPIC_NEWEST' => url()->topicNewest($topicId, $topicTitle),
                'U_LAST_POST' => url()->topicPost($topicId, $topicTitle, $watch[$i]['topic_last_post_id']),
                'FORUM_TITLE' => $watch[$i]['forum_name'],
                'U_FORUM' => url()->forum($watch[$i]['forum_id'], $watch[$i]['forum_name']),
                'REPLIES' => $watch[$i]['topic_replies'],
                'AUTHOR' => $authorDisplay,
                'LAST_POST' => bb_date($watch[$i]['topic_last_post_time']) . '<br />' . $lastPosterDisplay,
                'LAST_POST_RAW' => $watch[$i]['topic_last_post_time'],
                'LAST_POST_ID' => $watch[$i]['topic_last_post_id'],
                'IS_UNREAD' => $is_unread,
                'POLL' => (bool)$watch[$i]['topic_vote'],
                'TOPIC_ICON' => get_topic_icon($watch[$i], $is_unread),
                'PAGINATION' => ($watch[$i]['topic_status'] == TOPIC_MOVED) ? '' : build_topic_pagination($topicUrl, $watch[$i]['topic_replies'], config()->get('posts_per_page')),
            ];
        }

        $matches = (count($watch) == 1) ? sprintf(__('FOUND_SEARCH_MATCH'), count($watch)) : sprintf(__('FOUND_SEARCH_MATCHES'), count($watch));
        $pagination = generate_pagination(WATCHLIST_URL, $watch_count, $per_page, $start);
        $pageNumber = sprintf(__('PAGE_OF'), (floor($start / $per_page) + 1), ceil($watch_count / $per_page));
    }
    DB()->sql_freeresult($result);

    print_page('usercp_topic_watch.twig', variables: [
        'PAGE_TITLE' => __('WATCHED_TOPICS'),
        'S_FORM_ACTION' => WATCHLIST_URL,
        'WATCH_LIST' => $watchList,
        'MATCHES' => $matches,
        'PAGINATION' => $pagination,
        'PAGE_NUMBER' => $pageNumber,
        'U_PER_PAGE' => WATCHLIST_URL,
        'PER_PAGE' => $per_page,
    ]);
} else {
    meta_refresh('index.php');
    bb_die(__('NO_WATCHED_TOPICS'));
}
