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

use \TorrentPier\Di;

define('BB_SCRIPT', 'memberlist');
define('BB_ROOT', './');
require(BB_ROOT . 'common.php');

/** @var \TorrentPier\Di $di */
$di = \TorrentPier\Di::getInstance();

$page_cfg['use_tablesorter'] = true;

$user->session_start(array('req_login' => true));

$start = abs(intval(request_var('start', 0)));
$mode = (string)request_var('mode', 'joined');
$sort_order = (request_var('order', 'ASC') == 'ASC') ? 'ASC' : 'DESC';
$username = request_var('username', '');
$paginationusername = $username;

//
// Memberlist sorting
//
$mode_types_text = array(
    $lang['SORT_JOINED'],
    $lang['SORT_USERNAME'],
    $lang['SORT_LOCATION'],
    $lang['SORT_POSTS'],
    $lang['SORT_EMAIL'],
    $lang['SORT_WEBSITE'],
    $lang['SORT_TOP_TEN']
);

$mode_types = array(
    'joined',
    'username',
    'location',
    'posts',
    'email',
    'website',
    'topten'
);

// <select> mode
$select_sort_mode = '<select name="mode">';

for ($i = 0, $cnt = count($mode_types_text); $i < $cnt; $i++) {
    $selected = ($mode == $mode_types[$i]) ? ' selected="selected"' : '';
    $select_sort_mode .= '<option value="' . $mode_types[$i] . '"' . $selected . '>' . $mode_types_text[$i] . '</option>';
}
$select_sort_mode .= '</select>';

// <select> order
$select_sort_order = '<select name="order">';

if ($sort_order == 'ASC') {
    $select_sort_order .= '<option value="ASC" selected="selected">' . $lang['ASC'] . '</option><option value="DESC">' . $lang['DESC'] . '</option>';
} else {
    $select_sort_order .= '<option value="ASC">' . $lang['ASC'] . '</option><option value="DESC" selected="selected">' . $lang['DESC'] . '</option>';
}
$select_sort_order .= '</select>';

//
// Generate page
//
$template->assign_vars(array(
    'S_MODE_SELECT' => $select_sort_mode,
    'S_ORDER_SELECT' => $select_sort_order,
    'S_MODE_ACTION' => "memberlist.php",
    'S_USERNAME' => $paginationusername,
));

switch ($mode) {
    case 'joined':
        $order_by = "user_id $sort_order LIMIT $start, " . $di->config->get('topics_per_page');
        break;
    case 'username':
        $order_by = "username $sort_order LIMIT $start, " . $di->config->get('topics_per_page');
        break;
    case 'location':
        $order_by = "user_from $sort_order LIMIT $start, " . $di->config->get('topics_per_page');
        break;
    case 'posts':
        $order_by = "user_posts $sort_order LIMIT $start, " . $di->config->get('topics_per_page');
        break;
    case 'email':
        $order_by = "user_email $sort_order LIMIT $start, " . $di->config->get('topics_per_page');
        break;
    case 'website':
        $order_by = "user_website $sort_order LIMIT $start, " . $di->config->get('topics_per_page');
        break;
    case 'topten':
        $order_by = "user_posts $sort_order LIMIT 10";
        break;
    default:
        $order_by = "user_regdate $sort_order LIMIT $start, " . $di->config->get('topics_per_page');
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
    } elseif ($letter_req = preg_replace("#[^$letters_range]#ui", '', iconv('windows-1251', 'UTF-8', $by_letter_req[0]))) {
        $by_letter = Di::getInstance()->db->escape($letter_req);
        $letter_sql = "LOWER(username) LIKE '$by_letter%'";
    }
}

// ENG
for ($i = ord('A'), $cnt = ord('Z'); $i <= $cnt; $i++) {
    $select_letter .= ($by_letter == chr($i)) ? '<b>' . chr($i) . '</b>&nbsp;' : '<a class="genmed" href="' . ("memberlist.php?letter=" . chr($i) . "&amp;mode=$mode&amp;order=$sort_order") . '">' . chr($i) . '</a>&nbsp;';
}
// RUS
$select_letter .= ': ';
for ($i = 224, $cnt = 255; $i <= $cnt; $i++) {
    $select_letter .= ($by_letter == iconv('windows-1251', 'UTF-8', chr($i))) ? '<b>' . iconv('windows-1251', 'UTF-8', chr($i - 32)) . '</b>&nbsp;' : '<a class="genmed" href="' . ("memberlist.php?letter=%" . strtoupper(base_convert($i, 10, 16)) . "&amp;mode=$mode&amp;order=$sort_order") . '">' . iconv('windows-1251', 'UTF-8', chr($i - 32)) . '</a>&nbsp;';
}

$select_letter .= ':&nbsp;';
$select_letter .= ($by_letter == 'others') ? '<b>' . $lang['OTHERS'] . '</b>&nbsp;' : '<a class="genmed" href="' . ("memberlist.php?letter=others&amp;mode=$mode&amp;order=$sort_order") . '">' . $lang['OTHERS'] . '</a>&nbsp;';
$select_letter .= ':&nbsp;';
$select_letter .= ($by_letter == 'all') ? '<b>' . $lang['ALL'] . '</b>' : '<a class="genmed" href="' . ("memberlist.php?letter=all&amp;mode=$mode&amp;order=$sort_order") . '">' . $lang['ALL'] . '</a>';

$template->assign_vars(array(
    'S_LETTER_SELECT' => $select_letter,
    'S_LETTER_HIDDEN' => '<input type="hidden" name="letter" value="' . $by_letter . '">',
));

// per-letter selection end
$sql = "SELECT username, user_id, user_rank, user_opt, user_posts, user_regdate, user_from, user_website, user_email FROM bb_users WHERE user_id NOT IN(" . EXCLUDED_USERS . ")";
if ($username) {
    $username = preg_replace('/\*/', '%', clean_username($username));
    $letter_sql = "username LIKE '" . Di::getInstance()->db->escape($username) . "'";
}
$sql .= ($letter_sql) ? " AND $letter_sql" : '';
$sql .= " ORDER BY $order_by";

if ($result = Di::getInstance()->db->fetch_rowset($sql)) {
    foreach ($result as $i => $row) {
        $user_id = $row['user_id'];
        $from = $row['user_from'];
        $joined = bb_date($row['user_regdate'], $di->config->get('date_format'));
        $posts = $row['user_posts'];
        $pm = ($di->config->get('text_buttons')) ? '<a class="txtb" href="' . (PM_URL . "?mode=post&amp;" . POST_USERS_URL . "=$user_id") . '">' . $lang['SEND_PM_TXTB'] . '</a>' : '<a href="' . (PM_URL . "?mode=post&amp;" . POST_USERS_URL . "=$user_id") . '"><img src="' . $images['icon_pm'] . '" alt="' . $lang['SEND_PRIVATE_MESSAGE'] . '" title="' . $lang['SEND_PRIVATE_MESSAGE'] . '" border="0" /></a>';

        if (bf($row['user_opt'], 'user_opt', 'user_viewemail') || IS_ADMIN) {
            $email_uri = ($di->config->get('board_email_form')) ? ("profile.php?mode=email&amp;" . POST_USERS_URL . "=$user_id") : 'mailto:' . $row['user_email'];
            $email = '<a class="editable" href="' . $email_uri . '">' . $row['user_email'] . '</a>';
        } else {
            $email = '';
        }

        if ($row['user_website']) {
            $www = ($di->config->get('text_buttons')) ? '<a class="txtb" href="' . $row['user_website'] . '"  target="_userwww">' . $lang['VISIT_WEBSITE_TXTB'] . '</a>' : '<a class="txtb" href="' . $row['user_website'] . '" target="_userwww"><img src="' . $images['icon_www'] . '" alt="' . $lang['VISIT_WEBSITE'] . '" title="' . $lang['VISIT_WEBSITE'] . '" border="0" /></a>';
        } else {
            $www = '';
        }

        $row_class = !($i % 2) ? 'row1' : 'row2';
        $template->assign_block_vars('memberrow', array(
            'ROW_NUMBER' => $i + ($start + 1),
            'ROW_CLASS' => $row_class,
            'USER' => profile_url($row),
            'FROM' => $from,
            'JOINED_RAW' => $row['user_regdate'],
            'JOINED' => $joined,
            'POSTS' => $posts,
            'PM' => $pm,
            'EMAIL' => $email,
            'WWW' => $www,
            'U_VIEWPROFILE' => PROFILE_URL . $user_id,
        ));
    }
} else {
    $template->assign_block_vars('no_username', array(
        'NO_USER_ID_SPECIFIED' => $lang['NO_USER_ID_SPECIFIED'],
    ));
}

$paginationurl = "memberlist.php?mode=$mode&amp;order=$sort_order&amp;letter=$by_letter";
if ($paginationusername) {
    $paginationurl .= "&amp;username=$paginationusername";
}
if ($mode != 'topten' || $di->config->get('topics_per_page') < 10) {
    $sql = "SELECT COUNT(*) AS total FROM " . BB_USERS;
    $sql .= ($letter_sql) ? " WHERE $letter_sql" : '';
    if (!$result = Di::getInstance()->db->sql_query($sql)) {
        bb_die('Error getting total users');
    }
    if ($total = Di::getInstance()->db->sql_fetchrow($result)) {
        $total_members = $total['total'];
        generate_pagination($paginationurl, $total_members, $di->config->get('topics_per_page'), $start) . '&nbsp;';
    }
    Di::getInstance()->db->sql_freeresult($result);
}

$template->assign_vars(array(
    'PAGE_TITLE' => $lang['USERS'],
));

print_page('memberlist.tpl');
