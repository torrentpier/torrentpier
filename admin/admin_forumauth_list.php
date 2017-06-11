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
    $module['FORUMS']['PERMISSIONS_LIST'] = basename(__FILE__);
    return;
}

require __DIR__ . '/pagestart.php';

//  View  Read  Post  Reply  Edit  Delete  Sticky  Announce  Vote  Poll  PostAttach  Download
$simple_auth_ary = [
    0 => [AUTH_ALL, AUTH_ALL, AUTH_ALL, AUTH_ALL, AUTH_REG, AUTH_REG, AUTH_MOD, AUTH_MOD, AUTH_REG, AUTH_REG, AUTH_REG, AUTH_ALL], // Public
    1 => [AUTH_ALL, AUTH_ALL, AUTH_REG, AUTH_REG, AUTH_REG, AUTH_REG, AUTH_MOD, AUTH_MOD, AUTH_REG, AUTH_REG, AUTH_REG, AUTH_REG], // Registered
    2 => [AUTH_REG, AUTH_REG, AUTH_REG, AUTH_REG, AUTH_REG, AUTH_REG, AUTH_MOD, AUTH_MOD, AUTH_REG, AUTH_REG, AUTH_REG, AUTH_REG], // Registered [Hidden]
    3 => [AUTH_REG, AUTH_ACL, AUTH_ACL, AUTH_ACL, AUTH_ACL, AUTH_ACL, AUTH_MOD, AUTH_MOD, AUTH_ACL, AUTH_ACL, AUTH_ACL, AUTH_ACL], // Private
    4 => [AUTH_ACL, AUTH_ACL, AUTH_ACL, AUTH_ACL, AUTH_ACL, AUTH_ACL, AUTH_MOD, AUTH_MOD, AUTH_ACL, AUTH_ACL, AUTH_ACL, AUTH_ACL], // Private [Hidden]
    5 => [AUTH_REG, AUTH_MOD, AUTH_MOD, AUTH_MOD, AUTH_MOD, AUTH_MOD, AUTH_MOD, AUTH_MOD, AUTH_MOD, AUTH_MOD, AUTH_MOD, AUTH_MOD], // Moderators
    6 => [AUTH_MOD, AUTH_MOD, AUTH_MOD, AUTH_MOD, AUTH_MOD, AUTH_MOD, AUTH_MOD, AUTH_MOD, AUTH_MOD, AUTH_MOD, AUTH_MOD, AUTH_MOD], // Moderators [Hidden]
];

$simple_auth_types = [
    $lang['PUBLIC'],
    $lang['REGISTERED'],
    $lang['REGISTERED'] . ' [' . $lang['HIDDEN'] . ']',
    $lang['PRIVATE'],
    $lang['PRIVATE'] . ' [' . $lang['HIDDEN'] . ']',
    $lang['MODERATORS'],
    $lang['MODERATORS'] . ' [' . $lang['HIDDEN'] . ']',
];

$forum_auth_fields = [
    'auth_view',
    'auth_read',
    'auth_reply',
    'auth_edit',
    'auth_delete',
    'auth_vote',
    'auth_pollcreate',
    'auth_attachments',
    'auth_download',
    'auth_post',
    'auth_sticky',
    'auth_announce',
];

$field_names = [];
foreach ($forum_auth_fields as $auth_type) {
    $field_names[$auth_type] = $lang[strtoupper($auth_type)];
}

$forum_auth_levels = ['ALL', 'REG', 'PRIVATE', 'MOD', 'ADMIN'];
$forum_auth_const = [AUTH_ALL, AUTH_REG, AUTH_ACL, AUTH_MOD, AUTH_ADMIN];

if (isset($_GET[POST_FORUM_URL]) || isset($_POST[POST_FORUM_URL])) {
    $forum_id = isset($_POST[POST_FORUM_URL]) ? (int)$_POST[POST_FORUM_URL] : (int)$_GET[POST_FORUM_URL];
    $forum_sql = "AND forum_id = $forum_id";
} else {
    unset($forum_id);
    $forum_sql = '';
}

if (isset($_GET[POST_CAT_URL]) || isset($_POST[POST_CAT_URL])) {
    $cat_id = isset($_POST[POST_CAT_URL]) ? (int)$_POST[POST_CAT_URL] : (int)$_GET[POST_CAT_URL];
    $cat_sql = "AND c.cat_id = $cat_id";
} else {
    unset($cat_id);
    $cat_sql = '';
}

if (isset($_GET['adv'])) {
    $adv = (int)$_GET['adv'];
} else {
    unset($adv);
}

//
// Start program proper
//
if (isset($_POST['submit'])) {
    $sql = '';

    if (!empty($forum_id)) {
        if (isset($_POST['simpleauth'])) {
            $simple_ary = $simple_auth_ary[(int)$_POST['simpleauth']];

            for ($i = 0, $iMax = count($simple_ary); $i < $iMax; $i++) {
                $sql .= (($sql != '') ? ', ' : '') . $forum_auth_fields[$i] . ' = ' . $simple_ary[$i];
            }

            if (is_array($simple_ary)) {
                $sql = 'UPDATE ' . BB_FORUMS . " SET $sql WHERE forum_id = $forum_id";
            }
        } else {
            for ($i = 0, $iMax = count($forum_auth_fields); $i < $iMax; $i++) {
                $value = (int)$_POST[$forum_auth_fields[$i]];

                if ($forum_auth_fields[$i] == 'auth_vote') {
                    if ($_POST['auth_vote'] == AUTH_ALL) {
                        $value = AUTH_REG;
                    }
                }

                $sql .= (($sql != '') ? ', ' : '') . $forum_auth_fields[$i] . ' = ' . $value;
            }

            $sql = 'UPDATE ' . BB_FORUMS . " SET $sql WHERE forum_id = $forum_id";
        }

        if ($sql != '') {
            if (!DB()->sql_query($sql)) {
                bb_die('Could not update auth table #1');
            }
        }

        $forum_sql = '';
        $adv = 0;
    } elseif (!empty($cat_id)) {
        for ($i = 0, $iMax = count($forum_auth_fields); $i < $iMax; $i++) {
            $value = (int)$_POST[$forum_auth_fields[$i]];

            if ($forum_auth_fields[$i] == 'auth_vote') {
                if ($_POST['auth_vote'] == AUTH_ALL) {
                    $value = AUTH_REG;
                }
            }

            $sql .= (($sql != '') ? ', ' : '') . $forum_auth_fields[$i] . ' = ' . $value;
        }

        $sql = 'UPDATE ' . BB_FORUMS . " SET $sql WHERE cat_id = $cat_id";

        if ($sql != '') {
            if (!DB()->sql_query($sql)) {
                bb_die('Could not update auth table #2');
            }
        }

        $cat_sql = '';
    }

    $datastore->update('cat_forums');
    bb_die($lang['FORUM_AUTH_UPDATED'] . '<br /><br />' . sprintf($lang['CLICK_RETURN_FORUMAUTH'], '<a href="admin_forumauth_list.php">', '</a>'));
} // End of submit

//
// Get required information, either all forums if
// no id was specified or just the requsted forum
// or category if it was
//
$sql = 'SELECT f.*
	FROM ' . BB_FORUMS . ' f, ' . BB_CATEGORIES . " c
	WHERE c.cat_id = f.cat_id
	$forum_sql $cat_sql
	ORDER BY c.cat_order ASC, f.forum_order ASC";
if (!($result = DB()->sql_query($sql))) {
    bb_die('Could not obtain forum list');
}

$forum_rows = DB()->sql_fetchrowset($result);
DB()->sql_freeresult($result);

if (empty($forum_id) && empty($cat_id)) {
    //
    // Output the summary list if no forum id was
    // specified
    //
    $template->assign_vars(array(
        'TPL_AUTH_FORUM_LIST' => true,
        'S_COLUMN_SPAN' => count($forum_auth_fields) + 1,
    ));

    for ($i = 0, $iMax = count($forum_auth_fields); $i < $iMax; $i++) {
        $template->assign_block_vars('forum_auth_titles', array(
            'CELL_TITLE' => $field_names[$forum_auth_fields[$i]],
        ));
    }

    // Obtain the category list
    $sql = 'SELECT c.cat_id, c.cat_title, c.cat_order
		FROM ' . BB_CATEGORIES . ' c
		ORDER BY c.cat_order';
    if (!($result = DB()->sql_query($sql))) {
        bb_die('Could not query categories list #1');
    }

    $category_rows = DB()->sql_fetchrowset($result);
    $cat_count = count($category_rows);

    for ($i = 0; $i < $cat_count; $i++) {
        $cat_id = $category_rows[$i]['cat_id'];

        $template->assign_block_vars('cat_row', array(
                'CAT_NAME' => htmlCHR($category_rows[$i]['cat_title']),
                'CAT_URL' => 'admin_forumauth_list.php' . '?' . POST_CAT_URL . '=' . $category_rows[$i]['cat_id'])
        );

        for ($j = 0, $jMax = count($forum_rows); $j < $jMax; $j++) {
            if ($cat_id == $forum_rows[$j]['cat_id']) {
                $template->assign_block_vars('cat_row.forum_row', array(
                    'ROW_CLASS' => !($j % 2) ? 'row4' : 'row5',
                    'FORUM_NAME' => '<a class="' . ($forum_rows[$j]['forum_parent'] ? 'genmed' : 'gen') . '" href="admin_forumauth.php?' . POST_FORUM_URL . '=' . $forum_rows[$j]['forum_id'] . '">' . htmlCHR($forum_rows[$j]['forum_name']) . '</a>',
                    'IS_SUBFORUM' => $forum_rows[$j]['forum_parent'],
                ));

                for ($k = 0, $kMax = count($forum_auth_fields); $k < $kMax; $k++) {
                    $item_auth_value = $forum_rows[$j][$forum_auth_fields[$k]];
                    for ($l = 0, $lMax = count($forum_auth_const); $l < $lMax; $l++) {
                        if ($item_auth_value == $forum_auth_const[$l]) {
                            $item_auth_level = $forum_auth_levels[$l];
                            break;
                        }
                    }
                    $template->assign_block_vars('cat_row.forum_row.forum_auth_data', array(
                            'CELL_VALUE' => $lang['FORUM_' . $item_auth_level],
                            'AUTH_EXPLAIN' => sprintf($lang[strtoupper('FORUM_AUTH_LIST_EXPLAIN_' . $forum_auth_fields[$k])], $lang[strtoupper('FORUM_AUTH_LIST_EXPLAIN_' . $item_auth_level)]))
                    );
                }
            }
        }
    }
} else {
    //
    // output the authorisation details if an category id was
    // specified
    //

    //
    // first display the current details for all forums
    // in the category
    //
    for ($i = 0, $iMax = count($forum_auth_fields); $i < $iMax; $i++) {
        $template->assign_block_vars('forum_auth_titles', array(
            'CELL_TITLE' => $field_names[$forum_auth_fields[$i]],
        ));
    }

    // obtain the category list
    $sql = 'SELECT c.cat_id, c.cat_title, c.cat_order
		FROM ' . BB_CATEGORIES . " c
		WHERE c.cat_id = $cat_id
		ORDER BY c.cat_order";
    if (!($result = DB()->sql_query($sql))) {
        bb_die('Could not query categories list #2');
    }

    $category_rows = DB()->sql_fetchrowset($result);

    $cat_id = reset($category_rows)['cat_id'];
    $cat_name = reset($category_rows)['cat_title'];

    $template->assign_block_vars('cat_row', array(
            'CAT_NAME' => htmlCHR($cat_name),
            'CAT_URL' => 'admin_forumauth_list.php?' . POST_CAT_URL . '=' . $cat_id)
    );

    for ($j = 0, $jMax = count($forum_rows); $j < $jMax; $j++) {
        if ($cat_id == $forum_rows[$j]['cat_id']) {
            $template->assign_block_vars('cat_row.forum_row', array(
                'ROW_CLASS' => !($j % 2) ? 'row4' : 'row5',
                'FORUM_NAME' => '<a class="' . ($forum_rows[$j]['forum_parent'] ? 'genmed' : 'gen') . '" href="admin_forumauth.php?' . POST_FORUM_URL . '=' . $forum_rows[$j]['forum_id'] . '">' . htmlCHR($forum_rows[$j]['forum_name']) . '</a>',
                'IS_SUBFORUM' => $forum_rows[$j]['forum_parent'],
            ));

            for ($k = 0, $kMax = count($forum_auth_fields); $k < $kMax; $k++) {
                $item_auth_value = $forum_rows[$j][$forum_auth_fields[$k]];
                for ($l = 0, $lMax = count($forum_auth_const); $l < $lMax; $l++) {
                    if ($item_auth_value == $forum_auth_const[$l]) {
                        $item_auth_level = $forum_auth_levels[$l];
                        break;
                    }
                }
                $template->assign_block_vars('cat_row.forum_row.forum_auth_data', array(
                        'CELL_VALUE' => $lang['FORUM_' . $item_auth_level],
                        'AUTH_EXPLAIN' => sprintf($lang[strtoupper('FORUM_AUTH_LIST_EXPLAIN_' . $forum_auth_fields[$k])], $lang[strtoupper('FORUM_AUTH_LIST_EXPLAIN_' . $item_auth_level)]))
                );
            }
        }
    }

    //
    // next generate the information to allow the permissions to be changed
    // note: we always read from the first forum in the category
    //
    for ($j = 0, $jMax = count($forum_auth_fields); $j < $jMax; $j++) {
        $custom_auth[$j] = '<select name="' . $forum_auth_fields[$j] . '">';

        for ($k = 0, $kMax = count($forum_auth_levels); $k < $kMax; $k++) {
            $selected = (!empty($forum_rows) && $forum_rows[0][$forum_auth_fields[$j]] == $forum_auth_const[$k]) ? ' selected="selected"' : '';
            $custom_auth[$j] .= '<option value="' . $forum_auth_const[$k] . '"' . $selected . '>' . $lang['FORUM_' . $forum_auth_levels[$k]] . '</option>';
        }
        $custom_auth[$j] .= '</select>';

        $template->assign_block_vars('forum_auth_data', array(
                'S_AUTH_LEVELS_SELECT' => $custom_auth[$j])
        );
    }

    //
    // finally pass any remaining items to the template
    //
    $s_hidden_fields = '<input type="hidden" name="' . POST_CAT_URL . '" value="' . $cat_id . '">';

    $template->assign_vars(array(
        'TPL_AUTH_CAT' => true,
        'CAT_NAME' => htmlCHR($cat_name),
        'S_FORUMAUTH_ACTION' => 'admin_forumauth_list.php',
        'S_COLUMN_SPAN' => count($forum_auth_fields) + 1,
        'S_HIDDEN_FIELDS' => $s_hidden_fields,
    ));
}

print_page('admin_forumauth_list.tpl', 'admin');
