<?php
/**
 * TorrentPier – Bull-powered BitTorrent tracker engine
 *
 * @copyright Copyright (c) 2005-2025 TorrentPier (https://torrentpier.com)
 * @link      https://github.com/torrentpier/torrentpier for the canonical source repository
 * @license   https://github.com/torrentpier/torrentpier/blob/master/LICENSE MIT License
 */

if (!empty($setmodules)) {
    $module['GROUPS']['MANAGE'] = basename(__FILE__);
    return;
}

require __DIR__ . '/pagestart.php';

$group_id = request()->getInt(POST_GROUPS_URL);
$mode = request()->getString('mode');

if (request()->post->get('edit') || request()->post->get('new')) {
    if (request()->post->get('edit')) {
        if (!$row = \TorrentPier\Legacy\Group::get_group_data($group_id)) {
            bb_die(__('GROUP_NOT_EXIST'));
        }
        $group_info = [
            'group_name' => $row['group_name'],
            'group_description' => $row['group_description'],
            'group_moderator' => $row['group_moderator'],
            'group_mod_name' => $row['moderator_name'],
            'group_type' => $row['group_type'],
            'release_group' => $row['release_group']
        ];
        $mode = 'editgroup';
        template()->assign_block_vars('group_edit', []);
    } elseif (request()->post->get('new')) {
        $group_info = [
            'group_name' => '',
            'group_description' => '',
            'group_moderator' => '',
            'group_mod_name' => '',
            'group_type' => GROUP_OPEN,
            'release_group' => 0
        ];
        $mode = 'newgroup';
    }

    // Ok, now we know everything about them, let's show the page.
    $s_hidden_fields = '
		<input type="hidden" name="mode" value="' . $mode . '" />
		<input type="hidden" name="' . POST_GROUPS_URL . '" value="' . $group_id . '" />
	';

    template()->assign_vars([
        'TPL_EDIT_GROUP' => true,

        'GROUP_NAME' => stripslashes(htmlspecialchars($group_info['group_name'])),
        'GROUP_DESCRIPTION' => stripslashes(htmlspecialchars($group_info['group_description'])),
        'GROUP_MODERATOR' => replace_quote($group_info['group_mod_name']),
        'T_GROUP_EDIT_DELETE' => ($mode == 'newgroup') ? __('CREATE_NEW_GROUP') : __('EDIT_GROUP'),
        'U_SEARCH_USER' => BB_ROOT . 'search.php?mode=searchuser',
        'S_GROUP_OPEN_TYPE' => GROUP_OPEN,
        'S_GROUP_CLOSED_TYPE' => GROUP_CLOSED,
        'S_GROUP_HIDDEN_TYPE' => GROUP_HIDDEN,
        'S_GROUP_OPEN_CHECKED' => ($group_info['group_type'] == GROUP_OPEN) ? HTML_CHECKED : '',
        'S_GROUP_CLOSED_CHECKED' => ($group_info['group_type'] == GROUP_CLOSED) ? HTML_CHECKED : '',
        'S_GROUP_HIDDEN_CHECKED' => ($group_info['group_type'] == GROUP_HIDDEN) ? HTML_CHECKED : '',
        'RELEASE_GROUP' => (bool)$group_info['release_group'],
        'S_GROUP_ACTION' => 'admin_groups.php',
        'S_HIDDEN_FIELDS' => $s_hidden_fields
    ]);
} elseif (request()->post->get('group_update')) {
    if (request()->post->get('group_delete')) {
        if (!$group_info = \TorrentPier\Legacy\Group::get_group_data($group_id)) {
            bb_die(__('GROUP_NOT_EXIST'));
        }
        // Delete Group
        \TorrentPier\Legacy\Group::delete_group($group_id);

        $message = __('DELETED_GROUP') . '<br /><br />';
        $message .= sprintf(__('CLICK_RETURN_GROUPSADMIN'), '<a href="admin_groups.php">', '</a>') . '<br /><br />';
        $message .= sprintf(__('CLICK_RETURN_ADMIN_INDEX'), '<a href="index.php?pane=right">', '</a>');

        bb_die($message);
    } else {
        $group_type = request()->getInt('group_type', GROUP_OPEN);
        $release_group = request()->getInt('release_group');
        $group_name = trim(request()->getString('group_name'));
        $group_desc = trim(request()->getString('group_description'));
        $group_moderator = request()->getString('username');

        if ($group_name === '') {
            bb_die(__('NO_GROUP_NAME'));
        } elseif ($group_moderator === '') {
            bb_die(__('NO_GROUP_MODERATOR'));
        }
        $this_userdata = get_userdata($group_moderator, true);

        if (!$group_moderator = $this_userdata['user_id']) {
            bb_die(__('NO_GROUP_MODERATOR'));
        }

        $sql_ary = [
            'group_type' => (int)$group_type,
            'release_group' => (int)$release_group,
            'group_name' => (string)$group_name,
            'group_description' => (string)$group_desc,
            'group_moderator' => (int)$group_moderator,
            'group_single_user' => 0,
        ];

        if ($mode == 'editgroup') {
            if (!$group_info = \TorrentPier\Legacy\Group::get_group_data($group_id)) {
                bb_die(__('GROUP_NOT_EXIST'));
            }

            if ($group_info['group_moderator'] != $group_moderator) {
                // Create user_group for new group's moderator
                \TorrentPier\Legacy\Group::add_user_into_group($group_id, $group_moderator);
                $sql_ary['mod_time'] = TIMENOW;

                // Delete old moderator's user_group
                if (request()->post->get('delete_old_moderator')) {
                    \TorrentPier\Legacy\Group::delete_user_group($group_id, $group_info['group_moderator']);
                }
            }

            $sql_args = DB()->build_array('UPDATE', $sql_ary);

            // Update group's data
            DB()->query('UPDATE ' . BB_GROUPS . " SET $sql_args WHERE group_id = $group_id");

            $message = __('UPDATED_GROUP') . '<br /><br />';
            $message .= sprintf(__('CLICK_RETURN_GROUPSADMIN'), '<a href="admin_groups.php">', '</a>') . '<br /><br />';
            $message .= sprintf(__('CLICK_RETURN_ADMIN_INDEX'), '<a href="index.php?pane=right">', '</a>');

            bb_die($message);
        } elseif ($mode == 'newgroup') {
            $sql_ary['group_time'] = $sql_ary['mod_time'] = TIMENOW;
            $sql_args = DB()->build_array('INSERT', $sql_ary);

            // Create new group
            DB()->query('INSERT INTO ' . BB_GROUPS . " $sql_args");
            $new_group_id = DB()->sql_nextid();

            // Create user_group for group's moderator
            \TorrentPier\Legacy\Group::add_user_into_group($new_group_id, $group_moderator);

            $message = __('ADDED_NEW_GROUP') . '<br /><br />';
            $message .= sprintf(__('CLICK_RETURN_GROUPSADMIN'), '<a href="admin_groups.php">', '</a>') . '<br /><br />';
            $message .= sprintf(__('CLICK_RETURN_ADMIN_INDEX'), '<a href="index.php?pane=right">', '</a>');

            bb_die($message);
        } else {
            bb_die(__('NO_GROUP_ACTION'));
        }
    }
} else {
    template()->assign_vars([
        'TPL_GROUP_SELECT' => true,

        'S_GROUP_ACTION' => 'admin_groups.php',
        'S_GROUP_SELECT' => stripslashes(get_select('groups')),
    ]);
}

print_page('admin_groups.tpl', 'admin');
