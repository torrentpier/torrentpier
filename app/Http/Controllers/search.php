<?php

/**
 * TorrentPier – Bull-powered BitTorrent tracker engine
 *
 * @copyright Copyright (c) 2005-2025 TorrentPier (https://torrentpier.com)
 * @link      https://github.com/torrentpier/torrentpier for the canonical source repository
 * @license   https://github.com/torrentpier/torrentpier/blob/master/LICENSE MIT License
 */

require INC_DIR . '/bbcode.php';

use TorrentPier\Search\SearchParams;

page_cfg('use_tablesorter', true);
page_cfg('load_tpl_vars', [
    'post_buttons',
    'post_icons',
    'topic_icons',
]);
page_cfg('allow_robots', false);

// Start session management
user()->session_start(['req_login' => config()->get('disable_search_for_guest')]);

set_die_append_msg();

if (request()->post->has('del_my_post')) {
    template()->assign_var('BB_DIE_APPEND_MSG', '
		<a href="#" onclick="window.close(); window.opener.focus();">' . __('GOTO_MY_MESSAGE') . '</a>
		<br /><br />
		<a href="' . FORUM_PATH . '">' . __('INDEX_RETURN') . '</a>
	');

    if (empty(request()->post->get('topic_id_list')) or !$topic_csv = get_id_csv(request()->post->get('topic_id_list'))) {
        bb_die(__('NONE_SELECTED'));
    }

    DB()->query('UPDATE ' . BB_POSTS . ' SET user_post = 0 WHERE poster_id = ' . user()->id . " AND topic_id IN({$topic_csv})");

    if (DB()->affected_rows()) {
        //bb_die('Выбранные темы ['. count(request()->post->get('topic_id_list')) .' шт.] удалены из списка "Мои сообщения"');
        bb_die(__('DEL_MY_MESSAGE'));
    } else {
        bb_die(__('NO_TOPICS_MY_MESSAGE'));
    }
} elseif (request()->post->has('add_my_post')) {
    template()->assign_var('BB_DIE_APPEND_MSG', '
		<a href="#" onclick="window.close(); window.opener.focus();">' . __('GOTO_MY_MESSAGE') . '</a>
		<br /><br />
		<a href="' . FORUM_PATH . '">' . __('INDEX_RETURN') . '</a>
	');

    if (IS_GUEST) {
        redirect(FORUM_PATH);
    }

    DB()->query('UPDATE ' . BB_POSTS . ' SET user_post = 1 WHERE poster_id = ' . user()->id);

    redirect('search?' . POST_USERS_URL . '=' . user()->id);
}

if ($mode = request()->get('mode')) {
    // This handles the simple windowed user search functions called from various other scripts
    if ($mode == 'searchuser') {
        $username = request()->post->get('search_username') ?? '';
        username_search($username);
        exit;
    }
}

$excluded_forums_csv = user()->get_excluded_forums(AUTH_READ);

$search_limit = 500;
$forum_select_size = 16;   // forum select box max rows
$max_forum_name_len = 60;   // inside forum select box
$text_match_max_len = 60;
$poster_name_max_len = 25;

$start = request()->has('start') ? abs(request()->getInt('start')) : 0;
$url = 'search';

$anon_id = GUEST_UID;
$user_id = userdata('user_id');
$lastvisit = IS_GUEST ? TIMENOW : userdata('user_lastvisit');
$search_id = (request()->query->has('id') && verify_id(request()->query->get('id'), SEARCH_ID_LENGTH)) ? request()->query->get('id') : '';
$session_id = userdata('session_id');

$items_found = $items_display = $previous_settings = null;
$text_match_sql = '';

$cat_tbl = BB_CATEGORIES . ' c';
$dl_stat_tbl = BB_BT_DLSTATUS . ' dl';
$forums_tbl = BB_FORUMS . ' f';
$posts_tbl = BB_POSTS . ' p';
$posts_text_tbl = BB_POSTS_TEXT . ' pt';
$posts_html_tbl = BB_POSTS_HTML . ' h';
$tr_snap_tbl = BB_BT_TRACKER_SNAP . ' sn';
$topics_tbl = BB_TOPICS . ' t';
$torrents_tbl = BB_BT_TORRENTS . ' tor';
$tracker_tbl = BB_BT_TRACKER . ' tr';
$users_tbl = BB_USERS . ' u';

// Cat/forum data
$forums = forum_tree();
$forum_name_html = $forums['forum_name_html'];

//
// Search options
//
// Key values
$search_all = 0;

$sort_asc = 1;
$sort_desc = 0;

$as_topics = 0;
$as_posts = 1;

$show_all = 0;
$show_briefly = 1;

$ord_posted = 1;
$ord_name = 2;
$ord_repl = 3;
$ord_views = 4;
$ord_last_p = 5;
$ord_created = 6;

// Order options
$order_opt = [
    $ord_posted => [
        'lang' => __('SORT_TIME'),
        'sql' => 'item_id',
    ],
    $ord_last_p => [
        'lang' => __('BT_LAST_POST'),
        'sql' => 't.topic_last_post_id',
    ],
    $ord_created => [
        'lang' => __('BT_CREATED'),
        'sql' => 't.topic_time',
    ],
    $ord_name => [
        'lang' => __('SORT_TOPIC_TITLE'),
        'sql' => 't.topic_title',
    ],
    $ord_repl => [
        'lang' => __('REPLIES'),
        'sql' => 't.topic_replies',
    ],
];
$order_select = [];
foreach ($order_opt as $val => $opt) {
    $order_select[$opt['lang']] = $val;
}

// Sort direction
$sort_opt = [
    $sort_asc => [
        'lang' => __('ASC'),
        'sql' => ' ASC ',
    ],
    $sort_desc => [
        'lang' => __('DESC'),
        'sql' => ' DESC ',
    ],
];
$sort_select = [];
foreach ($sort_opt as $val => $opt) {
    $sort_select[$opt['lang']] = $val;
}

// Previous days
$time_opt = [
    $search_all => [
        'lang' => __('BT_ALL_DAYS_FOR'),
        'sql' => 0,
    ],
    1 => [
        'lang' => __('BT_1_DAY_FOR'),
        'sql' => TIMENOW - 86400,
    ],
    3 => [
        'lang' => __('BT_3_DAY_FOR'),
        'sql' => TIMENOW - 86400 * 3,
    ],
    7 => [
        'lang' => __('BT_7_DAYS_FOR'),
        'sql' => TIMENOW - 86400 * 7,
    ],
    14 => [
        'lang' => __('BT_2_WEEKS_FOR'),
        'sql' => TIMENOW - 86400 * 14,
    ],
    30 => [
        'lang' => __('BT_1_MONTH_FOR'),
        'sql' => TIMENOW - 86400 * 30,
    ],
];
$time_select = [];
foreach ($time_opt as $val => $opt) {
    $time_select[$opt['lang']] = $val;
}

// Display as
$display_as_opt = [
    $as_topics => [
        'lang' => __('TOPICS'),
    ],
    $as_posts => [
        'lang' => __('MESSAGE'),
    ],
];
$display_as_select = [];
foreach ($display_as_opt as $val => $opt) {
    $display_as_select[$opt['lang']] = $val;
}

// Chars
$chars_opt = [
    $show_all => [
        'lang' => __('ALL_AVAILABLE'),
    ],
    $show_briefly => [
        'lang' => __('BRIEFLY'),
    ],
];
$chars_select = [];
foreach ($chars_opt as $val => $opt) {
    $chars_select[$opt['lang']] = $val;
}

$GPC = [
    //	  var_name              key_name  def_value    GPC type
    'all_words' => ['allw', 1, CHBOX],
    'cat' => ['c', null, REQUEST],
    'chars' => ['ch', $show_all, SELECT],
    'display_as' => ['dm', $as_topics, SELECT],
    'dl_cancel' => ['dla', 0, CHBOX],
    'dl_compl' => ['dlc', 0, CHBOX],
    'dl_down' => ['dld', 0, CHBOX],
    'dl_user_id' => ['dlu', $user_id, CHBOX],
    'dl_will' => ['dlw', 0, CHBOX],
    'forum' => ['f', $search_all, REQUEST],
    'my_topics' => ['myt', 0, CHBOX],
    'new' => ['new', 0, CHBOX],
    'new_topics' => ['nt', 0, CHBOX],
    'order' => ['o', $ord_posted, SELECT],
    'poster_id' => ['uid', null, REQUEST],
    'poster_name' => ['pn', null, REQUEST],
    'sort' => ['s', $sort_desc, SELECT],
    'text_match' => ['nm', null, REQUEST],
    'time' => ['tm', $search_all, SELECT],
    'title_only' => ['to', 0, CHBOX],
    'topic' => ['t', null, REQUEST],
];

// Initialize search parameters container
$params = new SearchParams($GPC);

// Output basic page
if (empty(request()->query->all()) && empty(request()->post->all())) {
    // Make forum select box
    $forum_select_mode = explode(',', $excluded_forums_csv);
    $forum_select = get_forum_select($forum_select_mode, $params->key('forum') . '[]', $search_all, $max_forum_name_len, $forum_select_size, 'style="width: 95%;"', $search_all);

    template()->assign_vars([
        'TPL_SEARCH_MAIN' => true,
        'PAGE_TITLE' => __('SEARCH'),

        'POSTER_ID_KEY' => $params->key('poster_id'),
        'TEXT_MATCH_KEY' => $params->key('text_match'),
        'POSTER_NAME_KEY' => $params->key('poster_name'),

        'THIS_USER_ID' => userdata('user_id'),
        'THIS_USER_NAME' => addslashes(userdata('username')),
        'SEARCH_ACTION' => FORUM_PATH . 'search',
        'U_SEARCH_USER' => FORUM_PATH . 'search?mode=searchuser&amp;input_name=' . $params->key('poster_name'),
        'ONLOAD_FOCUS_ID' => 'text_match_input',

        'MY_TOPICS_ID' => 'my_topics',
        'MY_TOPICS_CHBOX' => build_checkbox($params->key('my_topics'), __('SEARCH_MY_TOPICS'), $params->val('my_topics'), true, null, 'my_topics'),
        'TITLE_ONLY_CHBOX' => build_checkbox($params->key('title_only'), __('SEARCH_TITLES_ONLY'), true, config()->get('disable_ft_search_in_posts')),
        'ALL_WORDS_CHBOX' => build_checkbox($params->key('all_words'), __('SEARCH_ALL_WORDS'), true),
        'DL_CANCEL_CHBOX' => build_checkbox($params->key('dl_cancel'), __('SEARCH_DL_CANCEL'), $params->val('dl_cancel'), IS_GUEST, dl_link_css(DL_STATUS_CANCEL)),
        'DL_COMPL_CHBOX' => build_checkbox($params->key('dl_compl'), __('SEARCH_DL_COMPLETE'), $params->val('dl_compl'), IS_GUEST, dl_link_css(DL_STATUS_COMPLETE)),
        'DL_DOWN_CHBOX' => build_checkbox($params->key('dl_down'), __('SEARCH_DL_DOWN'), $params->val('dl_down'), IS_GUEST, dl_link_css(DL_STATUS_DOWN)),
        'DL_WILL_CHBOX' => build_checkbox($params->key('dl_will'), __('SEARCH_DL_WILL'), $params->val('dl_will'), IS_GUEST, dl_link_css(DL_STATUS_WILL)),
        'ONLY_NEW_CHBOX' => build_checkbox($params->key('new'), __('BT_ONLY_NEW'), $params->val('new'), IS_GUEST),
        'NEW_TOPICS_CHBOX' => build_checkbox($params->key('new_topics'), __('NEW_TOPICS'), $params->val('new_topics'), IS_GUEST),

        'FORUM_SELECT' => $forum_select,
        'TIME_SELECT' => build_select($params->key('time'), $time_select, $params->val('time')),
        'ORDER_SELECT' => build_select($params->key('order'), $order_select, $params->val('order')),
        'SORT_SELECT' => build_select($params->key('sort'), $sort_select, $params->val('sort')),
        'CHARS_SELECT' => '', // build_select ($params->key('chars'), $chars_select, $params->val('chars')),
        'DISPLAY_AS_SELECT' => build_select($params->key('display_as'), $display_as_select, $params->val('display_as')),
    ]);

    print_page('search.tpl');
}

unset($forums);
datastore()->rm('cat_forums');

// Restore previously found items list and search settings if we have valid $search_id
if ($search_id) {
    $row = DB()->fetch_row('
		SELECT search_array, search_settings
		FROM ' . BB_SEARCH . "
		WHERE session_id = '{$session_id}'
			AND search_type = " . SEARCH_TYPE_POST . "
			AND search_id = '" . DB()->escape($search_id) . "'
		LIMIT 1
	");

    if (empty($row['search_settings'])) {
        bb_die(__('SESSION_EXPIRED'));
    }

    $previous_settings = unserialize($row['search_settings']);
    $items_found = explode(',', $row['search_array']);
}

// Get simple "CHBOX" and "SELECT" type vars
foreach ($GPC as $name => $gpcParams) {
    $tmpVal = $params->val($name);
    $optVar = "{$name}_opt";
    if ($gpcParams[GPC_TYPE] == CHBOX) {
        checkbox_get_val($gpcParams[KEY_NAME], $tmpVal, $gpcParams[DEF_VAL], 1, 0, $previous_settings, $search_id);
    } elseif ($gpcParams[GPC_TYPE] == SELECT) {
        select_get_val($gpcParams[KEY_NAME], $tmpVal, ${$optVar}, $gpcParams[DEF_VAL], true, $previous_settings);
    }
    $params->setVal($name, $tmpVal);
}

// Get other "REQUEST" vars
$egosearch = false;

if (!$items_found) {
    // For compatibility with old-style params
    if (request()->has('search_id')) {
        switch (request()->get('search_id')) {
            case 'egosearch':
                $egosearch = true;
                $params->setVal('display_as', $as_topics);
                if (empty(request()->get($params->key('poster_id')))) {
                    $params->setVal('poster_id', $user_id);
                }
                break;
            case 'newposts':
                $params->setVal('new', true);
                break;
        }
    }

    // Forum
    $forum_selected = [];
    if ($var = request()->get($params->key('forum'))) {
        $forum_selected = get_id_ary($var);

        if (!in_array($search_all, $forum_selected)) {
            $params->setVal('forum', implode(',', $forum_selected));
        }
    }

    // Topic
    if ($var = request()->get($params->key('topic'))) {
        $params->setVal('topic', implode(',', get_id_ary($var)));
    }

    // Poster id (from requested name or id)
    if ($var = request()->getInt($params->key('poster_id'))) {
        $params->setVal('poster_id', $var);

        if ($params->val('poster_id') != $user_id && !get_username($params->val('poster_id'))) {
            bb_die(__('USER_NOT_EXIST'));
        }
    } elseif ($var = request()->post->get($params->key('poster_name'))) {
        $poster_name_sql = str_replace("\\'", "''", clean_username($var));

        if (!$poster_id_val = get_user_id($poster_name_sql)) {
            bb_die(__('USER_NOT_EXIST'));
        }
        $params->setVal('poster_id', $poster_id_val);
    }

    // Search words
    if ($var = request()->get($params->key('text_match'))) {
        if ($tmp = mb_substr(trim($var), 0, $text_match_max_len)) {
            $params->setVal('text_match', $tmp);
            $text_match_sql = clean_text_match($params->val('text_match'), $params->val('all_words'), true);
        }
    }
}

$dl_status = [];
if ($params->val('dl_cancel')) {
    $dl_status[] = DL_STATUS_CANCEL;
}
if ($params->val('dl_compl')) {
    $dl_status[] = DL_STATUS_COMPLETE;
}
if ($params->val('dl_down')) {
    $dl_status[] = DL_STATUS_DOWN;
}
if ($params->val('dl_will')) {
    $dl_status[] = DL_STATUS_WILL;
}
$dl_status_csv = implode(',', $dl_status);

// Switches
$dl_search = ($dl_status && !IS_GUEST);
$new_posts = ($params->val('new') && !IS_GUEST);
$prev_days = ($params->val('time') != $search_all);
$new_topics = (!IS_GUEST && ($params->val('new_topics') || request()->query->has('newposts')));
$my_topics = ($params->val('poster_id') && $params->val('my_topics'));
$my_posts = ($params->val('poster_id') && !$params->val('my_topics'));
$title_match = ($text_match_sql && ($params->val('title_only') || config()->get('disable_ft_search_in_posts')));

// "Display as" mode (posts or topics)
$post_mode = (!$dl_search && ($params->val('display_as') == $as_posts || request()->query->has('search_author')));

// Start building SQL
$SQL = DB()->get_empty_sql_array();

// Displaying "as posts" mode
if ($post_mode) {
    $order = $order_opt[$params->val('order')]['sql'];
    $sort = $sort_opt[$params->val('sort')]['sql'];
    $per_page = config()->get('posts_per_page');
    $params->setVal('display_as', $as_posts);

    // Run initial search for post_ids
    if (!$items_found) {
        $join_t = ($title_match || $my_topics || $new_topics || in_array($params->val('order'), [$ord_last_p, $ord_created, $ord_name, $ord_repl]));
        $join_s = ($text_match_sql && !$title_match);
        $join_p = ($my_posts || $join_s);

        $tbl = ($join_t && !$join_p) ? 't' : 'p';
        $time_field = ($join_t && !$join_p) ? 'topic_last_post_time' : 'post_time';

        // SELECT
        $SQL['SELECT'][] = ($join_t && !$join_p) ? 't.topic_first_post_id AS item_id' : 'p.post_id AS item_id';

        // FROM
        if ($join_t) {
            $SQL['FROM'][] = $topics_tbl;
        }
        if ($join_p) {
            $SQL['FROM'][] = $posts_tbl;
        }

        if (!$SQL['FROM']) {
            $join_p = true;
            $SQL['FROM'][] = $posts_tbl;
        }

        // WHERE
        if ($join_p && $join_t) {
            $SQL['WHERE'][] = 't.topic_id = p.topic_id';
        }

        if ($excluded_forums_csv) {
            $SQL['WHERE'][] = "{$tbl}.forum_id NOT IN({$excluded_forums_csv})";
        }

        if ($params->val('forum')) {
            $SQL['WHERE'][] = "{$tbl}.forum_id IN(" . $params->val('forum') . ')';
        }
        if ($params->val('topic')) {
            $SQL['WHERE'][] = "{$tbl}.topic_id IN(" . $params->val('topic') . ')';
        }
        if ($new_posts) {
            $SQL['WHERE'][] = "{$tbl}.{$time_field} > {$lastvisit}";
        }
        if ($new_topics) {
            $SQL['WHERE'][] = "t.topic_time > {$lastvisit}";
        }
        if ($prev_days) {
            $SQL['WHERE'][] = "{$tbl}.{$time_field} > " . $time_opt[$params->val('time')]['sql'];
        }
        if ($my_posts) {
            $SQL['WHERE'][] = 'p.poster_id = ' . $params->val('poster_id');
        }
        if ($my_topics) {
            $SQL['WHERE'][] = 't.topic_poster = ' . $params->val('poster_id');
        }

        if ($text_match_sql) {
            $search_match_topics_csv = '';
            $title_match_topics = get_title_match_topics($text_match_sql, $forum_selected, $title_match);

            if (!$search_match_topics_csv = implode(',', $title_match_topics)) {
                bb_die(__('NO_SEARCH_MATCH'));
            }

            $where_id = ($title_match) ? 'topic_id' : 'post_id';

            $SQL['WHERE'][] = "{$tbl}.{$where_id} IN({$search_match_topics_csv})";
            prevent_huge_searches($SQL);
        }

        if (!$SQL['WHERE']) {
            redirect($url);
        }

        $SQL['GROUP BY'][] = 'item_id';
        // Fix for MySQL only_full_group_by mode: use MAX() when ordering by post_time with GROUP BY
        $SQL['ORDER BY'][] = ($new_posts && $join_p) ? 'p.topic_id ASC, MAX(p.post_time) ASC' : "{$order} {$sort}";
        $SQL['LIMIT'][] = (string)$search_limit;

        $result = fetch_search_ids($SQL, SEARCH_TYPE_POST, $session_id, $per_page, $params);
        $search_id = $result['search_id'];
        $items_found = $result['items_found'];
        $items_display = $result['items_display'];
    } elseif (!$items_display = array_slice($items_found, $start, $per_page)) {
        bb_die(__('NO_SEARCH_MATCH'));
    }

    // Build SQL for displaying posts
    $excluded_forums_sql = ($excluded_forums_csv) ? " AND t.forum_id NOT IN({$excluded_forums_csv}) " : '';

    $sql = "
		SELECT
		  p.post_id AS item_id,
		  t.*,
		  p.*,
		  h.post_html, IF(h.post_html IS NULL, pt.post_text, NULL) AS post_text,
		  IF(p.poster_id = {$anon_id}, p.post_username, u.username) AS username, u.user_id, u.user_rank
		FROM       {$posts_tbl}
		INNER JOIN {$topics_tbl}     ON(t.topic_id = p.topic_id)
		INNER JOIN {$posts_text_tbl} ON(pt.post_id = p.post_id)
		 LEFT JOIN {$posts_html_tbl} ON(h.post_id = pt.post_id)
		INNER JOIN {$users_tbl}      ON(u.user_id = p.poster_id)
		WHERE
		      p.post_id IN(" . implode(',', $items_display) . ")
		    {$excluded_forums_sql}
		LIMIT {$per_page}
	";

    // Fetch posts data
    if (!$unsorted_rows = DB()->fetch_rowset($sql)) {
        bb_die(__('NO_SEARCH_MATCH'));
    }
    $tmp = $sorted_rows = [];

    foreach ($unsorted_rows as $row) {
        $tmp[$row['post_id']] = $row;
    }
    foreach ($items_display as $post_id) {
        if (empty($tmp[$post_id])) {
            continue; // if post was deleted but still remain in search results
        }
        $topic_id = $tmp[$post_id]['topic_id'];
        $sorted_rows[$topic_id][] = $tmp[$post_id];
    }

    // Output page
    $new_tracks = [];

    foreach ($sorted_rows as $topic_id => $topic_posts) {
        // Topic title block
        $first_post = reset($topic_posts);
        $topic_id = (int)$topic_id;
        $forum_id = (int)$first_post['forum_id'];
        $is_unread_t = is_unread($first_post['topic_last_post_time'], $topic_id, $forum_id);

        template()->assign_block_vars('t', [
            'FORUM_ID' => $forum_id,
            'FORUM_NAME' => $forum_name_html[$forum_id],
            'TOPIC_ID' => $topic_id,
            'TOPIC_TITLE' => censor()->censorString($first_post['topic_title']),
            'TOPIC_ICON' => get_topic_icon($first_post, $is_unread_t),
        ]);

        $quote_btn = $edit_btn = $ip_btn = '';
        $delpost_btn = IS_AM;

        // Topic posts block
        foreach ($topic_posts as $row_num => $post) {
            if ($post['poster_id'] != BOT_UID) {
                $quote_btn = !IS_GUEST;
                $edit_btn = $ip_btn = IS_AM;
            }

            $message = get_parsed_post($post);
            $message = censor()->censorString($message);

            template()->assign_block_vars('t.p', [
                'ROW_NUM' => $row_num,
                'POSTER_ID' => $post['poster_id'],
                'POSTER' => profile_url($post),
                'POST_ID' => $post['post_id'],
                'POST_DATE' => bb_date($post['post_time'], config()->get('post_date_format')),
                'IS_UNREAD' => is_unread($post['post_time'], $topic_id, $forum_id),
                'MESSAGE' => $message,
                'POSTED_AFTER' => '',
                'QUOTE' => $quote_btn,
                'EDIT' => $edit_btn,
                'DELETE' => $delpost_btn,
                'IP' => $ip_btn,
            ]);

            $curr_new_track_val = !empty($new_tracks[$topic_id]) ? $new_tracks[$topic_id] : 0;
            $new_tracks[$topic_id] = max($curr_new_track_val, $post['post_time']);
        }
    }
    $topics_tracking = &tracking_topics();
    set_tracks(COOKIE_TOPIC, $topics_tracking, $new_tracks);
} // Displaying "as topics" mode
else {
    $order = $order_opt[$params->val('order')]['sql'];
    $sort = $sort_opt[$params->val('sort')]['sql'];
    $per_page = config()->get('topics_per_page');
    $params->setVal('display_as', $as_topics);

    // Run initial search for topic_ids
    if (!$items_found) {
        $join_t = ($title_match || $my_topics || $new_topics || $dl_search || $new_posts || in_array($params->val('order'), [$ord_last_p, $ord_created, $ord_name, $ord_repl]));
        $join_s = ($text_match_sql && !$title_match);
        $join_p = ($my_posts || $join_s);
        $join_dl = ($dl_search);

        $tbl = ($join_p && !$join_t) ? 'p' : 't';
        $time_field = ($join_p && !$join_t) ? 'post_time' : 'topic_last_post_time';

        // SELECT
        if ($egosearch) {
            $SQL['SELECT'][] = 'p.topic_id AS item_id, MAX(p.post_time) AS max_post_time';
        } else {
            $SQL['SELECT'][] = ($join_p && !$join_t) ? 'p.topic_id AS item_id' : 't.topic_id AS item_id';
        }

        // FROM
        if ($join_t) {
            $SQL['FROM'][] = $topics_tbl;
        }
        if ($join_p) {
            $SQL['FROM'][] = $posts_tbl;
        }

        if (!$SQL['FROM']) {
            $join_t = true;
            $SQL['FROM'][] = $topics_tbl;
        }

        // WHERE
        if ($join_p && $join_t) {
            $SQL['WHERE'][] = 't.topic_id = p.topic_id';
        }

        if ($excluded_forums_csv) {
            $SQL['WHERE'][] = "{$tbl}.forum_id NOT IN({$excluded_forums_csv})";
        }

        if ($join_t) {
            $SQL['WHERE'][] = 't.topic_status != ' . TOPIC_MOVED;
        }
        if ($params->val('forum')) {
            $SQL['WHERE'][] = "{$tbl}.forum_id IN(" . $params->val('forum') . ')';
        }
        if ($params->val('topic')) {
            $SQL['WHERE'][] = "{$tbl}.topic_id IN(" . $params->val('topic') . ')';
        }
        if ($new_posts) {
            $SQL['WHERE'][] = "{$tbl}.{$time_field} > {$lastvisit}";
        }
        if ($new_topics) {
            $SQL['WHERE'][] = "t.topic_time > {$lastvisit}";
        }
        if ($prev_days) {
            $SQL['WHERE'][] = "{$tbl}.{$time_field} > " . $time_opt[$params->val('time')]['sql'];
        }
        if ($my_posts) {
            $SQL['WHERE'][] = 'p.poster_id = ' . $params->val('poster_id');
        }
        if ($my_posts && user()->id == $params->val('poster_id')) {
            $SQL['WHERE'][] = 'p.user_post = 1';

            if (userdata('user_posts')) {
                template()->assign_var('BB_DIE_APPEND_MSG', '
					<form id="mod-action" method="POST" action="' . FORUM_PATH . 'search">
						<input type="submit" name="add_my_post" value="' . __('RESTORE_ALL_POSTS') . '" class="bold" onclick="if (!window.confirm( this.value +\'?\' )){ return false };" />
					</form>
					<br /><br />
					<a href="' . FORUM_PATH . '">' . __('INDEX_RETURN') . '</a>
				');
            }
        }
        if ($my_topics) {
            $SQL['WHERE'][] = 't.topic_poster = ' . $params->val('poster_id');
        }

        if ($text_match_sql) {
            $search_match_topics_csv = '';
            $title_match_topics = get_title_match_topics($text_match_sql, $forum_selected, $title_match);

            if (!$search_match_topics_csv = implode(',', $title_match_topics)) {
                bb_die(__('NO_SEARCH_MATCH'));
            }

            $where_id = ($title_match) ? 't.topic_id' : 'p.post_id';

            $SQL['WHERE'][] = "{$where_id} IN({$search_match_topics_csv})";
            prevent_huge_searches($SQL);
        }

        if ($join_dl) {
            $SQL['FROM'][] = $dl_stat_tbl;
        }
        if ($join_dl) {
            $SQL['WHERE'][] = 'dl.topic_id = t.topic_id AND dl.user_id = ' . $params->val('dl_user_id') . " AND dl.user_status IN({$dl_status_csv})";
        }

        if (!$SQL['WHERE']) {
            redirect($url);
        }

        $SQL['GROUP BY'][] = 'item_id';
        $SQL['LIMIT'][] = (string)$search_limit;

        if ($egosearch) {
            $SQL['ORDER BY'][] = 'max_post_time DESC';
        } else {
            // Fix for MySQL only_full_group_by mode: use MAX() when ordering by post_time with GROUP BY
            if ($params->val('order') == $ord_posted) {
                $SQL['ORDER BY'][] = "MAX({$tbl}.{$time_field}) {$sort}";
            } else {
                $SQL['ORDER BY'][] = "{$order} {$sort}";
            }
        }

        $result = fetch_search_ids($SQL, SEARCH_TYPE_POST, $session_id, $per_page, $params);
        $search_id = $result['search_id'];
        $items_found = $result['items_found'];
        $items_display = $result['items_display'];
    } elseif (!$items_display = array_slice($items_found, $start, $per_page)) {
        bb_die(__('NO_SEARCH_MATCH'));
    }

    // Build SQL for displaying topics
    $SQL = DB()->get_empty_sql_array();
    $join_dl = (config()->get('show_dl_status_in_search') && !IS_GUEST);

    $SQL['SELECT'][] = "
		t.*, t.topic_poster AS first_user_id, u1.user_rank AS first_user_rank,
		IF(t.topic_poster = {$anon_id}, p1.post_username, u1.username) AS first_username,
		p2.poster_id AS last_user_id, u2.user_rank AS last_user_rank,
		IF(p2.poster_id = {$anon_id}, p2.post_username, u2.username) AS last_username
	";
    if ($join_dl) {
        $SQL['SELECT'][] = 'dl.user_status AS dl_status';
    }

    $SQL['FROM'][] = BB_TOPICS . ' t';
    $SQL['LEFT JOIN'][] = BB_POSTS . ' p1 ON(t.topic_first_post_id = p1.post_id)';
    $SQL['LEFT JOIN'][] = BB_USERS . ' u1 ON(t.topic_poster = u1.user_id)';
    $SQL['LEFT JOIN'][] = BB_POSTS . ' p2 ON(t.topic_last_post_id = p2.post_id)';
    $SQL['LEFT JOIN'][] = BB_USERS . ' u2 ON(p2.poster_id = u2.user_id)';
    if ($join_dl) {
        $SQL['LEFT JOIN'][] = BB_BT_DLSTATUS . " dl ON(dl.user_id = {$user_id} AND dl.topic_id = t.topic_id)";
    }

    $SQL['WHERE'][] = 't.topic_id IN(' . implode(',', $items_display) . ')';
    if ($excluded_forums_csv) {
        $SQL['WHERE'][] = "t.forum_id NOT IN({$excluded_forums_csv})";
    }

    $SQL['LIMIT'][] = (string)$per_page;

    // Fetch topics data
    $topic_rows = [];
    foreach (DB()->fetch_rowset($SQL) as $row) {
        $topic_rows[$row['topic_id']] = $row;
    }
    if (!$topic_rows) {
        bb_die(__('NO_SEARCH_MATCH'));
    }

    // Output page
    foreach ($items_display as $row_num => $item_id) {
        if (empty($topic_rows[$item_id])) {
            continue;  // if topic was deleted but still remain in search results
        }
        $topic = $topic_rows[$item_id];
        $topic_id = $topic['topic_id'];
        $forum_id = $topic['forum_id'];
        $is_unread = is_unread($topic['topic_last_post_time'], $topic_id, $forum_id);
        $moved = ($topic['topic_status'] == TOPIC_MOVED);

        $topicTitle = $topic['topic_title'];
        $hrefTopicId = $moved ? $topic['topic_moved_id'] : $topic_id;
        $topicUrl = url()->topic($hrefTopicId, $topicTitle);

        template()->assign_block_vars('t', [
            'ROW_NUM' => $row_num,
            'FORUM_ID' => $forum_id,
            'FORUM_NAME' => $forum_name_html[$forum_id],
            'TOPIC_ID' => $topic_id,
            'TOPIC_URL' => $topicUrl,
            'TOPIC_NEWEST_URL' => url()->topicNewest($hrefTopicId, $topicTitle),
            'LAST_POST_URL' => url()->topicPost($hrefTopicId, $topicTitle, $topic['topic_last_post_id']),
            'TOPIC_TITLE' => censor()->censorString($topicTitle),
            'IS_UNREAD' => $is_unread,
            'TOPIC_ICON' => get_topic_icon($topic, $is_unread),
            'PAGINATION' => $moved ? '' : build_topic_pagination($topicUrl, $topic['topic_replies'], config()->get('posts_per_page')),
            'REPLIES' => $moved ? '' : $topic['topic_replies'],
            'ATTACH' => !empty($topic['attach_ext_id']),
            'STATUS' => $topic['topic_status'],
            'TYPE' => $topic['topic_type'],
            'DL' => ($topic['topic_dl_type'] == TOPIC_DL_TYPE_DL),
            'POLL' => (bool)$topic['topic_vote'],
            'DL_CLASS' => isset($topic['dl_status']) ? dl_link_css($topic['dl_status']) : '',

            'TOPIC_AUTHOR' => profile_url(['username' => $topic['first_username'], 'user_id' => $topic['first_user_id'], 'user_rank' => $topic['first_user_rank']]),
            'LAST_POSTER' => profile_url(['username' => $topic['last_username'], 'user_id' => $topic['last_user_id'], 'user_rank' => $topic['last_user_rank']]),
            'LAST_POST_TIME' => bb_date($topic['topic_last_post_time']),
            'LAST_POST_TIME_RAW' => $topic['topic_last_post_time'],
            'LAST_POST_ID' => $topic['topic_last_post_id'],
        ]);
    }
}

if ($items_display) {
    $items_count = count($items_found);
    $pages = (!$items_count) ? 1 : ceil($items_count / $per_page);
    $url = ($search_id) ? url_arg($url, 'id', $search_id) : $url;

    generate_pagination($url, $items_count, $per_page, $start);

    template()->assign_vars([
        'PAGE_TITLE' => __('SEARCH'),

        'SEARCH_MATCHES' => ($items_count) ? sprintf(__('FOUND_SEARCH_MATCHES'), $items_count) : '',
        'DISPLAY_AS_POSTS' => $post_mode,

        'DL_CONTROLS' => ($dl_search && $params->val('dl_user_id') == $user_id),
        'DL_ACTION' => 'dl_list.php',
        'MY_POSTS' => (!$post_mode && $my_posts && user()->id == $params->val('poster_id')),
    ]);

    print_page('search_results.tpl');
}

redirect($url);

// ----------------------------------------------------------- //
// Functions
//
function fetch_search_ids(
    $sql,
    int $search_type,
    string $session_id,
    int $per_page,
    SearchParams $params,
): array {
    $items_found = [];
    foreach (DB()->fetch_rowset($sql) as $row) {
        $items_found[] = $row['item_id'];
    }
    if (!$items_count = count($items_found)) {
        bb_die(__('NO_SEARCH_MATCH'));
    }

    // Save results in DB
    $search_id = make_rand_str(SEARCH_ID_LENGTH);

    if ($items_count > $per_page) {
        $search_array = implode(',', $items_found);

        $save_in_db = [
            'order',
            'sort',
            'display_as',
            'chars',
        ];
        if ($params->val('dl_cancel')) {
            $save_in_db[] = 'dl_cancel';
        }
        if ($params->val('dl_compl')) {
            $save_in_db[] = 'dl_compl';
        }
        if ($params->val('dl_down')) {
            $save_in_db[] = 'dl_down';
        }
        if ($params->val('dl_will')) {
            $save_in_db[] = 'dl_will';
        }

        $curr_set = [];
        foreach ($save_in_db as $name) {
            $curr_set[$params->key($name)] = $params->val($name);
        }
        $search_settings = DB()->escape(serialize($curr_set));

        $columns = 'session_id,   search_type,   search_id,   search_time,    search_settings,    search_array';
        $values = "'{$session_id}', {$search_type}, '{$search_id}', " . TIMENOW . ", '{$search_settings}', '{$search_array}'";

        DB()->query('REPLACE INTO ' . BB_SEARCH . " ({$columns}) VALUES ({$values})");
    }

    return [
        'search_id' => $search_id,
        'items_found' => $items_found,
        'items_display' => array_slice($items_found, 0, $per_page),
    ];
}

function prevent_huge_searches($SQL)
{
    if (config()->get('limit_max_search_results')) {
        $SQL['select_options'][] = 'SQL_CALC_FOUND_ROWS';
        $SQL['ORDER BY'] = [];
        $SQL['LIMIT'] = ['0'];

        if (DB()->query($SQL) and $row = DB()->fetch_row('SELECT FOUND_ROWS() AS rows_count')) {
            if ($row['rows_count'] > config()->get('limit_max_search_results')) {
                //				bb_log(str_compact(DB()->build_sql($SQL)) ." [{$row['rows_count']} rows]". LOG_LF, 'sql_huge_search');
                bb_die('Too_many_search_results');
            }
        }
    }
}

function username_search($search_match)
{
    $username_list = '';

    if (!empty($search_match)) {
        $username_search = str_replace('*', '%', clean_username($search_match));

        $sql = '
			SELECT username
			FROM ' . BB_USERS . "
			WHERE username LIKE '" . DB()->escape($username_search) . "'
				AND user_id <> " . GUEST_UID . '
			ORDER BY username
			LIMIT 200
		';

        foreach (DB()->fetch_rowset($sql) as $row) {
            $username = htmlCHR(stripslashes(html_entity_decode($row['username'])));
            $username_list .= '<option value="' . $username . '">' . $username . '</option>';
        }
        if (!$username_list) {
            $username_list = '<option value="">' . __('NO_MATCH') . '</option>';
        }
    }

    $input_name = request()->has('input_name') ? htmlCHR(request()->get('input_name')) : 'username';

    template()->assign_vars([
        'TPL_SEARCH_USERNAME' => true,

        'PAGE_TITLE' => __('SEARCH'),
        'USERNAME' => !empty($search_match) ? htmlCHR(stripslashes(html_entity_decode($search_match))) : '',
        'INPUT_NAME' => $input_name,
        'USERNAME_OPTIONS' => $username_list,
        'SEARCH_ACTION' => "search?mode=searchuser&amp;input_name={$input_name}",
    ]);

    print_page('search.tpl', 'simple');
}
