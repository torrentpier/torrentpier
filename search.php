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

define('BB_SCRIPT', 'search');
define('BB_ROOT', './');
require __DIR__ . '/common.php';

require INC_DIR . '/bbcode.php';

$page_cfg['load_tpl_vars'] = array(
    'post_buttons',
    'post_icons',
    'topic_icons',
);

$user->session_start(array('req_login' => $bb_cfg['disable_search_for_guest']));

set_die_append_msg();

if (isset($_POST['del_my_post'])) {
    $template->assign_var('BB_DIE_APPEND_MSG', '
		<a href="#" onclick="window.close(); window.opener.focus();">' . $lang['GOTO_MY_MESSAGE'] . '</a>
		<br /><br />
		<a href="index.php">' . $lang['INDEX_RETURN'] . '</a>
	');

    if (empty($_POST['topic_id_list']) or !$topic_csv = get_id_csv($_POST['topic_id_list'])) {
        bb_die($lang['NONE_SELECTED']);
    }

    DB()->query("UPDATE " . BB_POSTS . " SET user_post = 0 WHERE poster_id = {$user->id} AND topic_id IN($topic_csv)");

    if (DB()->affected_rows()) {
        //bb_die('Выбранные темы ['. count($_POST['topic_id_list']) .' шт.] удалены из списка "Мои сообщения"');
        bb_die($lang['DEL_MY_MESSAGE']);
    } else {
        bb_die($lang['NO_TOPICS_MY_MESSAGE']);
    }
} elseif (isset($_POST['add_my_post'])) {
    $template->assign_var('BB_DIE_APPEND_MSG', '
		<a href="#" onclick="window.close(); window.opener.focus();">' . $lang['GOTO_MY_MESSAGE'] . '</a>
		<br /><br />
		<a href="index.php">' . $lang['INDEX_RETURN'] . '</a>
	');

    if (IS_GUEST) {
        redirect('index.php');
    }

    DB()->query("UPDATE " . BB_POSTS . " SET user_post = 1 WHERE poster_id = {$user->id}");

    redirect("search.php?u={$user->id}");
}

//
// Define censored word matches
//
$orig_word = $replacement_word = array();
obtain_word_list($orig_word, $replacement_word);

$tracking_topics = get_tracks('topic');
$tracking_forums = get_tracks('forum');

if ($mode =& $_REQUEST['mode']) {
    // This handles the simple windowed user search functions called from various other scripts
    if ($mode == 'searchuser') {
        $username = isset($_POST['search_username']) ? $_POST['search_username'] : '';
        username_search($username);
        exit;
    }
}

$excluded_forums_csv = $user->get_excluded_forums(AUTH_READ);

$search_limit = 500;
$forum_select_size = 16;   // forum select box max rows
$max_forum_name_len = 60;   // inside forum select box
$text_match_max_len = 60;
$poster_name_max_len = 25;

$start = isset($_REQUEST['start']) ? abs((int)$_REQUEST['start']) : 0;
$url = basename(__FILE__);

$anon_id = GUEST_UID;
$user_id = $userdata['user_id'];
$lastvisit = (IS_GUEST) ? TIMENOW : $userdata['user_lastvisit'];
$search_id = (isset($_GET['id']) && verify_id($_GET['id'], SEARCH_ID_LENGTH)) ? $_GET['id'] : '';
$session_id = $userdata['session_id'];

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
if (!$forums = $datastore->get('cat_forums')) {
    $datastore->update('cat_forums');
    $forums = $datastore->get('cat_forums');
}
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
$order_opt = array(
    $ord_posted => array(
        'lang' => $lang['SORT_TIME'],
        'sql' => 'item_id',
    ),
    $ord_last_p => array(
        'lang' => $lang['BT_LAST_POST'],
        'sql' => 't.topic_last_post_id',
    ),
    $ord_created => array(
        'lang' => $lang['BT_CREATED'],
        'sql' => 't.topic_time',
    ),
    $ord_name => array(
        'lang' => $lang['SORT_TOPIC_TITLE'],
        'sql' => 't.topic_title',
    ),
    $ord_repl => array(
        'lang' => $lang['REPLIES'],
        'sql' => 't.topic_replies',
    ),
);
$order_select = array();
foreach ($order_opt as $val => $opt) {
    $order_select[$opt['lang']] = $val;
}

// Sort direction
$sort_opt = array(
    $sort_asc => array(
        'lang' => $lang['ASC'],
        'sql' => ' ASC ',
    ),
    $sort_desc => array(
        'lang' => $lang['DESC'],
        'sql' => ' DESC ',
    ),
);
$sort_select = array();
foreach ($sort_opt as $val => $opt) {
    $sort_select[$opt['lang']] = $val;
}

// Previous days
$time_opt = array(
    $search_all => array(
        'lang' => $lang['BT_ALL_DAYS_FOR'],
        'sql' => 0,
    ),
    1 => array(
        'lang' => $lang['BT_1_DAY_FOR'],
        'sql' => TIMENOW - 86400,
    ),
    3 => array(
        'lang' => $lang['BT_3_DAY_FOR'],
        'sql' => TIMENOW - 86400 * 3,
    ),
    7 => array(
        'lang' => $lang['BT_7_DAYS_FOR'],
        'sql' => TIMENOW - 86400 * 7,
    ),
    14 => array(
        'lang' => $lang['BT_2_WEEKS_FOR'],
        'sql' => TIMENOW - 86400 * 14,
    ),
    30 => array(
        'lang' => $lang['BT_1_MONTH_FOR'],
        'sql' => TIMENOW - 86400 * 30,
    ),
);
$time_select = array();
foreach ($time_opt as $val => $opt) {
    $time_select[$opt['lang']] = $val;
}

// Display as
$display_as_opt = array(
    $as_topics => array(
        'lang' => $lang['TOPICS'],
    ),
    $as_posts => array(
        'lang' => $lang['MESSAGE'],
    ),
);
$display_as_select = array();
foreach ($display_as_opt as $val => $opt) {
    $display_as_select[$opt['lang']] = $val;
}

// Chars
$chars_opt = array(
    $show_all => array(
        'lang' => $lang['ALL_AVAILABLE'],
    ),
    $show_briefly => array(
        'lang' => $lang['BRIEFLY'],
    ),
);
$chars_select = array();
foreach ($chars_opt as $val => $opt) {
    $chars_select[$opt['lang']] = $val;
}

$GPC = array(
#	  var_name              key_name  def_value    GPC type
    'all_words' => array('allw', 1, CHBOX),
    'cat' => array('c', null, REQUEST),
    'chars' => array('ch', $show_all, SELECT),
    'display_as' => array('dm', $as_topics, SELECT),
    'dl_cancel' => array('dla', 0, CHBOX),
    'dl_compl' => array('dlc', 0, CHBOX),
    'dl_down' => array('dld', 0, CHBOX),
    'dl_user_id' => array('dlu', $user_id, CHBOX),
    'dl_will' => array('dlw', 0, CHBOX),
    'forum' => array('f', $search_all, REQUEST),
    'my_topics' => array('myt', 0, CHBOX),
    'new' => array('new', 0, CHBOX),
    'new_topics' => array('nt', 0, CHBOX),
    'order' => array('o', $ord_posted, SELECT),
    'poster_id' => array('uid', null, REQUEST),
    'poster_name' => array('pn', null, REQUEST),
    'sort' => array('s', $sort_desc, SELECT),
    'text_match' => array('nm', null, REQUEST),
    'time' => array('tm', $search_all, SELECT),
    'title_only' => array('to', 0, CHBOX),
    'topic' => array('t', null, REQUEST),
);

// Define all GPC vars with default values
foreach ($GPC as $var_name => $var_options) {
    $GLOBALS["{$var_name}_key"] = $var_options[KEY_NAME];
    $GLOBALS["{$var_name}_val"] = $var_options[DEF_VAL];
}

// Output basic page
if (empty($_GET) && empty($_POST)) {
    // Make forum select box
    $forum_select_mode = explode(',', $excluded_forums_csv);
    $forum_select = get_forum_select($forum_select_mode, "{$forum_key}[]", $search_all, $max_forum_name_len, $forum_select_size, 'style="width: 95%;"', $search_all);

    $template->assign_vars(array(
        'TPL_SEARCH_MAIN' => true,
        'PAGE_TITLE' => $lang['SEARCH'],

        'POSTER_ID_KEY' => $poster_id_key,
        'TEXT_MATCH_KEY' => $text_match_key,
        'POSTER_NAME_KEY' => $poster_name_key,

        'THIS_USER_ID' => $userdata['user_id'],
        'THIS_USER_NAME' => addslashes($userdata['username']),
        'SEARCH_ACTION' => "search.php",
        'U_SEARCH_USER' => "search.php?mode=searchuser&amp;input_name=$poster_name_key",
        'ONLOAD_FOCUS_ID' => 'text_match_input',

        'MY_TOPICS_ID' => 'my_topics',
        'MY_TOPICS_CHBOX' => build_checkbox($my_topics_key, $lang['SEARCH_MY_TOPICS'], $my_topics_val, true, null, 'my_topics'),
        'TITLE_ONLY_CHBOX' => build_checkbox($title_only_key, $lang['SEARCH_TITLES_ONLY'], true, $bb_cfg['disable_ft_search_in_posts']),
        'ALL_WORDS_CHBOX' => build_checkbox($all_words_key, $lang['SEARCH_ALL_WORDS'], true),
        'DL_CANCEL_CHBOX' => build_checkbox($dl_cancel_key, $lang['SEARCH_DL_CANCEL'], $dl_cancel_val, IS_GUEST, 'dlCancel'),
        'DL_COMPL_CHBOX' => build_checkbox($dl_compl_key, $lang['SEARCH_DL_COMPLETE'], $dl_compl_val, IS_GUEST, 'dlComplete'),
        'DL_DOWN_CHBOX' => build_checkbox($dl_down_key, $lang['SEARCH_DL_DOWN'], $dl_down_val, IS_GUEST, 'dlDown'),
        'DL_WILL_CHBOX' => build_checkbox($dl_will_key, $lang['SEARCH_DL_WILL'], $dl_will_val, IS_GUEST, 'dlWill'),
        'ONLY_NEW_CHBOX' => build_checkbox($new_key, $lang['BT_ONLY_NEW'], $new_val, IS_GUEST),
        'NEW_TOPICS_CHBOX' => build_checkbox($new_topics_key, $lang['NEW_TOPICS'], $new_topics_val, IS_GUEST),

        'FORUM_SELECT' => $forum_select,
        'TIME_SELECT' => build_select($time_key, $time_select, $time_val),
        'ORDER_SELECT' => build_select($order_key, $order_select, $order_val),
        'SORT_SELECT' => build_select($sort_key, $sort_select, $sort_val),
        'CHARS_SELECT' => '', # build_select ($chars_key, $chars_select, $chars_val),
        'DISPLAY_AS_SELECT' => build_select($display_as_key, $display_as_select, $display_as_val),
    ));

    print_page('search.tpl');
}

unset($forums);
$datastore->rm('cat_forums');

// Restore previously found items list and search settings if we have valid $search_id
if ($search_id) {
    $row = DB()->fetch_row("
		SELECT search_array, search_settings
		FROM " . BB_SEARCH . "
		WHERE session_id = '$session_id'
			AND search_type = " . SEARCH_TYPE_POST . "
			AND search_id = '$search_id'
		LIMIT 1
	");

    if (empty($row['search_settings'])) {
        bb_die($lang['SESSION_EXPIRED']);
    }

    $previous_settings = unserialize($row['search_settings']);
    $items_found = explode(',', $row['search_array']);
}

// Get simple "CHBOX" and "SELECT" type vars
foreach ($GPC as $name => $params) {
    if ($params[GPC_TYPE] == CHBOX) {
        checkbox_get_val($params[KEY_NAME], ${"{$name}_val"}, $params[DEF_VAL]);
    } elseif ($params[GPC_TYPE] == SELECT) {
        select_get_val($params[KEY_NAME], ${"{$name}_val"}, ${"{$name}_opt"}, $params[DEF_VAL]);
    }
}

// Get other "REQUEST" vars
$egosearch = false;

if (!$items_found) {
    // For compatibility with old-style params
    if (isset($_REQUEST['search_id'])) {
        switch ($_REQUEST['search_id']) {
            case 'egosearch':
                $egosearch = true;
                $display_as_val = $as_topics;
                if (empty($_REQUEST[$poster_id_key])) {
                    $_REQUEST[$poster_id_key] = $user_id;
                }
                break;
            case 'newposts':
                $new_val = true;
                break;
        }
    }

    // Forum
    $forum_selected = '';
    if ($var =& $_REQUEST[$forum_key]) {
        $forum_selected = get_id_ary($var);

        if (!in_array($search_all, $forum_selected)) {
            $forum_val = implode(',', $forum_selected);
        }
    }

    // Topic
    if ($var =& $_REQUEST[$topic_key]) {
        $topic_val = implode(',', get_id_ary($var));
    }

    // Poster id (from requested name or id)
    if ($var = request_var($poster_id_key, 0)) {
        $poster_id_val = (int)$var;

        if ($poster_id_val != $user_id && !get_username($poster_id_val)) {
            bb_die($lang['USER_NOT_EXIST']);
        }
    } elseif ($var =& $_POST[$poster_name_key]) {
        $poster_name_sql = str_replace("\\'", "''", clean_username($var));

        if (!$poster_id_val = get_user_id($poster_name_sql)) {
            bb_die($lang['USER_NOT_EXIST']);
        }
    }

    // Search words
    if ($var =& $_REQUEST[$text_match_key]) {
        if ($tmp = mb_substr(trim($var), 0, $text_match_max_len)) {
            $title_match_val = $tmp;
            $text_match_sql = clean_text_match($title_match_val, $all_words_val, true);
        }
    }
}

$dl_status = array();
if ($dl_cancel_val) {
    $dl_status[] = DL_STATUS_CANCEL;
}
if ($dl_compl_val) {
    $dl_status[] = DL_STATUS_COMPLETE;
}
if ($dl_down_val) {
    $dl_status[] = DL_STATUS_DOWN;
}
if ($dl_will_val) {
    $dl_status[] = DL_STATUS_WILL;
}
$dl_status_csv = implode(',', $dl_status);

// Switches
$dl_search = ($dl_status && !IS_GUEST);
$new_posts = ($new_val && !IS_GUEST);
$prev_days = ($time_val != $search_all);
$new_topics = (!IS_GUEST && ($new_topics_val || isset($_GET['newposts'])));
$my_topics = ($poster_id_val && $my_topics_val);
$my_posts = ($poster_id_val && !$my_topics_val);
$title_match = ($text_match_sql && ($title_only_val || $bb_cfg['disable_ft_search_in_posts']));

// "Display as" mode (posts or topics)
$post_mode = (!$dl_search && ($display_as_val == $as_posts || isset($_GET['search_author'])));

// Start building SQL
$SQL = DB()->get_empty_sql_array();

// Displaying "as posts" mode
if ($post_mode) {
    $order = $order_opt[$order_val]['sql'];
    $sort = $sort_opt[$sort_val]['sql'];
    $per_page = $bb_cfg['posts_per_page'];
    $display_as_val = $as_posts;

    // Run initial search for post_ids
    if (!$items_found) {
        $join_t = ($title_match || $my_topics || $new_topics || in_array($order_val, array($ord_last_p, $ord_created, $ord_name, $ord_repl)));
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
            $SQL['WHERE'][] = "t.topic_id = p.topic_id";
        }

        if ($excluded_forums_csv) {
            $SQL['WHERE'][] = "$tbl.forum_id NOT IN($excluded_forums_csv)";
        }

        if ($forum_val) {
            $SQL['WHERE'][] = "$tbl.forum_id IN($forum_val)";
        }
        if ($topic_val) {
            $SQL['WHERE'][] = "$tbl.topic_id IN($topic_val)";
        }
        if ($new_posts) {
            $SQL['WHERE'][] = "$tbl.$time_field > $lastvisit";
        }
        if ($new_topics) {
            $SQL['WHERE'][] = "t.topic_time > $lastvisit";
        }
        if ($prev_days) {
            $SQL['WHERE'][] = "$tbl.$time_field > " . $time_opt[$time_val]['sql'];
        }
        if ($my_posts) {
            $SQL['WHERE'][] = "p.poster_id = $poster_id_val";
        }
        if ($my_topics) {
            $SQL['WHERE'][] = "t.topic_poster = $poster_id_val";
        }

        if ($text_match_sql) {
            $search_match_topics_csv = '';
            $title_match_topics = get_title_match_topics($text_match_sql, $forum_selected);

            if (!$search_match_topics_csv = implode(',', $title_match_topics)) {
                bb_die($lang['NO_SEARCH_MATCH']);
            }

            $where_id = ($title_match) ? 'topic_id' : 'post_id';

            $SQL['WHERE'][] = "$tbl.$where_id IN($search_match_topics_csv)";
            prevent_huge_searches($SQL);
        }

        if (!$SQL['WHERE']) {
            redirect(basename(__FILE__));
        }

        $SQL['GROUP BY'][] = "item_id";
        $SQL['ORDER BY'][] = ($new_posts && $join_p) ? "p.topic_id ASC, p.post_time ASC" : "$order $sort";
        $SQL['LIMIT'][] = "$search_limit";

        $items_display = fetch_search_ids($SQL);
    } elseif (!$items_display = array_slice($items_found, $start, $per_page)) {
        bb_die($lang['NO_SEARCH_MATCH']);
    }

    // Build SQL for displaying posts
    $excluded_forums_sql = ($excluded_forums_csv) ? " AND t.forum_id NOT IN($excluded_forums_csv) " : '';

    $sql = "
		SELECT
		  p.post_id AS item_id,
		  t.*,
		  p.*,
		  h.post_html, IF(h.post_html IS NULL, pt.post_text, NULL) AS post_text,
		  IF(p.poster_id = $anon_id, p.post_username, u.username) AS username, u.user_id, u.user_rank
		FROM       $posts_tbl
		INNER JOIN $topics_tbl     ON(t.topic_id = p.topic_id)
		INNER JOIN $posts_text_tbl ON(pt.post_id = p.post_id)
		 LEFT JOIN $posts_html_tbl ON(h.post_id = pt.post_id)
		INNER JOIN $users_tbl      ON(u.user_id = p.poster_id)
		WHERE
		      p.post_id IN(" . implode(',', $items_display) . ")
		    $excluded_forums_sql
		LIMIT $per_page
	";

    // Fetch posts data
    if (!$unsorted_rows = DB()->fetch_rowset($sql)) {
        bb_die($lang['NO_SEARCH_MATCH']);
    }
    $tmp = $sorted_rows = array();

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
    $new_tracks = array();

    foreach ($sorted_rows as $topic_id => $topic_posts) {
        // Topic title block
        $first_post = reset($topic_posts);
        $topic_id = (int)$topic_id;
        $forum_id = (int)$first_post['forum_id'];
        $is_unread_t = is_unread($first_post['topic_last_post_time'], $topic_id, $forum_id);
        $topic_title = $first_post['topic_title'];

        if (count($orig_word)) {
            $topic_title = preg_replace($orig_word, $replacement_word, $topic_title);
        }

        $template->assign_block_vars('t', array(
            'FORUM_ID' => $forum_id,
            'FORUM_NAME' => $forum_name_html[$forum_id],
            'TOPIC_ID' => $topic_id,
            'TOPIC_TITLE' => $topic_title,
            'TOPIC_ICON' => get_topic_icon($first_post, $is_unread_t),
        ));

        $quote_btn = true;
        $edit_btn = $delpost_btn = $ip_btn = (IS_AM);

        // Topic posts block
        foreach ($topic_posts as $row_num => $post) {
            $message = get_parsed_post($post);

            if (count($orig_word)) {
                $message = preg_replace($orig_word, $replacement_word, $message);
            }

            $template->assign_block_vars('t.p', array(
                'ROW_NUM' => $row_num,
                'POSTER_ID' => $post['poster_id'],
                'POSTER' => profile_url($post),
                'POST_ID' => $post['post_id'],
                'POST_DATE' => bb_date($post['post_time'], $bb_cfg['post_date_format']),
                'IS_UNREAD' => is_unread($post['post_time'], $topic_id, $forum_id),
                'MESSAGE' => $message,
                'POSTED_AFTER' => '',
                'QUOTE' => $quote_btn,
                'EDIT' => $edit_btn,
                'DELETE' => $delpost_btn,
                'IP' => $ip_btn,
            ));

            $curr_new_track_val = !empty($new_tracks[$topic_id]) ? $new_tracks[$topic_id] : 0;
            $new_tracks[$topic_id] = max($curr_new_track_val, $post['post_time']);
        }
    }
    set_tracks(COOKIE_TOPIC, $tracking_topics, $new_tracks);
} // Displaying "as topics" mode
else {
    $order = $order_opt[$order_val]['sql'];
    $sort = $sort_opt[$sort_val]['sql'];
    $per_page = $bb_cfg['topics_per_page'];
    $display_as_val = $as_topics;

    // Run initial search for topic_ids
    if (!$items_found) {
        $join_t = ($title_match || $my_topics || $new_topics || $dl_search || $new_posts || in_array($order_val, array($ord_last_p, $ord_created, $ord_name, $ord_repl)));
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
            $SQL['WHERE'][] = "t.topic_id = p.topic_id";
        }

        if ($excluded_forums_csv) {
            $SQL['WHERE'][] = "$tbl.forum_id NOT IN($excluded_forums_csv)";
        }

        if ($join_t) {
            $SQL['WHERE'][] = "t.topic_status != " . TOPIC_MOVED;
        }
        if ($forum_val) {
            $SQL['WHERE'][] = "$tbl.forum_id IN($forum_val)";
        }
        if ($topic_val) {
            $SQL['WHERE'][] = "$tbl.topic_id IN($topic_val)";
        }
        if ($new_posts) {
            $SQL['WHERE'][] = "$tbl.$time_field > $lastvisit";
        }
        if ($new_topics) {
            $SQL['WHERE'][] = "t.topic_time > $lastvisit";
        }
        if ($prev_days) {
            $SQL['WHERE'][] = "$tbl.$time_field > " . $time_opt[$time_val]['sql'];
        }
        if ($my_posts) {
            $SQL['WHERE'][] = "p.poster_id = $poster_id_val";
        }
        if ($my_posts && $user->id == $poster_id_val) {
            $SQL['WHERE'][] = "p.user_post = 1";

            if ($userdata['user_posts']) {
                $template->assign_var('BB_DIE_APPEND_MSG', '
					<form id="mod-action" method="POST" action="search.php">
						<input type="submit" name="add_my_post" value="' . $lang['RESTORE_ALL_POSTS'] . '" class="bold" onclick="if (!window.confirm( this.value +\'?\' )){ return false };" />
					</form>
					<br /><br />
					<a href="index.php">' . $lang['INDEX_RETURN'] . '</a>
				');
            }
        }
        if ($my_topics) {
            $SQL['WHERE'][] = "t.topic_poster = $poster_id_val";
        }

        if ($text_match_sql) {
            $search_match_topics_csv = '';
            $title_match_topics = get_title_match_topics($text_match_sql, $forum_selected);

            if (!$search_match_topics_csv = implode(',', $title_match_topics)) {
                bb_die($lang['NO_SEARCH_MATCH']);
            }

            $where_id = ($title_match) ? 't.topic_id' : 'p.post_id';

            $SQL['WHERE'][] = "$where_id IN($search_match_topics_csv)";
            prevent_huge_searches($SQL);
        }

        if ($join_dl) {
            $SQL['FROM'][] = $dl_stat_tbl;
        }
        if ($join_dl) {
            $SQL['WHERE'][] = "dl.topic_id = t.topic_id AND dl.user_id = $dl_user_id_val AND dl.user_status IN($dl_status_csv)";
        }

        if (!$SQL['WHERE']) {
            redirect(basename(__FILE__));
        }

        $SQL['GROUP BY'][] = "item_id";
        $SQL['LIMIT'][] = "$search_limit";

        if ($egosearch) {
            $SQL['ORDER BY'][] = 'max_post_time DESC';
        } else {
            $SQL['ORDER BY'][] = ($order_val == $ord_posted) ? "$tbl.$time_field $sort" : "$order $sort";
        }

        $items_display = fetch_search_ids($SQL);
    } elseif (!$items_display = array_slice($items_found, $start, $per_page)) {
        bb_die($lang['NO_SEARCH_MATCH']);
    }

    // Build SQL for displaying topics
    $SQL = DB()->get_empty_sql_array();
    $join_dl = ($bb_cfg['show_dl_status_in_search'] && !IS_GUEST);

    $SQL['SELECT'][] = "
		t.*, t.topic_poster AS first_user_id, u1.user_rank AS first_user_rank,
		IF(t.topic_poster = $anon_id, p1.post_username, u1.username) AS first_username,
		p2.poster_id AS last_user_id, u2.user_rank AS last_user_rank,
		IF(p2.poster_id = $anon_id, p2.post_username, u2.username) AS last_username
	";
    if ($join_dl) {
        $SQL['SELECT'][] = "dl.user_status AS dl_status";
    }

    $SQL['FROM'][] = BB_TOPICS . " t";
    $SQL['LEFT JOIN'][] = BB_POSTS . " p1 ON(t.topic_first_post_id = p1.post_id)";
    $SQL['LEFT JOIN'][] = BB_USERS . " u1 ON(t.topic_poster = u1.user_id)";
    $SQL['LEFT JOIN'][] = BB_POSTS . " p2 ON(t.topic_last_post_id = p2.post_id)";
    $SQL['LEFT JOIN'][] = BB_USERS . " u2 ON(p2.poster_id = u2.user_id)";
    if ($join_dl) {
        $SQL['LEFT JOIN'][] = BB_BT_DLSTATUS . " dl ON(dl.user_id = $user_id AND dl.topic_id = t.topic_id)";
    }

    $SQL['WHERE'][] = "t.topic_id IN(" . implode(',', $items_display) . ")";
    if ($excluded_forums_csv) {
        $SQL['WHERE'][] = "t.forum_id NOT IN($excluded_forums_csv)";
    }

    $SQL['LIMIT'][] = "$per_page";

    // Fetch topics data
    $topic_rows = array();
    foreach (DB()->fetch_rowset($SQL) as $row) {
        $topic_rows[$row['topic_id']] = $row;
    }
    if (!$topic_rows) {
        bb_die($lang['NO_SEARCH_MATCH']);
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

        $template->assign_block_vars('t', array(
            'ROW_NUM' => $row_num,
            'FORUM_ID' => $forum_id,
            'FORUM_NAME' => $forum_name_html[$forum_id],
            'TOPIC_ID' => $topic_id,
            'HREF_TOPIC_ID' => ($moved) ? $topic['topic_moved_id'] : $topic['topic_id'],
            'TOPIC_TITLE' => wbr($topic['topic_title']),
            'IS_UNREAD' => $is_unread,
            'TOPIC_ICON' => get_topic_icon($topic, $is_unread),
            'PAGINATION' => ($moved) ? '' : build_topic_pagination(TOPIC_URL . $topic_id, $topic['topic_replies'], $bb_cfg['posts_per_page']),
            'REPLIES' => $topic['topic_replies'],
            'ATTACH' => $topic['topic_attachment'],
            'STATUS' => $topic['topic_status'],
            'TYPE' => $topic['topic_type'],
            'DL' => ($topic['topic_dl_type'] == TOPIC_DL_TYPE_DL),
            'POLL' => $topic['topic_vote'],
            'DL_CLASS' => isset($topic['dl_status']) ? $dl_link_css[$topic['dl_status']] : '',

            'TOPIC_AUTHOR' => profile_url(array('username' => $topic['first_username'], 'user_id' => $topic['first_user_id'], 'user_rank' => $topic['first_user_rank'])),
            'LAST_POSTER' => profile_url(array('username' => $topic['last_username'], 'user_id' => $topic['last_user_id'], 'user_rank' => $topic['last_user_rank'])),
            'LAST_POST_TIME' => bb_date($topic['topic_last_post_time']),
            'LAST_POST_ID' => $topic['topic_last_post_id'],
        ));
    }
}

if ($items_display) {
    $items_count = count($items_found);
    $pages = (!$items_count) ? 1 : ceil($items_count / $per_page);
    $url = ($search_id) ? url_arg($url, 'id', $search_id) : $url;

    generate_pagination($url, $items_count, $per_page, $start);

    $template->assign_vars(array(
        'PAGE_TITLE' => $lang['SEARCH'],

        'SEARCH_MATCHES' => ($items_count) ? sprintf($lang['FOUND_SEARCH_MATCHES'], $items_count) : '',
        'DISPLAY_AS_POSTS' => $post_mode,

        'DL_CONTROLS' => ($dl_search && $dl_user_id_val == $user_id),
        'DL_ACTION' => 'dl_list.php',
        'MY_POSTS' => (!$post_mode && $my_posts && $user->id == $poster_id_val),
    ));

    print_page('search_results.tpl');
}

redirect(basename(__FILE__));

// ----------------------------------------------------------- //
// Functions
//
function fetch_search_ids($sql, $search_type = SEARCH_TYPE_POST)
{
    global $lang, $search_id, $session_id, $items_found, $per_page;

    $items_found = array();
    foreach (DB()->fetch_rowset($sql) as $row) {
        $items_found[] = $row['item_id'];
    }
    if (!$items_count = count($items_found)) {
        bb_die($lang['NO_SEARCH_MATCH']);
    }

    // Save results in DB
    $search_id = make_rand_str(SEARCH_ID_LENGTH);

    if ($items_count > $per_page) {
        $search_array = implode(',', $items_found);

        $save_in_db = array(
            'order',
            'sort',
            'display_as',
            'chars',
        );
        if ($GLOBALS['dl_cancel_val']) {
            $save_in_db[] = 'dl_cancel';
        }
        if ($GLOBALS['dl_compl_val']) {
            $save_in_db[] = 'dl_compl';
        }
        if ($GLOBALS['dl_down_val']) {
            $save_in_db[] = 'dl_down';
        }
        if ($GLOBALS['dl_will_val']) {
            $save_in_db[] = 'dl_will';
        }

        $curr_set = array();
        foreach ($save_in_db as $name) {
            $curr_set[$GLOBALS["{$name}_key"]] = $GLOBALS["{$name}_val"];
        }
        $search_settings = DB()->escape(serialize($curr_set));

        $columns = 'session_id,   search_type,   search_id,   search_time,    search_settings,    search_array';
        $values = "'$session_id', $search_type, '$search_id', " . TIMENOW . ", '$search_settings', '$search_array'";

        DB()->query("REPLACE INTO " . BB_SEARCH . " ($columns) VALUES ($values)");
    }

    return array_slice($items_found, 0, $per_page);
}

function prevent_huge_searches($SQL)
{
    global $bb_cfg;

    if ($bb_cfg['limit_max_search_results']) {
        $SQL['select_options'][] = 'SQL_CALC_FOUND_ROWS';
        $SQL['ORDER BY'] = array();
        $SQL['LIMIT'] = array('0');

        if (DB()->query($SQL) and $row = DB()->fetch_row("SELECT FOUND_ROWS() AS rows_count")) {
            if ($row['rows_count'] > $bb_cfg['limit_max_search_results']) {
                #				bb_log(str_compact(DB()->build_sql($SQL)) ." [{$row['rows_count']} rows]". LOG_LF, 'sql_huge_search');
                bb_die('Too_many_search_results');
            }
        }
    }
}

function username_search($search_match)
{
    global $template, $lang, $gen_simple_header;

    $username_list = '';

    if (!empty($search_match)) {
        $username_search = preg_replace('/\*/', '%', clean_username($search_match));

        $sql = "
			SELECT username
			FROM " . BB_USERS . "
			WHERE username LIKE '" . DB()->escape($username_search) . "'
				AND user_id <> " . GUEST_UID . "
			ORDER BY username
			LIMIT 200
		";

        foreach (DB()->fetch_rowset($sql) as $row) {
            $username = htmlCHR(stripslashes(html_entity_decode($row['username'])));
            $username_list .= '<option value="' . $username . '">' . $username . '</option>';
        }
        if (!$username_list) {
            $username_list = '<option>' . $lang['NO_MATCH'] . '</option>';
        }
    }

    $input_name = isset($_REQUEST['input_name']) ? htmlCHR($_REQUEST['input_name']) : 'username';

    $template->assign_vars(array(
        'TPL_SEARCH_USERNAME' => true,

        'PAGE_TITLE' => $lang['SEARCH'],
        'USERNAME' => !empty($search_match) ? htmlCHR(stripslashes(html_entity_decode($search_match))) : '',
        'INPUT_NAME' => $input_name,
        'USERNAME_OPTIONS' => $username_list,
        'SEARCH_ACTION' => "search.php?mode=searchuser&amp;input_name=$input_name",
    ));

    print_page('search.tpl', 'simple');
}
