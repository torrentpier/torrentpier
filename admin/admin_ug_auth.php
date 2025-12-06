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

use TorrentPier\Legacy\Group;

$max_forum_name_length = 50;

$yes_sign = '&radic;';
$no_sign = 'x';

// Human-readable names for forum access modes
$auth_mode_names = [
    AUTH_ALL => __('FORUM_ALL'),
    AUTH_REG => __('FORUM_REG'),
    AUTH_ACL => __('FORUM_PRIVATE'),
    AUTH_MOD => __('FORUM_MOD'),
    AUTH_ADMIN => __('FORUM_ADMIN'),
];

/**
 * Show auth updated message and exit
 */
function auth_updated_redirect(string $return_key, string $mode, string $post_param, int $id): void
{
    $message = __('AUTH_UPDATED') . '<br /><br />';
    $message .= sprintf(__($return_key), '<a href="admin_ug_auth.php?mode=' . $mode . '&' . $post_param . '=' . $id . '">', '</a>') . '<br /><br />';
    $message .= sprintf(__('CLICK_RETURN_ADMIN_INDEX'), '<a href="index.php?pane=right">', '</a>');
    bb_die($message);
}

/**
 * Assign ACL type columns to template and return column span
 */
function assign_acl_type_columns(array $forum_auth_fields): int
{
    $s_column_span = 2;
    foreach ($forum_auth_fields as $auth_type) {
        template()->assign_block_vars('acltype', [
            'ACL_TYPE_NAME' => preg_replace('#(.{5})#u', "\\1<br />", __(strtoupper($auth_type))),
            'ACL_TYPE_BF' => bitfields('forum_perm')[$auth_type],
        ]);
        $s_column_span++;
    }
    return $s_column_span;
}

/**
 * Build ACL cell data for permission matrix
 */
function build_acl_cell(int $f_perm, bool $auth_via_acl, bool $auth_mod, bool $has_group_access, array $auth_mode_names, string $yes_sign, string $no_sign): array
{
    $tooltip = '';

    if ($f_perm == AUTH_ACL) {
        $disabled = $auth_mod || $has_group_access;
        $perm_sign = ($auth_via_acl || $auth_mod) ? $yes_sign : $no_sign;
        $acl_class = ($auth_via_acl || $auth_mod) ? 'yes' : 'no';
        if ($disabled) {
            $tooltip = $auth_mod ? __('AUTH_TOOLTIP_MOD_OVERRIDE') : __('AUTH_TOOLTIP_GROUP_HAS');
        }
    } else {
        $disabled = true;
        $perm_sign = $auth_via_acl ? $yes_sign : $no_sign;
        $acl_class = $auth_via_acl ? 'yes' : 'no';
        $mode_name = $auth_mode_names[$f_perm] ?? '?';
        $tooltip = sprintf(__('AUTH_TOOLTIP_FORUM_MODE'), $mode_name);
    }

    return [
        'disabled' => $disabled,
        'perm_sign' => $perm_sign,
        'acl_class' => $acl_class,
        'tooltip' => $tooltip,
    ];
}

$group_id = isset($_REQUEST[POST_GROUPS_URL]) ? (int)$_REQUEST[POST_GROUPS_URL] : 0;
$user_id = isset($_REQUEST[POST_USERS_URL]) ? (int)$_REQUEST[POST_USERS_URL] : 0;
$cat_id = isset($_REQUEST[POST_CAT_URL]) ? (int)$_REQUEST[POST_CAT_URL] : 0;
$mode = isset($_REQUEST['mode']) ? (string)$_REQUEST['mode'] : '';
$submit = isset($_REQUEST['submit']);

// Check for demo mode
if (IN_DEMO_MODE && $submit) {
    bb_die(__('CANT_EDIT_IN_DEMO_MODE'));
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

    // Collect relevant data for this user
    if (!$row = get_userdata($user_id)) {
        bb_die(__('NO_SUCH_USER'));
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
        $group_id = Group::create_user_group($user_id);
    }

    if (!$group_id || !$user_id || null === $this_user_level) {
        throw new RuntimeException('data missing');
    }

    // Make a user an admin (if already a user)
    if (isset($_POST['userlevel'])) {
        if ($_POST['userlevel'] === 'admin') {
            if (userdata('user_id') == $user_id || $user_id == GUEST_UID || $user_id == BOT_UID) {
                bb_die(__('AUTH_GENERAL_ERROR'));
            }

            DB()->query('UPDATE ' . BB_USERS . ' SET user_level = ' . ADMIN . " WHERE user_id = $user_id");

            // Delete any entries in auth_access, they are not required if the user is becoming an admin
            Group::delete_permissions($group_id, $user_id);

            auth_updated_redirect('CLICK_RETURN_USERAUTH', $mode, POST_USERS_URL, $user_id);
        } // Make admin a user (if already admin)
        elseif ($_POST['userlevel'] === 'user') {
            // ignore if you're trying to change yourself from an admin to user!
            if (userdata('user_id') == $user_id) {
                bb_die(__('AUTH_SELF_ERROR'));
            }
            // Update users' level, reset to USER
            DB()->query('UPDATE ' . BB_USERS . ' SET user_level = ' . USER . " WHERE user_id = $user_id");

            Group::delete_permissions($group_id, $user_id);

            auth_updated_redirect('CLICK_RETURN_USERAUTH', $mode, POST_USERS_URL, $user_id);
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

    Group::delete_permissions($group_id, null, $cat_id);
    Group::store_permissions($group_id, $auth);
    Group::update_user_level($user_id);

    auth_updated_redirect('CLICK_RETURN_USERAUTH', $mode, POST_USERS_URL, $user_id);
}
//
// Submit new GROUP permissions
//
elseif ($submit && $mode == 'group' && (!empty($_POST['auth']) && is_array($_POST['auth']))) {
    if (!$group_data = Group::get_group_data($group_id)) {
        bb_die(__('GROUP_NOT_EXIST'));
    }

    $auth = [];
    array_deep($_POST['auth'], 'intval');

    foreach ($_POST['auth'] as $f_id => $bf_ary) {
        if (array_sum($bf_ary)) {
            $auth[$f_id] = bit2dec(array_keys($bf_ary, 1));
        }
    }

    Group::delete_permissions($group_id, null, $cat_id);
    Group::store_permissions($group_id, $auth);
    Group::update_user_level('all');

    auth_updated_redirect('CLICK_RETURN_GROUPAUTH', $mode, POST_GROUPS_URL, $group_id);
}

//
// Front end (changing permissions)
//
if ($mode == 'user' && (!empty($_POST['username']) || $user_id)) {
    page_cfg('quirks_mode', true);

    if (!empty($_POST['username'])) {
        $this_userdata = get_userdata($_POST['username'], true);
    } else {
        $this_userdata = get_userdata($user_id);
    }
    if (!$this_userdata) {
        bb_die(__('NO_SUCH_USER'));
    }
    $user_id = $this_userdata['user_id'];

    $forums = forum_tree();

    // Check if forums exist
    if (empty($forums['f'])) {
        $message = __('NO_FORUMS_AVAILABLE');
        $message .= '<br /><br />' . sprintf(__('CLICK_RETURN_ADMIN_INDEX'), '<a href="index.php?pane=right">', '</a>');
        bb_die($message);
    }

    $base_url = basename(__FILE__) . "?mode=user&amp;" . POST_USERS_URL . "=$user_id";

    $ug_data = $this_userdata;
    $ug_data['session_logged_in'] = 1;

    $u_access = auth(AUTH_ALL, AUTH_LIST_ALL, $ug_data, [], UG_PERM_USER_ONLY);
    $g_access = auth(AUTH_ALL, AUTH_LIST_ALL, $ug_data, [], UG_PERM_GROUP_ONLY);

    foreach ($forums['c'] as $c_id => $c_data) {
        template()->assign_block_vars('c', array(
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

            template()->assign_block_vars('c.f', array(
                'DISABLED' => $disabled,
                'FORUM_ID' => $f_id,
                'FORUM_NAME' => str_short($forums['forum_name_html'][$f_id], $max_forum_name_length),
                'SF_SPACER' => $f_data['forum_parent'] ? HTML_SF_SPACER : '',
                'IS_MODERATOR' => $auth_mod,
                'MOD_STATUS' => $auth_mod ? __('MODERATOR') : __('NONE'),
                'MOD_CLASS' => $auth_mod ? ($disabled ? 'yesDisabled' : 'yesMOD') : 'noMOD',
                'AUTH_MOD_VAL' => $auth_mod ? 1 : 0,
            ));

            foreach ($forum_auth_fields as $auth_type) {
                $bf_num = bitfields('forum_perm')[$auth_type];
                $f_perm = $f_data[$auth_type];
                $auth_via_acl = ($u_access[$f_id][$auth_type] || $g_access[$f_id][$auth_type]);
                $has_group_access = (bool)$g_access[$f_id][$auth_type];

                $cell = build_acl_cell($f_perm, $auth_via_acl, $auth_mod, $has_group_access, $auth_mode_names, $yes_sign, $no_sign);

                template()->assign_block_vars('c.f.acl', [
                    'DISABLED' => $cell['disabled'],
                    'PERM_SIGN' => $cell['perm_sign'],
                    'ACL_CLASS' => $cell['acl_class'],
                    'FORUM_ID' => $f_id,
                    'ACL_TYPE_BF' => $bf_num,
                    'ACL_VAL' => $auth_via_acl ? 1 : 0,
                    'TOOLTIP' => $cell['tooltip'],
                ]);
            }
        }
    }

    template()->assign_vars(['AUTH_MOD_BF' => AUTH_MOD]);
    $s_column_span = assign_acl_type_columns($forum_auth_fields);

    unset($forums, $u_access, $g_access);
    datastore()->rm('cat_forums');

    $s_hidden_fields = '<input type="hidden" name="mode" value="' . $mode . '" />'
        . '<input type="hidden" name="' . POST_USERS_URL . '" value="' . $user_id . '" />';

    $s_user_type = '
        <select name="userlevel">
            <option value="admin"' . ($this_userdata['user_level'] == ADMIN ? ' selected' : '') . '>' . __('AUTH_ADMIN') . '</option>
            <option value="user"' . ($this_userdata['user_level'] != ADMIN ? ' selected' : '') . '>' . __('AUTH_USER') . '</option>
        </select>';

    template()->assign_block_vars('switch_user_auth', []);

    template()->assign_vars(array(
        'TPL_AUTH_UG_MAIN' => true,
        'USER_OR_GROUPNAME' => profile_url($this_userdata, true),
        'USER_LEVEL' => __('USER_LEVEL') . ' : ' . $s_user_type,
        'T_USER_OR_GROUPNAME' => __('USERNAME'),
        'T_AUTH_TITLE' => __('AUTH_CONTROL_USER'),
        'T_AUTH_EXPLAIN' => __('USER_AUTH_EXPLAIN'),
        'S_COLUMN_SPAN' => $s_column_span,
        'S_HIDDEN_FIELDS' => $s_hidden_fields,
    ));
} elseif ($mode == 'group' && $group_id) {
    page_cfg('quirks_mode', true);

    if (!$group_data = Group::get_group_data($group_id)) {
        bb_die(__('GROUP_NOT_EXIST'));
    }

    $forums = forum_tree();

    // Check if forums exist
    if (empty($forums['f'])) {
        $message = __('NO_FORUMS_AVAILABLE');
        $message .= '<br /><br />' . sprintf(__('CLICK_RETURN_ADMIN_INDEX'), '<a href="index.php?pane=right">', '</a>');
        bb_die($message);
    }

    $base_url = basename(__FILE__) . "?mode=group&amp;" . POST_GROUPS_URL . "=$group_id";

    $ug_data = array('group_id' => $group_id);
    $u_access = auth(AUTH_ALL, AUTH_LIST_ALL, $ug_data);

    foreach ($forums['c'] as $c_id => $c_data) {
        template()->assign_block_vars('c', array(
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

            template()->assign_block_vars('c.f', array(
                'DISABLED' => false,
                'FORUM_ID' => $f_id,
                'FORUM_NAME' => str_short($forums['forum_name_html'][$f_id], $max_forum_name_length),
                'SF_SPACER' => $f_data['forum_parent'] ? HTML_SF_SPACER : '',
                'IS_MODERATOR' => (bool)$auth_mod,
                'MOD_STATUS' => $auth_mod ? __('MODERATOR') : __('NO'),
                'MOD_CLASS' => $auth_mod ? 'yesMOD' : 'noMOD',
                'AUTH_MOD_VAL' => $auth_mod ? 1 : 0,
            ));

            foreach ($forum_auth_fields as $auth_type) {
                $bf_num = bitfields('forum_perm')[$auth_type];
                $f_perm = $f_data[$auth_type];
                $auth_via_acl = $u_access[$f_id][$auth_type];

                $cell = build_acl_cell($f_perm, $auth_via_acl, $auth_mod, false, $auth_mode_names, $yes_sign, $no_sign);

                template()->assign_block_vars('c.f.acl', [
                    'DISABLED' => $cell['disabled'],
                    'PERM_SIGN' => $cell['perm_sign'],
                    'ACL_CLASS' => $cell['acl_class'],
                    'FORUM_ID' => $f_id,
                    'ACL_TYPE_BF' => $bf_num,
                    'ACL_VAL' => $auth_via_acl ? 1 : 0,
                    'TOOLTIP' => $cell['tooltip'],
                ]);
            }
        }
    }

    template()->assign_vars(['AUTH_MOD_BF' => AUTH_MOD]);
    $s_column_span = assign_acl_type_columns($forum_auth_fields);

    unset($forums, $ug_data, $u_access);
    datastore()->rm('cat_forums');

    $s_hidden_fields = '<input type="hidden" name="mode" value="' . $mode . '" />'
        . '<input type="hidden" name="' . POST_GROUPS_URL . '" value="' . $group_id . '" />';

    template()->assign_vars([
        'TPL_AUTH_UG_MAIN' => true,
        'T_USER_OR_GROUPNAME' => __('GROUP_NAME'),
        'USER_LEVEL' => false,
        'T_AUTH_TITLE' => __('AUTH_CONTROL_GROUP'),
        'T_AUTH_EXPLAIN' => __('GROUP_AUTH_EXPLAIN'),
        'USER_OR_GROUPNAME' => '<span class="gen">' . htmlCHR($group_data['group_name']) . '</span>',
        'S_COLUMN_SPAN' => $s_column_span,
        'S_HIDDEN_FIELDS' => $s_hidden_fields,
    ]);
} else {
    // Select a user/group
    if ($mode == 'user') {
        template()->assign_vars(array(
            'TPL_SELECT_USER' => true,
            'U_SEARCH_USER' => BB_ROOT . 'search.php?mode=searchuser',
        ));
    } else {
        template()->assign_vars(array(
            'TPL_SELECT_GROUP' => true,
            'S_GROUP_SELECT' => get_select('groups'),
        ));
    }

    $s_hidden_fields = '<input type="hidden" name="mode" value="' . $mode . '" />';

    template()->assign_vars(array(
        'S_HIDDEN_FIELDS' => $s_hidden_fields,
    ));
}

template()->assign_vars(array(
    'YES_SIGN' => $yes_sign,
    'NO_SIGN' => $no_sign,
    'S_AUTH_ACTION' => 'admin_ug_auth.php',
    'SELECTED_CAT' => !empty($_REQUEST[POST_CAT_URL]) ? $_REQUEST[POST_CAT_URL] : '',
    'U_ALL_FORUMS' => !empty($base_url) ? "$base_url&amp;" . POST_CAT_URL . "=all" : '',
));

print_page('admin_ug_auth.tpl', 'admin');
