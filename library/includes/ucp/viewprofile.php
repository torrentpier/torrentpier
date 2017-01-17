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
use \TorrentPier\Di;

if (!defined('BB_ROOT')) {
    die(basename(__FILE__));
}

/** @var \TorrentPier\Di $di */
$di = \TorrentPier\Di::getInstance();

require(INC_DIR . 'bbcode.php');

$datastore->enqueue(array(
    'ranks',
));

if (empty($_GET[POST_USERS_URL]) || $_GET[POST_USERS_URL] == GUEST_UID) {
    bb_die($lang['NO_USER_ID_SPECIFIED']);
}
if (!$profiledata = get_userdata($_GET[POST_USERS_URL])) {
    bb_die($lang['NO_USER_ID_SPECIFIED']);
}

if (!$userdata['session_logged_in']) {
    redirect(LOGIN_URL . "?redirect={$_SERVER['REQUEST_URI']}");
}

if (!$ranks = $datastore->get('ranks')) {
    $datastore->update('ranks');
    $ranks = $datastore->get('ranks');
}

$poster_rank = $rank_image = $rank_style = $rank_select = '';
if (($user_rank = $profiledata['user_rank']) && isset($ranks[$user_rank])) {
    $rank_image = ($ranks[$user_rank]['rank_image']) ? '<img src="' . $ranks[$user_rank]['rank_image'] . '" alt="" title="" border="0" />' : '';
    $poster_rank = $ranks[$user_rank]['rank_title'];
    $rank_style = $ranks[$user_rank]['rank_style'];
}
if (IS_ADMIN) {
    $rank_select = array($lang['NONE'] => 0);
    foreach ($ranks as $row) {
        $rank_select[$row['rank_title']] = $row['rank_id'];
    }
    $rank_select = build_select('rank-sel', $rank_select, $user_rank);
}

if (bf($profiledata['user_opt'], 'user_opt', 'user_viewemail') || $profiledata['user_id'] == $userdata['user_id'] || IS_AM) {
    $email_uri = ($di->config->get('board_email_form')) ? 'profile.php?mode=email&amp;' . POST_USERS_URL . '=' . $profiledata['user_id'] : 'mailto:' . $profiledata['user_email'];
    $email = '<a class="editable" href="' . $email_uri . '">' . $profiledata['user_email'] . '</a>';
} else {
    $email = '';
}

//
// Generate page
//

$profile_user_id = ($profiledata['user_id'] == $userdata['user_id']);

$signature = ($di->config->get('allow_sig') && $profiledata['user_sig']) ? $profiledata['user_sig'] : '';

if (bf($profiledata['user_opt'], 'user_opt', 'dis_sig')) {
    if ($profile_user_id) {
        $signature = $lang['SIGNATURE_DISABLE'];
    } else {
        $signature = '';
    }
} elseif ($signature) {
    $signature = bbcode2html($signature);
}

$template->assign_vars(array(
    'PAGE_TITLE' => sprintf($lang['VIEWING_USER_PROFILE'], $profiledata['username']),
    'USERNAME' => $profiledata['username'],
    'PROFILE_USER_ID' => $profiledata['user_id'],
    'PROFILE_USER' => $profile_user_id,
    'USER_REGDATE' => bb_date($profiledata['user_regdate'], 'Y-m-d H:i', false),
    'POSTER_RANK' => ($poster_rank) ? "<span class=\"$rank_style\">" . $poster_rank . "</span>" : $lang['USER'],
    'RANK_IMAGE' => $rank_image,
    'RANK_SELECT' => $rank_select,
    'POSTS' => $profiledata['user_posts'],
    'PM' => '<a href="' . PM_URL . '?mode=post&amp;' . POST_USERS_URL . '=' . $profiledata['user_id'] . '">' . $lang['SEND_PRIVATE_MESSAGE'] . '</a>',
    'EMAIL' => $email,
    'WWW' => $profiledata['user_website'],
    'ICQ' => $profiledata['user_icq'],
    'LAST_VISIT_TIME' => ($profiledata['user_lastvisit']) ? (bf($profiledata['user_opt'], 'user_opt', 'user_viewonline') && !IS_ADMIN) ? $lang['HIDDEN_USER'] : bb_date($profiledata['user_lastvisit'], 'Y-m-d H:i', false) : $lang['NEVER'],
    'LAST_ACTIVITY_TIME' => ($profiledata['user_session_time']) ? (bf($profiledata['user_opt'], 'user_opt', 'user_viewonline') && !IS_ADMIN) ? $lang['HIDDEN_USER'] : bb_date($profiledata['user_session_time'], 'Y-m-d H:i', false) : $lang['NEVER'],

    'USER_ACTIVE' => $profiledata['user_active'],
    'LOCATION' => $profiledata['user_from'],
    'OCCUPATION' => $profiledata['user_occ'],
    'INTERESTS' => $profiledata['user_interests'],
    'SKYPE' => $profiledata['user_skype'],
    'TWITTER' => $profiledata['user_twitter'],
    'USER_POINTS' => $profiledata['user_points'],
    'GENDER' => ($di->config->get('gender')) ? $lang['GENDER_SELECT'][$profiledata['user_gender']] : '',
    'BIRTHDAY' => ($di->config->get('birthday_enabled') && $profiledata['user_birthday'] != '0000-00-00') ? $profiledata['user_birthday'] : '',
    'AGE' => ($di->config->get('birthday_enabled') && $profiledata['user_birthday'] != '0000-00-00') ? birthday_age($profiledata['user_birthday']) : '',

    'L_VIEWING_PROFILE' => sprintf($lang['VIEWING_USER_PROFILE'], $profiledata['username']),
    'L_MY_PROFILE' => sprintf($lang['VIEWING_MY_PROFILE'], 'profile.php?mode=editprofile'),

    'U_SEARCH_USER' => "search.php?search_author=1&amp;uid={$profiledata['user_id']}",
    'U_SEARCH_TOPICS' => "search.php?uid={$profiledata['user_id']}&amp;myt=1",
    'U_SEARCH_RELEASES' => "tracker.php?rid={$profiledata['user_id']}#results",

    'AVATAR_IMG' => get_avatar($profiledata['user_id'], $profiledata['avatar_ext_id'], !bf($profiledata['user_opt'], 'user_opt', 'dis_avatar')),

    'SIGNATURE' => $signature,
    'SHOW_PASSKEY' => (IS_ADMIN || $profile_user_id),
    'SHOW_ROLE' => (IS_AM || $profile_user_id || $profiledata['user_active']),
    'GROUP_MEMBERSHIP' => false,
    'TRAF_STATS' => !(IS_AM || $profile_user_id),
));

if (IS_ADMIN) {
    $group_membership = array();
    $sql = "
		SELECT COUNT(g.group_id) AS groups_cnt, g.group_single_user, ug.user_pending
		FROM " . BB_USER_GROUP . " ug
		LEFT JOIN " . BB_GROUPS . " g USING(group_id)
		WHERE ug.user_id = {$profiledata['user_id']}
		GROUP BY ug.user_id, g.group_single_user, ug.user_pending
		ORDER BY NULL
	";
    if ($rowset = Di::getInstance()->db->fetch_rowset($sql)) {
        $member = $pending = $single = 0;
        foreach ($rowset as $row) {
            if (!$row['group_single_user'] && !$row['user_pending']) {
                $member = $row['groups_cnt'];
            } elseif (!$row['group_single_user'] && $row['user_pending']) {
                $pending = $row['groups_cnt'];
            } elseif ($row['group_single_user']) {
                $single = $row['groups_cnt'];
            }
        }
        if ($member) {
            $group_membership[] = $lang['PARTY'] . " <b>$member</b>";
        }
        if ($pending) {
            $group_membership[] = $lang['CANDIDATE'] . " <b>$pending</b>";
        }
        if ($single) {
            $group_membership[] = $lang['INDIVIDUAL'];
        }
        $group_membership = join(', ', $group_membership);
    }
    $template->assign_vars(array(
        'GROUP_MEMBERSHIP' => (bool)$group_membership,
        'GROUP_MEMBERSHIP_TXT' => $group_membership,
    ));
} elseif (IS_MOD) {
    $template->assign_vars(array(
        'SHOW_GROUP_MEMBERSHIP' => ($profiledata['user_level'] != USER),
    ));
}

// Show users torrent-profile
if (IS_AM || $profile_user_id || !bf($profiledata['user_opt'], 'user_opt', 'user_dls')) {
    require(UCP_DIR . 'viewtorrent.php');
}

// Ajax bt_userdata
if (IS_AM || $profile_user_id) {
    show_bt_userdata($profiledata['user_id']);
} else {
    $template->assign_vars(array(
        'DOWN_TOTAL_BYTES' => false,
        'MIN_DL_BYTES' => false,
    ));
}

if (IS_ADMIN) {
    $ajax_user_opt = \Zend\Json\Json::encode(array(
        'dis_avatar' => bf($profiledata['user_opt'], 'user_opt', 'dis_avatar'),
        'dis_sig' => bf($profiledata['user_opt'], 'user_opt', 'dis_sig'),
        'dis_passkey' => bf($profiledata['user_opt'], 'user_opt', 'dis_passkey'),
        'dis_pm' => bf($profiledata['user_opt'], 'user_opt', 'dis_pm'),
        'dis_post' => bf($profiledata['user_opt'], 'user_opt', 'dis_post'),
        'dis_post_edit' => bf($profiledata['user_opt'], 'user_opt', 'dis_post_edit'),
        'dis_topic' => bf($profiledata['user_opt'], 'user_opt', 'dis_topic'),
    ));

    $template->assign_vars(array(
        'EDITABLE_TPLS' => true,
        'AJAX_USER_OPT' => $ajax_user_opt,
        'U_MANAGE' => "profile.php?mode=editprofile&amp;u={$profiledata['user_id']}",
        'U_PERMISSIONS' => "admin/admin_ug_auth.php?mode=user&amp;u={$profiledata['user_id']}",
    ));
}

$user_restrictions = array();

if (bf($profiledata['user_opt'], 'user_opt', 'dis_avatar')) {
    $user_restrictions[] = $lang['HIDE_AVATARS'];
}
if (bf($profiledata['user_opt'], 'user_opt', 'dis_sig')) {
    $user_restrictions[] = $lang['SHOW_CAPTION'];
}
if (bf($profiledata['user_opt'], 'user_opt', 'dis_passkey')) {
    $user_restrictions[] = $lang['DOWNLOAD_TORRENT'];
}
if (bf($profiledata['user_opt'], 'user_opt', 'dis_pm')) {
    $user_restrictions[] = $lang['SEND_PM'];
}
if (bf($profiledata['user_opt'], 'user_opt', 'dis_post')) {
    $user_restrictions[] = $lang['SEND_MESSAGE'];
}
if (bf($profiledata['user_opt'], 'user_opt', 'dis_post_edit')) {
    $user_restrictions[] = $lang['EDIT_POST'];
}
if (bf($profiledata['user_opt'], 'user_opt', 'dis_topic')) {
    $user_restrictions[] = $lang['NEW_THREADS'];
}

$template->assign_var('USER_RESTRICTIONS', join('</li><li>', $user_restrictions));

print_page('usercp_viewprofile.tpl');
