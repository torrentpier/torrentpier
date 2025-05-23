<?php
/**
 * TorrentPier – Bull-powered BitTorrent tracker engine
 *
 * @copyright Copyright (c) 2005-2025 TorrentPier (https://torrentpier.com)
 * @link      https://github.com/torrentpier/torrentpier for the canonical source repository
 * @license   https://github.com/torrentpier/torrentpier/blob/master/LICENSE MIT License
 */

if (!empty($setmodules)) {
    $module['USERS']['PERMISSIONS'] = basename(__FILE__) . '?mode=user';
    $module['GROUPS']['PERMISSIONS'] = basename(__FILE__) . '?mode=group';
    return;
}

require __DIR__ . '/pagestart.php';

$max_forum_name_length = 50;

$yes_sign = '&radic;';
$no_sign = 'x';

$group_id = isset($_REQUEST[POST_GROUPS_URL]) ? (int)$_REQUEST[POST_GROUPS_URL] : 0;
$user_id = isset($_REQUEST[POST_USERS_URL]) ? (int)$_REQUEST[POST_USERS_URL] : 0;
$cat_id = isset($_REQUEST[POST_CAT_URL]) ? (int)$_REQUEST[POST_CAT_URL] : 0;
$mode = isset($_REQUEST['mode']) ? (string)$_REQUEST['mode'] : '';
$submit = isset($_REQUEST['submit']);

// Check for demo mode
if (IN_DEMO_MODE && $submit) {
    bb_die($lang['CANT_EDIT_IN_DEMO_MODE']);
}

$group_data = [];

$forum_auth_fields = array(
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
);

if ($submit && $mode == 'user') {
    $this_user_level = null;

    // Obtain relevant data for this user
    if (!$row = get_userdata($user_id)) {
        bb_die($lang['NO_SUCH_USER']);
    }
    $this_user_level = $row['user_level'];

    // Get "single_user" group_id for this user
    $sql = 'SELECT g.group_id
		FROM ' . BB_USER_GROUP . ' ug, ' . BB_GROUPS . " g
		WHERE ug.user_id = $user_id
			AND g.group_id = ug.group_id
			AND g.group_single_user = 1";

    if ($row = DB()->fetch_row($sql)) {
        $group_id = $row['group_id'];
    } else {
        $group_id = \TorrentPier\Legacy\Group::create_user_group($user_id);
    }

    if (!$group_id || !$user_id || null === $this_user_level) {
        trigger_error('data missing', E_USER_ERROR);
    }

    // Make user an admin (if already user)
    if (isset($_POST['userlevel'])) {
        if ($_POST['userlevel'] === 'admin') {
            if ($userdata['user_id'] == $user_id || $user_id == GUEST_UID || $user_id == BOT_UID) {
                bb_die($lang['AUTH_GENERAL_ERROR']);
            }

            DB()->query('UPDATE ' . BB_USERS . ' SET user_level = ' . ADMIN . " WHERE user_id = $user_id");

            // Delete any entries in auth_access, they are not required if user is becoming an admin
            \TorrentPier\Legacy\Group::delete_permissions($group_id, $user_id);

            $message = $lang['AUTH_UPDATED'] . '<br /><br />';
            $message .= sprintf($lang['CLICK_RETURN_USERAUTH'], '<a href="admin_ug_auth.php?mode=' . $mode . '&' . POST_USERS_URL . '=' . $user_id . '">', '</a>') . '<br /><br />';
            $message .= sprintf($lang['CLICK_RETURN_ADMIN_INDEX'], '<a href="index.php?pane=right">', '</a>');

            bb_die($message);
        } // Make admin a user (if already admin)
        elseif ($_POST['userlevel'] === 'user') {
            // ignore if you're trying to change yourself from an admin to user!
            if ($userdata['user_id'] == $user_id) {
                bb_die($lang['AUTH_SELF_ERROR']);
            }
            // Update users level, reset to USER
            DB()->query('UPDATE ' . BB_USERS . ' SET user_level = ' . USER . " WHERE user_id = $user_id");

            \TorrentPier\Legacy\Group::delete_permissions($group_id, $user_id);

            $message = $lang['AUTH_UPDATED'] . '<br /><br />';
            $message .= sprintf($lang['CLICK_RETURN_USERAUTH'], '<a href="admin_ug_auth.php?mode=' . $mode . '&' . POST_USERS_URL . '=' . $user_id . '">', '</a>') . '<br /><br />';
            $message .= sprintf($lang['CLICK_RETURN_ADMIN_INDEX'], '<a href="index.php?pane=right">', '</a>');

            bb_die($message);
        }
    }

    //
    // Submit new USER permissions
    //
    $auth = [];

    if (!empty($_POST['auth']) && is_array($_POST['auth'])) {
        array_deep($_POST['auth'], 'intval');

        foreach ($_POST['auth'] as $f_id => $bf_ary) {
            if (array_sum($bf_ary)) {
                $auth[$f_id] = bit2dec(array_keys($bf_ary, 1));
            }
        }
    }

    \TorrentPier\Legacy\Group::delete_permissions($group_id, null, $cat_id);
    \TorrentPier\Legacy\Group::store_permissions($group_id, $auth);
    \TorrentPier\Legacy\Group::update_user_level($user_id);

    $l_auth_return = ($mode == 'user') ? $lang['CLICK_RETURN_USERAUTH'] : $lang['CLICK_RETURN_GROUPAUTH'];
    $message = $lang['AUTH_UPDATED'] . '<br /><br />';
    $message .= sprintf($l_auth_return, '<a href="admin_ug_auth.php?mode=' . $mode . '&' . POST_USERS_URL . '=' . $user_id . '">', '</a>') . '<br /><br />';
    $message .= sprintf($lang['CLICK_RETURN_ADMIN_INDEX'], '<a href="index.php?pane=right">', '</a>');

    bb_die($message);
}
//
// Submit new GROUP permissions
//
elseif ($submit && $mode == 'group' && (!empty($_POST['auth']) && is_array($_POST['auth']))) {
    if (!$group_data = \TorrentPier\Legacy\Group::get_group_data($group_id)) {
        bb_die($lang['GROUP_NOT_EXIST']);
    }

    $auth = [];
    array_deep($_POST['auth'], 'intval');

    foreach ($_POST['auth'] as $f_id => $bf_ary) {
        if (array_sum($bf_ary)) {
            $auth[$f_id] = bit2dec(array_keys($bf_ary, 1));
        }
    }

    \TorrentPier\Legacy\Group::delete_permissions($group_id, null, $cat_id);
    \TorrentPier\Legacy\Group::store_permissions($group_id, $auth);
    \TorrentPier\Legacy\Group::update_user_level('all');

    $l_auth_return = $lang['CLICK_RETURN_GROUPAUTH'];
    $message = $lang['AUTH_UPDATED'] . '<br /><br />';
    $message .= sprintf($l_auth_return, '<a href="admin_ug_auth.php?mode=' . $mode . '&' . POST_GROUPS_URL . '=' . $group_id . '">', '</a>') . '<br /><br />';
    $message .= sprintf($lang['CLICK_RETURN_ADMIN_INDEX'], '<a href="index.php?pane=right">', '</a>');

    bb_die($message);
}

//
// Front end (changing permissions)
//
if ($mode == 'user' && (!empty($_POST['username']) || $user_id)) {
    $page_cfg['quirks_mode'] = true;

    if (!empty($_POST['username'])) {
        $this_userdata = get_userdata($_POST['username'], true);
        $user_id = $this_userdata['user_id'];
    } else {
        $this_userdata = get_userdata($user_id);
    }
    if (!$this_userdata) {
        bb_die($lang['NO_SUCH_USER']);
    }

    if (!$forums = $datastore->get('cat_forums')) {
        $datastore->update('cat_forums');
        $forums = $datastore->get('cat_forums');
    }
    $base_url = basename(__FILE__) . "?mode=user&amp;" . POST_USERS_URL . "=$user_id";

    $ug_data = $this_userdata;
    $ug_data['session_logged_in'] = 1;

    $u_access = auth(AUTH_ALL, AUTH_LIST_ALL, $ug_data, [], UG_PERM_USER_ONLY);
    $g_access = auth(AUTH_ALL, AUTH_LIST_ALL, $ug_data, [], UG_PERM_GROUP_ONLY);

    foreach ($forums['c'] as $c_id => $c_data) {
        $template->assign_block_vars('c', array(
            'CAT_ID' => $c_id,
            'CAT_TITLE' => $forums['cat_title_html'][$c_id],
            'CAT_HREF' => "$base_url&amp;" . POST_CAT_URL . "=$c_id",
        ));

        if (!$c =& $_REQUEST[POST_CAT_URL] or !in_array($c, array('all', $c_id)) or empty($c_data['forums'])) {
            continue;
        }

        foreach ($c_data['forums'] as $f_id) {
            $f_data = $forums['f'][$f_id];
            $auth_mod = ($u_access[$f_id]['auth_mod'] || $g_access[$f_id]['auth_mod']);
            $disabled = $g_access[$f_id]['auth_mod'];

            $template->assign_block_vars('c.f', array(
                'DISABLED' => $disabled,
                'FORUM_ID' => $f_id,
                'FORUM_NAME' => str_short($forums['forum_name_html'][$f_id], $max_forum_name_length),
                'SF_SPACER' => $f_data['forum_parent'] ? HTML_SF_SPACER : '',
                'IS_MODERATOR' => (bool)$auth_mod,
                'MOD_STATUS' => $auth_mod ? $lang['MODERATOR'] : $lang['NONE'],
                'MOD_CLASS' => $auth_mod ? ($disabled ? 'yesDisabled' : 'yesMOD') : 'noMOD',
                'AUTH_MOD_VAL' => $auth_mod ? 1 : 0,
            ));

            foreach ($forum_auth_fields as $auth_type) {
                $bf_num = $bf['forum_perm'][$auth_type];
                $f_perm = $f_data[$auth_type];
                $auth_via_acl = ($u_access[$f_id][$auth_type] || $g_access[$f_id][$auth_type]);

                if ($f_perm == AUTH_ACL) {
                    $disabled = ($auth_mod || $g_access[$f_id][$auth_type]);
                    $perm_sign = ($auth_via_acl || $auth_mod) ? $yes_sign : $no_sign;
                    $acl_class = ($auth_via_acl || $auth_mod) ? 'yes' : 'no';
                } else {
                    $disabled = true;
                    $perm_sign = $auth_via_acl ? $yes_sign : $no_sign;
                    $acl_class = $auth_via_acl ? 'yes' : 'no';
                }

                $template->assign_block_vars('c.f.acl', array(
                    'DISABLED' => $disabled,
                    'PERM_SIGN' => $perm_sign,
                    'ACL_CLASS' => $acl_class,
                    'FORUM_ID' => $f_id,
                    'ACL_TYPE_BF' => $bf_num,
                    'ACL_VAL' => $auth_via_acl ? 1 : 0,
                ));
            }
        }
    }

    $template->assign_vars(array(
        'AUTH_MOD_BF' => AUTH_MOD,
    ));

    $s_column_span = 2;

    foreach ($forum_auth_fields as $auth_type) {
        $template->assign_block_vars('acltype', array(
            'ACL_TYPE_NAME' => preg_replace('#(.{5})#u', "\\1<br />", $lang[strtoupper($auth_type)]),
            'ACL_TYPE_BF' => $bf['forum_perm'][$auth_type],
        ));
        $s_column_span++;
    }

    unset($forums, $u_access, $g_access);
    $datastore->rm('cat_forums');

    $s_hidden_fields = '
		<input type="hidden" name="mode" value="' . $mode . '" />
		<input type="hidden" name="' . POST_USERS_URL . '" value="' . $user_id . '" />
	';

    $s_user_type = ($this_userdata['user_level'] == ADMIN) ? '
		<select name="userlevel">
			<option value="admin" selected>' . $lang['AUTH_ADMIN'] . '</option>
			<option value="user">' . $lang['AUTH_USER'] . '</option>
		</select>
	' : '
		<select name="userlevel">
			<option value="admin">' . $lang['AUTH_ADMIN'] . '</option>
			<option value="user" selected>' . $lang['AUTH_USER'] . '</option>
		</select>
	';

    $template->assign_block_vars('switch_user_auth', []);

    $template->assign_vars(array(
        'TPL_AUTH_UG_MAIN' => true,
        'USER_OR_GROUPNAME' => profile_url($this_userdata, true),
        'USER_LEVEL' => $lang['USER_LEVEL'] . ' : ' . $s_user_type,
        'T_USER_OR_GROUPNAME' => $lang['USERNAME'],
        'T_AUTH_TITLE' => $lang['AUTH_CONTROL_USER'],
        'T_AUTH_EXPLAIN' => $lang['USER_AUTH_EXPLAIN'],
        'S_COLUMN_SPAN' => $s_column_span,
        'S_HIDDEN_FIELDS' => $s_hidden_fields,
    ));
} elseif ($mode == 'group' && $group_id) {
    $page_cfg['quirks_mode'] = true;

    if (!$group_data = \TorrentPier\Legacy\Group::get_group_data($group_id)) {
        bb_die($lang['GROUP_NOT_EXIST']);
    }

    if (!$forums = $datastore->get('cat_forums')) {
        $datastore->update('cat_forums');
        $forums = $datastore->get('cat_forums');
    }
    $base_url = basename(__FILE__) . "?mode=group&amp;" . POST_GROUPS_URL . "=$group_id";

    $ug_data = array('group_id' => $group_id);
    $u_access = auth(AUTH_ALL, AUTH_LIST_ALL, $ug_data);

    foreach ($forums['c'] as $c_id => $c_data) {
        $template->assign_block_vars('c', array(
            'CAT_ID' => $c_id,
            'CAT_TITLE' => $forums['cat_title_html'][$c_id],
            'CAT_HREF' => "$base_url&amp;" . POST_CAT_URL . "=$c_id",
        ));

        if (!($c =& $_REQUEST[POST_CAT_URL]) || !in_array($c, array('all', $c_id)) || empty($c_data['forums'])) {
            continue;
        }

        foreach ($c_data['forums'] as $f_id) {
            $f_data = $forums['f'][$f_id];
            $auth_mod = $u_access[$f_id]['auth_mod'];

            $template->assign_block_vars('c.f', array(
                'DISABLED' => false,
                'FORUM_ID' => $f_id,
                'FORUM_NAME' => str_short($forums['forum_name_html'][$f_id], $max_forum_name_length),
                'SF_SPACER' => $f_data['forum_parent'] ? HTML_SF_SPACER : '',
                'IS_MODERATOR' => (bool)$auth_mod,
                'MOD_STATUS' => $auth_mod ? $lang['MODERATOR'] : $lang['NO'],
                'MOD_CLASS' => $auth_mod ? 'yesMOD' : 'noMOD',
                'AUTH_MOD_VAL' => $auth_mod ? 1 : 0,
            ));

            foreach ($forum_auth_fields as $auth_type) {
                $bf_num = $bf['forum_perm'][$auth_type];
                $f_perm = $f_data[$auth_type];
                $auth_via_acl = $u_access[$f_id][$auth_type];

                if ($f_perm == AUTH_ACL) {
                    $disabled = $auth_mod;
                    $perm_sign = ($auth_via_acl || $auth_mod) ? $yes_sign : $no_sign;
                    $acl_class = ($auth_via_acl || $auth_mod) ? 'yes' : 'no';
                } else {
                    $disabled = true;
                    $perm_sign = $auth_via_acl ? $yes_sign : $no_sign;
                    $acl_class = $auth_via_acl ? 'yes' : 'no';
                }

                $template->assign_block_vars('c.f.acl', array(
                    'DISABLED' => $disabled,
                    'PERM_SIGN' => $perm_sign,
                    'ACL_CLASS' => $acl_class,
                    'FORUM_ID' => $f_id,
                    'ACL_TYPE_BF' => $bf_num,
                    'ACL_VAL' => $auth_via_acl ? 1 : 0,
                ));
            }
        }
    }

    $template->assign_vars(array(
        'AUTH_MOD_BF' => AUTH_MOD,
    ));

    $s_column_span = 2;

    foreach ($forum_auth_fields as $auth_type) {
        $template->assign_block_vars('acltype', array(
            'ACL_TYPE_NAME' => preg_replace('#(.{5})#u', "\\1<br />", $lang[strtoupper($auth_type)]),
            'ACL_TYPE_BF' => $bf['forum_perm'][$auth_type],
        ));
        $s_column_span++;
    }

    unset($forums, $ug_data, $u_access);
    $datastore->rm('cat_forums');

    $s_hidden_fields = '
		<input type="hidden" name="mode" value="' . $mode . '" />
		<input type="hidden" name="' . POST_GROUPS_URL . '" value="' . $group_id . '" />
	';

    $template->assign_vars(array(
        'TPL_AUTH_UG_MAIN' => true,
        'T_USER_OR_GROUPNAME' => $lang['GROUP_NAME'],
        'USER_LEVEL' => false,
        'T_AUTH_TITLE' => $lang['AUTH_CONTROL_GROUP'],
        'T_AUTH_EXPLAIN' => $lang['GROUP_AUTH_EXPLAIN'],
        'USER_OR_GROUPNAME' => ('<span class="gen">' . htmlCHR($group_data['group_name']) . '</span>'),
        'S_COLUMN_SPAN' => $s_column_span,
        'S_HIDDEN_FIELDS' => $s_hidden_fields,
    ));
} else {
    // Select a user/group
    if ($mode == 'user') {
        $template->assign_vars(array(
            'TPL_SELECT_USER' => true,
            'U_SEARCH_USER' => BB_ROOT . 'search.php?mode=searchuser',
        ));
    } else {
        $template->assign_vars(array(
            'TPL_SELECT_GROUP' => true,
            'S_GROUP_SELECT' => get_select('groups'),
        ));
    }

    $s_hidden_fields = '<input type="hidden" name="mode" value="' . $mode . '" />';

    $template->assign_vars(array(
        'S_HIDDEN_FIELDS' => $s_hidden_fields,
    ));
}

$template->assign_vars(array(
    'YES_SIGN' => $yes_sign,
    'NO_SIGN' => $no_sign,
    'S_AUTH_ACTION' => 'admin_ug_auth.php',
    'SELECTED_CAT' => !empty($_REQUEST[POST_CAT_URL]) ? $_REQUEST[POST_CAT_URL] : '',
    'U_ALL_FORUMS' => !empty($base_url) ? "$base_url&amp;" . POST_CAT_URL . "=all" : '',
));

print_page('admin_ug_auth.tpl', 'admin');
