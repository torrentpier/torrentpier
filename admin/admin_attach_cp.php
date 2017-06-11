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

if (!empty($setmodules)) {
    $module['ATTACHMENTS']['CONTROL_PANEL'] = basename(__FILE__);
    return;
}
require __DIR__ . '/pagestart.php';

if (($attach_config['upload_dir'][0] == '/') || (($attach_config['upload_dir'][0] != '/') && ($attach_config['upload_dir'][1] == ':'))) {
    $upload_dir = $attach_config['upload_dir'];
} else {
    $upload_dir = '../' . $attach_config['upload_dir'];
}

include ATTACH_DIR . '/includes/functions_selects.php';

// Init Variables
$start = get_var('start', 0);
$sort_order = get_var('order', 'ASC');
$sort_order = ($sort_order == 'ASC') ? 'ASC' : 'DESC';
$mode = get_var('mode', '');
$view = get_var('view', '');
$uid = isset($_POST['u_id']) ? get_var('u_id', 0) : get_var('uid', 0);

$view = (isset($_POST['search']) && $_POST['search']) ? 'attachments' : $view;

// process modes based on view
if ($view === 'username') {
    $mode_types_text = array($lang['SORT_USERNAME'], $lang['SORT_ATTACHMENTS'], $lang['SORT_SIZE']);
    $mode_types = array('username', 'attachments', 'filesize');

    if (!$mode) {
        $mode = 'attachments';
        $sort_order = 'DESC';
    }
} elseif ($view === 'attachments') {
    $mode_types_text = array($lang['SORT_FILENAME'], $lang['SORT_COMMENT'], $lang['SORT_EXTENSION'], $lang['SORT_SIZE'], $lang['SORT_DOWNLOADS'], $lang['SORT_POSTTIME']);
    $mode_types = array('real_filename', 'comment', 'extension', 'filesize', 'downloads', 'post_time');

    if (!$mode) {
        $mode = 'real_filename';
        $sort_order = 'ASC';
    }
} elseif ($view === 'search') {
    $mode_types_text = array($lang['SORT_FILENAME'], $lang['SORT_COMMENT'], $lang['SORT_EXTENSION'], $lang['SORT_SIZE'], $lang['SORT_DOWNLOADS'], $lang['SORT_POSTTIME']);
    $mode_types = array('real_filename', 'comment', 'extension', 'filesize', 'downloads', 'post_time');

    $sort_order = 'DESC';
} else {
    $view = 'stats';
    $mode_types_text = array();
    $sort_order = 'ASC';
}

// Pagination ?
$do_pagination = ($view !== 'stats' && $view !== 'search');

// Set Order
$order_by = '';

if ($view === 'username') {
    switch ($mode) {
        case 'username':
            $order_by = 'ORDER BY u.username ' . $sort_order . ' LIMIT ' . $start . ', ' . $bb_cfg['topics_per_page'];
            break;
        case 'attachments':
            $order_by = 'ORDER BY total_attachments ' . $sort_order . ' LIMIT ' . $start . ', ' . $bb_cfg['topics_per_page'];
            break;
        case 'filesize':
            $order_by = 'ORDER BY total_size ' . $sort_order . ' LIMIT ' . $start . ', ' . $bb_cfg['topics_per_page'];
            break;
        default:
            $mode = 'attachments';
            $sort_order = 'DESC';
            $order_by = 'ORDER BY total_attachments ' . $sort_order . ' LIMIT ' . $start . ', ' . $bb_cfg['topics_per_page'];
            break;
    }
} elseif ($view === 'attachments') {
    switch ($mode) {
        case 'filename':
            $order_by = 'ORDER BY a.real_filename ' . $sort_order . ' LIMIT ' . $start . ', ' . $bb_cfg['topics_per_page'];
            break;
        case 'comment':
            $order_by = 'ORDER BY a.comment ' . $sort_order . ' LIMIT ' . $start . ', ' . $bb_cfg['topics_per_page'];
            break;
        case 'extension':
            $order_by = 'ORDER BY a.extension ' . $sort_order . ' LIMIT ' . $start . ', ' . $bb_cfg['topics_per_page'];
            break;
        case 'filesize':
            $order_by = 'ORDER BY a.filesize ' . $sort_order . ' LIMIT ' . $start . ', ' . $bb_cfg['topics_per_page'];
            break;
        case 'downloads':
            $order_by = 'ORDER BY a.download_count ' . $sort_order . ' LIMIT ' . $start . ', ' . $bb_cfg['topics_per_page'];
            break;
        case 'post_time':
            $order_by = 'ORDER BY a.filetime ' . $sort_order . ' LIMIT ' . $start . ', ' . $bb_cfg['topics_per_page'];
            break;
        default:
            $mode = 'a.real_filename';
            $sort_order = 'ASC';
            $order_by = 'ORDER BY a.real_filename ' . $sort_order . ' LIMIT ' . $start . ', ' . $bb_cfg['topics_per_page'];
            break;
    }
}

// Set select fields
$view_types_text = array($lang['VIEW_STATISTIC'], $lang['VIEW_SEARCH']);
$view_types = array('stats', 'search');

$select_view = '<select name="view">';

for ($i = 0, $iMax = count($view_types_text); $i < $iMax; $i++) {
    $selected = ($view === $view_types[$i]) ? ' selected="selected"' : '';
    $select_view .= '<option value="' . $view_types[$i] . '"' . $selected . '>' . $view_types_text[$i] . '</option>';
}
$select_view .= '</select>';

if (count($mode_types_text) > 0) {
    $select_sort_mode = '<select name="mode">';

    for ($i = 0, $iMax = count($mode_types_text); $i < $iMax; $i++) {
        $selected = ($mode === $mode_types[$i]) ? ' selected="selected"' : '';
        $select_sort_mode .= '<option value="' . $mode_types[$i] . '"' . $selected . '>' . $mode_types_text[$i] . '</option>';
    }
    $select_sort_mode .= '</select>';
}

$select_sort_order = '<select name="order">';
if ($sort_order === 'ASC') {
    $select_sort_order .= '<option value="ASC" selected="selected">' . $lang['ASC'] . '</option><option value="DESC">' . $lang['DESC'] . '</option>';
} else {
    $select_sort_order .= '<option value="ASC">' . $lang['ASC'] . '</option><option value="DESC" selected="selected">' . $lang['DESC'] . '</option>';
}
$select_sort_order .= '</select>';

$submit_change = isset($_POST['submit_change']);
$delete = isset($_POST['delete']);
$delete_id_list = get_var('delete_id_list', array(0));

$confirm = isset($_POST['confirm']);

if ($confirm && count($delete_id_list) > 0) {
    $attachments = array();

    delete_attachment(0, $delete_id_list);
} elseif ($delete && count($delete_id_list) > 0) {
    // Not confirmed, show confirmation message
    $hidden_fields = '<input type="hidden" name="view" value="' . $view . '" />';
    $hidden_fields .= '<input type="hidden" name="mode" value="' . $mode . '" />';
    $hidden_fields .= '<input type="hidden" name="order" value="' . $sort_order . '" />';
    $hidden_fields .= '<input type="hidden" name="u_id" value="' . $uid . '" />';
    $hidden_fields .= '<input type="hidden" name="start" value="' . $start . '" />';

    for ($i = 0, $iMax = count($delete_id_list); $i < $iMax; $i++) {
        $hidden_fields .= '<input type="hidden" name="delete_id_list[]" value="' . $delete_id_list[$i] . '" />';
    }

    print_confirmation(array(
        'FORM_ACTION' => 'admin_attach_cp.php',
        'HIDDEN_FIELDS' => $hidden_fields,
    ));
}

// Assign Default Template Vars
$template->assign_vars(array(
    'S_VIEW_SELECT' => $select_view,
    'S_MODE_ACTION' => 'admin_attach_cp.php',
));

if ($submit_change && $view === 'attachments') {
    $attach_change_list = get_var('attach_id_list', array(0));
    $attach_comment_list = get_var('attach_comment_list', array(''));
    $attach_download_count_list = get_var('attach_count_list', array(0));

    // Generate correct Change List
    $attachments = array();

    for ($i = 0, $iMax = count($attach_change_list); $i < $iMax; $i++) {
        $attachments['_' . $attach_change_list[$i]]['comment'] = $attach_comment_list[$i];
        $attachments['_' . $attach_change_list[$i]]['download_count'] = $attach_download_count_list[$i];
    }

    $sql = 'SELECT *
		FROM ' . BB_ATTACHMENTS_DESC . '
		ORDER BY attach_id';

    if (!($result = DB()->sql_query($sql))) {
        bb_die('Could not get attachment informations');
    }

    while ($attachrow = DB()->sql_fetchrow($result)) {
        if (isset($attachments['_' . $attachrow['attach_id']])) {
            if ($attachrow['comment'] != $attachments['_' . $attachrow['attach_id']]['comment'] || $attachrow['download_count'] != $attachments['_' . $attachrow['attach_id']]['download_count']) {
                $sql = 'UPDATE ' . BB_ATTACHMENTS_DESC . "
					SET comment = '" . attach_mod_sql_escape($attachments['_' . $attachrow['attach_id']]['comment']) . "', download_count = " . (int)$attachments['_' . $attachrow['attach_id']]['download_count'] . '
					WHERE attach_id = ' . (int)$attachrow['attach_id'];

                if (!DB()->sql_query($sql)) {
                    bb_die('Could not update attachments informations');
                }
            }
        }
    }
    DB()->sql_freeresult($result);
}

// Statistics
if ($view == 'stats') {
    $upload_dir_size = get_formatted_dirsize();

    $attachment_quota = humn_size($attach_config['attachment_quota']);

    // number_of_attachments
    $row = DB()->fetch_row('SELECT COUNT(*) AS total FROM ' . BB_ATTACHMENTS_DESC);
    $number_of_attachments = $number_of_posts = $row['total'];

    $number_of_pms = 0;

    // number_of_topics
    $row = DB()->fetch_row('SELECT COUNT(*) AS topics FROM ' . BB_TOPICS . ' WHERE topic_attachment = 1');
    $number_of_topics = $row['topics'];

    // number_of_users
    $row = DB()->fetch_row('SELECT COUNT(DISTINCT user_id_1) AS users FROM ' . BB_ATTACHMENTS . ' WHERE post_id != 0');
    $number_of_users = $row['users'];

    $template->assign_vars(array(
        'TPL_ATTACH_STATISTICS' => true,
        'TOTAL_FILESIZE' => $upload_dir_size,
        'ATTACH_QUOTA' => $attachment_quota,
        'NUMBER_OF_ATTACHMENTS' => $number_of_attachments,
        'NUMBER_OF_POSTS' => $number_of_posts,
        'NUMBER_OF_PMS' => $number_of_pms,
        'NUMBER_OF_TOPICS' => $number_of_topics,
        'NUMBER_OF_USERS' => $number_of_users,
    ));
}

// Search
if ($view === 'search') {
    // Get Forums and Categories
    //sf - add [, f.forum_parent]
    $sql = 'SELECT c.cat_title, c.cat_id, f.forum_name, f.forum_id, f.forum_parent
	FROM ' . BB_CATEGORIES . ' c, ' . BB_FORUMS . ' f
	WHERE f.cat_id = c.cat_id
	ORDER BY c.cat_id, f.forum_order';

    if (!($result = DB()->sql_query($sql))) {
        bb_die('Could not obtain forum_name / forum_id');
    }

    $s_forums = '';
    $list_cat = [];
    while ($row = DB()->sql_fetchrow($result)) { //sf
        $s_forums .= '<option value="' . $row['forum_id'] . '">' . ($row['forum_parent'] ? HTML_SF_SPACER : '') . htmlCHR($row['forum_name']) . '</option>';

        if (empty($list_cat[$row['cat_id']])) {
            $list_cat[$row['cat_id']] = $row['cat_title'];
        }
    }

    $s_categories = '';
    if ($s_forums) {
        $s_forums = '<option value="0">' . $lang['ALL_AVAILABLE'] . '</option>' . $s_forums;

        // Category to search
        $s_categories = '<option value="0">' . $lang['ALL_AVAILABLE'] . '</option>';

        foreach ($list_cat as $cat_id => $cat_title) {
            $s_categories .= '<option value="' . $cat_id . '">' . htmlCHR($cat_title) . '</option>';
        }
    } else {
        bb_die($lang['NO_SEARCHABLE_FORUMS']);
    }

    $template->assign_vars(array(
        'TPL_ATTACH_SEARCH' => true,
        'S_FORUM_OPTIONS' => $s_forums,
        'S_CATEGORY_OPTIONS' => $s_categories,
        'S_SORT_OPTIONS' => $select_sort_mode,
        'S_SORT_ORDER' => $select_sort_order,
    ));
}

// Username
if ($view === 'username') {
    $template->assign_vars(array(
        'TPL_ATTACH_USER' => true,
        'S_MODE_SELECT' => $select_sort_mode,
        'S_ORDER_SELECT' => $select_sort_order,
    ));
    $total_rows = 0;
    bb_die('removed');
}

// Attachments
if ($view === 'attachments') {
    $user_based = $uid ? true : false;
    $search_based = (isset($_POST['search']) && $_POST['search']);

    $hidden_fields = '';

    $template->assign_vars(array(
        'TPL_ATTACH_ATTACHMENTS' => true,
        'S_MODE_SELECT' => $select_sort_mode,
        'S_ORDER_SELECT' => $select_sort_order,
    ));

    $total_rows = 0;

    // Are we called from Username ?
    if ($user_based) {
        $sql = 'SELECT username FROM ' . BB_USERS . ' WHERE user_id = ' . (int)$uid;

        if (!($result = DB()->sql_query($sql))) {
            bb_die('Error getting username');
        }

        $row = DB()->sql_fetchrow($result);
        DB()->sql_freeresult($result);
        $username = $row['username'];

        $s_hidden = '<input type="hidden" name="u_id" value="' . (int)$uid . '" />';

        $template->assign_block_vars('switch_user_based', array());

        $template->assign_vars(array(
            'S_USER_HIDDEN' => $s_hidden,
            'L_STATISTICS_FOR_USER' => sprintf($lang['STATISTICS_FOR_USER'], $username),
        ));

        $sql = 'SELECT attach_id
		FROM ' . BB_ATTACHMENTS . '
		WHERE user_id_1 = ' . (int)$uid . '
		GROUP BY attach_id';

        if (!($result = DB()->sql_query($sql))) {
            bb_die('Could not query attachments #1');
        }

        $attach_ids = DB()->sql_fetchrowset($result);
        $num_attach_ids = DB()->num_rows($result);
        DB()->sql_freeresult($result);

        if ($num_attach_ids == 0) {
            bb_die('For some reason no attachments are assigned to the user ' . $username);
        }

        $total_rows = $num_attach_ids;

        $attach_id = array();

        for ($j = 0; $j < $num_attach_ids; $j++) {
            $attach_id[] = (int)$attach_ids[$j]['attach_id'];
        }

        $sql = 'SELECT a.*
		FROM ' . BB_ATTACHMENTS_DESC . ' a
		WHERE a.attach_id IN (' . implode(', ', $attach_id) . ') ' .
            $order_by;

        if (!($result = DB()->sql_query($sql))) {
            bb_die('Could not query attachments #2');
        }

        $attachments = DB()->sql_fetchrowset($result);
        $num_attach = DB()->num_rows($result);
        DB()->sql_freeresult($result);
    } else {
        // we are called from search
        $attachments = search_attachments($order_by, $total_rows);
    }

    if (count($attachments) > 0) {
        for ($i = 0, $iMax = count($attachments); $i < $iMax; $i++) {
            $delete_box = '<input type="checkbox" name="delete_id_list[]" value="' . (int)$attachments[$i]['attach_id'] . '" />';

            for ($j = 0, $jMax = count($delete_id_list); $j < $jMax; $j++) {
                if ($delete_id_list[$j] == $attachments[$i]['attach_id']) {
                    $delete_box = '<input type="checkbox" name="delete_id_list[]" value="' . (int)$attachments[$i]['attach_id'] . '" checked="checked" />';
                    break;
                }
            }

            $row_class = !($i % 2) ? 'row1' : 'row2';

            // Is the Attachment assigned to more than one post ?
            // If it's not assigned to any post, it's an private message thingy. ;)
            $post_titles = [];

            $sql = 'SELECT *
			FROM ' . BB_ATTACHMENTS . '
			WHERE attach_id = ' . (int)$attachments[$i]['attach_id'];

            if (!($result = DB()->sql_query($sql))) {
                bb_die('Could not query attachments #3');
            }

            $ids = DB()->sql_fetchrowset($result);
            $num_ids = DB()->num_rows($result);
            DB()->sql_freeresult($result);

            for ($j = 0; $j < $num_ids; $j++) {
                if ($ids[$j]['post_id'] != 0) {
                    $sql = 'SELECT t.topic_title
					FROM ' . BB_TOPICS . ' t, ' . BB_POSTS . ' p
					WHERE p.post_id = ' . (int)$ids[$j]['post_id'] . ' AND p.topic_id = t.topic_id
					GROUP BY t.topic_id, t.topic_title';

                    if (!($result = DB()->sql_query($sql))) {
                        bb_die('Could not query topic');
                    }

                    $row = DB()->sql_fetchrow($result);
                    DB()->sql_freeresult($result);
                    $post_title = $row['topic_title'];

                    if (strlen($post_title) > 32) {
                        $post_title = str_short($post_title, 30);
                    }

                    $view_topic = BB_ROOT . 'viewtopic.php?' . POST_POST_URL . '=' . $ids[$j]['post_id'] . '#' . $ids[$j]['post_id'];

                    $post_titles[] = '<a href="' . $view_topic . '" class="gen" target="_blank">' . $post_title . '</a>';
                } else {
                    $post_titles[] = $lang['PRIVATE_MESSAGE'];
                }
            }

            $post_titles = implode('<br />', $post_titles);

            $hidden_field = '<input type="hidden" name="attach_id_list[]" value="' . (int)$attachments[$i]['attach_id'] . '" />';

            $template->assign_block_vars('attachrow', array(
                'ROW_NUMBER' => $i + ($_GET['start'] + 1),
                'ROW_CLASS' => $row_class,

                'FILENAME' => htmlspecialchars($attachments[$i]['real_filename']),
                'COMMENT' => htmlspecialchars($attachments[$i]['comment']),
                'EXTENSION' => $attachments[$i]['extension'],
                'SIZE' => round($attachments[$i]['filesize'] / 1024, 2),
                'DOWNLOAD_COUNT' => $attachments[$i]['download_count'],
                'POST_TIME' => bb_date($attachments[$i]['filetime']),
                'POST_TITLE' => $post_titles,

                'S_DELETE_BOX' => $delete_box,
                'S_HIDDEN' => $hidden_field,
                'U_VIEW_ATTACHMENT' => BB_ROOT . DOWNLOAD_URL . $attachments[$i]['attach_id'],
            ));
        }
    }

    if (!$search_based && !$user_based) {
        if (!$attachments) {
            $sql = 'SELECT attach_id FROM ' . BB_ATTACHMENTS_DESC;

            if (!($result = DB()->sql_query($sql))) {
                bb_die('Could not query attachment description table');
            }

            $total_rows = DB()->num_rows($result);
            DB()->sql_freeresult($result);
        }
    }
}

// Generate Pagination
if ($do_pagination && $total_rows > $bb_cfg['topics_per_page']) {
    generate_pagination('admin_attach_cp.php?view=' . $view . '&amp;mode=' . $mode . '&amp;order=' . $sort_order . '&amp;uid=' . $uid, $total_rows, $bb_cfg['topics_per_page'], $start);
}

print_page('admin_attach_cp.tpl', 'admin');
