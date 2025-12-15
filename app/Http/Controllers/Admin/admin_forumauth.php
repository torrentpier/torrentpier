<?php

/**
 * TorrentPier – Bull-powered BitTorrent tracker engine
 *
 * @copyright Copyright (c) 2005-2025 TorrentPier (https://torrentpier.com)
 * @link      https://github.com/torrentpier/torrentpier for the canonical source repository
 * @license   https://github.com/torrentpier/torrentpier/blob/master/LICENSE MIT License
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
    __('PUBLIC'),
    __('REGISTERED'),
    __('REGISTERED') . ' [' . __('HIDDEN') . ']',
    __('PRIVATE'),
    __('PRIVATE') . ' [' . __('HIDDEN') . ']',
    __('MODERATORS'),
    __('MODERATORS') . ' [' . __('HIDDEN') . ']',
];

$field_names = [];
foreach ($forum_auth_fields as $auth_type) {
    $field_names[$auth_type] = __(strtoupper($auth_type));
}

$forum_auth_levels = ['ALL', 'REG', 'PRIVATE', 'MOD', 'ADMIN'];
$forum_auth_const = [AUTH_ALL, AUTH_REG, AUTH_ACL, AUTH_MOD, AUTH_ADMIN];

if (request()->has(POST_FORUM_URL)) {
    $forum_id = request()->getInt(POST_FORUM_URL);
    $forum_sql = "WHERE forum_id = {$forum_id}";
} else {
    unset($forum_id);
    $forum_sql = '';
}

if (request()->query->has('adv')) {
    $adv = request()->query->getInt('adv');
} else {
    unset($adv);
}

$submit = request()->post->has('submit');

/**
 * Start program proper
 */
if ($submit) {
    $sql = '';

    if (!empty($forum_id)) {
        if (request()->post->has('simpleauth')) {
            $simple_ary = $simple_auth_ary[request()->post->getInt('simpleauth')];

            for ($i = 0, $iMax = count($simple_ary); $i < $iMax; $i++) {
                $sql .= (($sql != '') ? ', ' : '') . $forum_auth_fields[$i] . ' = ' . $simple_ary[$i];
            }

            if (is_array($simple_ary)) {
                $sql = 'UPDATE ' . BB_FORUMS . " SET {$sql} WHERE forum_id = {$forum_id}";
            }
        } else {
            for ($i = 0, $iMax = count($forum_auth_fields); $i < $iMax; $i++) {
                $value = (int)request()->post->get($forum_auth_fields[$i]);

                if ($forum_auth_fields[$i] == 'auth_vote') {
                    if (request()->post->get('auth_vote') == AUTH_ALL) {
                        $value = AUTH_REG;
                    }
                }

                $sql .= (($sql != '') ? ', ' : '') . $forum_auth_fields[$i] . ' = ' . $value;
            }

            $sql = 'UPDATE ' . BB_FORUMS . " SET {$sql} WHERE forum_id = {$forum_id}";
        }

        if ($sql != '') {
            $query = $sql;
            if (!empty(request()->post->get('apply_to_subforums'))) {
                // Apply to subforums if checkbox is checked
                $query .= " OR forum_parent = {$forum_id}";
            }
            if (!DB()->sql_query($query)) {
                bb_die('Could not update auth table');
            }
        }

        $forum_sql = '';
        $adv = 0;
    }

    forum_tree(refresh: true);
    CACHE('bb_cache')->rm();
    bb_die(__('FORUM_AUTH_UPDATED') . '<br /><br />' . sprintf(__('CLICK_RETURN_FORUMAUTH'), '<a href="admin_forumauth.php">', '</a>'));
}

/**
 * Get required information
 */
$forum_rows = DB()->fetch_rowset('SELECT * FROM ' . BB_FORUMS . " {$forum_sql}");

if (empty($forum_id)) {
    // Output the selection table if no forum id was specified
    template()->assign_vars([
        'TPL_AUTH_SELECT_FORUM' => true,
        'S_AUTH_ACTION' => 'admin_forumauth.php',
        'S_AUTH_SELECT' => get_forum_select('admin', 'f', null, 80),
    ]);
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
            $selected = ($matched_type == $j) ? ' selected' : '';
            $simple_auth .= '<option value="' . $j . '"' . $selected . '>' . $simple_auth_types[$j] . '</option>';
        }

        $simple_auth .= '</select>';

        template()->assign_block_vars('forum_auth', [
            'CELL_TITLE' => __('SIMPLE_MODE'),
            'S_AUTH_LEVELS_SELECT' => $simple_auth,
        ]);

        $s_column_span++;
    } else {
        // Output values of individual fields
        for ($j = 0, $jMax = count($forum_auth_fields); $j < $jMax; $j++) {
            $custom_auth[$j] = '&nbsp;<select name="' . $forum_auth_fields[$j] . '">';

            for ($k = 0, $kMax = count($forum_auth_levels); $k < $kMax; $k++) {
                $selected = ($forum_rows[0][$forum_auth_fields[$j]] == $forum_auth_const[$k]) ? ' selected' : '';
                $custom_auth[$j] .= '<option value="' . $forum_auth_const[$k] . '"' . $selected . '>' . __('FORUM_' . strtoupper($forum_auth_levels[$k])) . '</OPTION>';
            }
            $custom_auth[$j] .= '</select>&nbsp;';

            $cell_title = $field_names[$forum_auth_fields[$j]];

            template()->assign_block_vars('forum_auth', [
                'CELL_TITLE' => $cell_title,
                'S_AUTH_LEVELS_SELECT' => $custom_auth[$j],
            ]);

            $s_column_span++;
        }
    }

    $adv_mode = empty($adv) ? '1' : '0';
    $switch_mode = 'admin_forumauth.php?' . POST_FORUM_URL . "={$forum_id}&amp;adv={$adv_mode}";
    $switch_mode_text = empty($adv) ? __('ADVANCED_MODE') : __('SIMPLE_MODE');
    $u_switch_mode = '<a href="' . $switch_mode . '">' . $switch_mode_text . '</a>';

    $s_hidden_fields = '<input type="hidden" name="' . POST_FORUM_URL . '" value="' . $forum_id . '">';
    $is_subforum = $forum_rows[0]['forum_parent'] > 0;

    template()->assign_vars([
        'TPL_EDIT_FORUM_AUTH' => true,
        'FORUM_NAME' => htmlCHR($forum_name),
        'U_VIEWFORUM' => BB_ROOT . FORUM_URL . $forum_id,
        'U_SWITCH_MODE' => $u_switch_mode,
        'S_FORUMAUTH_ACTION' => 'admin_forumauth.php',
        'S_COLUMN_SPAN' => $s_column_span,
        'S_HIDDEN_FIELDS' => $s_hidden_fields,
        'IS_SUBFORUM' => $is_subforum,
    ]);
}

print_page('admin_forumauth.tpl', 'admin');
