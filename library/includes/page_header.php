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

if (defined('PAGE_HEADER_SENT')) {
    return;
}

// Parse and show the overall page header

$logged_in = (int) !empty(userdata('session_logged_in'));

// Generate logged in/logged out status
if ($logged_in) {
    $u_login_logout = BB_ROOT . LOGIN_URL . "?logout=1";
} else {
    $u_login_logout = BB_ROOT . LOGIN_URL;
}

// Online userlist
if (defined('SHOW_ONLINE') && SHOW_ONLINE) {
    $online_full = request()->has('online_full');
    $online_list = $online_full ? 'online_' . userdata('user_lang') : 'online_short_' . userdata('user_lang');

    ${$online_list} = [
        'stat' => '',
        'userlist' => '',
        'cnt' => '',
    ];

    if (defined('IS_GUEST') && !(IS_GUEST || IS_USER)) {
        template()->assign_var('SHOW_ONLINE_LIST');

        if (!${$online_list} = CACHE('bb_cache')->get($online_list)) {
            require INC_DIR . '/online_userlist.php';

            ${$online_list} = CACHE('bb_cache')->get($online_list);
        }
    }

    template()->assign_vars([
        'TOTAL_USERS_ONLINE' => ${$online_list}['stat'],
        'LOGGED_IN_USER_LIST' => ${$online_list}['userlist'],
        'USERS_ONLINE_COUNTS' => ${$online_list}['cnt'],
        'RECORD_USERS' => sprintf(__('RECORD_ONLINE_USERS'), config()->get('record_online_users'), bb_date(config()->get('record_online_date'))),
    ]);
}

// Info about new private messages
$icon_pm = theme_images('pm_no_new_msg');
$pm_info = __('NO_NEW_PM');
$have_new_pm = $have_unread_pm = 0;

if ($logged_in && !simple_header() && !defined('IN_ADMIN')) {
    if (userdata('user_new_privmsg')) {
        $have_new_pm = userdata('user_new_privmsg');
        $icon_pm = theme_images('pm_new_msg');
        $pm_info = declension(userdata('user_new_privmsg'), __('NEW_PMS_DECLENSION'), __('NEW_PMS_FORMAT'));

        if (userdata('user_last_privmsg') > userdata('user_lastvisit') && defined('IN_PM')) {
            user()->data['user_last_privmsg'] = userdata('user_lastvisit');

            \TorrentPier\Sessions::db_update_userdata(userdata(), [
                'user_last_privmsg' => userdata('user_lastvisit'),
            ]);

            $have_new_pm = (userdata('user_new_privmsg') > 1);
        }
    }
    if (!$have_new_pm && userdata('user_unread_privmsg')) {
        // sync unread pm count
        if (defined('IN_PM')) {
            $row = DB()->fetch_row("
				SELECT COUNT(*) AS pm_count
				FROM " . BB_PRIVMSGS . "
				WHERE privmsgs_to_userid = " . userdata('user_id') . "
					AND privmsgs_type = " . PRIVMSGS_UNREAD_MAIL . "
				GROUP BY privmsgs_to_userid
			");

            $real_unread_pm_count = (int) ($row['pm_count'] ?? 0);

            if (userdata('user_unread_privmsg') != $real_unread_pm_count) {
                user()->data['user_unread_privmsg'] = $real_unread_pm_count;

                \TorrentPier\Sessions::db_update_userdata(userdata(), [
                    'user_unread_privmsg' => $real_unread_pm_count,
                ]);
            }
        }

        $pm_info = declension(userdata('user_unread_privmsg'), __('UNREAD_PMS_DECLENSION'), __('UNREAD_PMS_FORMAT'));
        $have_unread_pm = true;
    }
}
template()->assign_vars([
    'HAVE_NEW_PM' => $have_new_pm,
    'HAVE_UNREAD_PM' => $have_unread_pm,
]);

// The following assigns all _common_ variables that may be used at any point in a template
template()->assign_vars([
    'SIMPLE_HEADER' => simple_header(),
    'CONTENT_ENCODING' => DEFAULT_CHARSET,

    'IN_ADMIN' => defined('IN_ADMIN'),
    'USER_HIDE_CAT' => (defined('BB_SCRIPT') && BB_SCRIPT === 'index'),

    'USER_LANG' => userdata('user_lang'),
    'USER_LANG_DIRECTION' => (function () {
        $langConfig = config()->get('lang') ?? [];
        $userLang = userdata('user_lang');
        return (isset($langConfig[$userLang]['rtl']) && $langConfig[$userLang]['rtl'] === true) ? 'rtl' : 'ltr';
    })(),

    'INCLUDE_BBCODE_JS' => (bool) page_cfg('include_bbcode_js'),
    'USER_OPTIONS_JS' => IS_GUEST ? '{}' : json_encode(user()->opt_js, JSON_THROW_ON_ERROR),

    'USE_TABLESORTER' => (bool) page_cfg('use_tablesorter'),
    'ALLOW_ROBOTS' => !config()->get('board_disable') && (page_cfg('allow_robots') ?? true),
    'META_DESCRIPTION' => (!defined('HAS_DIED') && page_cfg('meta_description')) ? trim(htmlCHR(page_cfg('meta_description'))) : '',

    'SITENAME' => config()->get('sitename'),
    'U_INDEX' => FORUM_PATH,
    'T_INDEX' => sprintf(__('FORUM_INDEX'), config()->get('sitename')),

    'IS_GUEST' => IS_GUEST,
    'IS_USER' => IS_USER,
    'IS_ADMIN' => IS_ADMIN,
    'IS_MOD' => IS_MOD,
    'IS_AM' => IS_AM,

    'FORUM_PATH' => FORUM_PATH,
    'FULL_URL' => FULL_URL,

    'CURRENT_TIME' => sprintf(__('CURRENT_TIME'), bb_date(TIMENOW, config()->get('last_visit_date_format'), false)),
    'S_TIMEZONE' => preg_replace('/\(.*?\)/', '', sprintf(__('ALL_TIMES'), config()->get('timezones')[str_replace(',', '.', (float) config()->get('board_timezone'))])),
    'BOARD_TIMEZONE' => config()->get('board_timezone'),

    'PM_INFO' => $pm_info,
    'PRIVMSG_IMG' => $icon_pm,

    'LOGGED_IN' => $logged_in,
    'SESSION_USER_ID' => userdata('user_id'),
    'POINTS' => userdata('user_points'),
    'THIS_USER' => profile_url(userdata()),
    'THIS_AVATAR' => get_avatar(userdata('user_id'), userdata('avatar_ext_id'), !bf(userdata('user_opt'), 'user_opt', 'dis_avatar')),
    'SHOW_LOGIN_LINK' => !defined('IN_LOGIN'),
    'AUTOLOGIN_DISABLED' => !config()->get('allow_autologin'),
    'S_LOGIN_ACTION' => LOGIN_URL,

    'U_CUR_DOWNLOADS' => FORUM_PATH . PROFILE_URL . userdata('user_id'),
    'U_FORUM' => FORUM_PATH . 'viewforum',
    'U_GROUPS' => url()->groups(),
    'U_LOGIN_LOGOUT' => FORUM_PATH . ltrim($u_login_logout, './'),
    'U_MEMBERLIST' => url()->members(),
    'U_MODCP' => FORUM_PATH . 'modcp',
    'U_OPTIONS' => FORUM_PATH . 'profile?mode=editprofile',
    'U_PRIVATEMSGS' => FORUM_PATH . PM_URL . "?folder=inbox",
    'U_PROFILE' => FORUM_PATH . PROFILE_URL . userdata('user_id'),
    'U_READ_PM' => FORUM_PATH . PM_URL . "?folder=inbox" . ((userdata('user_newest_pm_id') && userdata('user_new_privmsg') == 1) ? "&mode=read&" . POST_POST_URL . "=" . userdata('user_newest_pm_id') : ''),
    'U_REGISTER' => FORUM_PATH . 'profile?mode=register',
    'U_SEARCH' => FORUM_PATH . 'search',
    'U_SEND_PASSWORD' => FORUM_PATH . "profile?mode=sendpassword",
    'U_TERMS' => FORUM_PATH . ltrim(config()->get('terms_and_conditions_url'), './'),
    'U_TRACKER' => FORUM_PATH . 'tracker',

    'SHOW_SIDEBAR1' => (defined('BB_SCRIPT') && !empty((config()->get('page.show_sidebar1') ?? [])[BB_SCRIPT])) || config()->get('show_sidebar1_on_every_page'),
    'SHOW_SIDEBAR2' => (defined('BB_SCRIPT') && !empty((config()->get('page.show_sidebar2') ?? [])[BB_SCRIPT])) || config()->get('show_sidebar2_on_every_page'),

    'HTML_AGREEMENT' => LANG_DIR . 'html/user_agreement.html',
    'HTML_COPYRIGHT' => LANG_DIR . 'html/copyright_holders.html',
    'HTML_ADVERT' => LANG_DIR . 'html/advert.html',
    'HTML_SIDEBAR_1' => LANG_DIR . 'html/sidebar1.html',
    'HTML_SIDEBAR_2' => LANG_DIR . 'html/sidebar2.html',

    // Common urls (absolute paths for SEO-friendly routing)
    'AVATARS_URL' => FORUM_PATH . 'data/avatars',
    'CAT_URL' => FORUM_PATH . CAT_URL,
    'DOWNLOAD_URL' => FORUM_PATH . DL_URL,
    'FORUM_URL' => FORUM_PATH . FORUM_URL,
    'LOGIN_URL' => FORUM_PATH . ltrim(config()->get('login_url'), './'),
    'NEWEST_URL' => '&amp;view=newest#newest',
    'PM_URL' => FORUM_PATH . ltrim(config()->get('pm_url'), './'),
    'POST_URL' => FORUM_PATH . POST_URL,
    'POSTING_URL' => FORUM_PATH . ltrim(config()->get('posting_url'), './'),
    'PROFILE_URL' => FORUM_PATH . PROFILE_URL,
    'BONUS_URL' => FORUM_PATH . BONUS_URL,
    'TOPIC_URL' => FORUM_PATH . TOPIC_URL,

    'AJAX_HTML_DIR' => AJAX_HTML_DIR,

    'ONLY_NEW_POSTS' => ONLY_NEW_POSTS,
    'ONLY_NEW_TOPICS' => ONLY_NEW_TOPICS,

    // Misc
    'BOT_UID' => BOT_UID,
    'COOKIE_MARK' => COOKIE_MARK,
    'SID' => userdata('session_id'),
    'SID_HIDDEN' => '<input type="hidden" name="sid" value="' . userdata('session_id') . '" />',

    'CHECKED' => HTML_CHECKED,
    'DISABLED' => HTML_DISABLED,
    'READONLY' => HTML_READONLY,
    'SELECTED' => HTML_SELECTED,

    'U_SEARCH_SELF_BY_LAST' => FORUM_PATH . "search?uid=" . userdata('user_id') . "&amp;o=5",
    'U_WATCHED_TOPICS' => FORUM_PATH . 'profile?mode=watch',
]);

if (defined('BB_SCRIPT') && !empty((config()->get('page.show_torhelp') ?? [])[BB_SCRIPT]) && !empty(userdata('torhelp'))) {
    $ignore_time = !empty($_COOKIE['torhelp']) ? (int) $_COOKIE['torhelp'] : 0;

    if (TIMENOW > $ignore_time) {
        if ($ignore_time) {
            bb_setcookie('torhelp', '', COOKIE_EXPIRED);
        }

        $sql = "
			SELECT topic_id, topic_title
			FROM " . BB_TOPICS . "
			WHERE topic_id IN(" . userdata('torhelp') . ")
			LIMIT 8
		";
        $torhelp_topics = [];

        foreach (DB()->fetch_rowset($sql) as $row) {
            $torhelp_topics[] = '<a href="' . TOPIC_URL . $row['topic_id'] . '">' . $row['topic_title'] . '</a>';
        }

        template()->assign_vars([
            'TORHELP_TOPICS' => implode("</li>\n<li>", $torhelp_topics),
        ]);
    }
}

// Login box
$in_out = ($logged_in) ? 'in' : 'out';
template()->assign_block_vars("switch_user_logged_{$in_out}", []);

if (!IS_GUEST) {
    header('Cache-Control: private, no-cache, no-store, must-revalidate');
}

template()->set_filenames(['page_header' => 'page_header.tpl']);
template()->pparse('page_header');

define('PAGE_HEADER_SENT', true);

if (!config()->get('gzip_compress')) {
    flush();
}
