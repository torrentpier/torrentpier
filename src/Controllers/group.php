<?php
/**
 * TorrentPier – Bull-powered BitTorrent tracker engine
 *
 * @copyright Copyright (c) 2005-2025 TorrentPier (https://torrentpier.com)
 * @link      https://github.com/torrentpier/torrentpier for the canonical source repository
 * @license   https://github.com/torrentpier/torrentpier/blob/master/LICENSE MIT License
 */

require INC_DIR . '/bbcode.php';

page_cfg('use_tablesorter', true);

$s_member_groups = $s_pending_groups = $s_member_groups_opt = $s_pending_groups_opt = '';
$select_sort_mode = $select_sort_order = '';

// Init userdata
user()->session_start(['req_login' => true]);

set_die_append_msg();

$group_id = isset($_REQUEST[POST_GROUPS_URL]) ? (int)$_REQUEST[POST_GROUPS_URL] : null;
$start = isset($_REQUEST['start']) ? abs((int)$_REQUEST['start']) : 0;
$per_page = config()->get('group_members_per_page');
$view_mode = isset($_REQUEST['view']) ? (string)$_REQUEST['view'] : null;
$rel_limit = 50;

$group_info = [];
$is_moderator = false;

if ($group_id) {
    if (!$group_info = \TorrentPier\Legacy\Group::get_group_data($group_id)) {
        bb_die(__('GROUP_NOT_EXIST'));
    }
    if (!$group_info['group_id'] || !$group_info['group_moderator'] || !$group_info['moderator_name']) {
        bb_die("Invalid group data [group_id: $group_id]");
    }
    $is_moderator = (userdata('user_id') == $group_info['group_moderator'] || IS_ADMIN);
}

if (!$group_id) {
    // Show the main screen where the user can select a group.
    $groups = [];
    $pending = 10;
    $member = 20;

    $sql = "
		SELECT
			g.group_name, g.group_description, g.group_id, g.group_type, g.release_group,
			IF(ug.user_id IS NOT NULL, IF(ug.user_pending = 1, $pending, $member), 0) AS membership,
			g.group_moderator, u.username AS moderator_name,
			IF(g.group_moderator = ug.user_id, 1, 0) AS is_group_mod,
			COUNT(ug2.user_id) AS members, SUM(ug2.user_pending) AS candidates
		FROM
			" . BB_GROUPS . " g
		LEFT JOIN
			" . BB_USER_GROUP . " ug ON
			    ug.group_id = g.group_id
			AND ug.user_id = " . userdata('user_id') . "
		LEFT JOIN
			" . BB_USER_GROUP . " ug2 ON
			    ug2.group_id = g.group_id
		LEFT JOIN
			" . BB_USERS . " u ON g.group_moderator = u.user_id
		WHERE
			g.group_single_user = 0
		GROUP BY g.group_id
		ORDER BY
			is_group_mod DESC,
			membership   DESC,
			g.group_type ASC,
			g.group_name ASC
	";

    foreach (DB()->fetch_rowset($sql) as $row) {
        if ($row['is_group_mod']) {
            $type = 'MOD';
        } elseif ($row['membership'] == $member) {
            $type = 'MEMBER';
        } elseif ($row['membership'] == $pending) {
            $type = 'PENDING';
        } elseif ($row['group_type'] == GROUP_OPEN) {
            $type = 'OPEN';
        } elseif ($row['group_type'] == GROUP_CLOSED) {
            $type = 'CLOSED';
        } elseif ($row['group_type'] == GROUP_HIDDEN && IS_ADMIN) {
            $type = 'HIDDEN';
        } else {
            continue;
        }

        $data = ['id' => $row['group_id'], 'm' => ($row['members'] - $row['candidates']), 'c' => $row['candidates'], 'rg' => $row['release_group']];

        $groups[$type][$row['group_name']] = $data;
    }

    function build_group($params)
    {
        $options = '';
        foreach ($params as $name => $data) {
            $text = str_short(rtrim(htmlCHR($name)), HTML_SELECT_MAX_LENGTH);

            $members = ($data['m']) ? __('MEMBERS_IN_GROUP') . ': ' . $data['m'] : __('NO_GROUP_MEMBERS');
            $candidates = ($data['c']) ? __('PENDING_MEMBERS') . ': ' . $data['c'] : __('NO_PENDING_GROUP_MEMBERS');

            $options .= '<li class="pad_2"><a href="' . GROUP_URL . $data['id'] . '" class="med bold">' . $text . '</a></li>';
            $options .= ($data['rg']) ? '<ul><li class="med">' . __('RELEASE_GROUP') . '</li>' : '<ul>';
            $options .= '<li class="seedmed">' . $members . '</li>';
            if (IS_AM) {
                $options .= '<li class="leechmed">' . $candidates . '</li>';
            }
            $options .= '</ul>';
        }
        return $options;
    }

    if ($groups) {
        $s_hidden_fields = '';

        foreach ($groups as $type => $grp) {
            template()->assign_block_vars('groups', [
                'MEMBERSHIP' => __('GROUP_MEMBER_' . strtoupper($type)),
                'GROUP_SELECT' => build_group($grp)
            ]);
        }

        template()->assign_vars([
            'SELECT_GROUP' => true,
            'PAGE_TITLE' => __('GROUP_CONTROL_PANEL'),
            'S_USERGROUP_ACTION' => 'group',
            'S_HIDDEN_FIELDS' => $s_hidden_fields
        ]);
    } else {
        if (IS_ADMIN) {
            redirect('admin/admin_groups.php');
        } else {
            bb_die(__('NO_GROUPS_EXIST'));
        }
    }
} elseif (isset($_POST['joingroup']) && $_POST['joingroup']) {
    if ($group_info['group_type'] != GROUP_OPEN) {
        bb_die(__('THIS_CLOSED_GROUP'));
    }

    $sql = "SELECT g.group_id, g.group_name, ug.user_id, u.user_email, u.username, u.user_lang
		FROM " . BB_GROUPS . " g
		LEFT JOIN " . BB_USERS . " u ON(u.user_id = g.group_moderator)
		LEFT JOIN " . BB_USER_GROUP . " ug ON(ug.group_id = g.group_id AND ug.user_id = " . userdata('user_id') . ")
		WHERE g.group_id = $group_id
			AND group_single_user = 0
			AND g.group_type = " . GROUP_OPEN . "
		LIMIT 1";

    $row = $moderator = DB()->fetch_row($sql);

    if (!$row['group_id']) {
        bb_die(__('NO_GROUPS_EXIST'));
    }
    if ($row['user_id']) {
        set_die_append_msg(group_id: $group_id);
        bb_die(__('ALREADY_MEMBER_GROUP'));
    }

    \TorrentPier\Legacy\Group::add_user_into_group($group_id, userdata('user_id'), 1, TIMENOW);

    if (config()->get('group_send_email')) {
        // Sending email
        $emailer = new TorrentPier\Emailer();

        $emailer->set_to($moderator['user_email'], $moderator['username']);
        $emailer->set_subject(__('EMAILER_SUBJECT')['GROUP_REQUEST']);

        $emailer->set_template('group_request', $moderator['user_lang']);
        $emailer->assign_vars([
            'USER' => userdata('username'),
            'GROUP_MODERATOR' => $moderator['username'],
            'U_GROUP' => make_url(GROUP_URL . $group_id)
        ]);

        $emailer->send();
    }

    set_die_append_msg(group_id: $group_id);
    bb_die(__('GROUP_JOINED'));
} elseif (!empty($_POST['unsub']) || !empty($_POST['unsubpending'])) {
    \TorrentPier\Legacy\Group::delete_user_group($group_id, userdata('user_id'));

    set_die_append_msg(group_id: $group_id);
    bb_die(__('UNSUB_SUCCESS'));
} else {
    // Handle Additions, removals, approvals and denials
    $group_moderator = $group_info['group_moderator'];

    if (!empty($_POST['add']) || !empty($_POST['remove']) || !empty($_POST['approve']) || !empty($_POST['deny'])) {
        if (!$is_moderator) {
            set_die_append_msg(group_id: $group_id);
            bb_die(__('NOT_GROUP_MODERATOR'));
        }

        if (!empty($_POST['add'])) {
            if (isset($_POST['username']) && !($row = get_userdata($_POST['username'], true))) {
                set_die_append_msg(group_id: $group_id);
                bb_die(__('COULD_NOT_ADD_USER'));
            }

            // Prevent adding moderator
            if ($row['user_id'] == $group_moderator) {
                set_die_append_msg(group_id: $group_id);
                bb_die(sprintf(__('USER_IS_MOD_GROUP'), profile_url($row)));
            }

            // Prevent infinity user adding into group
            if ($is_member = DB()->fetch_row("SELECT user_id FROM " . BB_USER_GROUP . " WHERE group_id = $group_id AND user_id = " . $row['user_id'] . " LIMIT 1")) {
                if ($is_member['user_id']) {
                    set_die_append_msg(group_id: $group_id);
                    bb_die(sprintf(__('USER_IS_MEMBER_GROUP'), profile_url($row)));
                }
            }

            \TorrentPier\Legacy\Group::add_user_into_group($group_id, $row['user_id']);

            if (config()->get('group_send_email')) {
                // Sending email
                $emailer = new TorrentPier\Emailer();

                $emailer->set_to($row['user_email'], $row['username']);
                $emailer->set_subject(__('EMAILER_SUBJECT')['GROUP_ADDED']);

                $emailer->set_template('group_added', $row['user_lang']);
                $emailer->assign_vars([
                    'GROUP_NAME' => $group_info['group_name'],
                    'U_GROUP' => make_url(GROUP_URL . $group_id)
                ]);

                $emailer->send();
            }
        } else {
            if (((!empty($_POST['approve']) || !empty($_POST['deny'])) && !empty($_POST['pending_members'])) || (!empty($_POST['remove']) && !empty($_POST['members']))) {
                $members = (!empty($_POST['approve']) || !empty($_POST['deny'])) ? $_POST['pending_members'] : $_POST['members'];

                $sql_in = [];
                foreach ($members as $members_id) {
                    $sql_in[] = (int)$members_id;
                }
                if (!$sql_in = implode(',', $sql_in)) {
                    set_die_append_msg(group_id: $group_id);
                    bb_die(__('NONE_SELECTED'));
                }

                if (!empty($_POST['approve'])) {
                    DB()->query("
						UPDATE " . BB_USER_GROUP . " SET
							user_pending = 0
						WHERE user_id IN($sql_in)
							AND group_id = $group_id
					");

                    \TorrentPier\Legacy\Group::update_user_level($sql_in);
                } elseif (!empty($_POST['deny']) || !empty($_POST['remove'])) {
                    DB()->query("
						DELETE FROM " . BB_USER_GROUP . "
						WHERE user_id IN($sql_in)
							AND group_id = $group_id
					");

                    if (!empty($_POST['remove'])) {
                        \TorrentPier\Legacy\Group::update_user_level($sql_in);
                    }
                }
                // Email users when they are approved
                if (!empty($_POST['approve']) && config()->get('group_send_email')) {
                    $sql_select = "SELECT username, user_email, user_lang
                        FROM " . BB_USERS . "
                        WHERE user_id IN($sql_in)";

                    if (!$result = DB()->sql_query($sql_select)) {
                        bb_die('Could not get user email information');
                    }

                    foreach (DB()->fetch_rowset($sql_select) as $row) {
                        // Sending email
                        $emailer = new TorrentPier\Emailer();

                        $emailer->set_to($row['user_email'], $row['username']);
                        $emailer->set_subject(__('EMAILER_SUBJECT')['GROUP_APPROVED']);

                        $emailer->set_template('group_approved', $row['user_lang']);
                        $emailer->assign_vars([
                            'GROUP_NAME' => $group_info['group_name'],
                            'U_GROUP' => make_url(GROUP_URL . $group_id)
                        ]);

                        $emailer->send();
                    }
                }
            }
        }
    }
    // END approve or deny

    // Get moderator details for this group
    $group_moderator = DB()->fetch_row("
		SELECT *
		FROM " . BB_USERS . "
		WHERE user_id = " . $group_info['group_moderator'] . "
	");

    // Current user membership
    $is_group_member = $is_group_pending_member = false;

    $sql = "SELECT user_pending
		FROM " . BB_USER_GROUP . "
		WHERE group_id = $group_id
			AND user_id = " . userdata('user_id') . "
		LIMIT 1";

    if ($row = DB()->fetch_row($sql)) {
        if ($row['user_pending'] == 0) {
            $is_group_member = true;
        } else {
            $is_group_pending_member = true;
        }
    }

    if (userdata('user_id') == $group_moderator['user_id']) {
        $group_details = __('ARE_GROUP_MODERATOR');
        $s_hidden_fields = '<input type="hidden" name="' . POST_GROUPS_URL . '" value="' . $group_id . '" />';
    } elseif ($is_group_member || $is_group_pending_member) {
        template()->assign_vars([
            'SHOW_UNSUBSCRIBE_CONTROLS' => true,
            'CONTROL_NAME' => ($is_group_member) ? 'unsub' : 'unsubpending',
        ]);
        $group_details = ($is_group_pending_member) ? __('PENDING_THIS_GROUP') : __('MEMBER_THIS_GROUP');
        $s_hidden_fields = '<input type="hidden" name="' . POST_GROUPS_URL . '" value="' . $group_id . '" />';
    } elseif (IS_GUEST) {
        $group_details = __('LOGIN_TO_JOIN');
        $s_hidden_fields = '';
    } else {
        if ($group_info['group_type'] == GROUP_OPEN) {
            template()->assign_var('SHOW_SUBSCRIBE_CONTROLS');

            $group_details = __('THIS_OPEN_GROUP');
            $s_hidden_fields = '<input type="hidden" name="' . POST_GROUPS_URL . '" value="' . $group_id . '" />';
        } elseif ($group_info['group_type'] == GROUP_CLOSED) {
            $group_details = __('THIS_CLOSED_GROUP');
            $s_hidden_fields = '';
        } elseif ($group_info['group_type'] == GROUP_HIDDEN) {
            $group_details = __('THIS_HIDDEN_GROUP');
            $s_hidden_fields = '';
        }
    }

    // Add the moderator
    $username = $group_moderator['username'];
    $user_id = $group_moderator['user_id'];

    $moderator_info = generate_user_info($group_moderator, $is_moderator);

    $group_type = '';
    if ($group_info['group_type'] == GROUP_OPEN) {
        $group_type = __('GROUP_OPEN');
    } elseif ($group_info['group_type'] == GROUP_CLOSED) {
        $group_type = __('GROUP_CLOSED');
    } elseif ($group_info['group_type'] == GROUP_HIDDEN) {
        $group_type = __('GROUP_HIDDEN');
    }

    $i = 0;
    template()->assign_vars([
        'ROW_NUMBER' => $i + ($start + 1),
        'GROUP_INFO' => true,
        'PAGE_TITLE' => __('GROUP_CONTROL_PANEL'),
        'GROUP_NAME' => htmlCHR($group_info['group_name']),
        'GROUP_DESCRIPTION' => bbcode2html($group_info['group_description']),
        'GROUP_SIGNATURE' => bbcode2html($group_info['group_signature']),
        'GROUP_AVATAR' => get_avatar(GROUP_AVATAR_MASK . $group_id, $group_info['avatar_ext_id']),
        'GROUP_DETAILS' => $group_details,
        'GROUP_TIME' => !empty($group_info['group_time']) ? sprintf('%s <span class="signature">(%s)</span>', bb_date($group_info['group_time']), humanTime($group_info['group_time'])) : __('NONE'),
        'MOD_USER' => profile_url($group_moderator),
        'MOD_AVATAR' => $moderator_info['avatar'],
        'MOD_FROM' => $moderator_info['from'],
        'MOD_JOINED' => $moderator_info['joined'],
        'MOD_JOINED_RAW' => $moderator_info['joined_raw'],
        'MOD_POSTS' => $moderator_info['posts'],
        'MOD_PM' => $moderator_info['pm'],
        'MOD_EMAIL' => $moderator_info['email'],
        'MOD_WWW' => $moderator_info['www'],
        'MOD_TIME' => !empty($group_info['mod_time']) ? sprintf('%s <span class="signature">(%s)</span>', bb_date($group_info['mod_time']), humanTime($group_info['mod_time'])) : __('NONE'),
        'MOD_TIME_RAW' => !empty($group_info['mod_time']) ? $group_info['mod_time'] : '',
        'U_SEARCH_USER' => 'search?mode=searchuser',
        'U_SEARCH_RELEASES' => "tracker?srg=$group_id",
        'U_GROUP_RELEASES' => GROUP_URL . $group_id . "&view=releases",
        'U_GROUP_MEMBERS' => GROUP_URL . $group_id . "&view=members",
        'U_GROUP_CONFIG' => "group_edit?" . POST_GROUPS_URL . "=$group_id",
        'RELEASE_GROUP' => (bool)$group_info['release_group'],
        'GROUP_TYPE' => $group_type,

        'S_GROUP_OPEN_TYPE' => GROUP_OPEN,
        'S_GROUP_CLOSED_TYPE' => GROUP_CLOSED,
        'S_GROUP_HIDDEN_TYPE' => GROUP_HIDDEN,
        'S_GROUP_OPEN_CHECKED' => ($group_info['group_type'] == GROUP_OPEN) ? ' checked' : '',
        'S_GROUP_CLOSED_CHECKED' => ($group_info['group_type'] == GROUP_CLOSED) ? ' checked' : '',
        'S_GROUP_HIDDEN_CHECKED' => ($group_info['group_type'] == GROUP_HIDDEN) ? ' checked' : '',
        'S_HIDDEN_FIELDS' => $s_hidden_fields,
        'S_MODE_SELECT' => $select_sort_mode,
        'S_ORDER_SELECT' => $select_sort_order,

        'S_GROUP_ACTION' => GROUP_URL . $group_id,
    ]);

    switch ($view_mode) {
        case 'releases':
            // TODO Correct SQL to posts with attach and limit them, optimization

            if (!$group_info['release_group']) {
                set_die_append_msg(group_id: $group_id);
                bb_die(__('NOT_A_RELEASE_GROUP'));
            }

            // Count releases for pagination
            $all_releases = DB()->fetch_rowset("
				SELECT p.topic_id, p.forum_id, p.poster_id, t.topic_title, t.topic_time, f.forum_name, u.username, u.avatar_ext_id, u.user_opt, u.user_rank
				FROM " . BB_POSTS . " p
				LEFT JOIN " . BB_TOPICS . " t ON(p.topic_id = t.topic_id)
				LEFT JOIN " . BB_FORUMS . " f ON(p.forum_id= f.forum_id)
				LEFT JOIN " . BB_USERS . " u ON(p.poster_id = u.user_id)
				WHERE p.poster_rg_id = $group_id
				ORDER BY t.topic_time DESC
				LIMIT $rel_limit
			");
            $count_releases = count($all_releases);

            generate_pagination(GROUP_URL . $group_id . "&amp;view=releases", $count_releases, $per_page, $start);

            $sql = "
				SELECT p.topic_id, p.forum_id, p.poster_id, t.topic_title, t.topic_time, f.forum_name, u.username, u.avatar_ext_id, u.user_opt, u.user_rank
				FROM " . BB_POSTS . " p
				LEFT JOIN " . BB_TOPICS . " t ON(p.topic_id = t.topic_id)
				LEFT JOIN " . BB_FORUMS . " f ON(p.forum_id= f.forum_id)
				LEFT JOIN " . BB_USERS . " u ON(p.poster_id = u.user_id)
				WHERE p.poster_rg_id = $group_id
				ORDER BY t.topic_time DESC
				LIMIT $start, $per_page
			";

            if (!$releases = DB()->fetch_rowset($sql)) {
                set_die_append_msg(group_id: $group_id);
                bb_die(__('NO_SEARCH_MATCH'));
            }

            foreach ($releases as $i => $release) {
                $row_class = !($i % 2) ? 'row1' : 'row2';

                template()->assign_block_vars('releases', [
                    'ROW_NUMBER' => $i + ($start + 1),
                    'ROW_CLASS' => $row_class,
                    'RELEASER' => profile_url(['user_id' => $release['poster_id'], 'username' => $release['username'], 'user_rank' => $release['user_rank']]),
                    'AVATAR_IMG' => get_avatar($release['poster_id'], $release['avatar_ext_id'], !bf($release['user_opt'], 'user_opt', 'dis_avatar'), 50, 50),
                    'RELEASE_NAME' => sprintf('<a href="%s">%s</a>', TOPIC_URL . $release['topic_id'], htmlCHR($release['topic_title'])),
                    'RELEASE_TIME' => bb_date($release['topic_time']),
                    'RELEASE_FORUM' => sprintf('<a href="%s">%s</a>', FORUM_URL . $release['forum_id'], htmlCHR($release['forum_name'])),
                ]);
            }

            template()->assign_vars(['RELEASES' => true]);

            break;

        case 'members':
        default:

            // Members
            $count_members = DB()->fetch_rowset("
				SELECT u.username, u.user_rank, u.user_id, u.user_opt, u.user_posts, u.user_regdate, u.user_from, u.user_website, u.user_email, ug.user_pending, ug.user_time
				FROM " . BB_USER_GROUP . " ug, " . BB_USERS . " u
				WHERE ug.group_id = $group_id
					AND ug.user_pending = 0
					AND ug.user_id <> " . $group_moderator['user_id'] . "
					AND u.user_id = ug.user_id
				ORDER BY u.username
			");
            $count_members = count($count_members);

            // Get user information for this group
            $modgroup_pending_count = 0;

            // Members
            $group_members = DB()->fetch_rowset("
				SELECT u.username, u.avatar_ext_id, u.user_rank, u.user_id, u.user_opt, u.user_posts, u.user_regdate, u.user_from, u.user_website, u.user_email, ug.user_pending, ug.user_time
				FROM " . BB_USER_GROUP . " ug, " . BB_USERS . " u
				WHERE ug.group_id = $group_id
					AND ug.user_pending = 0
					AND ug.user_id <> " . $group_moderator['user_id'] . "
					AND u.user_id = ug.user_id
				ORDER BY u.username
				LIMIT $start, $per_page
			");
            $members_count = count($group_members);

            generate_pagination(GROUP_URL . $group_id, $count_members, $per_page, $start);

            // Dump out the remaining users
            foreach ($group_members as $i => $member) {
                $user_id = $member['user_id'];

                $member_info = generate_user_info($member, $is_moderator);

                if ($group_info['group_type'] != GROUP_HIDDEN || $is_group_member || $is_moderator) {
                    $row_class = !($i % 2) ? 'row1' : 'row2';

                    template()->assign_block_vars('member', [
                        'ROW_NUMBER' => $i + ($start + 1),
                        'ROW_CLASS' => $row_class,
                        'USER' => profile_url($member),
                        'AVATAR_IMG' => $member_info['avatar'],
                        'FROM' => $member_info['from'],
                        'JOINED' => $member_info['joined'],
                        'JOINED_RAW' => $member_info['joined_raw'],
                        'POSTS' => $member_info['posts'],
                        'USER_ID' => $user_id,
                        'PM' => $member_info['pm'],
                        'EMAIL' => $member_info['email'],
                        'WWW' => $member_info['www'],
                        'TIME' => $member_info['user_time'],
                        'TIME_RAW' => $member_info['user_time_raw']
                    ]);

                    if ($is_moderator) {
                        template()->assign_block_vars('member.switch_mod_option', []);
                    }
                }
            }

            // No group members
            if (!$members_count) {
                template()->assign_block_vars('switch_no_members', []);
            }

            // No group members
            if ($group_info['group_type'] == GROUP_HIDDEN && !$is_group_member && !$is_moderator) {
                template()->assign_block_vars('switch_hidden_group', []);
            }

            // Pending
            if ($is_moderator) {
                $modgroup_pending_list = DB()->fetch_rowset("
					SELECT u.username, u.avatar_ext_id, u.user_rank, u.user_id, u.user_opt, u.user_posts, u.user_regdate, u.user_from, u.user_website, u.user_email
					FROM " . BB_USER_GROUP . " ug, " . BB_USERS . " u
					WHERE ug.group_id = $group_id
						AND ug.user_pending = 1
						AND u.user_id = ug.user_id
					ORDER BY u.username
					LIMIT 200
				");
                $modgroup_pending_count = count($modgroup_pending_list);
            }

            if ($is_moderator && $modgroup_pending_list) {
                foreach ($modgroup_pending_list as $i => $member) {
                    $user_id = $member['user_id'];

                    $pending_info = generate_user_info($member, $is_moderator);

                    $row_class = !($i % 2) ? 'row1' : 'row2';

                    $user_select = '<input type="checkbox" name="member[]" value="' . $user_id . '">';

                    template()->assign_block_vars('pending', [
                        'ROW_CLASS' => $row_class,
                        'AVATAR_IMG' => $pending_info['avatar'],
                        'USER' => profile_url($member),
                        'FROM' => $pending_info['from'],
                        'JOINED' => $pending_info['joined'],
                        'JOINED_RAW' => $pending_info['joined_raw'],
                        'POSTS' => $pending_info['posts'],
                        'USER_ID' => $user_id,
                        'PM' => $pending_info['pm'],
                        'EMAIL' => $pending_info['email'],
                        'WWW' => $pending_info['www']
                    ]);
                }

                template()->assign_vars(['PENDING_USERS' => true]);
            }

            template()->assign_vars(['MEMBERS' => true]);
    }

    if ($is_moderator) {
        template()->assign_block_vars('switch_mod_option', []);
        template()->assign_block_vars('switch_add_member', []);
    }
}

print_page('group.tpl');
