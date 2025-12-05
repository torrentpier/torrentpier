<?php
/**
 * TorrentPier – Bull-powered BitTorrent tracker engine
 *
 * @copyright Copyright (c) 2005-2025 TorrentPier (https://torrentpier.com)
 * @link      https://github.com/torrentpier/torrentpier for the canonical source repository
 * @license   https://github.com/torrentpier/torrentpier/blob/master/LICENSE MIT License
 */

define('BB_SCRIPT', 'group_edit');

require __DIR__ . '/common.php';

page_cfg('include_bbcode_js', true);

// Start session management
user()->session_start(['req_login' => true]);

$group_id = isset($_REQUEST[POST_GROUPS_URL]) ? (int)$_REQUEST[POST_GROUPS_URL] : null;
$group_info = [];
$is_moderator = false;

$submit = !empty($_POST['submit']);

if ($group_id) {
    if (!$group_info = \TorrentPier\Legacy\Group::get_group_data($group_id)) {
        bb_die(__('GROUP_NOT_EXIST'));
    }
    if (!$group_info['group_id'] || !$group_info['group_moderator'] || !$group_info['moderator_name']) {
        bb_die("Invalid group data [group_id: $group_id]");
    }
    $is_moderator = (userdata('user_id') == $group_info['group_moderator'] || IS_ADMIN);
}

if ($is_moderator) {
    // Avatar
    if ($submit) {
        if (!empty($_FILES['avatar']['name']) && config()->get('group_avatars.up_allowed')) {
            $upload = new TorrentPier\Legacy\Common\Upload();

            if ($upload->init(config()->get('group_avatars'), $_FILES['avatar']) and $upload->store('avatar', ['user_id' => GROUP_AVATAR_MASK . $group_id, 'avatar_ext_id' => $group_info['avatar_ext_id']])) {
                $avatar_ext_id = (int)$upload->file_ext_id;
                DB()->query("UPDATE " . BB_GROUPS . " SET avatar_ext_id = $avatar_ext_id WHERE group_id = $group_id LIMIT 1");
            } else {
                bb_die(implode($upload->errors));
            }
        }
    }

    $group_type = '';
    if ($group_info['group_type'] == GROUP_OPEN) {
        $group_type = __('GROUP_OPEN');
    } elseif ($group_info['group_type'] == GROUP_CLOSED) {
        $group_type = __('GROUP_CLOSED');
    } elseif ($group_info['group_type'] == GROUP_HIDDEN) {
        $group_type = __('GROUP_HIDDEN');
    }

    $s_hidden_fields = '<input type="hidden" name="' . POST_GROUPS_URL . '" value="' . $group_id . '" />';

    template()->assign_vars([
        'PAGE_TITLE' => __('GROUP_CONTROL_PANEL'),
        'GROUP_NAME' => htmlCHR($group_info['group_name']),
        'GROUP_ID' => $group_id,
        'GROUP_DESCRIPTION' => htmlCHR($group_info['group_description']),
        'GROUP_SIGNATURE' => htmlCHR($group_info['group_signature']),
        'U_GROUP_URL' => GROUP_URL . $group_id,
        'RELEASE_GROUP' => (bool)$group_info['release_group'],
        'GROUP_TYPE' => $group_type,
        'S_GROUP_OPEN_TYPE' => GROUP_OPEN,
        'S_GROUP_CLOSED_TYPE' => GROUP_CLOSED,
        'S_GROUP_HIDDEN_TYPE' => GROUP_HIDDEN,
        'S_GROUP_OPEN_CHECKED' => ($group_info['group_type'] == GROUP_OPEN) ? ' checked' : '',
        'S_GROUP_CLOSED_CHECKED' => ($group_info['group_type'] == GROUP_CLOSED) ? ' checked' : '',
        'S_GROUP_HIDDEN_CHECKED' => ($group_info['group_type'] == GROUP_HIDDEN) ? ' checked' : '',
        'S_HIDDEN_FIELDS' => $s_hidden_fields,
        'S_GROUP_CONFIG_ACTION' => "group_edit.php?" . POST_GROUPS_URL . "=$group_id",

        'AVATAR_EXPLAIN' => sprintf(__('AVATAR_EXPLAIN'), config()->get('group_avatars.max_width'), config()->get('group_avatars.max_height'), humn_size(config()->get('group_avatars.max_size'))),
        'AVATAR_IMG' => get_avatar(GROUP_AVATAR_MASK . $group_id, $group_info['avatar_ext_id']),
    ]);

    template()->set_filenames(['body' => 'group_edit.tpl']);
    template()->assign_vars(['PAGE_TITLE' => __('GROUP_CONFIGURATION')]);

    require(PAGE_HEADER);

    template()->pparse('body');

    require(PAGE_FOOTER);
} else {
    $redirect = 'index.php';

    if ($group_id) {
        $redirect = GROUP_URL . $group_id;
    }
    redirect($redirect);
}
