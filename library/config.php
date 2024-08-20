<?php
/**
 * TorrentPier – Bull-powered BitTorrent tracker engine
 *
 * @copyright Copyright (c) 2005-2024 TorrentPier (https://torrentpier.com)
 * @link      https://github.com/torrentpier/torrentpier for the canonical source repository
 * @license   https://github.com/torrentpier/torrentpier/blob/master/LICENSE MIT License
 */

if (!defined('BB_ROOT')) {
    die(basename(__FILE__));
}

// Server settings
$reserved_name = 'torrentpier.com';
$reserved_port = 80;

$bb_cfg = [];

// Increase number after changing js or css
$bb_cfg['js_ver'] = $bb_cfg['css_ver'] = 1;

// Version info
$bb_cfg['tp_version'] = 'v2.4.5-dev';
$bb_cfg['tp_release_date'] = 'XX-XX-2024';
$bb_cfg['tp_release_codename'] = 'Cattle';

// Database
// Settings for database ['db']['srv_name'] => (array) srv_cfg;
$bb_cfg['db'] = [
    'db' => [
        // Don't change the settings here!!! Go to .env file
        env('DB_HOST', 'localhost'),
        env('DB_PORT', 3306),
        env('DB_DATABASE', 'torrentpier'),
        env('DB_USERNAME', 'root'),
        env('DB_PASSWORD'),
        'utf8mb4',
        false
    ],
];

$bb_cfg['db_alias'] = [
    'log' => 'db', // BB_LOG
    'search' => 'db', // BB_TOPIC_SEARCH
    'sres' => 'db', // BB_BT_USER_SETTINGS, BB_SEARCH_RESULTS
    'u_ses' => 'db', // BB_USER_SES, BB_USER_LASTVISIT
    'dls' => 'db', // BB_BT_DLS_*
    'ip' => 'db', // BB_POSTS_IP
    'ut' => 'db', // BB_TOPICS_USER_POSTED
    'pm' => 'db', // BB_PRIVMSGS, BB_PRIVMSGS_TEXT
    'pt' => 'db', // BB_POSTS_TEXT
];

// Cache
$bb_cfg['cache'] = [
    'db_dir' => realpath(BB_ROOT) . '/internal_data/cache/filecache/',
    'prefix' => 'tp_',
    'memcached' => [
        'host' => '127.0.0.1',
        'port' => 11211,
    ],
    'redis' => [
        'host' => '127.0.0.1',
        'port' => 6379,
        'pconnect' => !PHP_ZTS, // Redis pconnect supported only for non-thread safe compilations of PHP
    ],
    // Available cache types: filecache, memcached, sqlite, redis, apcu (filecache by default)
    'engines' => [
        'bb_cache' => ['filecache'],
        'bb_config' => ['filecache'],
        'tr_cache' => ['filecache'],
        'session_cache' => ['filecache'],
        'bb_cap_sid' => ['filecache'],
        'bb_login_err' => ['filecache'],
        'bb_poll_data' => ['filecache'],
        'bb_ip2countries' => ['filecache'],
    ],
];

// Datastore
// Available datastore types: filecache, memcached, sqlite, redis, apcu (filecache by default)
$bb_cfg['datastore_type'] = 'filecache';

// Server
$bb_cfg['server_name'] = $domain_name = !empty($_SERVER['SERVER_NAME']) ? idn_to_utf8($_SERVER['SERVER_NAME']) : $reserved_name;
$bb_cfg['server_port'] = !empty($_SERVER['SERVER_PORT']) ? $_SERVER['SERVER_PORT'] : $reserved_port;
$bb_cfg['script_path'] = '/'; // The path where FORUM is located relative to the domain name

// GZip
$bb_cfg['gzip_compress'] = false; // Compress output

// Tracker
$bb_cfg['announce_interval'] = 1800; // Announce interval (default: 1800)
$bb_cfg['scrape_interval'] = 300; // Scrape interval (default: 300)
$bb_cfg['max_scrapes'] = 150; // Allowed number of info-hashes for simultaneous scraping, only not cached info-hashes will abide by these limits (default: 150)
$bb_cfg['passkey_key'] = 'uk'; // Passkey key name in GET request
$bb_cfg['ignore_reported_ip'] = false; // Ignore IP reported by client
$bb_cfg['verify_reported_ip'] = true; // Verify IP reported by client against $_SERVER['HTTP_X_FORWARDED_FOR']
$bb_cfg['allow_internal_ip'] = false; // Allow internal IP (10.xx.. etc.)
$bb_cfg['client_ban'] = [
    'enabled' => false,
    // Clients to be blocked, for example, peer id '-UT' will block all uTorrent clients, '-UT2' will block builds starting with 2 (default: false)
    // The second argument is being shown in the torrent client as a failure message
    // Handy client list: https://github.com/transmission/transmission/blob/f85c3b6f8db95d5363f6ec38eee603f146c6adb6/libtransmission/clients.cc#L504
    'clients' => [
        '-UT' => 'uTorrent — NOT ad-free and open-source',
        '-MG' => 'Mostly leeching client',
    ]
];

// TorrServer integration
$bb_cfg['torr_server'] = [
    // Read more: https://github.com/YouROK/TorrServer
    'enabled' => false,
    'url' => "http://$domain_name:8090",
    'timeout' => 5
];

// Ocelot
$bb_cfg['ocelot'] = [
    'enabled' => false,
    'host' => $domain_name,
    'port' => 34000,
    'url' => "http://$domain_name:34000/", // with '/'
    'secret' => 'some_10_chars', // 10 chars
    'stats' => 'some_10_chars', // 10 chars
];

// FAQ url help link
$bb_cfg['how_to_download_url_help'] = 'viewtopic.php?t=1'; // How to download?
$bb_cfg['what_is_torrent_url_help'] = 'viewtopic.php?t=2'; // What is a torrent?
$bb_cfg['ratio_url_help'] = 'viewtopic.php?t=3'; // Rating and limits
$bb_cfg['search_help_url'] = 'viewtopic.php?t=4'; // Help doc about performing basic searches

// Torrents
$bb_cfg['bt_min_ratio_allow_dl_tor'] = 0.3; // 0 - disable
$bb_cfg['bt_min_ratio_warning'] = 0.6; // 0 - disable

$bb_cfg['show_dl_status_in_search'] = true;
$bb_cfg['show_dl_status_in_forum'] = true;
$bb_cfg['show_tor_info_in_dl_list'] = true;
$bb_cfg['allow_dl_list_names_mode'] = true;

// Null ratio
$bb_cfg['ratio_null_enabled'] = true;
$bb_cfg['ratio_to_null'] = $bb_cfg['bt_min_ratio_allow_dl_tor']; // 0.3

// Days to keep torrent registered
$bb_cfg['seeder_last_seen_days_keep'] = 0; // Max time storing for the last seen peer status
$bb_cfg['seeder_never_seen_days_keep'] = 0; // Max time for storing status - Never seen

// DL-Status (days to keep user's dlstatus records)
$bb_cfg['dl_will_days_keep'] = 360;
$bb_cfg['dl_down_days_keep'] = 180;
$bb_cfg['dl_complete_days_keep'] = 180;
$bb_cfg['dl_cancel_days_keep'] = 30;

// Tor-Stats
$bb_cfg['torstat_days_keep'] = 60; // Days to keep user's per-torrent stats

// Tor-Help
$bb_cfg['torhelp_enabled'] = false; // Find dead torrents (without seeder) that user might help seeding

// URL's
$bb_cfg['ajax_url'] = 'ajax.php'; # "http://{$_SERVER['SERVER_NAME']}/ajax.php"
$bb_cfg['dl_url'] = 'dl.php?id='; # "http://{$domain_name}/dl.php?id="
$bb_cfg['login_url'] = 'login.php'; # "http://{$domain_name}/login.php"
$bb_cfg['posting_url'] = 'posting.php'; # "http://{$domain_name}/posting.php"
$bb_cfg['pm_url'] = 'privmsg.php'; # "http://{$domain_name}/privmsg.php"

// Language
$bb_cfg['charset'] = 'UTF-8';
$bb_cfg['lang'] = [
    // Languages available for selecting
    'af' => [
        'name' => 'Afrikaans',
        'locale' => 'af_ZA.UTF-8',
    ],
    'sq' => [
        'name' => 'Albanian',
        'locale' => 'sq_AL.UTF-8',
    ],
    'ar' => [
        'name' => 'Arabic',
        'locale' => 'ar_SA.UTF-8',
    ],
    'hy' => [
        'name' => 'Armenian',
        'locale' => 'hy_AM.UTF-8',
    ],
    'az' => [
        'name' => 'Azerbaijani',
        'locale' => 'az_AZ.UTF-8',
    ],
    'be' => [
        'name' => 'Belarusian',
        'locale' => 'be_BY.UTF-8',
    ],
    'bs' => [
        'name' => 'Bosnian',
        'locale' => 'bs_BA.UTF-8',
    ],
    'bg' => [
        'name' => 'Bulgarian',
        'locale' => 'bg_BG.UTF-8',
    ],
    'ca' => [
        'name' => 'Catalan',
        'locale' => 'ca_ES.UTF-8',
    ],
    'zh' => [
        'name' => 'Chinese Simplified',
        'locale' => 'zh_CN.UTF-8',
    ],
    'hr' => [
        'name' => 'Croatian',
        'locale' => 'hr_HR.UTF-8',
    ],
    'cs' => [
        'name' => 'Czech',
        'locale' => 'cs_CZ.UTF-8',
    ],
    'da' => [
        'name' => 'Danish',
        'locale' => 'da_DK.UTF-8',
    ],
    'nl' => [
        'name' => 'Dutch',
        'locale' => 'nl_NL.UTF-8',
    ],
    'en' => [
        'name' => 'English',
        'locale' => 'en_US.UTF-8',
    ],
    'et' => [
        'name' => 'Estonian',
        'locale' => 'et_EE.UTF-8',
    ],
    'fi' => [
        'name' => 'Finnish',
        'locale' => 'fi_FI.UTF-8',
    ],
    'fr' => [
        'name' => 'French',
        'locale' => 'fr_FR.UTF-8',
    ],
    'ka' => [
        'name' => 'Georgian',
        'locale' => 'ka_GE.UTF-8',
    ],
    'de' => [
        'name' => 'German',
        'locale' => 'de_DE.UTF-8',
    ],
    'el' => [
        'name' => 'Greek',
        'locale' => 'el_GR.UTF-8',
    ],
    'he' => [
        'name' => 'Hebrew',
        'locale' => 'he_IL.UTF-8',
    ],
    'hi' => [
        'name' => 'Hindi',
        'locale' => 'hi_IN.UTF-8',
    ],
    'hu' => [
        'name' => 'Hungarian',
        'locale' => 'hu_HU.UTF-8',
    ],
    'id' => [
        'name' => 'Indonesian',
        'locale' => 'id_ID.UTF-8',
    ],
    'it' => [
        'name' => 'Italian',
        'locale' => 'it_IT.UTF-8',
    ],
    'ja' => [
        'name' => 'Japanese',
        'locale' => 'ja_JP.UTF-8',
    ],
    'kk' => [
        'name' => 'Kazakh',
        'locale' => 'kk_KZ.UTF-8',
    ],
    'ko' => [
        'name' => 'Korean',
        'locale' => 'ko_KR.UTF-8',
    ],
    'lv' => [
        'name' => 'Latvian',
        'locale' => 'lv_LV.UTF-8',
    ],
    'lt' => [
        'name' => 'Lithuanian',
        'locale' => 'lt_LT.UTF-8',
    ],
    'no' => [
        'name' => 'Norwegian',
        'locale' => 'nn_NO.UTF-8',
    ],
    'pl' => [
        'name' => 'Polish',
        'locale' => 'pl_PL.UTF-8',
    ],
    'pt' => [
        'name' => 'Portuguese',
        'locale' => 'pt_PT.UTF-8',
    ],
    'ro' => [
        'name' => 'Romanian',
        'locale' => 'ro_RO.UTF-8',
    ],
    'ru' => [
        'name' => 'Russian',
        'locale' => 'ru_RU.UTF-8',
    ],
    'sr' => [
        'name' => 'Serbian',
        'locale' => 'sr_CS.UTF-8',
    ],
    'sk' => [
        'name' => 'Slovak',
        'locale' => 'sk_SK.UTF-8',
    ],
    'sl' => [
        'name' => 'Slovenian',
        'locale' => 'sl_SI.UTF-8',
    ],
    'es' => [
        'name' => 'Spanish',
        'locale' => 'es_ES.UTF-8',
    ],
    'sv' => [
        'name' => 'Swedish',
        'locale' => 'sv_SE.UTF-8',
    ],
    'tg' => [
        'name' => 'Tajik',
        'locale' => 'tg_TJ.UTF-8',
    ],
    'th' => [
        'name' => 'Thai',
        'locale' => 'th_TH.UTF-8',
    ],
    'tr' => [
        'name' => 'Turkish',
        'locale' => 'tr_TR.UTF-8',
    ],
    'uk' => [
        'name' => 'Ukrainian',
        'locale' => 'uk_UA.UTF-8',
    ],
    'uz' => [
        'name' => 'Uzbek',
        'locale' => 'uz_UZ.UTF-8',
    ],
    'vi' => [
        'name' => 'Vietnamese',
        'locale' => 'vi_VN.UTF-8',
    ],
];

// Templates
$bb_cfg['templates'] = [
    // Available templates for selecting
    'default' => 'Default',
];

$bb_cfg['tpl_name'] = 'default'; // Default template
$bb_cfg['stylesheet'] = 'main.css';

$bb_cfg['show_sidebar1_on_every_page'] = false; // Show left sidebar in every page
$bb_cfg['show_sidebar2_on_every_page'] = false; // Show right sidebar in every page

// Cookie
$bb_cfg['cookie_domain'] = in_array($domain_name, [$_SERVER['SERVER_ADDR'], 'localhost'], true) ? '' : ".$domain_name";
$bb_cfg['cookie_secure'] = \TorrentPier\Helpers\IsHelper::isHTTPS();
$bb_cfg['cookie_prefix'] = 'bb_'; // 'bb_'
$bb_cfg['cookie_same_site'] = 'Lax'; // Lax, None, Strict | https://developer.mozilla.org/en-US/docs/Web/HTTP/Headers/Set-Cookie/SameSite

// Sessions
$bb_cfg['session_update_intrv'] = 180; // sec
$bb_cfg['user_session_duration'] = 1800; // sec
$bb_cfg['admin_session_duration'] = 6 * 3600; // sec
$bb_cfg['user_session_gc_ttl'] = 1800; // number of seconds that a staled session entry may remain in sessions table
$bb_cfg['session_cache_gc_ttl'] = 1200; // sec
$bb_cfg['max_last_visit_days'] = 14; // days
$bb_cfg['last_visit_update_intrv'] = 3600; // sec

// Registration
$bb_cfg['invalid_logins'] = 5; // Max incorrect password submits before showing captcha
$bb_cfg['new_user_reg_disabled'] = false; // Disable registration of new users
$bb_cfg['unique_ip'] = false; // Disallow registration from multiple IP addresses
$bb_cfg['new_user_reg_restricted'] = false; // Disallow registration in below hours
$bb_cfg['new_user_reg_interval'] = [0, 1, 2, 3, 4, 5, 6, 7, 8, 11, 12, 13, 14, 15, 16, 17, 18, 19, 20, 21, 22, 23]; // Available hours
$bb_cfg['reg_email_activation'] = true; // Demand to activate profile by email confirmation
$bb_cfg['invites_system'] = [
    'enabled' => false,
    'codes' => [
        // Syntax: 'invite_code' => 'validity_period'
        // The 'validity_period' value is based on strtotime() function: https://www.php.net/manual/en/function.strtotime.php
        // Invite link example: site_url/profile.php?mode=register&invite=new_year
        'new_year' => '2022-12-31 00:00:01',
        'ein_volk' => '29 July 1936',
        'catch_up_overtake' => '1917-10-09'
    ]
];
$bb_cfg['password_symbols'] = [
    // What symbols should be required in the password
    'nums' => true, // Numeric
    'spec_symbols' => false, // Special symbols
    'letters' => [ // Letters
        'uppercase' => true, // Uppercase letters
        'lowercase' => true // Lowercase letters
    ]
];
$bb_cfg['password_hash_options'] = [
    // https://www.php.net/manual/ru/password.constants.php
    'algo' => PASSWORD_BCRYPT,
    'options' => []
];

// Email
$bb_cfg['emailer'] = [
    'enabled' => true,
    'smtp' => [
        'enabled' => false, // send email via external SMTP server
        'host' => 'localhost', // SMTP server host
        'port' => 25, // SMTP server port
        'username' => '', // SMTP username (if server requires it)
        'password' => '', // SMTP password (if server requires it)
        'ssl_type' => '', // SMTP ssl type (ssl or tls)
    ],
];
$bb_cfg['extended_email_validation'] = true; // DNS & RFC checks for entered email addresses

$bb_cfg['board_email'] = "noreply@$domain_name"; // admin email address
$bb_cfg['board_email_form'] = false; // can users send email to each other via board
$bb_cfg['board_email_sig'] = ''; // this text will be attached to all emails the board sends
$bb_cfg['board_email_sitename'] = $domain_name; // sitename used in all emails header

$bb_cfg['topic_notify_enabled'] = true; // Send emails to users if subscribed to the topic
$bb_cfg['pm_notify_enabled'] = true; // Send emails to users if there's a new message in inbox
$bb_cfg['group_send_email'] = true; // Send emails to users if user was invited/added to a group
$bb_cfg['email_change_disabled'] = false; // Allow changing emails for users
$bb_cfg['show_email_visibility_settings'] = true; // Allow changing privacy status of profile for users (e.g. last time seen)

$bb_cfg['bounce_email'] = "bounce@$domain_name"; // bounce email address
$bb_cfg['tech_admin_email'] = "admin@$domain_name"; // email for sending error reports
$bb_cfg['abuse_email'] = "abuse@$domain_name"; // abuse email (e.g. DMCA)
$bb_cfg['adv_email'] = "adv@$domain_name"; // advertisement email

// Error reporting
$bb_cfg['whoops'] = [
    'error_message' => 'Sorry, something went wrong. Drink coffee and come back after some time... ☕️',
    'blacklist' => [
        '_COOKIE' => array_keys($_COOKIE),
        '_SERVER' => array_keys($_SERVER),
        '_ENV' => array_keys($_ENV),
    ]
];

$bb_cfg['bugsnag'] = [
    'enabled' => true,
    'api_key' => '33b3ed0102946bab71341f9edc125e21', // Don't change this if you want to help us find bugs
];

$bb_cfg['telegram_sender'] = [
    // How to get chat_id? https://api.telegram.org/bot{YOUR_TOKEN}/getUpdates
    'enabled' => false,
    'token' => '', // Bot token
    'chat_id' => '', // Bot chat_id
    'timeout' => 10 // Timeout for responses
];

// Special users
$bb_cfg['unlimited_users'] = [
    // Syntax: 'user_id' => 'username'
    2 => 'admin',
];
$bb_cfg['super_admins'] = [
    // Syntax: 'user_id' => 'username'
    2 => 'admin',
];

// Subforums
$bb_cfg['sf_on_first_page_only'] = true; // Show subforums only on the first page of the forum

// Forums
$bb_cfg['allowed_topics_per_page'] = [50, 100, 150, 200, 250, 300]; // Allowed number of topics per page

// Topics
$bb_cfg['show_post_bbcode_button'] = true; // Show "Code" button in topic to display BBCode of topic
$bb_cfg['show_quick_reply'] = true; // Show quick reply forim
$bb_cfg['show_rank_text'] = false; // Show user rank name in topics
$bb_cfg['show_rank_image'] = true; // Show user rank image in topics
$bb_cfg['show_poster_joined'] = true; // Show user's registration date in topics
$bb_cfg['show_poster_posts'] = true; // Show user's post count in topics
$bb_cfg['show_poster_from'] = true; // Show user's country in topics
$bb_cfg['show_bot_nick'] = true; // Show bot's nickname
$bb_cfg['text_buttons'] = false; // replace EDIT, QUOTE... images with text links
$bb_cfg['post_date_format'] = 'd-M-Y H:i'; // Date format for topics
$bb_cfg['ext_link_new_win'] = true; // open external links in new window

$bb_cfg['topic_moved_days_keep'] = 7; // remove topic moved links after xx days (or FALSE to disable)
$bb_cfg['allowed_posts_per_page'] = [15, 30, 50, 100];
$bb_cfg['user_signature_start'] = '<div class="signature"><br />_________________<br />';
$bb_cfg['user_signature_end'] = '</div>'; // It allows user signatures to have closings "<>"

// Posts
$bb_cfg['use_posts_cache'] = true;
$bb_cfg['posts_cache_days_keep'] = 14;
$bb_cfg['use_ajax_posts'] = true;

// Search
$bb_cfg['search_engine_type'] = 'mysql'; // none, mysql, sphinx

$bb_cfg['sphinx_topic_titles_host'] = '127.0.0.1';
$bb_cfg['sphinx_topic_titles_port'] = 3312;
$bb_cfg['sphinx_config_path'] = '../install/sphinx/sphinx.conf';

$bb_cfg['disable_ft_search_in_posts'] = false; // disable searching in post bodies
$bb_cfg['disable_search_for_guest'] = true; // Disable search for guests
$bb_cfg['allow_search_in_bool_mode'] = true;
$bb_cfg['max_search_words_per_post'] = 200; // Max word count for a post
$bb_cfg['search_min_word_len'] = 3; // Min letters to perform a search
$bb_cfg['search_max_word_len'] = 35; // Maximum letters to perform a search
$bb_cfg['limit_max_search_results'] = false; // Limit for number of search results (false - unlimited)

// Posting
$bb_cfg['prevent_multiposting'] = true; // TODO: replace "reply" with "edit last msg" if user (not admin or mod) is last topic poster
$bb_cfg['max_smilies'] = 25; //Max number of smilies in a post (0 - unlimited)
$bb_cfg['max_symbols_post'] = 5000; // TODO: Max number of symbols in a post (0 - unlimited)

// PM
$bb_cfg['privmsg_disable'] = false; // Disable private messages
$bb_cfg['max_outgoing_pm_cnt'] = 10; // TODO: Max number of messages in a short period of time to fight spam
$bb_cfg['max_inbox_privmsgs'] = 500; // Max number of messages in pm's inbox folder
$bb_cfg['max_savebox_privmsgs'] = 500; // Max number of messages in pm's saved folder
$bb_cfg['max_sentbox_privmsgs'] = 500; // Max number of messages in pm's sent folder
$bb_cfg['max_smilies_pm'] = 15; // Max number of smilies in a message (0 - unlimited)
$bb_cfg['max_symbols_pm'] = 1500; // TODO: Max number of symbols in a message (0 - unlimited)
$bb_cfg['pm_days_keep'] = 0; // Max time for storing personal messages (0 - unlimited)

// Actions log
$bb_cfg['log_days_keep'] = 365; // How much time will action history will be stored (0 - unlimited)

// Users
$bb_cfg['color_nick'] = true; // Colour usernames in accordance with user_rank
$bb_cfg['user_not_activated_days_keep'] = 7; // After how many days to delete users who have not completed registration (that is, the account is not activated)
$bb_cfg['user_not_active_days_keep'] = 180; // After how many days should I delete users who were inactive and did not have a single post?

// Vote for torrents
$bb_cfg['tor_thank'] = true;
$bb_cfg['tor_thanks_list_guests'] = true; // Show voters to guests
$bb_cfg['tor_thank_limit_per_topic'] = 50;

// Groups
$bb_cfg['group_members_per_page'] = 50; // How many groups will be displayed in a page

// Tidy
$bb_cfg['tidy_post'] = extension_loaded('tidy');

// Misc
$bb_cfg['mem_on_start'] = memory_get_usage();
$bb_cfg['translate_dates'] = true; // in displaying time
$bb_cfg['use_word_censor'] = true;
$bb_cfg['show_jumpbox'] = true; // Whether to show jumpbox (on viewtopic.php and viewforum.php)
$bb_cfg['flist_timeout'] = 15; // Max number of seconds to process file lists in forum before throwing an error (default: 15)
$bb_cfg['flist_max_files'] = 0; // Max allowed number of files to process for giving out to indexers (0 - unlimited)
$bb_cfg['last_visit_date_format'] = 'd-M H:i';
$bb_cfg['last_post_date_format'] = 'd-M-y H:i';
$bb_cfg['poll_max_days'] = 180; // How many days will the poll be active
$bb_cfg['integrity_check'] = true; // TorrentPier files integrity check (Not recommended to disable!)

$bb_cfg['allow_change'] = [
    'language' => true, // Allow user to change language
    'timezone' => true // Allow user to change time zone
];

$bb_cfg['trash_forum_id'] = 0; // (int) 7

$bb_cfg['first_logon_redirect_url'] = 'index.php'; // Which page should the user be redirected to after registration is completed?
$bb_cfg['terms_and_conditions_url'] = 'terms.php'; // Link to forum rules page
$bb_cfg['tor_help_links'] = '<div class="mrg_2"><a target="_blank" class="genmed" href="https://torrentpier.com/forums/osnovnye-voprosy-po-torrentpier.10/">Полезная информация</a></div>';

$bb_cfg['user_agreement_url'] = 'info.php?show=user_agreement';
$bb_cfg['copyright_holders_url'] = 'info.php?show=copyright_holders';
$bb_cfg['advert_url'] = 'info.php?show=advert';

$bb_cfg['sitemap_sending'] = [
    // A list of URLs to which to send a sitemap for indexing by a search robot.
    // Syntax: 'Resource name' => 'link to endpoint'
    'Google' => 'https://google.com/webmasters/sitemaps/ping?sitemap=',
];

// Extensions
$bb_cfg['file_id_ext'] = [
    1 => 'gif',
    2 => 'gz',
    3 => 'jpg',
    4 => 'png',
    5 => 'rar',
    6 => 'tar',
    8 => 'torrent',
    9 => 'zip',
    10 => '7z',
    11 => 'bmp',
    12 => 'webp'
];

// Attachments
$bb_cfg['attach'] = [
    'upload_path' => DATA_DIR . '/uploads', // Storage path for torrent files
    'max_size' => 5 * 1024 * 1024, // TODO: Max size of a file
];

$bb_cfg['tor_forums_allowed_ext'] = ['torrent', 'zip', 'rar']; // TODO: For sections with releases
$bb_cfg['gen_forums_allowed_ext'] = ['zip', 'rar']; // TODO: For regular sections

// Avatars
$bb_cfg['avatars'] = [
    'allowed_ext' => ['gif', 'jpg', 'png', 'bmp', 'webp'], // Allowed file extensions (after changing, do the same for $bb_cfg['file_id_ext'])
    'bot_avatar' => '/gallery/bot.gif', // The bot's avatar
    'max_size' => 100 * 1024, // Avatar's allowed dimensions
    'max_height' => 100, // Avatar height in px
    'max_width' => 100, // Avatar width in px
    'no_avatar' => '/gallery/noavatar.png', // Default avatar
    'display_path' => '/data/avatars', // Location for avatar files for displaying
    'upload_path' => BB_PATH . '/data/avatars/', // Storage path for avatar files
    'up_allowed' => true, // Allow changing avatars
];

// Group avatars
$bb_cfg['group_avatars'] = [
    'allowed_ext' => ['gif', 'jpg', 'png', 'bmp', 'webp'], // Allowed file extensions (add the same for $bb_cfg['file_id_ext'])
    'max_size' => 300 * 1024, // max avatar size in bytes
    'max_height' => 300, // Avatar height in px
    'max_width' => 300, // Avatar weight in px
    'no_avatar' => '/gallery/noavatar.png', // Default avatar
    'display_path' => '/data/avatars', // Location for avatar files for displaying
    'upload_path' => BB_PATH . '/data/avatars/', // Storage path for avatar files
    'up_allowed' => true, // Allow changing avatars
];

// Captcha
// Get a Google reCAPTCHA API Key: https://www.google.com/recaptcha/admin
$bb_cfg['captcha'] = [
    'disabled' => true,
    'public_key' => '',
    'secret_key' => '',
    'theme' => 'light', // theming (available: light, dark)
];

// Atom feed
$bb_cfg['atom'] = [
    'path' => INT_DATA_DIR . '/atom', // without ending slash
    'url' => './internal_data/atom', // without ending slash
    'direct_down' => true, // Allow direct downloading of torrents from feeds
    'direct_view' => true, // Allow direct viewing of post texts in feeds
];

// Nofollow
$bb_cfg['nofollow'] = [
    'disabled' => false,
    'allowed_url' => [$domain_name], // 'allowed.site', 'www.allowed.site'
];

// Page settings
$bb_cfg['page'] = [
    'show_torhelp' => [
        #BB_SCRIPT => true
        'index' => true,
        'tracker' => true,
    ],
    'show_sidebar1' => [
        #BB_SCRIPT => true
        'index' => true,
    ],
    'show_sidebar2' => [
        #BB_SCRIPT => true
        'index' => true,
    ]
];

// Tracker settings
$bb_cfg['tracker'] = [
    'autoclean' => true,
    'bt_off' => false,
    'bt_off_reason' => 'Temporarily disabled',
    'numwant' => 50,
    'update_dlstat' => true,
    'expire_factor' => 2.5,
    'compact_mode' => true,
    'upd_user_up_down_stat' => true,
    'browser_redirect_url' => '',
    'scrape' => true,
    'limit_active_tor' => true,
    'limit_seed_count' => 0,
    'limit_leech_count' => 8,
    'leech_expire_factor' => 60,
    'limit_concurrent_ips' => false,
    'limit_seed_ips' => 0,
    'limit_leech_ips' => 0,
    'tor_topic_up' => true,
    'retracker' => true,
    'retracker_host' => 'http://retracker.local/announce',
    'guest_tracker' => true,
    'search_by_tor_status' => true,
    'random_release_button' => true,
    'freeleech' => false, // freeleech mode (If enabled, then disable "gold_silver_enabled")
    'gold_silver_enabled' => true, // golden / silver days mode (If enabled, then disable "freeleech")
    'hybrid_stat_protocol' => 1, // For hybrid torrents there are two identical requests sent by clients, for counting stats we gotta choose one, you can change this to '2' in future, when v1 protocol is outdated
    'disabled_v1_torrents' => false, // disallow registration of v1-only torrents, for future implementations where client will use v2 only and there won't be need for v1, thus relieving tracker
    'disabled_v2_torrents' => false // disallow registration of v2-only torrents
];

// Ratio settings
// Don't change the order of ratios (from 0 to 1)
// rating < 0.4 -- allow only 1 torrent for leeching
// rating < 0.5 -- only 2
// rating < 0.6 -- only 3
// rating > 0.6 -- depend on your tracker config limits (in "ACP - Tracker Config - Limits")
$bb_cfg['rating'] = [
    '0.4' => 1,
    '0.5' => 2,
    '0.6' => 3,
];

// Icons for statuses of releases
$bb_cfg['tor_icons'] = [
    TOR_NOT_APPROVED => '<span class="tor-icon tor-not-approved">*</span>',
    TOR_CLOSED => '<span class="tor-icon tor-closed">x</span>',
    TOR_APPROVED => '<span class="tor-icon tor-approved">&radic;</span>',
    TOR_NEED_EDIT => '<span class="tor-icon tor-need-edit">?</span>',
    TOR_NO_DESC => '<span class="tor-icon tor-no-desc">!</span>',
    TOR_DUP => '<span class="tor-icon tor-dup">D</span>',
    TOR_CLOSED_CPHOLD => '<span class="tor-icon tor-closed-cp">&copy;</span>',
    TOR_CONSUMED => '<span class="tor-icon tor-consumed">&sum;</span>',
    TOR_DOUBTFUL => '<span class="tor-icon tor-approved">#</span>',
    TOR_CHECKING => '<span class="tor-icon tor-checking">%</span>',
    TOR_TMP => '<span class="tor-icon tor-dup">T</span>',
    TOR_PREMOD => '<span class="tor-icon tor-dup">&#8719;</span>',
    TOR_REPLENISH => '<span class="tor-icon tor-dup">R</span>',
];

// Disallowed for downloading
$bb_cfg['tor_frozen'] = [
    TOR_CHECKING => true,
    TOR_CLOSED => true,
    TOR_CLOSED_CPHOLD => true,
    TOR_CONSUMED => true,
    TOR_DUP => true,
    TOR_NO_DESC => true,
    TOR_PREMOD => true,
];

// Can the creator download torrent if release status is closed
$bb_cfg['tor_frozen_author_download'] = [
    TOR_CHECKING => true,
    TOR_NO_DESC => true,
    TOR_PREMOD => true,
];

// Disallowed release editing with a certain status
$bb_cfg['tor_cannot_edit'] = [TOR_CHECKING, TOR_CLOSED, TOR_CONSUMED, TOR_DUP];

// Disallowed for creating new releases if status is not fully formatted/unformatted/suspicious
$bb_cfg['tor_cannot_new'] = [TOR_NEED_EDIT, TOR_NO_DESC, TOR_DOUBTFUL];

// If the creator is allowed to answer if release has been changed
$bb_cfg['tor_reply'] = [TOR_NEED_EDIT, TOR_NO_DESC, TOR_DOUBTFUL];

// If release statistics are closed
$bb_cfg['tor_no_tor_act'] = [
    TOR_CLOSED => true,
    TOR_DUP => true,
    TOR_CLOSED_CPHOLD => true,
    TOR_CONSUMED => true,
];

// PeerID's of torrent clients list
$bb_cfg['tor_clients'] = [
    '-AG' => 'Ares', '-AZ' => 'Vuze', '-A~' => 'Ares', '-BC' => 'BitComet',
    '-BE' => 'BitTorrent SDK', '-BI' => 'BiglyBT', '-BL' => 'BitLord', '-BT' => 'BitTorrent',
    '-CT' => 'CTorrent', '-DE' => 'Deluge', '-FD' => 'Free Download Manager', 'FD6' => 'Free Download Manager',
    '-FG' => 'FlashGet', '-FL' => 'Folx', '-HL' => 'Halite', '-KG' => 'KGet',
    '-KT' => 'KTorrent', '-LT' => 'libTorrent', '-Lr' => 'LibreTorrent',
    '-TR' => 'Transmission', '-tT' => 'tTorrent', '-UM' => "uTorrent Mac", '-UT' => 'uTorrent',
    '-UW' => 'uTorrent Web', '-WW' => 'WebTorrent', '-WD' => 'WebTorrent', '-XL' => 'Xunlei',
    '-PI' => 'PicoTorrent', '-qB' => 'qBittorrent', 'M' => 'BitTorrent', 'MG' => 'MediaGet',
    '-MG' => 'MediaGet', 'OP' => 'Opera', 'TIX' => 'Tixati', 'aria2-' => 'Aria2', 'A2' => 'Aria2',
];

// Vote graphic length defines the maximum length of a vote result graphic, ie. 100% = this length
$bb_cfg['vote_graphic_length'] = 205;
$bb_cfg['privmsg_graphic_length'] = 175;
$bb_cfg['topic_left_column_witdh'] = 150;

// Images auto-resize
$bb_cfg['post_img_width_decr'] = 52;
$bb_cfg['attach_img_width_decr'] = 130;

// Get default lang
if (isset($bb_cfg['default_lang']) && is_file(LANG_ROOT_DIR . '/' . $bb_cfg['default_lang'])) {
    $bb_cfg['default_lang_dir'] = LANG_ROOT_DIR . '/' . $bb_cfg['default_lang'] . '/';
} else {
    $bb_cfg['default_lang_dir'] = LANG_ROOT_DIR . '/en/';
}
