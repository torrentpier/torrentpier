<?php
/**
 * TorrentPier – Bull-powered BitTorrent tracker engine
 *
 * @copyright Copyright (c) 2005-2023 TorrentPier (https://torrentpier.com)
 * @link      https://github.com/torrentpier/torrentpier for the canonical source repository
 * @license   https://github.com/torrentpier/torrentpier/blob/master/LICENSE MIT License
 */

define('BB_SCRIPT', 'memberlist');

require __DIR__ . '/common.php';

$page_cfg['use_tablesorter'] = true;

$user->session_start(['req_login' => true]);

$start = abs((int)request_var('start', 0));
$mode = (string)request_var('mode', 'joined');
$sort_order = (request_var('order', 'ASC') == 'ASC') ? 'ASC' : 'DESC';
$username = request_var('username', '');
$paginationusername = $username;

//
// Memberlist sorting
//
$mode_types_text = [
    $lang['SORT_JOINED'],
    $lang['SORT_USERNAME'],
    $lang['SORT_LOCATION'],
    $lang['SORT_POSTS'],
    $lang['SORT_EMAIL'],
    $lang['SORT_WEBSITE'],
    $lang['SORT_TOP_TEN']
];

$mode_types = [
    'joined',
    'username',
    'location',
    'posts',
    'email',
    'website',
    'topten'
];

// <select> mode
$select_sort_mode = '<select name="mode">';

for ($i = 0, $iMax = count($mode_types_text); $i < $iMax; $i++) {
    $selected = ($mode == $mode_types[$i]) ? ' selected' : '';
    $select_sort_mode .= '<option value="' . $mode_types[$i] . '"' . $selected . '>' . $mode_types_text[$i] . '</option>';
}
$select_sort_mode .= '</select>';

// <select> order
$select_sort_order = '<select name="order">';

if ($sort_order == 'ASC') {
    $select_sort_order .= '<option value="ASC" selected>' . $lang['ASC'] . '</option><option value="DESC">' . $lang['DESC'] . '</option>';
} else {
    $select_sort_order .= '<option value="ASC">' . $lang['ASC'] . '</option><option value="DESC" selected>' . $lang['DESC'] . '</option>';
}
$select_sort_order .= '</select>';

switch ($mode) {
    case 'joined':
        $order_by = "user_id $sort_order LIMIT $start, " . $bb_cfg['topics_per_page'];
        break;
    case 'username':
        $order_by = "username $sort_order LIMIT $start, " . $bb_cfg['topics_per_page'];
        break;
    case 'location':
        $order_by = "user_from $sort_order LIMIT $start, " . $bb_cfg['topics_per_page'];
        break;
    case 'posts':
        $order_by = "user_posts $sort_order LIMIT $start, " . $bb_cfg['topics_per_page'];
        break;
    case 'email':
        $order_by = "user_email $sort_order LIMIT $start, " . $bb_cfg['topics_per_page'];
        break;
    case 'website':
        $order_by = "user_website $sort_order LIMIT $start, " . $bb_cfg['topics_per_page'];
        break;
    case 'topten':
        $order_by = "user_posts $sort_order LIMIT 10";
        break;
    default:
        $order_by = "user_regdate $sort_order LIMIT $start, " . $bb_cfg['topics_per_page'];
        $mode = 'joined';
        break;
}

// per-letter selection
$by_letter = 'all';
$letters_range = 'a-z';
$letters_range .= iconv('windows-1251', 'UTF-8', chr(224));
$letters_range .= '-';
$letters_range .= iconv('windows-1251', 'UTF-8', chr(255));
$select_letter = $letter_sql = '';

$by_letter_req = isset($_REQUEST['letter']) ? strtolower(trim($_REQUEST['letter'])) : false;

if ($by_letter_req) {
    if ($by_letter_req === 'all') {
        $by_letter = 'all';
        $letter_sql = '';
    } elseif ($by_letter_req === 'others') {
        $by_letter = 'others';
        $letter_sql = "username REGEXP '^[!-@\\[-`].*$'";
    } elseif ($letter_req = preg_replace("#[^$letters_range]#ui", '', iconv('windows-1251', 'UTF-8', $by_letter_req))) {
        $by_letter = DB()->escape($letter_req);
        $letter_sql = "LOWER(username) LIKE '$by_letter%'";
    }
}

// ENG
for ($i = ord('A'), $cnt = ord('Z'); $i <= $cnt; $i++) {
    $select_letter .= (strtoupper($by_letter) == chr($i)) ? '<b>' . chr($i) . '</b>&nbsp;' : '<a class="genmed" href="' . ("memberlist.php?letter=" . chr($i) . "&amp;mode=$mode&amp;order=$sort_order") . '">' . chr($i) . '</a>&nbsp;';
}
// RUS
$select_letter .= ': ';
for ($i = 224, $cnt = 255; $i <= $cnt; $i++) {
    $select_letter .= (strtoupper($by_letter) == iconv('windows-1251', 'UTF-8', chr($i))) ? '<b>' . iconv('windows-1251', 'UTF-8', chr($i - 32)) . '</b>&nbsp;' : '<a class="genmed" href="' . ("memberlist.php?letter=%" . strtoupper(base_convert($i, 10, 16)) . "&amp;mode=$mode&amp;order=$sort_order") . '">' . iconv('windows-1251', 'UTF-8', chr($i - 32)) . '</a>&nbsp;';
}

$select_letter .= ':&nbsp;';
$select_letter .= ($by_letter == 'others') ? '<b>' . $lang['OTHERS'] . '</b>&nbsp;' : '<a class="genmed" href="' . ("memberlist.php?letter=others&amp;mode=$mode&amp;order=$sort_order") . '">' . $lang['OTHERS'] . '</a>&nbsp;';
$select_letter .= ':&nbsp;';
$select_letter .= ($by_letter == 'all') ? '<b>' . $lang['ALL'] . '</b>' : '<a class="genmed" href="' . ("memberlist.php?letter=all&amp;mode=$mode&amp;order=$sort_order") . '">' . $lang['ALL'] . '</a>';

// per-letter selection end
$sql = "SELECT username, user_id, user_rank, user_opt, user_posts, user_regdate, user_from, user_website, user_email, avatar_ext_id FROM " . BB_USERS . " WHERE user_id NOT IN(" . EXCLUDED_USERS . ")";
if ($username) {
    $username = str_replace("\*", '%', clean_username($username));
    $letter_sql = "username LIKE '" . DB()->escape($username) . "'";
}
$sql .= ($letter_sql) ? " AND $letter_sql" : '';
$sql .= " ORDER BY $order_by";

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
            'JOINED_RAW' => $row['user_regdate'],
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

$paginationurl = "memberlist.php?letter=$by_letter&amp;mode=$mode&amp;order=$sort_order";
if ($paginationusername) {
    $paginationurl .= "&amp;username=$paginationusername";
}
if ($mode != 'topten' || $bb_cfg['topics_per_page'] < 10) {
    $sql = "SELECT COUNT(*) AS total FROM " . BB_USERS;
    $sql .= ($letter_sql) ? " WHERE $letter_sql" : " WHERE user_id NOT IN(" . EXCLUDED_USERS . ")";
    if (!$result = DB()->sql_query($sql)) {
        bb_die('Error getting total users');
    }
    if ($total = DB()->sql_fetchrow($result)) {
        $total_members = $total['total'];
        generate_pagination($paginationurl, $total_members, $bb_cfg['topics_per_page'], $start);
    }
    DB()->sql_freeresult($result);
}

//
// Generate page
//
$template->assign_vars([
    'S_MODE_SELECT' => $select_sort_mode,
    'S_ORDER_SELECT' => $select_sort_order,
    'S_MODE_ACTION' => "memberlist.php?letter=$by_letter&amp;mode=$mode&amp;order=$sort_order",
    'S_USERNAME' => $paginationusername,
]);

$template->assign_vars([
    'PAGE_TITLE' => $lang['MEMBERLIST'],
    'S_LETTER_SELECT' => $select_letter,
    'S_LETTER_HIDDEN' => '<input type="hidden" name="letter" value="' . $by_letter . '">'
]);

print_page('memberlist.tpl');
