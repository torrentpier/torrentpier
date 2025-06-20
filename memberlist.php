<?php
/**
 * TorrentPier – Bull-powered BitTorrent tracker engine
 *
 * @copyright Copyright (c) 2005-2025 TorrentPier (https://torrentpier.com)
 * @link      https://github.com/torrentpier/torrentpier for the canonical source repository
 * @license   https://github.com/torrentpier/torrentpier/blob/master/LICENSE MIT License
 */

define('BB_SCRIPT', 'memberlist');

require __DIR__ . '/common.php';

$user->session_start(['req_login' => true]);

$start = abs((int)request_var('start', 0));
$mode = (string)request_var('mode', 'joined');
$sort_order = (request_var('order', 'ASC') == 'ASC') ? 'ASC' : 'DESC';
$username = trim(request_var('username', ''));
$role = (string)request_var('role', 'all');

// Memberlist sorting
$mode_types_text = [$lang['SORT_JOINED'], $lang['SORT_USERNAME'], $lang['SORT_LOCATION'], $lang['SORT_POSTS'], $lang['SORT_EMAIL'], $lang['SORT_WEBSITE'], $lang['SORT_TOP_TEN']];
$mode_types = ['joined', 'username', 'location', 'posts', 'email', 'website', 'topten'];

$select_sort_mode = '<select name="mode">';
for ($i = 0, $iMax = count($mode_types_text); $i < $iMax; $i++) {
    $selected = ($mode == $mode_types[$i]) ? ' selected' : '';
    $select_sort_mode .= '<option value="' . $mode_types[$i] . '"' . $selected . '>' . $mode_types_text[$i] . '</option>';
}
$select_sort_mode .= '</select>';

$select_sort_order = '<select name="order">';
if ($sort_order == 'ASC') {
    $select_sort_order .= '<option value="ASC" selected>' . $lang['ASC'] . '</option><option value="DESC">' . $lang['DESC'] . '</option>';
} else {
    $select_sort_order .= '<option value="ASC">' . $lang['ASC'] . '</option><option value="DESC" selected>' . $lang['DESC'] . '</option>';
}
$select_sort_order .= '</select>';

// Role selector
$role_select = [
    'all' => $lang['ALL'],
    'user' => $lang['AUTH_USER'],
    'admin' => $lang['AUTH_ADMIN'],
    'moderator' => $lang['MODERATOR']
];
$select_sort_role = '<select name="role">';
foreach ($role_select as $key => $value) {
    $selected = ($role == $key) ? ' selected' : '';
    $select_sort_role .= '<option value="' . $key . '"' . $selected . '>' . $value . '</option>';
}
$select_sort_role .= '</select>';

switch ($mode) {
    case 'username':
        $order_by = "username $sort_order LIMIT $start, " . config()->get('topics_per_page');
        break;
    case 'location':
        $order_by = "user_from $sort_order LIMIT $start, " . config()->get('topics_per_page');
        break;
    case 'posts':
        $order_by = "user_posts $sort_order LIMIT $start, " . config()->get('topics_per_page');
        break;
    case 'email':
        $order_by = "user_email $sort_order LIMIT $start, " . config()->get('topics_per_page');
        break;
    case 'website':
        $order_by = "user_website $sort_order LIMIT $start, " . config()->get('topics_per_page');
        break;
    case 'topten':
        $order_by = "user_posts $sort_order LIMIT 10";
        break;
    case 'joined':
    default:
        $order_by = "user_regdate $sort_order LIMIT $start, " . config()->get('topics_per_page');
        break;
}

$where_sql = '';

// Search by role
switch ($role) {
    case 'user':
        $where_sql .= ' AND user_level = ' . USER;
        break;
    case 'admin':
        $where_sql .= ' AND user_level = ' . ADMIN;
        break;
    case 'moderator':
        $where_sql .= ' AND user_level = ' . MOD;
        break;
}

// Search by username
if (!empty($username)) {
    $where_sql .= ' AND username LIKE "' . DB()->escape(str_replace('*', '%', clean_username($username))) . '"';
}

// Generate user information
$sql = "SELECT username, user_id, user_rank, user_opt, user_posts, user_regdate, user_from, user_website, user_email, avatar_ext_id FROM " . BB_USERS . " WHERE user_id NOT IN(" . EXCLUDED_USERS . ") $where_sql ORDER BY $order_by";
if ($result = DB()->fetch_rowset($sql)) {
    foreach ($result as $i => $row) {
        $user_id = $row['user_id'];
        $user_info = generate_user_info($row);

        $row_class = !($i % 2) ? 'row1' : 'row2';
        $template->assign_block_vars('memberrow', [
            'ROW_NUMBER' => $i + ($start + 1),
            'ROW_CLASS' => $row_class,
            'USER' => profile_url($row),
            'AVATAR' => $user_info['avatar'],
            'FROM' => $user_info['from'],
            'JOINED' => $user_info['joined'],
            'POSTS' => $user_info['posts'],
            'PM' => $user_info['pm'],
            'EMAIL' => $user_info['email'],
            'WWW' => $user_info['www'],
            'U_VIEWPROFILE' => PROFILE_URL . $user_id
        ]);
    }
} else {
    $template->assign_block_vars('no_username', ['NO_USER_ID_SPECIFIED' => $lang['NO_USER_ID_SPECIFIED']]);
}

// Pagination
$paginationurl = "memberlist.php?mode=$mode&amp;order=$sort_order&amp;role=$role";
$paginationurl .= !empty($username) ? "&amp;username=$username" : '';

if ($mode != 'topten') {
    $sql = "SELECT COUNT(*) AS total FROM " . BB_USERS . " WHERE user_id NOT IN(" . EXCLUDED_USERS . ") $where_sql";
    if (!$result = DB()->sql_query($sql)) {
        bb_die('Error getting total users');
    }
    if ($total = DB()->sql_fetchrow($result)) {
        $total_members = $total['total'];
        generate_pagination($paginationurl, $total_members, config()->get('topics_per_page'), $start);
    }
    DB()->sql_freeresult($result);
}

// Generate output
$template->assign_vars([
    'PAGE_TITLE' => $lang['MEMBERLIST'],
    'S_MODE_SELECT' => $select_sort_mode,
    'S_ORDER_SELECT' => $select_sort_order,
    'S_ROLE_SELECT' => $select_sort_role,
    'S_MODE_ACTION' => "memberlist.php?mode=$mode&amp;order=$sort_order&amp;role=$role",
    'S_USERNAME' => $username,
]);

print_page('memberlist.tpl');
