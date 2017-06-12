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

define('BB_SCRIPT', 'index');
define('BB_ROOT', './');
require __DIR__ . '/common.php';

$page_cfg['load_tpl_vars'] = array(
    'post_icons',
);

$show_last_topic = true;
$last_topic_max_len = 28;
$show_online_users = true;
$show_subforums = true;

$datastore->enqueue(array(
    'stats',
    'moderators',
    'cat_forums',
));
if ($bb_cfg['show_latest_news']) {
    $datastore->enqueue('latest_news');
}
if ($bb_cfg['show_network_news']) {
    $datastore->enqueue('network_news');
}

// Init userdata
$user->session_start();

// Init main vars
$viewcat = isset($_GET['c']) ? (int)$_GET['c'] : 0;
$lastvisit = (IS_GUEST) ? TIMENOW : $userdata['user_lastvisit'];

// Caching output
$req_page = 'index_page';
$req_page .= ($viewcat) ? "_c{$viewcat}" : '';

define('REQUESTED_PAGE', $req_page);
caching_output(IS_GUEST, 'send', REQUESTED_PAGE . '_guest_' . $bb_cfg['default_lang']);

$hide_cat_opt = isset($user->opt_js['h_cat']) ? (string)$user->opt_js['h_cat'] : 0;
$hide_cat_user = array_flip(explode('-', $hide_cat_opt));
$showhide = isset($_GET['sh']) ? (int)$_GET['sh'] : 0;

// Topics read tracks
$tracking_topics = get_tracks('topic');
$tracking_forums = get_tracks('forum');

// Statistics
if (!$stats = $datastore->get('stats')) {
    $datastore->update('stats');
    $stats = $datastore->get('stats');
}

// Forums data
if (!$forums = $datastore->get('cat_forums')) {
    $datastore->update('cat_forums');
    $forums = $datastore->get('cat_forums');
}
$cat_title_html = $forums['cat_title_html'];
$forum_name_html = $forums['forum_name_html'];

$anon = GUEST_UID;
$excluded_forums_csv = $user->get_excluded_forums(AUTH_VIEW);
$only_new = $user->opt_js['only_new'];

// Validate requested category id
if ($viewcat && !($viewcat =& $forums['c'][$viewcat]['cat_id'])) {
    redirect("index.php");
}

// Forums
$forums_join_sql = 'f.cat_id = c.cat_id';
$forums_join_sql .= ($viewcat) ? "
	AND f.cat_id = $viewcat
" : '';
$forums_join_sql .= ($excluded_forums_csv) ? "
	AND f.forum_id NOT IN($excluded_forums_csv)
	AND f.forum_parent NOT IN($excluded_forums_csv)
" : '';

// Posts
$posts_join_sql = "p.post_id = f.forum_last_post_id";
$posts_join_sql .= ($only_new == ONLY_NEW_POSTS) ? "
	AND p.post_time > $lastvisit
" : '';
$join_p_type = ($only_new == ONLY_NEW_POSTS) ? 'INNER JOIN' : 'LEFT JOIN';

// Topics
$topics_join_sql = "t.topic_last_post_id = p.post_id";
$topics_join_sql .= ($only_new == ONLY_NEW_TOPICS) ? "
	AND t.topic_time > $lastvisit
" : '';
$join_t_type = ($only_new == ONLY_NEW_TOPICS) ? 'INNER JOIN' : 'LEFT JOIN';

$sql = "
	SELECT SQL_CACHE
		f.cat_id, f.forum_id, f.forum_status, f.forum_parent, f.show_on_index,
		p.post_id AS last_post_id, p.post_time AS last_post_time,
		t.topic_id AS last_topic_id, t.topic_title AS last_topic_title,
		u.user_id AS last_post_user_id, u.user_rank AS last_post_user_rank,
		IF(p.poster_id = $anon, p.post_username, u.username) AS last_post_username
	FROM         " . BB_CATEGORIES . " c
	INNER JOIN   " . BB_FORUMS . " f ON($forums_join_sql)
	$join_p_type " . BB_POSTS . " p ON($posts_join_sql)
	$join_t_type " . BB_TOPICS . " t ON($topics_join_sql)
	LEFT JOIN    " . BB_USERS . " u ON(u.user_id = p.poster_id)
	ORDER BY c.cat_order, f.forum_order
";

$replace_in_parent = array(
    'last_post_id',
    'last_post_time',
    'last_post_user_id',
    'last_post_username',
    'last_post_user_rank',
    'last_topic_title',
    'last_topic_id',
);

$cache_name = 'index_sql_' . md5($sql);
if (!$cat_forums = CACHE('bb_cache')->get($cache_name)) {
    $cat_forums = [];
    foreach (DB()->fetch_rowset($sql) as $row) {
        if (!($cat_id = $row['cat_id']) || !($forum_id = $row['forum_id'])) {
            continue;
        }

        if ($parent_id = $row['forum_parent']) {
            if (!$parent =& $cat_forums[$cat_id]['f'][$parent_id]) {
                $parent = $forums['f'][$parent_id];
                $parent['last_post_time'] = 0;
            }
            if ($row['last_post_time'] > $parent['last_post_time']) {
                foreach ($replace_in_parent as $key) {
                    $parent[$key] = $row[$key];
                }
            }
            if ($show_subforums && $row['show_on_index']) {
                $parent['last_sf_id'] = $forum_id;
            } else {
                continue;
            }
        } else {
            $f =& $forums['f'][$forum_id];
            $row['forum_desc'] = $f['forum_desc'];
            $row['forum_posts'] = $f['forum_posts'];
            $row['forum_topics'] = $f['forum_topics'];
        }
        $cat_forums[$cat_id]['f'][$forum_id] = $row;
    }
    CACHE('bb_cache')->set($cache_name, $cat_forums, 180);
    unset($row, $forums);
    $datastore->rm('cat_forums');
}

// Obtain list of moderators
$moderators = array();
if (!$mod = $datastore->get('moderators')) {
    $datastore->update('moderators');
    $mod = $datastore->get('moderators');
}

if (!empty($mod)) {
    foreach ($mod['mod_users'] as $forum_id => $user_ids) {
        foreach ($user_ids as $user_id) {
            $moderators[$forum_id][] = '<a href="' . PROFILE_URL . $user_id . '">' . $mod['name_users'][$user_id] . '</a>';
        }
    }
    foreach ($mod['mod_groups'] as $forum_id => $group_ids) {
        foreach ($group_ids as $group_id) {
            $moderators[$forum_id][] = '<a href="' . GROUP_URL . $group_id . '">' . $mod['name_groups'][$group_id] . '</a>';
        }
    }
}

unset($mod);
$datastore->rm('moderators');

// Build index page
$forums_count = 0;
foreach ($cat_forums as $cid => $c) {
    $template->assign_block_vars('h_c', array(
        'H_C_ID' => $cid,
        'H_C_TITLE' => $cat_title_html[$cid],
        'H_C_CHEKED' => in_array($cid, preg_split("/[-]+/", $hide_cat_opt)) ? 'checked' : '',
    ));

    $template->assign_vars(array(
        'H_C_AL_MESS' => ($hide_cat_opt && !$showhide),
    ));

    if (!$showhide && isset($hide_cat_user[$cid]) && !$viewcat) {
        continue;
    }

    $template->assign_block_vars('c', array(
        'CAT_ID' => $cid,
        'CAT_TITLE' => $cat_title_html[$cid],
        'U_VIEWCAT' => CAT_URL . $cid,
    ));

    foreach ($c['f'] as $fid => $f) {
        if (!$fname_html =& $forum_name_html[$fid]) {
            continue;
        }
        $is_sf = $f['forum_parent'];

        $forums_count++;
        $new = is_unread($f['last_post_time'], $f['last_topic_id'], $f['forum_id']) ? '_new' : '';
        $folder_image = ($is_sf) ? $images["icon_minipost{$new}"] : $images["forum{$new}"];

        if ($f['forum_status'] == FORUM_LOCKED) {
            $folder_image = ($is_sf) ? $images['icon_minipost'] : $images['forum_locked'];
        }

        if ($is_sf) {
            $template->assign_block_vars('c.f.sf', array(
                'SF_ID' => $fid,
                'SF_NAME' => $fname_html,
                'SF_NEW' => $new ? ' new' : '',
            ));
            continue;
        }

        $template->assign_block_vars('c.f', array(
            'FORUM_FOLDER_IMG' => $folder_image,
            'FORUM_ID' => $fid,
            'FORUM_NAME' => $fname_html,
            'FORUM_DESC' => $f['forum_desc'],
            'POSTS' => commify($f['forum_posts']),
            'TOPICS' => commify($f['forum_topics']),
            'LAST_SF_ID' => isset($f['last_sf_id']) ? $f['last_sf_id'] : null,
            'MODERATORS' => isset($moderators[$fid]) ? implode(', ', $moderators[$fid]) : '',
            'FORUM_FOLDER_ALT' => ($new) ? $lang['NEW'] : $lang['OLD'],
        ));

        if ($f['last_post_id']) {
            $template->assign_block_vars('c.f.last', array(
                'LAST_TOPIC_ID' => $f['last_topic_id'],
                'LAST_TOPIC_TIP' => $f['last_topic_title'],
                'LAST_TOPIC_TITLE' => wbr(str_short($f['last_topic_title'], $last_topic_max_len)),
                'LAST_POST_TIME' => bb_date($f['last_post_time'], $bb_cfg['last_post_date_format']),
                'LAST_POST_USER' => profile_url(array('username' => str_short($f['last_post_username'], 15), 'user_id' => $f['last_post_user_id'], 'user_rank' => $f['last_post_user_rank'])),
            ));
        }
    }
}

$template->assign_vars(array(
    'SHOW_FORUMS' => $forums_count,
    'SHOW_MAP' => (isset($_GET['map']) && !IS_GUEST),
    'PAGE_TITLE' => ($viewcat) ? $cat_title_html[$viewcat] : $lang['HOME'],
    'NO_FORUMS_MSG' => ($only_new) ? $lang['NO_NEW_POSTS'] : $lang['NO_FORUMS'],

    'TOTAL_TOPICS' => sprintf($lang['POSTED_TOPICS_TOTAL'], $stats['topiccount']),
    'TOTAL_POSTS' => sprintf($lang['POSTED_ARTICLES_TOTAL'], $stats['postcount']),
    'TOTAL_USERS' => sprintf($lang['REGISTERED_USERS_TOTAL'], $stats['usercount']),
    'TOTAL_GENDER' => $bb_cfg['gender'] ? sprintf(
        $lang['USERS_TOTAL_GENDER'],
        $stats['male'],
        $stats['female'],
        $stats['unselect']
    ) : '',
    'NEWEST_USER' => sprintf($lang['NEWEST_USER'], profile_url($stats['newestuser'])),

    // Tracker stats
    'TORRENTS_STAT' => $bb_cfg['tor_stats'] ? sprintf(
        $lang['TORRENTS_STAT'],
        $stats['torrentcount'],
        humn_size($stats['size'])
    ) : '',
    'PEERS_STAT' => $bb_cfg['tor_stats'] ? sprintf(
        $lang['PEERS_STAT'],
        $stats['peers'],
        $stats['seeders'],
        $stats['leechers']
    ) : '',
    'SPEED_STAT' => $bb_cfg['tor_stats'] ? sprintf(
        $lang['SPEED_STAT'],
        humn_size($stats['speed']) . '/s'
    ) : '',
    'SHOW_MOD_INDEX' => $bb_cfg['show_mod_index'],
    'FORUM_IMG' => $images['forum'],
    'FORUM_NEW_IMG' => $images['forum_new'],
    'FORUM_LOCKED_IMG' => $images['forum_locked'],

    'SHOW_ONLY_NEW_MENU' => true,
    'ONLY_NEW_POSTS_ON' => ($only_new == ONLY_NEW_POSTS),
    'ONLY_NEW_TOPICS_ON' => ($only_new == ONLY_NEW_TOPICS),

    'U_SEARCH_NEW' => "search.php?new=1",
    'U_SEARCH_SELF_BY_MY' => "search.php?uid={$userdata['user_id']}&amp;o=1",
    'U_SEARCH_LATEST' => "search.php?search_id=latest",
    'U_SEARCH_UNANSWERED' => "search.php?search_id=unanswered",

    'SHOW_LAST_TOPIC' => $show_last_topic,
));

// Set tpl vars for bt_userdata
if ($bb_cfg['bt_show_dl_stat_on_index'] && !IS_GUEST) {
    show_bt_userdata($userdata['user_id']);
}

// Latest news
if ($bb_cfg['show_latest_news']) {
    if (!$latest_news = $datastore->get('latest_news')) {
        $datastore->update('latest_news');
        $latest_news = $datastore->get('latest_news');
    }

    $template->assign_vars(array(
        'SHOW_LATEST_NEWS' => true,
    ));

    foreach ($latest_news as $news) {
        $template->assign_block_vars('news', array(
            'NEWS_TOPIC_ID' => $news['topic_id'],
            'NEWS_TITLE' => str_short($news['topic_title'], $bb_cfg['max_news_title']),
            'NEWS_TIME' => bb_date($news['topic_time'], 'd-M', false),
            'NEWS_IS_NEW' => is_unread($news['topic_time'], $news['topic_id'], $news['forum_id']),
        ));
    }
}

// Network news
if ($bb_cfg['show_network_news']) {
    if (!$network_news = $datastore->get('network_news')) {
        $datastore->update('network_news');
        $network_news = $datastore->get('network_news');
    }

    $template->assign_vars(array(
        'SHOW_NETWORK_NEWS' => true,
    ));

    foreach ($network_news as $net) {
        $template->assign_block_vars('net', array(
            'NEWS_TOPIC_ID' => $net['topic_id'],
            'NEWS_TITLE' => str_short($net['topic_title'], $bb_cfg['max_net_title']),
            'NEWS_TIME' => bb_date($net['topic_time'], 'd-M', false),
            'NEWS_IS_NEW' => is_unread($net['topic_time'], $net['topic_id'], $net['forum_id']),
        ));
    }
}

if ($bb_cfg['birthday_check_day'] && $bb_cfg['birthday_enabled']) {
    $week_list = $today_list = array();
    $week_all = $today_all = false;

    if (isset($stats['birthday_week_list'])) {
        shuffle($stats['birthday_week_list']);
        foreach ($stats['birthday_week_list'] as $i => $week) {
            if ($i >= 5) {
                $week_all = true;
                continue;
            }
            $week_list[] = profile_url($week) . ' <span class="small">(' . birthday_age($week['user_birthday'] - 1) . ')</span>';
        }
        $week_all = ($week_all) ? '&nbsp;<a class="txtb" href="#" onclick="ajax.exec({action: \'index_data\', mode: \'birthday_week\'}); return false;" title="' . $lang['ALL'] . '">...</a>' : '';
        $week_list = sprintf($lang['BIRTHDAY_WEEK'], $bb_cfg['birthday_check_day'], implode(', ', $week_list)) . $week_all;
    } else {
        $week_list = sprintf($lang['NOBIRTHDAY_WEEK'], $bb_cfg['birthday_check_day']);
    }

    if (isset($stats['birthday_today_list'])) {
        shuffle($stats['birthday_today_list']);
        foreach ($stats['birthday_today_list'] as $i => $today) {
            if ($i >= 5) {
                $today_all = true;
                continue;
            }
            $today_list[] = profile_url($today) . ' <span class="small">(' . birthday_age($today['user_birthday']) . ')</span>';
        }
        $today_all = ($today_all) ? '&nbsp;<a class="txtb" href="#" onclick="ajax.exec({action: \'index_data\', mode: \'birthday_today\'}); return false;" title="' . $lang['ALL'] . '">...</a>' : '';
        $today_list = $lang['BIRTHDAY_TODAY'] . implode(', ', $today_list) . $today_all;
    } else {
        $today_list = $lang['NOBIRTHDAY_TODAY'];
    }

    $template->assign_vars(array(
        'WHOSBIRTHDAY_WEEK' => $week_list,
        'WHOSBIRTHDAY_TODAY' => $today_list,
    ));
}

// Allow cron
if (IS_AM) {
    if (file_exists(CRON_RUNNING)) {
        if (file_exists(CRON_ALLOWED)) {
            unlink(CRON_ALLOWED);
        }
        rename(CRON_RUNNING, CRON_ALLOWED);
    }
}

// Display page
define('SHOW_ONLINE', $show_online_users);

if (isset($_GET['map'])) {
    $template->assign_vars(array('PAGE_TITLE' => $lang['FORUM_MAP']));
}

print_page('index.tpl');
