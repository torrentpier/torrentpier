<?php
/**
 * TorrentPier – Bull-powered BitTorrent tracker engine
 *
 * @copyright Copyright (c) 2005-2023 TorrentPier (https://torrentpier.com)
 * @link      https://github.com/torrentpier/torrentpier for the canonical source repository
 * @license   https://github.com/torrentpier/torrentpier/blob/master/LICENSE MIT License
 */

if (!empty($setmodules)) {
    $module['GROUPS']['MANAGE'] = basename(__FILE__);
    return;
}
require __DIR__ . '/pagestart.php';

$group_id = isset($_REQUEST[POST_GROUPS_URL]) ? (int)$_REQUEST[POST_GROUPS_URL] : 0;
$mode = isset($_REQUEST['mode']) ? (string)$_REQUEST['mode'] : '';

attachment_quota_settings('group', isset($_POST['group_update']), $mode);

if (!empty($_POST['edit']) || !empty($_POST['new'])) {
    if (!empty($_POST['edit'])) {
        if (!$row = \TorrentPier\Legacy\Group::get_group_data($group_id)) {
            bb_die($lang['GROUP_NOT_EXIST']);
        }
        $group_info = array(
            'group_name' => $row['group_name'],
            'group_description' => $row['group_description'],
            'group_moderator' => $row['group_moderator'],
            'group_mod_name' => $row['moderator_name'],
            'group_type' => $row['group_type'],
            'release_group' => $row['release_group'],
        );
        $mode = 'editgroup';
        $template->assign_block_vars('group_edit', []);
    } elseif (!empty($_POST['new'])) {
        $group_info = array(
            'group_name' => '',
            'group_description' => '',
            'group_moderator' => '',
            'group_mod_name' => '',
            'group_type' => GROUP_OPEN,
            'release_group' => 0,
        );
        $mode = 'newgroup';
    }

    // Ok, now we know everything about them, let's show the page.
    $s_hidden_fields = '
		<input type="hidden" name="mode" value="' . $mode . '" />
		<input type="hidden" name="' . POST_GROUPS_URL . '" value="' . $group_id . '" />
	';

    $template->assign_vars(array(
        'TPL_EDIT_GROUP' => true,

        'GROUP_NAME' => stripslashes(htmlspecialchars($group_info['group_name'])),
        'GROUP_DESCRIPTION' => stripslashes(htmlspecialchars($group_info['group_description'])),
        'GROUP_MODERATOR' => replace_quote($group_info['group_mod_name']),
        'T_GROUP_EDIT_DELETE' => ($mode == 'newgroup') ? $lang['CREATE_NEW_GROUP'] : $lang['EDIT_GROUP'],
        'U_SEARCH_USER' => BB_ROOT . 'search.php?mode=searchuser',
        'S_GROUP_OPEN_TYPE' => GROUP_OPEN,
        'S_GROUP_CLOSED_TYPE' => GROUP_CLOSED,
        'S_GROUP_HIDDEN_TYPE' => GROUP_HIDDEN,
        'S_GROUP_OPEN_CHECKED' => ($group_info['group_type'] == GROUP_OPEN) ? HTML_CHECKED : '',
        'S_GROUP_CLOSED_CHECKED' => ($group_info['group_type'] == GROUP_CLOSED) ? HTML_CHECKED : '',
        'S_GROUP_HIDDEN_CHECKED' => ($group_info['group_type'] == GROUP_HIDDEN) ? HTML_CHECKED : '',
        'RELEASE_GROUP' => $group_info['release_group'] ? true : false,
        'S_GROUP_ACTION' => 'admin_groups.php',
        'S_HIDDEN_FIELDS' => $s_hidden_fields,
    ));
} elseif (!empty($_POST['group_update'])) {
    if (!empty($_POST['group_delete'])) {
        if (!$group_info = \TorrentPier\Legacy\Group::get_group_data($group_id)) {
            bb_die($lang['GROUP_NOT_EXIST']);
        }
        // Delete Group
        \TorrentPier\Legacy\Group::delete_group($group_id);

        $message = $lang['DELETED_GROUP'] . '<br /><br />';
        $message .= sprintf($lang['CLICK_RETURN_GROUPSADMIN'], '<a href="admin_groups.php">', '</a>') . '<br /><br />';
        $message .= sprintf($lang['CLICK_RETURN_ADMIN_INDEX'], '<a href="index.php?pane=right">', '</a>');

        bb_die($message);
    } else {
        $group_type = isset($_POST['group_type']) ? (int)$_POST['group_type'] : GROUP_OPEN;
        $release_group = isset($_POST['release_group']) ? (int)$_POST['release_group'] : 0;
        $group_name = isset($_POST['group_name']) ? trim($_POST['group_name']) : '';
        $group_desc = isset($_POST['group_description']) ? trim($_POST['group_description']) : '';
        $group_moderator = $_POST['username'] ?? '';

        if ($group_name === '') {
            bb_die($lang['NO_GROUP_NAME']);
        } elseif ($group_moderator === '') {
            bb_die($lang['NO_GROUP_MODERATOR']);
        }
        $this_userdata = get_userdata($group_moderator, true);

        if (!$group_moderator = $this_userdata['user_id']) {
            bb_die($lang['NO_GROUP_MODERATOR']);
        }

        $sql_ary = array(
            'group_type' => (int)$group_type,
            'release_group' => (int)$release_group,
            'group_name' => (string)$group_name,
            'group_description' => (string)$group_desc,
            'group_moderator' => (int)$group_moderator,
            'group_single_user' => 0,
        );

        if ($mode == 'editgroup') {
            if (!$group_info = \TorrentPier\Legacy\Group::get_group_data($group_id)) {
                bb_die($lang['GROUP_NOT_EXIST']);
            }

            if ($group_info['group_moderator'] != $group_moderator) {
                // Create user_group for new group's moderator
                \TorrentPier\Legacy\Group::add_user_into_group($group_id, $group_moderator);
                $sql_ary['mod_time'] = TIMENOW;

                // Delete old moderator's user_group
                if (isset($_POST['delete_old_moderator'])) {
                    \TorrentPier\Legacy\Group::delete_user_group($group_id, $group_info['group_moderator']);
                }
            }

            $sql_args = DB()->build_array('UPDATE', $sql_ary);

            // Update group's data
            DB()->query('UPDATE ' . BB_GROUPS . " SET $sql_args WHERE group_id = $group_id");

            $message = $lang['UPDATED_GROUP'] . '<br /><br />';
            $message .= sprintf($lang['CLICK_RETURN_GROUPSADMIN'], '<a href="admin_groups.php">', '</a>') . '<br /><br />';
            $message .= sprintf($lang['CLICK_RETURN_ADMIN_INDEX'], '<a href="index.php?pane=right">', '</a>');

            bb_die($message);
        } elseif ($mode == 'newgroup') {
            $sql_ary['group_time'] = $sql_ary['mod_time'] = TIMENOW;
            $sql_args = DB()->build_array('INSERT', $sql_ary);

            // Create new group
            DB()->query('INSERT INTO ' . BB_GROUPS . " $sql_args");
            $new_group_id = DB()->sql_nextid();

            // Create user_group for group's moderator
            \TorrentPier\Legacy\Group::add_user_into_group($new_group_id, $group_moderator);

            $message = $lang['ADDED_NEW_GROUP'] . '<br /><br />';
            $message .= sprintf($lang['CLICK_RETURN_GROUPSADMIN'], '<a href="admin_groups.php">', '</a>') . '<br /><br />';
            $message .= sprintf($lang['CLICK_RETURN_ADMIN_INDEX'], '<a href="index.php?pane=right">', '</a>');

            bb_die($message);
        } else {
            bb_die($lang['NO_GROUP_ACTION']);
        }
    }
} else {
    $template->assign_vars(array(
        'TPL_GROUP_SELECT' => true,

        'S_GROUP_ACTION' => 'admin_groups.php',
        'S_GROUP_SELECT' => stripslashes(get_select('groups')),
    ));
}

print_page('admin_groups.tpl', 'admin');
