<?php
/**
 * TorrentPier – Bull-powered BitTorrent tracker engine
 *
 * @copyright Copyright (c) 2005-2017 TorrentPier (https://torrentpier.com)
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

global $page_cfg, $userdata, $user, $ads, $template, $images;

$logged_in = (int)!empty($userdata['session_logged_in']);

// Generate logged in/logged out status
if ($logged_in) {
    $u_login_logout = BB_ROOT . LOGIN_URL . '?logout=1';
} else {
    $u_login_logout = BB_ROOT . LOGIN_URL;
}

// Online userlist
if (defined('SHOW_ONLINE') && SHOW_ONLINE) {
    $online_full = !empty($_REQUEST['online_full']);
    $online_list = $online_full ? 'online_' . $userdata['user_lang'] : 'online_short_' . $userdata['user_lang'];

    ${$online_list} = array(
        'stat' => '',
        'userlist' => '',
        'cnt' => '',
    );

    if (defined('IS_GUEST') && !(IS_GUEST || IS_USER)) {
        $template->assign_var('SHOW_ONLINE_LIST');

        if (!${$online_list} = OLD_CACHE('bb_cache')->get($online_list)) {
            require INC_DIR . '/online_userlist.php';
        }
    }

    $template->assign_vars(array(
        'TOTAL_USERS_ONLINE' => ${$online_list}['stat'],
        'LOGGED_IN_USER_LIST' => ${$online_list}['userlist'],
        'USERS_ONLINE_COUNTS' => ${$online_list}['cnt'],
        'RECORD_USERS' => sprintf(trans('messages.RECORD_ONLINE_USERS'), config('tp.record_online_users'), bb_date(config('tp.record_online_date'))),
    ));
}

// Info about new private messages
$icon_pm = $images['pm_no_new_msg'];
$pm_info = trans('messages.NO_NEW_PM');
$have_new_pm = $have_unread_pm = 0;

if ($logged_in && empty($gen_simple_header) && !defined('IN_ADMIN')) {
    if ($userdata['user_new_privmsg']) {
        $have_new_pm = $userdata['user_new_privmsg'];
        $icon_pm = $images['pm_new_msg'];
        $pm_info = declension($userdata['user_new_privmsg'], trans('messages.NEW_PMS_DECLENSION'), trans('messages.NEW_PMS_FORMAT'));

        if ($userdata['user_last_privmsg'] > $userdata['user_lastvisit'] && defined('IN_PM')) {
            $userdata['user_last_privmsg'] = $userdata['user_lastvisit'];

            db_update_userdata($userdata, array(
                'user_last_privmsg' => $userdata['user_lastvisit'],
            ));

            $have_new_pm = ($userdata['user_new_privmsg'] > 1);
        }
    }
    if (!$have_new_pm && $userdata['user_unread_privmsg']) {
        // synch unread pm count
        if (defined('IN_PM')) {
            $row = OLD_DB()->fetch_row('
				SELECT COUNT(*) AS pm_count
				FROM ' . BB_PRIVMSGS . '
				WHERE privmsgs_to_userid = ' . $userdata['user_id'] . '
					AND privmsgs_type = ' . PRIVMSGS_UNREAD_MAIL . '
				GROUP BY privmsgs_to_userid
			');

            $real_unread_pm_count = (int)$row['pm_count'];

            if ($userdata['user_unread_privmsg'] != $real_unread_pm_count) {
                $userdata['user_unread_privmsg'] = $real_unread_pm_count;

                db_update_userdata($userdata, array(
                    'user_unread_privmsg' => $real_unread_pm_count,
                ));
            }
        }

        $pm_info = declension($userdata['user_unread_privmsg'], trans('messages.UNREAD_PMS_DECLENSION'), trans('messages.UNREAD_PMS_FORMAT'));
        $have_unread_pm = true;
    }
}
$template->assign_vars(array(
    'HAVE_NEW_PM' => $have_new_pm,
    'HAVE_UNREAD_PM' => $have_unread_pm,
));

// The following assigns all _common_ variables that may be used at any point in a template
$template->assign_vars(array(
    'SIMPLE_HEADER' => !empty($gen_simple_header),
    'CONTENT_ENCODING' => config('language.charset'),

    'IN_ADMIN' => defined('IN_ADMIN'),
    'USER_HIDE_CAT' => BB_SCRIPT == 'index',

    'USER_LANG' => $userdata['user_lang'],

    'INCLUDE_BBCODE_JS' => !empty($page_cfg['include_bbcode_js']),
    'USER_OPTIONS_JS' => IS_GUEST ? '{}' : json_encode($user->opt_js),

    'USE_TABLESORTER' => !empty($page_cfg['use_tablesorter']),

    'SITENAME' => config('tp.sitename'),
    'U_INDEX' => BB_ROOT . 'index.php',
    'T_INDEX' => sprintf(trans('messages.FORUM_INDEX'), config('tp.sitename')),

    'IS_GUEST' => IS_GUEST,
    'IS_USER' => IS_USER,
    'IS_ADMIN' => IS_ADMIN,
    'IS_MOD' => IS_MOD,
    'IS_AM' => IS_AM,

    'FORUM_PATH' => FORUM_PATH,
    'FULL_URL' => FULL_URL,

    'CURRENT_TIME' => sprintf(trans('messages.CURRENT_TIME'), bb_date(TIMENOW, config('tp.last_visit_date_format'), false)),
    'S_TIMEZONE' => preg_replace('/\(.*?\)/', '', sprintf(trans('messages.ALL_TIMES'), trans('messages.TZ.' . str_replace(',', '.', (float)config('tp.board_timezone'))))),
    'BOARD_TIMEZONE' => config('tp.board_timezone'),

    'PM_INFO' => $pm_info,
    'PRIVMSG_IMG' => $icon_pm,

    'LOGGED_IN' => $logged_in,
    'SESSION_USER_ID' => $userdata['user_id'],
    'POINTS' => $userdata['user_points'],
    'THIS_USER' => profile_url($userdata),
    'THIS_AVATAR' => get_avatar($userdata['user_id'], $userdata['avatar_ext_id'], !bf($userdata['user_opt'], 'user_opt', 'dis_avatar')),
    'SHOW_LOGIN_LINK' => !defined('IN_LOGIN'),
    'AUTOLOGIN_DISABLED' => !config('tp.allow_autologin'),
    'S_LOGIN_ACTION' => LOGIN_URL,

    'U_CUR_DOWNLOADS' => PROFILE_URL . $userdata['user_id'],
    'U_FORUM' => 'viewforum.php',
    'U_GROUPS' => 'group.php',
    'U_LOGIN_LOGOUT' => $u_login_logout,
    'U_MEMBERLIST' => 'memberlist.php',
    'U_MODCP' => 'modcp.php',
    'U_OPTIONS' => 'profile.php?mode=editprofile',
    'U_PRIVATEMSGS' => PM_URL . '?folder=inbox',
    'U_PROFILE' => PROFILE_URL . $userdata['user_id'],
    'U_READ_PM' => PM_URL . '?folder=inbox' . (($userdata['user_newest_pm_id'] && $userdata['user_new_privmsg'] == 1) ? "&mode=read&p={$userdata['user_newest_pm_id']}" : ''),
    'U_REGISTER' => 'profile.php?mode=register',
    'U_SEARCH' => 'search.php',
    'U_SEND_PASSWORD' => 'profile.php?mode=sendpassword',
    'U_TERMS' => config('tp.terms_and_conditions_url'),
    'U_TRACKER' => 'tracker.php',

    'SHOW_SIDEBAR1' => !empty(config('page.show_sidebar1.' . BB_SCRIPT)) || config('tp.show_sidebar1_on_every_page'),
    'SHOW_SIDEBAR2' => !empty(config('page.show_sidebar2.' . BB_SCRIPT)) || config('tp.show_sidebar2_on_every_page'),

    'HTML_AGREEMENT' => LANG_DIR . 'html/user_agreement.html',
    'HTML_COPYRIGHT' => LANG_DIR . 'html/copyright_holders.html',
    'HTML_ADVERT' => LANG_DIR . 'html/advert.html',
    'HTML_SIDEBAR_1' => LANG_DIR . 'html/sidebar1.html',
    'HTML_SIDEBAR_2' => LANG_DIR . 'html/sidebar2.html',

    // Common urls
    'AVATARS_URL' => 'data/avatars',
    'CAT_URL' => BB_ROOT . CAT_URL,
    'DOWNLOAD_URL' => BB_ROOT . DOWNLOAD_URL,
    'FORUM_URL' => BB_ROOT . FORUM_URL,
    'GROUP_URL' => BB_ROOT . GROUP_URL,
    'LOGIN_URL' => config('tp.login_url'),
    'NEWEST_URL' => '&amp;view=newest#newest',
    'PM_URL' => config('tp.pm_url'),
    'POST_URL' => BB_ROOT . POST_URL,
    'POSTING_URL' => config('tp.posting_url'),
    'PROFILE_URL' => BB_ROOT . PROFILE_URL,
    'TOPIC_URL' => BB_ROOT . TOPIC_URL,

    'AJAX_HTML_DIR' => AJAX_HTML_DIR,

    'ONLY_NEW_POSTS' => ONLY_NEW_POSTS,
    'ONLY_NEW_TOPICS' => ONLY_NEW_TOPICS,

    // Misc
    'BOT_UID' => BOT_UID,
    'COOKIE_MARK' => COOKIE_MARK,
    'SID' => $userdata['session_id'],
    'SID_HIDDEN' => '<input type="hidden" name="sid" value="' . $userdata['session_id'] . '" />',

    'CHECKED' => HTML_CHECKED,
    'DISABLED' => HTML_DISABLED,
    'READONLY' => HTML_READONLY,
    'SELECTED' => HTML_SELECTED,

    'U_SEARCH_SELF_BY_LAST' => "search.php?uid={$userdata['user_id']}&amp;o=5",
    'U_WATCHED_TOPICS' => 'profile.php?mode=watch',

    /** TODO: конфигурация, используемая в шаблонах, избавиться */
    'CFG_GS_ENABLED' => config('tracker.gold_silver_enabled'),
    'CFG_UAU' => config('tp.user_agreement_url'),
    'CFG_CHU' => config('tp.copyright_holders_url'),
    'CFG_SHU' => config('tp.search_help_url'),
    'CFG_AU' => config('tp.advert_url'),
    'CFG_SB_E' => config('tp.seed_bonus_enabled'),
    'CFG_USE_AP' => config('tp.use_ajax_posts'),
    'CFG_JS_VER' => config('tp.js_ver'),
    'CFG_CSS_VER' => config('tp.css_ver'),
    'CFG_SCRIPT_PATH' => config('tp.script_path'),
    'CFG_COOKIE_DOMAIN' => config('tp.cookie_domain'),
    'CFG_COOKIE_PREFIX' => config('tp.cookie_prefix'),
    'CFG_COOKIE_SECURE' => config('tp.cookie_secure'),
    'CFG_TOR_COMMENT' => config('tp.tor_comment'),
    'CFG_AV_MS' => config('tp.avatars.max_size'),
    'CFG_AV_UA' => config('tp.avatars.up_allowed'),
    'CFG_GA_MS' => config('tp.group_avatars.max_size'),
    'CFG_GA_UA' => config('tp.group_avatars.up_allowed'),
    'CFG_PM_NE' => config('tp.pm_notify_enabled'),
    'CFG_AC_LANG' => config('tp.allow_change.language'),
    'CFG_BIRTH_E' => config('tp.birthday_enabled'),
    'CFG_GENDER' => config('tp.gender'),
    'CFG_EMAIL_E' => config('tp.email.enabled'),
    'CFG_REQ_EA' => config('tp.reg_email_activation'),
    'CFG_TOR_STATS' => config('tp.tor_stats'),
    'CFG_SEARCH_ET' => config('tp.search_engine_type'),
    'CFG_TB_ONLY_REG' => config('tp.bt_tor_browse_only_reg'),
    'CFG_RTU_HELP' => config('tp.ratio_url_help'),
    'CFG_WIT_HELP' => config('tp.what_is_torrent_url_help'),
    'CFG_HTD_HELP' => config('tp.how_to_download_url_help'),
    'CFG_MAX_PO' => config('tp.max_poll_options'),
    'CFG_LOG_DK' => config('tp.log_days_keep'),
    'CFG_AJAX_URL' => config('tp.ajax_url'),
    'CFG_TP_RD' => config('tp.tp_release_date'),
    'CFG_TP_CN' => config('tp.tp_release_codename'),
    'CFG_TP_VER' => config('tp.tp_version'),

    /** TODO: подмененные языковые, используемые в шаблонах, избавиться */
    'LNG_GENDER_O' => trans('messages.GENDER_SELECT.0'),
    'LNG_GENDER_1' => trans('messages.GENDER_SELECT.1'),
    'LNG_GENDER_2' => trans('messages.GENDER_SELECT.2'),
));

if (!empty(config('page.show_torhelp.' . BB_SCRIPT)) && !empty($userdata['torhelp'])) {
    $ignore_time = !empty($_COOKIE['torhelp']) ? (int)$_COOKIE['torhelp'] : 0;

    if (TIMENOW > $ignore_time) {
        if ($ignore_time) {
            bb_setcookie('torhelp', '', COOKIE_EXPIRED);
        }

        $sql = '
			SELECT topic_id, topic_title
			FROM ' . BB_TOPICS . '
			WHERE topic_id IN(' . $userdata['torhelp'] . ')
			LIMIT 8
		';
        $torhelp_topics = array();

        foreach (OLD_DB()->fetch_rowset($sql) as $row) {
            $torhelp_topics[] = '<a href="viewtopic.php?t=' . $row['topic_id'] . '">' . $row['topic_title'] . '</a>';
        }

        $template->assign_vars(array(
            'TORHELP_TOPICS' => implode("</li>\n<li>", $torhelp_topics),
        ));
    }
}

// Login box
$in_out = $logged_in ? 'in' : 'out';
$template->assign_block_vars("switch_user_logged_{$in_out}", array());

if (!IS_GUEST) {
    header('Cache-Control: private, pre-check=0, post-check=0, max-age=0');
    header('Expires: 0');
    header('Pragma: no-cache');
}

$template->set_filenames(array('page_header' => 'page_header.tpl'));
$template->pparse('page_header');

define('PAGE_HEADER_SENT', true);

if (!config('tp.gzip_compress')) {
    flush();
}
