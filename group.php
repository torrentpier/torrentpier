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

define('BB_SCRIPT', 'group');
define('BB_ROOT', './');
require __DIR__ . '/common.php';
require INC_DIR . '/bbcode.php';
require INC_DIR . '/functions_group.php';

$page_cfg['use_tablesorter'] = true;

$s_member_groups = $s_pending_groups = $s_member_groups_opt = $s_pending_groups_opt = '';
$select_sort_mode = $select_sort_order = '';

function generate_user_info(&$row, $date_format, $group_mod, &$from, &$posts, &$joined, &$pm, &$email, &$www, &$user_time, &$avatar)
{
    global $lang, $images, $bb_cfg;

    $from = (!empty($row['user_from'])) ? $row['user_from'] : '';
    $joined = bb_date($row['user_regdate']);
    $user_time = (!empty($row['user_time'])) ? bb_date($row['user_time']) : $lang['NONE'];
    $posts = $row['user_posts'] ?: 0;
    $pm = $bb_cfg['text_buttons'] ? '<a class="txtb" href="' . (PM_URL . "?mode=post&amp;" . POST_USERS_URL . "=" . $row['user_id']) . '">' . $lang['SEND_PM_TXTB'] . '</a>' : '<a href="' . (PM_URL . "?mode=post&amp;" . POST_USERS_URL . "=" . $row['user_id']) . '"><img src="' . $images['icon_pm'] . '" alt="' . $lang['SEND_PRIVATE_MESSAGE'] . '" title="' . $lang['SEND_PRIVATE_MESSAGE'] . '" border="0" /></a>';
    $avatar = get_avatar($row['user_id'], $row['avatar_ext_id'], !bf($row['user_opt'], 'user_opt', 'dis_avatar'), '', 50, 50);

    if (bf($row['user_opt'], 'user_opt', 'user_viewemail') || $group_mod) {
        $email_uri = ($bb_cfg['board_email_form']) ? ("profile.php?mode=email&amp;" . POST_USERS_URL . "=" . $row['user_id']) : 'mailto:' . $row['user_email'];
        $email = '<a class="editable" href="' . $email_uri . '">' . $row['user_email'] . '</a>';
    } else {
        $email = '';
    }

    if ($row['user_website']) {
        $www = $bb_cfg['text_buttons'] ? '<a class="txtb" href="' . $row['user_website'] . '"  target="_userwww">' . $lang['VISIT_WEBSITE_TXTB'] . '</a>' : '<a class="txtb" href="' . $row['user_website'] . '" target="_userwww"><img src="' . $images['icon_www'] . '" alt="' . $lang['VISIT_WEBSITE'] . '" title="' . $lang['VISIT_WEBSITE'] . '" border="0" /></a>';
    } else {
        $www = '';
    }

    return;
}

$user->session_start(array('req_login' => true));

set_die_append_msg();

$group_id = isset($_REQUEST[POST_GROUPS_URL]) ? (int)$_REQUEST[POST_GROUPS_URL] : null;
$start = isset($_REQUEST['start']) ? abs((int)$_REQUEST['start']) : 0;
$per_page = $bb_cfg['group_members_per_page'];
$view_mode = isset($_REQUEST['view']) ? (string)$_REQUEST['view'] : null;
$rel_limit = 50;

$group_info = array();
$is_moderator = false;

if ($group_id) {
    if (!$group_info = get_group_data($group_id)) {
        bb_die($lang['GROUP_NOT_EXIST']);
    }
    if (!$group_info['group_id'] || !$group_info['group_moderator'] || !$group_info['moderator_name']) {
        bb_die("Invalid group data [group_id: $group_id]");
    }
    $is_moderator = ($userdata['user_id'] == $group_info['group_moderator'] || IS_ADMIN);
}

if (!$group_id) {
    // Show the main screen where the user can select a group.
    $groups = array();
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
			AND ug.user_id = " . $userdata['user_id'] . "
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

        $data = array('id' => $row['group_id'], 'm' => ($row['members'] - $row['candidates']), 'c' => $row['candidates'], 'rg' => $row['release_group']);

        $groups[$type][$row['group_name']] = $data;
    }

    function build_group($params)
    {
        global $lang;

        $options = '';
        foreach ($params as $name => $data) {
            $text = htmlCHR(str_short(rtrim($name), HTML_SELECT_MAX_LENGTH));

            $members = ($data['m']) ? $lang['MEMBERS_IN_GROUP'] . ': ' . $data['m'] : $lang['NO_GROUP_MEMBERS'];
            $candidates = ($data['c']) ? $lang['PENDING_MEMBERS'] . ': ' . $data['c'] : $lang['NO_PENDING_GROUP_MEMBERS'];

            $options .= '<li class="pad_2"><a href="' . GROUP_URL . $data['id'] . '" class="med bold">' . $text . '</a></li>';
            $options .= ($data['rg']) ? '<ul><li class="med">' . $lang['RELEASE_GROUP'] . '</li>' : '<ul>';
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
            $template->assign_block_vars('groups', array(
                'MEMBERSHIP' => $lang["GROUP_MEMBER_{$type}"],
                'GROUP_SELECT' => build_group($grp),
            ));
        }

        $template->assign_vars(array(
            'SELECT_GROUP' => true,
            'PAGE_TITLE' => $lang['GROUP_CONTROL_PANEL'],
            'S_USERGROUP_ACTION' => 'group.php',
            'S_HIDDEN_FIELDS' => $s_hidden_fields,
        ));
    } else {
        if (IS_ADMIN) {
            redirect('admin/admin_groups.php');
        } else {
            bb_die($lang['NO_GROUPS_EXIST']);
        }
    }
} elseif (isset($_POST['joingroup']) && $_POST['joingroup']) {
    if ($group_info['group_type'] != GROUP_OPEN) {
        bb_die($lang['THIS_CLOSED_GROUP']);
    }

    $sql = "SELECT g.group_id, g.group_name, ug.user_id, u.user_email, u.username, u.user_lang
		FROM " . BB_GROUPS . " g
		LEFT JOIN " . BB_USERS . " u ON(u.user_id = g.group_moderator)
		LEFT JOIN " . BB_USER_GROUP . " ug ON(ug.group_id = g.group_id AND ug.user_id = {$userdata['user_id']})
		WHERE g.group_id = $group_id
			AND group_single_user = 0
			AND g.group_type = " . GROUP_OPEN . "
		LIMIT 1";

    $row = $moderator = DB()->fetch_row($sql);

    if (!$row['group_id']) {
        bb_die($lang['NO_GROUPS_EXIST']);
    }
    if ($row['user_id']) {
        set_die_append_msg(false, false, $group_id);
        bb_die($lang['ALREADY_MEMBER_GROUP']);
    }

    add_user_into_group($group_id, $userdata['user_id'], 1, TIMENOW);

    if ($bb_cfg['group_send_email']) {
        /** @var TorrentPier\Legacy\Emailer() $emailer */
        $emailer = new TorrentPier\Legacy\Emailer();

        $emailer->set_from([$bb_cfg['board_email'] => $bb_cfg['sitename']]);
        $emailer->set_to([$moderator['user_email'] => $moderator['username']]);
        $emailer->set_subject($lang['EMAILER_SUBJECT']['GROUP_REQUEST']);

        $emailer->set_template('group_request', $moderator['user_lang']);
        $emailer->assign_vars(array(
            'USER' => $userdata['username'],
            'SITENAME' => $bb_cfg['sitename'],
            'GROUP_MODERATOR' => $moderator['username'],
            'U_GROUP' => make_url(GROUP_URL . $group_id),
        ));

        $emailer->send();
    }

    set_die_append_msg(false, false, $group_id);
    bb_die($lang['GROUP_JOINED']);
} elseif (!empty($_POST['unsub']) || !empty($_POST['unsubpending'])) {
    delete_user_group($group_id, $userdata['user_id']);

    set_die_append_msg(false, false, $group_id);
    bb_die($lang['UNSUB_SUCCESS']);
} else {
    // Handle Additions, removals, approvals and denials
    $group_moderator = $group_info['group_moderator'];

    if (!empty($_POST['add']) || !empty($_POST['remove']) || !empty($_POST['approve']) || !empty($_POST['deny'])) {
        if (!$is_moderator) {
            bb_die($lang['NOT_GROUP_MODERATOR']);
        }

        if (!empty($_POST['add'])) {
            if (isset($_POST['username']) && !($row = get_userdata($_POST['username'], true))) {
                bb_die($lang['COULD_NOT_ADD_USER']);
            }

            add_user_into_group($group_id, $row['user_id']);

            if ($bb_cfg['group_send_email']) {
                /** @var TorrentPier\Legacy\Emailer() $emailer */
                $emailer = new TorrentPier\Legacy\Emailer();

                $emailer->set_from([$bb_cfg['board_email'] => $bb_cfg['sitename']]);
                $emailer->set_to([$row['user_email'] => $row['username']]);
                $emailer->set_subject($lang['EMAILER_SUBJECT']['GROUP_ADDED']);

                $emailer->set_template('group_added', $row['user_lang']);
                $emailer->assign_vars(array(
                    'SITENAME' => $bb_cfg['sitename'],
                    'GROUP_NAME' => $group_info['group_name'],
                    'U_GROUP' => make_url(GROUP_URL . $group_id),
                ));

                $emailer->send();
            }
        } else {
            if (((!empty($_POST['approve']) || !empty($_POST['deny'])) && !empty($_POST['pending_members'])) || (!empty($_POST['remove']) && !empty($_POST['members']))) {
                $members = (!empty($_POST['approve']) || !empty($_POST['deny'])) ? $_POST['pending_members'] : $_POST['members'];

                $sql_in = array();
                foreach ($members as $members_id) {
                    $sql_in[] = (int)$members_id;
                }
                if (!$sql_in = implode(',', $sql_in)) {
                    bb_die($lang['NONE_SELECTED']);
                }

                if (!empty($_POST['approve'])) {
                    DB()->query("
						UPDATE " . BB_USER_GROUP . " SET
							user_pending = 0
						WHERE user_id IN($sql_in)
							AND group_id = $group_id
					");

                    update_user_level($sql_in);
                } elseif (!empty($_POST['deny']) || !empty($_POST['remove'])) {
                    DB()->query("
						DELETE FROM " . BB_USER_GROUP . "
						WHERE user_id IN($sql_in)
							AND group_id = $group_id
					");

                    if (!empty($_POST['remove'])) {
                        update_user_level($sql_in);
                    }
                }
                // Email users when they are approved
                if (!empty($_POST['approve']) && $bb_cfg['group_send_email']) {
                    $sql_select = "SELECT username, user_email, user_lang
						FROM " . BB_USERS . "
						WHERE user_id IN($sql_in)";

                    if (!$result = DB()->sql_query($sql_select)) {
                        bb_die('Could not get user email information');
                    }

                    foreach (DB()->fetch_rowset($sql_select) as $row) {
                        /** @var TorrentPier\Legacy\Emailer() $emailer */
                        $emailer = new TorrentPier\Legacy\Emailer();

                        $emailer->set_from([$bb_cfg['board_email'] => $bb_cfg['sitename']]);
                        $emailer->set_to([$row['user_email'] => $row['username']]);
                        $emailer->set_subject($lang['EMAILER_SUBJECT']['GROUP_APPROVED']);

                        $emailer->set_template('group_approved', $row['user_lang']);
                        $emailer->assign_vars(array(
                            'SITENAME' => $bb_cfg['sitename'],
                            'GROUP_NAME' => $group_info['group_name'],
                            'U_GROUP' => make_url(GROUP_URL . $group_id),
                        ));

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
			AND user_id = " . $userdata['user_id'] . "
		LIMIT 1";

    if ($row = DB()->fetch_row($sql)) {
        if ($row['user_pending'] == 0) {
            $is_group_member = true;
        } else {
            $is_group_pending_member = true;
        }
    }

    if ($userdata['user_id'] == $group_moderator['user_id']) {
        $group_details = $lang['ARE_GROUP_MODERATOR'];
        $s_hidden_fields = '<input type="hidden" name="' . POST_GROUPS_URL . '" value="' . $group_id . '" />';
    } elseif ($is_group_member || $is_group_pending_member) {
        $template->assign_vars(array(
            'SHOW_UNSUBSCRIBE_CONTROLS' => true,
            'CONTROL_NAME' => ($is_group_member) ? 'unsub' : 'unsubpending',
        ));
        $group_details = ($is_group_pending_member) ? $lang['PENDING_THIS_GROUP'] : $lang['MEMBER_THIS_GROUP'];
        $s_hidden_fields = '<input type="hidden" name="' . POST_GROUPS_URL . '" value="' . $group_id . '" />';
    } elseif (IS_GUEST) {
        $group_details = $lang['LOGIN_TO_JOIN'];
        $s_hidden_fields = '';
    } else {
        if ($group_info['group_type'] == GROUP_OPEN) {
            $template->assign_var('SHOW_SUBSCRIBE_CONTROLS');

            $group_details = $lang['THIS_OPEN_GROUP'];
            $s_hidden_fields = '<input type="hidden" name="' . POST_GROUPS_URL . '" value="' . $group_id . '" />';
        } elseif ($group_info['group_type'] == GROUP_CLOSED) {
            $group_details = $lang['THIS_CLOSED_GROUP'];
            $s_hidden_fields = '';
        } elseif ($group_info['group_type'] == GROUP_HIDDEN) {
            $group_details = $lang['THIS_HIDDEN_GROUP'];
            $s_hidden_fields = '';
        }
    }

    // Add the moderator
    $username = $group_moderator['username'];
    $user_id = $group_moderator['user_id'];

    generate_user_info($group_moderator, $bb_cfg['default_dateformat'], $is_moderator, $from, $posts, $joined, $pm, $email, $www, $user_time, $avatar);

    $group_type = '';
    if ($group_info['group_type'] == GROUP_OPEN) {
        $group_type = $lang['GROUP_OPEN'];
    } elseif ($group_info['group_type'] == GROUP_CLOSED) {
        $group_type = $lang['GROUP_CLOSED'];
    } elseif ($group_info['group_type'] == GROUP_HIDDEN) {
        $group_type = $lang['GROUP_HIDDEN'];
    }

    $i = 0;
    $template->assign_vars(array(
        'ROW_NUMBER' => $i + ($start + 1),
        'GROUP_INFO' => true,
        'PAGE_TITLE' => $lang['GROUP_CONTROL_PANEL'],
        'GROUP_NAME' => htmlCHR($group_info['group_name']),
        'GROUP_DESCRIPTION' => bbcode2html($group_info['group_description']),
        'GROUP_SIGNATURE' => bbcode2html($group_info['group_signature']),
        'GROUP_AVATAR' => get_avatar(GROUP_AVATAR_MASK . $group_id, $group_info['avatar_ext_id'], true),
        'GROUP_DETAILS' => $group_details,
        'GROUP_TIME' => (!empty($group_info['group_time'])) ? sprintf('%s <span class="posted_since">(%s)</span>', bb_date($group_info['group_time']), delta_time($group_info['group_time'])) : $lang['NONE'],
        'MOD_USER' => profile_url($group_moderator),
        'MOD_AVATAR' => $avatar,
        'MOD_FROM' => $from,
        'MOD_JOINED' => $joined,
        'MOD_POSTS' => $posts,
        'MOD_PM' => $pm,
        'MOD_EMAIL' => $email,
        'MOD_WWW' => $www,
        'MOD_TIME' => (!empty($group_info['mod_time'])) ? bb_date($group_info['mod_time']) : $lang['NONE'],
        'U_SEARCH_USER' => "search.php?mode=searchuser",
        'U_SEARCH_RELEASES' => "tracker.php?srg=$group_id",
        'U_GROUP_RELEASES' => "group.php?view=releases&amp;" . POST_GROUPS_URL . "=$group_id",
        'U_GROUP_MEMBERS' => "group.php?view=members&amp;" . POST_GROUPS_URL . "=$group_id",
        'U_GROUP_CONFIG' => "group_edit.php?g=$group_id",
        'RELEASE_GROUP' => ($group_info['release_group']) ? true : false,
        'GROUP_TYPE' => $group_type,

        'S_GROUP_OPEN_TYPE' => GROUP_OPEN,
        'S_GROUP_CLOSED_TYPE' => GROUP_CLOSED,
        'S_GROUP_HIDDEN_TYPE' => GROUP_HIDDEN,
        'S_GROUP_OPEN_CHECKED' => ($group_info['group_type'] == GROUP_OPEN) ? ' checked="checked"' : '',
        'S_GROUP_CLOSED_CHECKED' => ($group_info['group_type'] == GROUP_CLOSED) ? ' checked="checked"' : '',
        'S_GROUP_HIDDEN_CHECKED' => ($group_info['group_type'] == GROUP_HIDDEN) ? ' checked="checked"' : '',
        'S_HIDDEN_FIELDS' => $s_hidden_fields,
        'S_MODE_SELECT' => $select_sort_mode,
        'S_ORDER_SELECT' => $select_sort_order,

        'S_GROUP_ACTION' => "group.php?" . POST_GROUPS_URL . "=$group_id",
    ));

    switch ($view_mode) {
        case 'releases':
            // TODO Correct SQL to posts with attach and limit them, optimization

            if (!$group_info['release_group']) {
                set_die_append_msg(false, false, $group_id);
                bb_die($lang['NOT_A_RELEASE_GROUP']);
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
                set_die_append_msg(false, false, $group_id);
                bb_die($lang['NO_SEARCH_MATCH']);
            }

            foreach ($releases as $i => $release) {
                $row_class = !($i % 2) ? 'row1' : 'row2';

                $template->assign_block_vars('releases', array(
                    'ROW_NUMBER' => $i + ($start + 1),
                    'ROW_CLASS' => $row_class,
                    'RELEASER' => profile_url(array('user_id' => $release['poster_id'], 'username' => $release['username'], 'user_rank' => $release['user_rank'])),
                    'AVATAR_IMG' => get_avatar($release['poster_id'], $release['avatar_ext_id'], !bf($release['user_opt'], 'user_opt', 'dis_avatar'), '', 50, 50),
                    'RELEASE_NAME' => sprintf('<a href="%s">%s</a>', TOPIC_URL . $release['topic_id'], htmlCHR($release['topic_title'])),
                    'RELEASE_TIME' => bb_date($release['topic_time']),
                    'RELEASE_FORUM' => sprintf('<a href="%s">%s</a>', FORUM_URL . $release['forum_id'], htmlCHR($release['forum_name'])),
                ));
            }

            $template->assign_vars(array(
                'RELEASES' => true,
            ));

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

                generate_user_info($member, $bb_cfg['default_dateformat'], $is_moderator, $from, $posts, $joined, $pm, $email, $www, $user_time, $avatar);

                if ($group_info['group_type'] != GROUP_HIDDEN || $is_group_member || $is_moderator) {
                    $row_class = !($i % 2) ? 'row1' : 'row2';

                    $template->assign_block_vars('member', array(
                        'ROW_NUMBER' => $i + ($start + 1),
                        'ROW_CLASS' => $row_class,
                        'USER' => profile_url($member),
                        'AVATAR_IMG' => $avatar,
                        'FROM' => $from,
                        'JOINED' => $joined,
                        'POSTS' => $posts,
                        'USER_ID' => $user_id,
                        'PM' => $pm,
                        'EMAIL' => $email,
                        'WWW' => $www,
                        'TIME' => $user_time,
                    ));

                    if ($is_moderator) {
                        $template->assign_block_vars('member.switch_mod_option', array());
                    }
                }
            }

            // No group members
            if (!$members_count) {
                $template->assign_block_vars('switch_no_members', array());
            }

            // No group members
            if ($group_info['group_type'] == GROUP_HIDDEN && !$is_group_member && !$is_moderator) {
                $template->assign_block_vars('switch_hidden_group', array());
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

                    generate_user_info($member, $bb_cfg['default_dateformat'], $is_moderator, $from, $posts, $joined, $pm, $email, $www, $user_time, $avatar);

                    $row_class = !($i % 2) ? 'row1' : 'row2';

                    $user_select = '<input type="checkbox" name="member[]" value="' . $user_id . '">';

                    $template->assign_block_vars('pending', array(
                        'ROW_CLASS' => $row_class,
                        'AVATAR_IMG' => $avatar,
                        'USER' => profile_url($member),
                        'FROM' => $from,
                        'JOINED' => $joined,
                        'POSTS' => $posts,
                        'USER_ID' => $user_id,
                        'PM' => $pm,
                        'EMAIL' => $email,
                    ));
                }

                $template->assign_vars(array(
                    'PENDING_USERS' => true,
                ));
            }

            $template->assign_vars(array('MEMBERS' => true));
    }

    if ($is_moderator) {
        $template->assign_block_vars('switch_mod_option', array());
        $template->assign_block_vars('switch_add_member', array());
    }
}

print_page('group.tpl');
