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
    $module['FORUMS']['PERMISSIONS'] = basename(__FILE__);
    return;
}

require __DIR__ . '/pagestart.php';

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

// View  Read  Reply  Edit  Delete  Vote  Poll  PostAttach  DownAttach  PostTopic  Sticky  Announce
$simple_auth_ary = [
    0 => [AUTH_ALL, AUTH_ALL, AUTH_ALL, AUTH_REG, AUTH_REG, AUTH_REG, AUTH_REG, AUTH_REG, AUTH_ALL, AUTH_ALL, AUTH_MOD, AUTH_MOD], // Public
    1 => [AUTH_ALL, AUTH_ALL, AUTH_REG, AUTH_REG, AUTH_REG, AUTH_REG, AUTH_REG, AUTH_REG, AUTH_REG, AUTH_REG, AUTH_MOD, AUTH_MOD], // Registered
    2 => [AUTH_REG, AUTH_REG, AUTH_REG, AUTH_REG, AUTH_REG, AUTH_REG, AUTH_REG, AUTH_REG, AUTH_REG, AUTH_REG, AUTH_MOD, AUTH_MOD], // Registered [Hidden]
    3 => [AUTH_REG, AUTH_ACL, AUTH_ACL, AUTH_ACL, AUTH_ACL, AUTH_ACL, AUTH_ACL, AUTH_ACL, AUTH_ACL, AUTH_ACL, AUTH_MOD, AUTH_MOD], // Private
    4 => [AUTH_ACL, AUTH_ACL, AUTH_ACL, AUTH_ACL, AUTH_ACL, AUTH_ACL, AUTH_ACL, AUTH_ACL, AUTH_ACL, AUTH_ACL, AUTH_MOD, AUTH_MOD], // Private [Hidden]
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

$field_names = [];
foreach ($forum_auth_fields as $auth_type) {
    $field_names[$auth_type] = $lang[strtoupper($auth_type)];
}

$forum_auth_levels = ['ALL', 'REG', 'PRIVATE', 'MOD', 'ADMIN'];
$forum_auth_const = [AUTH_ALL, AUTH_REG, AUTH_ACL, AUTH_MOD, AUTH_ADMIN];

if (isset($_REQUEST[POST_FORUM_URL])) {
    $forum_id = (int)$_REQUEST[POST_FORUM_URL];
    $forum_sql = "WHERE forum_id = $forum_id";
} else {
    unset($forum_id);
    $forum_sql = '';
}

if (isset($_GET['adv'])) {
    $adv = (int)$_GET['adv'];
} else {
    unset($adv);
}

/**
 * Start program proper
 */
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
                bb_die('Could not update auth table');
            }
        }

        $forum_sql = '';
        $adv = 0;
    }

    $datastore->update('cat_forums');
    bb_die($lang['FORUM_AUTH_UPDATED'] . '<br /><br />' . sprintf($lang['CLICK_RETURN_FORUMAUTH'], '<a href="' . 'admin_forumauth.php' . '">', '</a>'));
}

/**
 * Get required information
 */
$forum_rows = DB()->fetch_rowset('SELECT * FROM ' . BB_FORUMS . " $forum_sql");

if (empty($forum_id)) {
    // Output the selection table if no forum id was specified
    $template->assign_vars(array(
        'TPL_AUTH_SELECT_FORUM' => true,
        'S_AUTH_ACTION' => 'admin_forumauth.php',
        'S_AUTH_SELECT' => get_forum_select('admin', 'f', null, 80),
    ));
} else {
    // Output the authorisation details if an id was specified
    $forum_name = reset($forum_rows)['forum_name'];

    reset($simple_auth_ary);
    foreach ($simple_auth_ary as $key => $auth_levels) {
        $matched = 1;
        for ($k = 0, $kMax = count($auth_levels); $k < $kMax; $k++) {
            $matched_type = $key;

            if ($forum_rows[0][$forum_auth_fields[$k]] != $auth_levels[$k]) {
                $matched = 0;
            }
        }

        if ($matched) {
            break;
        }
    }

    //
    // If we didn't get a match above then we
    // automatically switch into 'advanced' mode
    //
    if (!isset($adv) && !$matched) {
        $adv = 1;
    }

    $s_column_span = 0;

    if (empty($adv)) {
        $simple_auth = '<select name="simpleauth">';

        for ($j = 0, $jMax = count($simple_auth_types); $j < $jMax; $j++) {
            $selected = ($matched_type == $j) ? ' selected="selected"' : '';
            $simple_auth .= '<option value="' . $j . '"' . $selected . '>' . $simple_auth_types[$j] . '</option>';
        }

        $simple_auth .= '</select>';

        $template->assign_block_vars('forum_auth', array(
            'CELL_TITLE' => $lang['SIMPLE_MODE'],
            'S_AUTH_LEVELS_SELECT' => $simple_auth,
        ));

        $s_column_span++;
    } else {
        // Output values of individual fields
        for ($j = 0, $jMax = count($forum_auth_fields); $j < $jMax; $j++) {
            $custom_auth[$j] = '&nbsp;<select name="' . $forum_auth_fields[$j] . '">';

            for ($k = 0, $kMax = count($forum_auth_levels); $k < $kMax; $k++) {
                $selected = ($forum_rows[0][$forum_auth_fields[$j]] == $forum_auth_const[$k]) ? ' selected="selected"' : '';
                $custom_auth[$j] .= '<option value="' . $forum_auth_const[$k] . '"' . $selected . '>' . $lang['FORUM_' . strtoupper($forum_auth_levels[$k])] . '</OPTION>';
            }
            $custom_auth[$j] .= '</select>&nbsp;';

            $cell_title = $field_names[$forum_auth_fields[$j]];

            $template->assign_block_vars('forum_auth', array(
                'CELL_TITLE' => $cell_title,
                'S_AUTH_LEVELS_SELECT' => $custom_auth[$j],
            ));

            $s_column_span++;
        }
    }

    $adv_mode = empty($adv) ? '1' : '0';
    $switch_mode = "admin_forumauth.php?f=$forum_id&amp;adv=$adv_mode";
    $switch_mode_text = empty($adv) ? $lang['ADVANCED_MODE'] : $lang['SIMPLE_MODE'];
    $u_switch_mode = '<a href="' . $switch_mode . '">' . $switch_mode_text . '</a>';

    $s_hidden_fields = '<input type="hidden" name="' . POST_FORUM_URL . '" value="' . $forum_id . '">';

    $template->assign_vars(array(
        'TPL_EDIT_FORUM_AUTH' => true,
        'FORUM_NAME' => htmlCHR($forum_name),
        'U_SWITCH_MODE' => $u_switch_mode,
        'S_FORUMAUTH_ACTION' => 'admin_forumauth.php',
        'S_COLUMN_SPAN' => $s_column_span,
        'S_HIDDEN_FIELDS' => $s_hidden_fields,
    ));
}

print_page('admin_forumauth.tpl', 'admin');
