<?php
/**
 * TorrentPier – Bull-powered BitTorrent tracker engine
 *
 * @copyright Copyright (c) 2005-2025 TorrentPier (https://torrentpier.com)
 * @link      https://github.com/torrentpier/torrentpier for the canonical source repository
 * @license   https://github.com/torrentpier/torrentpier/blob/master/LICENSE MIT License
 */

if (!empty($setmodules)) {
    $module['USERS']['ACTIONS_LOG'] = basename(__FILE__);
    return;
}

require __DIR__ . '/pagestart.php';

datastore()->enqueue([
    'moderators',
    'cat_forums',
]);

log_action()->init();

$per_page = 50;
$row_class_1 = 'row1';
$row_class_2 = 'row2';
$def_days = 3;
$def_datetime = TIMENOW;
$max_forum_name_len = 40;
$title_match_max_len = 60;
$poster_name_max_len = 25;
$select_max_height = 16;
$dt_format = 'Y-m-d';   // used in one-day filter

$url = basename(__FILE__);

// Key names
$type_key = 'type';
$forum_key = POST_FORUM_URL;
$topic_key = POST_TOPIC_URL;
$user_key = POST_USERS_URL;
$datetime_key = 'dt';   // value should be strtotime() time ("2006-06-25" etc.)
$daysback_key = 'db';
$sort_key = 'sort';
$title_match_key = 'tm';

// Key values
$all_types = 0;  // =|
$all_users = 0;  //  |> only "0" is a valid value
$all_forums = 0;  // =|

$sort_asc = 'ASC';
$sort_desc = 'DESC';

// Defaults
$def_types = $all_types;
$def_users = $all_users;
$def_forums = $all_forums;
$def_sort = $sort_desc;

// Moderators data
if (!$mod = datastore()->get('moderators')) {
    datastore()->update('moderators');
    $mod = datastore()->get('moderators');
}
array_deep($mod['moderators'], 'html_entity_decode');
array_deep($mod['admins'], 'html_entity_decode');

$users = array(__('ACTS_LOG_ALL_ACTIONS') => $all_users) + array_flip($mod['moderators']) + array_flip($mod['admins']);

unset($mod);

// Forums data
$forums = forum_tree();
$f_data = $forums['f'];

// Start
$start = request()->getInt('start');
if ($start < 0) {
    $start = abs($start);
}

// Type
$type_selected = array($def_types);
$type_csv = '';

if ($var = request()->get($type_key)) {
    $type_selected = get_id_ary($var);

    if (in_array($all_types, $type_selected)) {
        $type_selected = array($all_types);
    }
    $type_csv = implode(',', $type_selected);
    $url = ($type_csv != $def_types) ? url_arg($url, $type_key, $type_csv) : $url;
}

// User
$user_selected = array($def_users);
$user_csv = '';

if ($var = request()->get($user_key)) {
    $user_selected = get_id_ary($var);

    if (in_array($all_users, $user_selected)) {
        $user_selected = array($all_users);
    }
    $user_csv = implode(',', $user_selected);
    $url = ($user_csv != $def_users) ? url_arg($url, $user_key, $user_csv) : $url;
}

// Forum
$forum_selected = array($def_forums);
$forum_csv = '';

if ($var = request()->get($forum_key)) {
    $forum_selected = get_id_ary($var);

    if (in_array($all_forums, $forum_selected)) {
        $forum_selected = array($all_forums);
    }
    $forum_csv = implode(',', $forum_selected);
    $url = ($forum_csv != $def_forums) ? url_arg($url, $forum_key, $forum_csv) : $url;
}

// Topic
$topic_selected = null;
$topic_csv = '';

if ($var = request()->get($topic_key)) {
    $topic_selected = get_id_ary($var);
    $topic_csv = implode(',', $topic_selected);
    $url = $topic_csv ? url_arg($url, $topic_key, $topic_csv) : $url;
}

// Sort
$sort_val = $def_sort;

if (($var = request()->get($sort_key)) && $var != $def_sort) {
    $sort_val = ($var == $sort_asc) ? $sort_asc : $sort_desc;
    $url = url_arg($url, $sort_key, $sort_val);
}

// Time
$datetime_val = $def_datetime;
$daysback_val = $def_days;

if (($var = request()->get($daysback_key)) && $var != $def_days) {
    $daysback_val = max((int)$var, 1);
    $url = url_arg($url, $daysback_key, $daysback_val);
}
if (($var = request()->get($datetime_key)) && $var != $def_datetime) {
    $tz = TIMENOW + (3600 * config()->get('board_timezone'));
    if (($tmp_timestamp = strtotime($var, $tz)) > 0) {
        $datetime_val = $tmp_timestamp;
        $url = url_arg($url, $datetime_key, date($dt_format, $datetime_val));
    }
}

$time_end_val = 86400 + mktime(0, 0, 0, date('m', $datetime_val), date('d', $datetime_val), date('Y', $datetime_val));
$time_start_val = $time_end_val - 86400 * $daysback_val;

// First log time
$row = DB()->fetch_row('SELECT MIN(log_time) AS first_log_time FROM ' . BB_LOG);
$first_log_time = (int)$row['first_log_time'];

// Title match
$title_match_val = $title_match_sql = '';

if ($var = request()->get($title_match_key)) {
    if ($tmp_title_match = substr(urldecode(trim($var)), 0, $title_match_max_len)) {
        $title_match_sql = DB()->escape($tmp_title_match);
        $url = url_arg($url, $title_match_key, urlencode($tmp_title_match));
    }
}

// SQL
$where = " WHERE l.log_time BETWEEN '$time_start_val' AND '$time_end_val'";
$where .= $type_csv ? " AND l.log_type_id IN($type_csv)" : '';
$where .= $user_csv ? " AND l.log_user_id IN($user_csv)" : '';
$where .= $forum_csv ? " AND l.log_forum_id IN($forum_csv)" : '';
$where .= $topic_csv ? " AND l.log_topic_id IN($topic_csv)" : '';
$where .= $title_match_sql ? " AND MATCH (l.log_topic_title) AGAINST ('$title_match_sql' IN BOOLEAN MODE)" : '';

$sql = 'SELECT l.*, u.*
	FROM ' . BB_LOG . ' l
	LEFT JOIN ' . BB_USERS . " u ON(u.user_id = l.log_user_id)
	$where
	ORDER BY l.log_time
	$sort_val
	LIMIT $start, " . ($per_page + 1);

$log_rowset = DB()->fetch_rowset($sql);
$log_count = count($log_rowset);

if ($log_count == $per_page + 1) {
    $items_count = $start + ($per_page * 2);
    $pages = '?';
    array_pop($log_rowset);
} else {
    $items_count = $start + $log_count;
    $pages = (!$log_count) ? 1 : ceil($items_count / $per_page);
}

generate_pagination($url, $items_count, $per_page, $start);

$filter = [];

if ($log_rowset) {
    $log_type = log_action()->log_type;
    $log_type_flip = array_flip($log_type);

    foreach ($log_rowset as $row_num => $row) {
        $msg = '';
        $forum_name = $forum_name_new = '';
        $topic_title = $topic_title_new = '';

        $topic_deleted = ($row['log_type_id'] == $log_type['mod_topic_delete']);

        switch ($row['log_type_id']) {
            case $log_type['mod_topic_delete']:
            case $log_type['mod_topic_move']:
            case $log_type['mod_topic_lock']:
            case $log_type['mod_topic_unlock']:
            case $log_type['mod_topic_set_downloaded']:
            case $log_type['mod_topic_unset_downloaded']:
            case $log_type['mod_topic_change_tor_status']:
            case $log_type['mod_topic_change_tor_type']:
            case $log_type['mod_topic_tor_unregister']:
            case $log_type['mod_topic_tor_register']:
            case $log_type['mod_topic_tor_delete']:
            case $log_type['mod_topic_renamed']:
            case $log_type['mod_post_delete']:
            case $log_type['mod_post_pin']:
            case $log_type['mod_post_unpin']:
            case $log_type['mod_topic_split']:
            case $log_type['mod_topic_poll_started']:
            case $log_type['mod_topic_poll_finished']:
            case $log_type['mod_topic_poll_deleted']:
            case $log_type['mod_topic_poll_added']:
            case $log_type['mod_topic_poll_edited']:
                // topic_title
                if (!empty($row['log_topic_title'])) {
                    $topic_title = $row['log_topic_title'];
                }
                // topic_title_new
                if (!empty($row['log_topic_title_new'])) {
                    $topic_title_new = $row['log_topic_title_new'];
                }
                // forum_name
                if ($fid =& $row['log_forum_id']) {
                    $forum_name = ($fname =& $f_data[$fid]['forum_name']) ? $fname : 'id:' . $row['log_forum_id'];
                }
                // forum_name_new
                if ($fid =& $row['log_forum_id_new']) {
                    $forum_name_new = ($fname =& $f_data[$fid]['forum_name']) ? $fname : 'id:' . $row['log_forum_id'];
                }

                break;
        }

        $msg .= " {$row['log_msg']}";

        $row_class = !($row_num & 1) ? $row_class_1 : $row_class_2;

        $datetime_href_s = url_arg($url, $datetime_key, date($dt_format, $row['log_time']));
        $datetime_href_s = url_arg($datetime_href_s, $daysback_key, 1);

        template()->assign_block_vars('log', array(
            'ACTION_DESC' => __('LOG_ACTION')['LOG_TYPE'][$log_type_flip[$row['log_type_id']]],
            'ACTION_HREF_S' => url_arg($url, $type_key, $row['log_type_id']),

            'USER_ID' => $row['log_user_id'],
            'USERNAME' => profile_url($row, true),
            'USER_HREF_S' => url_arg($url, $user_key, $row['log_user_id']),
            'USER_IP' => \TorrentPier\Helpers\IPHelper::decode($row['log_user_ip']),

            'FORUM_ID' => $row['log_forum_id'],
            'FORUM_HREF' => BB_ROOT . FORUM_URL . $row['log_forum_id'],
            'FORUM_HREF_S' => url_arg($url, $forum_key, $row['log_forum_id']),
            'FORUM_NAME' => htmlCHR($forum_name),

            'FORUM_ID_NEW' => $row['log_forum_id_new'],
            'FORUM_HREF_NEW' => BB_ROOT . FORUM_URL . $row['log_forum_id_new'],
            'FORUM_HREF_NEW_S' => url_arg($url, $forum_key, $row['log_forum_id_new']),
            'FORUM_NAME_NEW' => htmlCHR($forum_name_new),

            'TOPIC_ID' => $row['log_topic_id'],
            'TOPIC_HREF' => (!$topic_deleted) ? BB_ROOT . TOPIC_URL . $row['log_topic_id'] : '',
            'TOPIC_HREF_S' => url_arg($url, $topic_key, $row['log_topic_id']),
            'TOPIC_TITLE' => $topic_title,

            'TOPIC_ID_NEW' => $row['log_topic_id_new'],
            'TOPIC_HREF_NEW' => BB_ROOT . TOPIC_URL . $row['log_topic_id_new'],
            'TOPIC_HREF_NEW_S' => url_arg($url, $topic_key, $row['log_topic_id_new']),
            'TOPIC_TITLE_NEW' => $topic_title_new,

            'DATETIME' => bb_date($row['log_time'], 'd-M-y H:i'),
            'DATETIME_HREF_S' => $datetime_href_s,
            'MSG' => $msg,
            'ROW_CLASS' => $row_class,

        ));

        // Topics
        if ($topic_csv && empty($filter['topics'][$row['log_topic_title']])) {
            template()->assign_block_vars('topics', array(
                'TOPIC_TITLE' => $row['log_topic_title'],
            ));
            $filter['topics'][$row['log_topic_title']] = true;
        }
        // Forums
        if ($forum_csv && empty($filter['forums'][$forum_name])) {
            template()->assign_block_vars('forums', array(
                'FORUM_NAME' => htmlCHR($forum_name),
            ));
            $filter['forums'][$forum_name] = true;
        }
        // Users
        if ($user_csv && empty($filter['users'])) {
            template()->assign_block_vars('users', array(
                'USERNAME' => profile_url($row, true),
            ));
            $filter['users'] = true;
        }
    }

    template()->assign_vars(array(
        'FILTERS' => $topic_csv || $forum_csv || $user_csv,
        'FILTER_TOPICS' => !empty($filter['topics']),
        'FILTER_FORUMS' => !empty($filter['forums']),
        'FILTER_USERS' => !empty($filter['users']),
    ));
} else {
    template()->assign_block_vars('log_not_found', []);
}

// Select
$log_type_select = array(__('ACTS_LOG_ALL_ACTIONS') => $all_types) + log_action()->log_type_select;

template()->assign_vars(array(
    'LOG_COLSPAN' => 4,

    'DATETIME_NAME' => $datetime_key,
    'DATETIME_VAL' => date('Y-m-d', $datetime_val),
    'DAYSBACK_NAME' => $daysback_key,
    'DAYSBACK_VAL' => $daysback_val,
    'FIRST_LOG_TIME' => $first_log_time ? date('Y-m-d', $first_log_time) : __('ACC_NONE'),

    'TITLE_MATCH_MAX' => $title_match_max_len,
    'TITLE_MATCH_NAME' => $title_match_key,
    'TITLE_MATCH_VAL' => $title_match_val,

    'SORT_NAME' => $sort_key,
    'SORT_ASC' => $sort_asc,
    'SORT_DESC' => $sort_desc,
    'SORT_ASC_CHECKED' => ($sort_val == $sort_asc) ? HTML_CHECKED : '',
    'SORT_DESC_CHECKED' => ($sort_val == $sort_desc) ? HTML_CHECKED : '',

    'SEL_FORUM' => get_forum_select('admin', "{$forum_key}[]", $forum_selected, $max_forum_name_len, $select_max_height, '', $all_forums),
    'SEL_LOG_TYPE' => build_select("{$type_key}[]", $log_type_select, $type_selected, 60, $select_max_height),
    'SEL_USERS' => build_select("{$user_key}[]", $users, $user_selected, 16, $select_max_height),

    'S_LOG_ACTION' => 'admin_log.php',
    'TOPIC_CSV' => $topic_csv,
));

print_page('admin_log.tpl', 'admin');
