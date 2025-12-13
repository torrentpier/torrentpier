<?php

/**
 * TorrentPier – Bull-powered BitTorrent tracker engine
 *
 * @copyright Copyright (c) 2005-2025 TorrentPier (https://torrentpier.com)
 * @link      https://github.com/torrentpier/torrentpier for the canonical source repository
 * @license   https://github.com/torrentpier/torrentpier/blob/master/LICENSE MIT License
 */

if (!defined('BB_ROOT')) {
    die(basename(__FILE__));
}

require INC_DIR . '/bbcode.php';

datastore()->enqueue([
    'ranks',
    'cat_forums',
]);

if (IS_GUEST) {
    redirect(LOGIN_URL . '?redirect=' . request()->server->get('REQUEST_URI'));
}

$user_id = request()->query->getInt(POST_USERS_URL) ?: userdata('user_id');

if (!$profiledata = get_userdata($user_id)) {
    bb_die(__('NO_USER_ID_SPECIFIED'));
}

// Assert canonical URL for SEO-friendly routing
if (request()->attributes->get('semantic_route') && request()->attributes->get('semantic_route_type') === 'members') {
    TorrentPier\Router\SemanticUrl\UrlBuilder::assertCanonical('members', $user_id, $profiledata['username']);
}

$profiledata['user_birthday'] = $profiledata['user_birthday']->format('Y-m-d');

if (!$ranks = datastore()->get('ranks')) {
    datastore()->update('ranks');
    $ranks = datastore()->get('ranks');
}

$poster_rank = $rank_image = $rank_style = $rank_select = '';
$user_rank = $profiledata['user_rank'];
if ($user_rank && isset($ranks[$user_rank])) {
    $rank_image = ($ranks[$user_rank]['rank_image']) ? '<img src="' . make_url($ranks[$user_rank]['rank_image']) . '" alt="" title="" border="0" />' : '';
    $poster_rank = $ranks[$user_rank]['rank_title'];
    $rank_style = $ranks[$user_rank]['rank_style'];
}
if (IS_ADMIN) {
    $rank_select = [__('NONE') => 0];
    foreach ($ranks as $row) {
        $rank_select[$row['rank_title']] = $row['rank_id'];
    }
    $rank_select = build_select('rank-sel', $rank_select, $user_rank);
}

if (bf($profiledata['user_opt'], 'user_opt', 'user_viewemail') || $profiledata['user_id'] == userdata('user_id') || IS_ADMIN) {
    $email_uri = (config()->get('board_email_form')) ? url()->memberEmail($profiledata['user_id'], $profiledata['username']) : 'mailto:' . $profiledata['user_email'];
    $email = '<a class="editable" href="' . $email_uri . '">' . $profiledata['user_email'] . '</a>';
} else {
    $email = '';
}

//
// Generate page
//

$profile_user_id = ($profiledata['user_id'] == userdata('user_id'));

$signature = (config()->get('allow_sig') && $profiledata['user_sig']) ? $profiledata['user_sig'] : '';

if (bf($profiledata['user_opt'], 'user_opt', 'dis_sig')) {
    if ($profile_user_id) {
        $signature = __('SIGNATURE_DISABLE');
    } else {
        $signature = '';
    }
} elseif ($signature) {
    $signature = bbcode2html($signature);
}

// Null ratio
if (config()->get('ratio_null_enabled') && $btu = get_bt_userdata($profiledata['user_id'])) {
    template()->assign_vars(['NULLED_RATIO' => $btu['ratio_nulled']]);
}

// Ban information
if ($banInfo = getBanInfo((int)$profiledata['user_id'])) {
    template()->assign_block_vars('ban', [
        'IS_BANNED' => true,
        'BAN_REASON' => IS_ADMIN ? $banInfo['ban_reason'] : '',
    ]);
}

template()->assign_vars([
    'PAGE_TITLE' => sprintf(__('VIEWING_USER_PROFILE'), $profiledata['username']),
    'CANONICAL_URL' => make_url(url()->member($profiledata['user_id'], $profiledata['username'])),
    'USERNAME' => $profiledata['username'],
    'PROFILE_USER_ID' => $profiledata['user_id'],
    'PROFILE_USER' => $profile_user_id,
    'USER_REGDATE' => bb_date($profiledata['user_regdate'], 'Y-m-d H:i', false),
    'POSTER_RANK' => $poster_rank ? "<span class=\"{$rank_style}\">" . $poster_rank . '</span>' : __('USER'),
    'RANK_IMAGE' => $rank_image,
    'RANK_SELECT' => $rank_select,
    'POSTS' => $profiledata['user_posts'],
    'PM' => '<a href="' . PM_URL . '?mode=post&amp;' . POST_USERS_URL . '=' . $profiledata['user_id'] . '">' . __('SEND_PRIVATE_MESSAGE') . '</a>',
    'EMAIL' => $email,
    'WWW' => $profiledata['user_website'],
    'LAST_VISIT_TIME' => $profiledata['user_lastvisit'] ? (!$profile_user_id && bf($profiledata['user_opt'], 'user_opt', 'user_viewonline') && !IS_ADMIN) ? __('HIDDEN_USER') : bb_date($profiledata['user_lastvisit'], 'Y-m-d H:i', false) : __('NEVER'),
    'LAST_ACTIVITY_TIME' => $profiledata['user_session_time'] ? (!$profile_user_id && bf($profiledata['user_opt'], 'user_opt', 'user_viewonline') && !IS_ADMIN) ? __('HIDDEN_USER') : bb_date($profiledata['user_session_time'], 'Y-m-d H:i', false) : __('NEVER'),
    'USER_ACTIVE' => $profiledata['user_active'],
    'LOCATION' => render_flag($profiledata['user_from']),
    'OCCUPATION' => $profiledata['user_occ'],
    'INTERESTS' => $profiledata['user_interests'],
    'TWITTER' => $profiledata['user_twitter'],
    'USER_POINTS' => $profiledata['user_points'],
    'GENDER' => config()->get('gender') ? __('GENDER_SELECT')[$profiledata['user_gender']] : '',
    'BIRTHDAY' => (config()->get('birthday_enabled') && !empty($profiledata['user_birthday']) && $profiledata['user_birthday'] != '1900-01-01') ? $profiledata['user_birthday'] : '',
    'BIRTHDAY_ICON' => user_birthday_icon($profiledata['user_birthday'], $profiledata['user_id']),
    'AGE' => (config()->get('birthday_enabled') && !empty($profiledata['user_birthday']) && $profiledata['user_birthday'] != '1900-01-01') ? birthday_age($profiledata['user_birthday']) : '',

    'L_VIEWING_PROFILE' => sprintf(__('VIEWING_USER_PROFILE'), $profiledata['username']),
    'L_MY_PROFILE' => sprintf(__('VIEWING_MY_PROFILE'), SETTINGS_URL),

    'U_SEARCH_USER' => FORUM_PATH . "search?search_author=1&amp;uid={$profiledata['user_id']}",
    'U_SEARCH_TOPICS' => FORUM_PATH . "search?uid={$profiledata['user_id']}&amp;myt=1",
    'U_SEARCH_RELEASES' => FORUM_PATH . "tracker?rid={$profiledata['user_id']}#results",

    'AVATAR_IMG' => get_avatar($profiledata['user_id'], $profiledata['avatar_ext_id'], !bf($profiledata['user_opt'], 'user_opt', 'dis_avatar')),

    'SIGNATURE' => $signature,
    'SHOW_PASSKEY' => (IS_ADMIN || $profile_user_id),
    'SHOW_ROLE' => (IS_AM || $profile_user_id || $profiledata['user_active']),
    'GROUP_MEMBERSHIP' => false,
    'TRAF_STATS' => !(IS_AM || $profile_user_id) && !IS_GUEST,
]);

if (IS_AM) {
    $group_membership = [];
    $sql = '
		SELECT COUNT(g.group_id) AS groups_cnt, g.group_single_user, ug.user_pending
		FROM ' . BB_USER_GROUP . ' ug
		LEFT JOIN ' . BB_GROUPS . " g USING(group_id)
		WHERE ug.user_id = {$profiledata['user_id']}
		GROUP BY ug.user_id, g.group_single_user, ug.user_pending
		ORDER BY NULL
	";
    if ($rowset = DB()->fetch_rowset($sql)) {
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
            $group_membership[] = __('PARTY') . " <b>{$member}</b>";
        }
        if ($pending) {
            $group_membership[] = __('CANDIDATE') . " <b>{$pending}</b>";
        }
        if ($single) {
            $group_membership[] = __('INDIVIDUAL');
        }
        $group_membership = implode(', ', $group_membership);
    }
    template()->assign_vars([
        'GROUP_MEMBERSHIP' => (bool)$group_membership,
        'GROUP_MEMBERSHIP_TXT' => $group_membership,
    ]);
}

// Show users torrent-profile
if (IS_AM || $profile_user_id || !bf($profiledata['user_opt'], 'user_opt', 'user_dls')) {
    require UCP_DIR . '/viewtorrent.php';
}

// Ajax bt_userdata
if (IS_AM || $profile_user_id) {
    show_bt_userdata($profiledata['user_id']);
} else {
    template()->assign_vars([
        'DOWN_TOTAL_BYTES' => false,
        'MIN_DL_BYTES' => false,
    ]);
}

if (IS_ADMIN) {
    $ajax_user_opt = json_encode([
        'dis_avatar' => bf($profiledata['user_opt'], 'user_opt', 'dis_avatar'),
        'dis_sig' => bf($profiledata['user_opt'], 'user_opt', 'dis_sig'),
        'dis_passkey' => bf($profiledata['user_opt'], 'user_opt', 'dis_passkey'),
        'dis_pm' => bf($profiledata['user_opt'], 'user_opt', 'dis_pm'),
        'dis_post' => bf($profiledata['user_opt'], 'user_opt', 'dis_post'),
        'dis_post_edit' => bf($profiledata['user_opt'], 'user_opt', 'dis_post_edit'),
        'dis_topic' => bf($profiledata['user_opt'], 'user_opt', 'dis_topic'),
    ], JSON_THROW_ON_ERROR);

    template()->assign_vars([
        'EDITABLE_TPLS' => true,
        'AJAX_USER_OPT' => $ajax_user_opt,
        'U_MANAGE' => SETTINGS_URL . '?' . POST_USERS_URL . "={$profiledata['user_id']}",
        'U_PERMISSIONS' => FORUM_PATH . 'admin/admin_ug_auth.php?mode=user&amp;' . POST_USERS_URL . "={$profiledata['user_id']}",
    ]);
}

$user_restrictions = [];

if (bf($profiledata['user_opt'], 'user_opt', 'dis_avatar')) {
    $user_restrictions[] = __('HIDE_AVATARS');
}
if (bf($profiledata['user_opt'], 'user_opt', 'dis_sig')) {
    $user_restrictions[] = __('SHOW_CAPTION');
}
if (bf($profiledata['user_opt'], 'user_opt', 'dis_passkey')) {
    $user_restrictions[] = __('DOWNLOAD_TORRENT');
}
if (bf($profiledata['user_opt'], 'user_opt', 'dis_pm')) {
    $user_restrictions[] = __('SEND_PM');
}
if (bf($profiledata['user_opt'], 'user_opt', 'dis_post')) {
    $user_restrictions[] = __('SEND_MESSAGE');
}
if (bf($profiledata['user_opt'], 'user_opt', 'dis_post_edit')) {
    $user_restrictions[] = __('EDIT_POST');
}
if (bf($profiledata['user_opt'], 'user_opt', 'dis_topic')) {
    $user_restrictions[] = __('NEW_THREADS');
}

template()->assign_var('USER_RESTRICTIONS', implode('</li><li>', $user_restrictions));

print_page('usercp_viewprofile.tpl');
