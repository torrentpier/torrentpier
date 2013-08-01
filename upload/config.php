<?php

/**
  * Database
  * Cache
    - Tracker Cache
    - Forum Cache
    - Session Cache
    - Datastore
  * Tracker
  * Torrents
    - Ratio limits
    - Seeding torrents limit
    - DL-Status (days to keep)
    - Tor-Stats (days to keep)
    - Tor-Help
  * Path
  * Language
  * Templates
  * Cookie
  * Server
    - Server load
    - Backup
    - GZip
  * Sessions
  * Registration
  * Email
  * AJAX
  * Debug
  * Special users (dbg_users, unlimited_users, super_admins)
  * LOG
  * Error reporting

  * Subforums
  * Forums
  * Topics
  * Posts
  * Search
  * Actions log
  * Users
  * GroupCP

  * Ads
  * Misc
  * Captcha

**/

if (!defined('BB_ROOT')) die(basename(__FILE__));

$bb_cfg = $tr_cfg = $page_cfg = array();

// Primary domain name
$domain_name = 'torrentpier.me';                    // Enter here your primary domain name of your site
$domain_name = (!empty($_SERVER['SERVER_NAME'])) ? $_SERVER['SERVER_NAME'] : $domain_name;

// Increase number of revision after update
$bb_cfg['tp_version'] = '2.5 (unstable)';
$bb_cfg['tp_release_date'] = '02-08-2013';
$bb_cfg['tp_release_state'] = 'R530';

// Database
$charset  = 'utf8';
$pconnect = false;

// Настройка баз данных ['db']['srv_name'] => (array) srv_cfg;
// порядок параметров srv_cfg (хост, название базы, пользователь, пароль, charset, pconnect);
$bb_cfg['db']['db1'] = array('localhost', 'dbase', 'user', 'pass', $charset, $pconnect);
//$bb_cfg['db']['db2'] = array('localhost2', 'dbase2', 'user2', 'pass2', $charset, $pconnect);
//$bb_cfg['db']['db3'] = array('localhost3', 'dbase3', 'user2', 'pass3', $charset, $pconnect);

$bb_cfg['db_alias'] = array(
//	'alias'        => 'srv_name'
	'cap'          => 'db1',  // BB_CAPTCHA
);

// Cache
$bb_cfg['cache']['pconnect'] = true;
$bb_cfg['cache']['db_dir']   = realpath(BB_ROOT) .'/cache/filecache/';
$bb_cfg['cache']['prefix']   = '';  // Префикс кеша 'tp_2'
$bb_cfg['cache']['memcache'] = array(
	'host'         => '127.0.0.1',
	'port'         => 11211,
	'pconnect'     => true,
	'con_required' => true,
);
$bb_cfg['cache']['redis']  = array(
	'host'         => '127.0.0.1',
	'port'         => 6379,
	'con_required' => true,
);

// Available cache types: memcache, sqlite, redis, eaccelerator, apc, xcache (default of filecache)
# name => array( (string) type, (array) cfg )
$bb_cfg['cache']['engines'] = array(
	'bb_cache'       => array('filecache',   array()),
	'tr_cache'       => array('filecache',   array()),
	'session_cache'  => array('filecache',   array()),

	'bb_cap_sid'     => array('filecache',   array()),
	'bb_login_err'   => array('filecache',   array()),
);

// Datastore
// Available datastore types: memcache, sqlite, redis, eaccelerator, apc, xcache  (default filecache)
$bb_cfg['datastore_type'] = 'filecache';

// Server
$bb_cfg['server_name'] = $domain_name;              // The domain name from which this board runs
$bb_cfg['server_port'] = (!empty($_SERVER['SERVER_PORT'])) ? $_SERVER['SERVER_PORT'] : 80;                       // The port your server is running on
$bb_cfg['script_path'] = '/';                       // The path where FORUM is located relative to the domain name

// Server load
$bb_cfg['max_srv_load']       = 0;                  // 0 - disable
$bb_cfg['tr_working_second']  = 0;                  // 0 - disable

// Increase number after changing js or css
$bb_cfg['js_ver']             = 1;
$bb_cfg['css_ver']            = 1;

// Information messages
$bb_cfg['board_disabled_msg'] = 'форум временно отключен'; // 'forums temporarily disabled'; // show this msg if board has been disabled via ON/OFF trigger
$bb_cfg['srv_overloaded_msg'] = "Извините, в данный момент сервер перегружен\nПопробуйте повторить запрос через несколько минут";

// Backup
$bb_cfg['db_backup_shell_cmd']     = '';           // '/path/to/db_backup.sh 2>&1'
$bb_cfg['site_backup_shell_cmd']   = '';

// GZip
$bb_cfg['gzip_compress']      = true;              // compress output

// Tracker
$bb_cfg['announce_type']      = 'php';             // Тип анонсера, xbt или php
$bb_cfg['announce_xbt']       = "http://{$domain_name}:2710";
$bb_cfg['announce_interval']  = 2400;              // Announce interval (default: 1800)
$bb_cfg['passkey_key']        = 'uk';              // Passkey key name in GET request
$bb_cfg['ignore_reported_ip'] = false;             // Ignore IP reported by client
$bb_cfg['verify_reported_ip'] = true;              // Verify IP reported by client against $_SERVER['HTTP_X_FORWARDED_FOR']
$bb_cfg['allow_internal_ip']  = false;             // Allow internal IP (10.xx.. etc.)

// FAQ URL help link
$bb_cfg['how_to_download_url_help']  = 'viewtopic.php?t=1'; // Как скачивать?
$bb_cfg['what_is_torrent_url_help']  = 'viewtopic.php?t=2'; // Что такое торрент?
$bb_cfg['ratio_url_help']            = 'viewtopic.php?t=3'; // Рейтинг и ограничения
$bb_cfg['search_help_url']           = 'viewtopic.php?t=4'; // Помощь по поиску

$bb_cfg['bt_min_ratio_allow_dl_tor'] = 0;          // 0 - disable
$bb_cfg['bt_min_ratio_warning']      = 0;          // 0 - disable
$bb_cfg['bt_min_ratio_dl_button']    = 0;          // 0 - disable

$tr_cfg = array(
	'autoclean'             => true,
	'off'                   => false,
	'off_reason'            => 'temporarily disabled',
	'numwant'               => 50,
	'update_dlstat'         => true,
	'expire_factor'         => 2.5,
	'compact_mode'          => true,
	'upd_user_up_down_stat' => true,
	'browser_redirect_url'  => '',
	'scrape'                => true,
	'limit_active_tor'      => true,
	'limit_seed_count'      => 0,
	'limit_leech_count'     => 8,
	'leech_expire_factor'   => 60,
	'limit_concurrent_ips'  => false,
	'limit_seed_ips'        => 0,
	'limit_leech_ips'       => 0,
	'tor_topic_up'          => true,
    'gold_silver_enabled'   => true,
);

$bb_cfg['show_dl_status_in_search'] = true;
$bb_cfg['show_dl_status_in_forum']  = true;

$bb_cfg['show_tor_info_in_dl_list'] = true;
$bb_cfg['allow_dl_list_names_mode'] = true;

// Torrents
$bb_cfg['torrent_sign']   = ''; // e.g. "[yoursite.com]"
$bb_cfg['torrent_name_style']  = true; // Use torrent name style [yoursite.com].txxx.torrent
$bb_cfg['tor_help_links'] = '';

// Сколько дней сохранять торрент зарегистрированным / Days to keep torrent registered, if:
$bb_cfg['seeder_last_seen_days_keep']  = 0; // сколько дней назад был сид последний раз
$bb_cfg['seeder_never_seen_days_keep'] = 0; // сколько дней имеется статус "Сида не было никогда"

// Ratio limits
define('TR_RATING_LIMITS', true);                  // ON/OFF
define('MIN_DL_FOR_RATIO', 10737418240);           // 10 GB in bytes, 0 - disable

// Don't change the order of ratios (from 0 to 1)
// rating < 0.4 -- allow only 1 torrent for leeching
// rating < 0.5 -- only 2
// rating < 0.6 -- only 3
// rating > 0.6 -- depend on your tracker config limits (in "ACP - Tracker Config - Limits")
$rating_limits = array(
	'0.4' => 1,
	'0.5' => 2,
	'0.6' => 3,
);

// Seeding torrents limit
$bb_cfg['max_seeding_torrents']     	 = 0;        // 0 - unlimited
$bb_cfg['min_up_speed_per_torrent'] 	 = 500;      // bytes
$bb_cfg['too_many_seeding_redirect_url'] = 'viewtopic.php?t=TOPIC_ID';

// DL-Status
$bb_cfg['dl_will_days_keep']     = 60;          // days to keep user's dlstatus records
$bb_cfg['dl_down_days_keep']     = 30;
$bb_cfg['dl_complete_days_keep'] = 180;
$bb_cfg['dl_cancel_days_keep']   = 30;

// Tor-Stats
$bb_cfg['torstat_days_keep']     = 60;          // days to keep user's per-torrent stats

// Tor-Help
$bb_cfg['torhelp_enabled']       = true;        // find dead torrents (without seeder) that user might help seeding

$page_cfg['show_torhelp'] = array(
#	BB_SCRIPT => true
	'index'   => true,
	'tracker' => true,
);

// Path (trailing slash '/' at the end: XX_PATH - without, XX_DIR - with)
define('DIR_SEPR', DIRECTORY_SEPARATOR);

define('BB_PATH',       realpath(BB_ROOT)     );  // absolute pathname to the forum root
define('ADMIN_DIR',     BB_PATH .'/admin/'    );
define('CACHE_DIR',     BB_PATH .'/cache/'    );
define('DEV_DIR',       BB_PATH .'/develop/'  );
define('INC_DIR',       BB_PATH .'/includes/' );
define('LANG_ROOT_DIR', BB_PATH .'/language/' );
define('LOG_DIR',       BB_PATH .'/log/'      );
define('TEMPLATES_DIR', BB_PATH .'/templates/');
define('TRIGGERS_DIR',  BB_PATH .'/triggers/' );

// Language
setlocale(LC_ALL, 'ru_RU.UTF-8');
setlocale(LC_NUMERIC, 'C');
$bb_cfg['auto_language'] = true;
if (!empty($_SERVER['HTTP_ACCEPT_LANGUAGE']) && $bb_cfg['auto_language'])
{
	if (substr($_SERVER['HTTP_ACCEPT_LANGUAGE'], 0, 2) == 'ru')
	{
		$bb_cfg['default_lang_dir'] = LANG_ROOT_DIR .'lang_russian/';
		$bb_cfg['default_lang'] = 'russian';
	}
	else
	{
		$bb_cfg['default_lang_dir'] = LANG_ROOT_DIR .'lang_english/';
		$bb_cfg['default_lang'] = 'english';
	}
}
else
{
	if (isset($bb_cfg['default_lang']) && $bb_cfg['default_lang'] == 'russian') $bb_cfg['default_lang_dir'] = LANG_ROOT_DIR .'lang_russian/';
	else $bb_cfg['default_lang_dir'] = LANG_ROOT_DIR .'lang_english/';
}

// Templates
define('ADMIN_TPL_DIR', TEMPLATES_DIR .'/admin/');

$bb_cfg['templates'] = array(
//	'folder'	=> 'Name',
	'default'	=> 'Стандартный'
);

$bb_cfg['tpl_name']   = 'default';
$bb_cfg['stylesheet'] = 'main.css';

$bb_cfg['show_sidebar1_on_every_page'] = false;
$bb_cfg['show_sidebar2_on_every_page'] = false;

$bb_cfg['sidebar1_static_content_path'] = BB_PATH .'/misc/html/sidebar1.html';
$bb_cfg['sidebar2_static_content_path'] = BB_PATH .'/misc/html/sidebar2.html';

$page_cfg['show_sidebar1'] = array(
#	BB_SCRIPT => true
	'index'  => true,
);
$page_cfg['show_sidebar2'] = array(
#	BB_SCRIPT => true
	'index' => false,
);

$bb_cfg['topic_tpl']['overall_header'] = TEMPLATES_DIR .'topic_tpl_overall_header.html';
$bb_cfg['topic_tpl']['rules_video']    = TEMPLATES_DIR .'topic_tpl_rules_video.html';

// Cookie
$bb_cfg['cookie_domain'] = '.'. $domain_name;      # '.yourdomain.com'
$bb_cfg['cookie_path']   = '/';                    # '/forum/'
$bb_cfg['cookie_secure'] = (!empty($_SERVER['HTTPS']) ? 1 : 0); # 0
$bb_cfg['cookie_prefix'] = 'bb_';                  # 'bb_'

define('COOKIE_DBG', 'bb_dbg');                    // debug cookie name

// Sessions
$bb_cfg['session_update_intrv']    = 180;          // sec
$bb_cfg['user_session_duration']   = 1800;         // sec
$bb_cfg['admin_session_duration']  = 6*3600;       // sec
$bb_cfg['user_session_gc_ttl']     = 1800;         // number of seconds that a staled session entry may remain in sessions table
$bb_cfg['session_cache_gc_ttl']    = 1200;         // sec
$bb_cfg['max_reg_users_online']    = 0;            // 0 - unlimited
$bb_cfg['max_last_visit_days']     = 14;           // days
$bb_cfg['last_visit_update_intrv'] = 3600;         // sec

// Registration
$bb_cfg['invalid_logins']          = 5;            // Кол-во неверных попыток ввода пароля, перед выводом проверки капчи
$bb_cfg['new_user_reg_disabled']   = false;        // Disable new user registrations
$bb_cfg['unique_ip']               = false;        // Deny registration of several accounts by one ip
$bb_cfg['new_user_reg_restricted'] = false;

// Email
$bb_cfg['emailer_disabled']        = false;
$bb_cfg['topic_notify_enabled']    = true;
$bb_cfg['pm_notify_enabled']       = true;
$bb_cfg['groupcp_send_email']      = true;

$bb_cfg['tech_admin_email']        = 'admin@' . $domain_name;  // email for sending error reports
$bb_cfg['abuse_email']             = 'abuse@' . $domain_name;
$bb_cfg['adv_email']               = 'adv@'   . $domain_name;

// AJAX
define('AJAX_HTML_DIR', BB_ROOT .'ajax/html/');
define('AJAX_DIR',      BB_ROOT .'ajax/');

// Debug
define('DEBUG',           false);                  // !!! "DEBUG" should be ALWAYS DISABLED on production environment !!!
define('DBG_LOG',         false);
define('DBG_TIME',        true);                   // false, true или рабочая секудна (при 3 - запись в лог будет только если текущее время кратно 3)
define('DBG_LOG_GENTIME', true);
define('DBG_LOG_ERRORS',  true);
define('PROFILER',        false);                  // Profiler extension name, or FALSE to disable (supported: 'dbg')

define('SQL_DEBUG',            true);
define('SQL_LOG_ERRORS',       true);              // all SQL_xxx options enabled only if SQL_DEBUG == TRUE
define('SQL_CALC_QUERY_TIME',  true);              // for stats
define('SQL_LOG_SLOW_QUERIES', true);
define('SQL_SLOW_QUERY_TIME',  10);                // sec
define('SQL_PREPEND_SRC_COMM', false);             // prepend source file(line) comment to sql query

// Special users
$bb_cfg['dbg_users'] = array(
#	user_id => 'name',
	2 => 'admin',
);

$bb_cfg['unlimited_users'] = array(
#	user_id => 'name',
	2 => 'admin',
);

$bb_cfg['super_admins'] = array(
#	user_id => 'name',
	2 => 'admin',
);

// Log options
define('LOG_EXT',      'log');
define('LOG_SEPR',     ' | ');
define('LOG_LF',       "\n");
define('LOG_MAX_SIZE', 1048576); // bytes

// Log request
$log_ip_req = array(
#	'127.0.0.1' => 'user1',  // CLIENT_IP => 'name'
#	'7f000001'  => 'user2',  // USER_IP   => 'name'
);

$log_passkey = array(
#	'passkey' => 'log_filename',
);

// Log response
$log_ip_resp = array(
#	'127.0.0.1' => 'user1',  // CLIENT_IP => 'name'
#	'7f000001'  => 'user2',  // USER_IP   => 'name'
);

// Error reporting
if (DEBUG)
{
	error_reporting(E_ALL);
	ini_set('display_errors', 1);
	ini_set('log_errors',     0);
}
else
{
	error_reporting(E_ALL);                          # E_ALL & ~E_NOTICE
	ini_set('display_errors', 0);
	ini_set('log_errors',     1);
}
ini_set('error_log', LOG_DIR .'php_err.log');

// magic quotes
if (get_magic_quotes_gpc()) die('set magic_quotes off');
// json
if (!function_exists('json_encode')) die('not json_encode');

// Triggers
define('BB_ENABLED',   TRIGGERS_DIR .'$on');
define('BB_DISABLED',  TRIGGERS_DIR .'$off');
define('CRON_ALLOWED', TRIGGERS_DIR .'cron_allowed');
define('CRON_RUNNING', TRIGGERS_DIR .'cron_running');

// Subforums
$bb_cfg['sf_on_first_page_only']     = true;

// Forums
$bb_cfg['allowed_topics_per_page'] = array(50, 100, 150, 200, 250, 300);

// Topics
$bb_cfg['show_quick_reply']   = true;
$bb_cfg['show_rank_text']     = false;
$bb_cfg['show_rank_image']    = true;
$bb_cfg['show_poster_joined'] = true;
$bb_cfg['show_poster_posts']  = true;
$bb_cfg['show_poster_from']   = true;
$bb_cfg['show_bot_nick']      = false;
$bb_cfg['text_buttons']       = false;              // replace EDIT, QUOTE... images with text links
$bb_cfg['parse_ed2k_links']   = true;              // make ed2k links clickable
$bb_cfg['post_date_format']   = 'd-M-Y H:i';
$bb_cfg['ext_link_new_win']   = true;              // open external links in new window

$bb_cfg['topic_moved_days_keep'] = 7;              // remove topic moved links after xx days (or FALSE to disable)

$bb_cfg['allowed_posts_per_page'] = array(15, 30, 50, 100);
$bb_cfg['user_signature_start'] = '<div class="signature"><br />_________________<br />';
$bb_cfg['user_signature_end']	= '</div>';	       //Это позволит использовать html теги, которые требуют закрытия. Например <table> или <font color>

// Posts
$bb_cfg['use_posts_cache']       = true;           // if you switch from ON to OFF, you need to TRUNCATE `bb_posts_html` table
$bb_cfg['posts_cache_days_keep'] = 14;
$bb_cfg['max_post_length']       = 120000;         // bytes
$bb_cfg['use_ajax_posts']        = true;

// Search
$bb_cfg['search_engine_type']          = 'mysql';  //  none, mysql, sphinx
$bb_cfg['sphinx_topic_titles_host']    = '127.0.0.1';
$bb_cfg['sphinx_topic_titles_port']    = 3312;
$bb_cfg['sphinx_config_path']          = realpath("../install/sphinx/sphinx.conf");
$bb_cfg['disable_ft_search_in_posts']  = false;    // disable searching in post bodies
$bb_cfg['disable_search_for_guest']    = true;
$bb_cfg['allow_search_in_bool_mode']   = true;
$bb_cfg['max_search_words_per_post']   = 200;
$bb_cfg['search_min_word_len']         = 3;
$bb_cfg['search_max_word_len']         = 35;
$bb_cfg['limit_max_search_results']    = false;
$bb_cfg['tidy_post']                   = true;
$bb_cfg['spam_filter_file_path']       = ''; //BB_PATH .'/misc/spam_filter_words.txt';

// Posting
$bb_cfg['prevent_multiposting']  = true;           // replace "reply" with "edit last msg" if user (not admin or mod) is last topic poster
$bb_cfg['max_smilies']           = 10;             // Максимальное число смайлов в посте (0 - без ограничения)

// Actions log
$bb_cfg['log_days_keep'] = 90;

// Users
$bb_cfg['color_nick']                   = true;    // Окраска ников пользователей по user_rank
$bb_cfg['user_not_activated_days_keep'] = 7;       // "not activated" == "not finished registration"
$bb_cfg['user_not_active_days_keep']    = 180;     // inactive users but only with no posts

// GroupCP
$bb_cfg['groupcp_members_per_page']     = 300;

// Ads
$bb_cfg['show_ads'] = false;
$bb_cfg['show_ads_users'] = array(
#	user_id => 'name',
	2      => 'admin',
);

// block_type => [block_id => block_desc]
$bb_cfg['ad_blocks'] = array(
	'trans' => array(
		100 => 'сквозная сверху',
	),
	'index' => array(
		200 => 'главная, под новостями',
	),
);

// Misc
define('LOADAVG',   function_exists('get_loadavg') ? get_loadavg() : 0);
define('MEM_USAGE', function_exists('memory_get_usage'));

$bb_cfg['mem_on_start'] = (MEM_USAGE) ? memory_get_usage() : 0;

$bb_cfg['translate_dates'] = true;                 // in displaying time
$bb_cfg['use_word_censor'] = true;

$bb_cfg['last_visit_date_format'] = 'd-M H:i';
$bb_cfg['last_post_date_format']  = 'd-M-y H:i';

$bb_cfg['allow_change'] = array(
	'language'   => true,
	'dateformat' => true,
);

define('GZIP_OUTPUT_ALLOWED', (extension_loaded('zlib') && !ini_get('zlib.output_compression')));

$banned_user_agents = array(
// Download Master
#	'download',
#	'master',
// Others
#	'wget',
);

$bb_cfg['porno_forums_screenshots_topic_id'] = 52267;
$bb_cfg['trash_forum_id'] = 0;                     // (int)    27

$bb_cfg['first_logon_redirect_url']    = 'index.php';
$bb_cfg['faq_url']                     = 'faq.php';
$bb_cfg['terms_and_conditions_url']    = 'index.php';

$bb_cfg['user_agreement_url']          = 'misc.php?do=info&show=user_agreement';
$bb_cfg['copyright_holders_url']       = 'misc.php?do=info&show=copyright_holders';
$bb_cfg['advert_url']                  = 'misc.php?do=info&show=advert';

$bb_cfg['html_path']                   = BB_PATH .'/misc/html/';  #
$bb_cfg['user_agreement_html_path']    = $bb_cfg['html_path'] .'user_agreement.html';  #
$bb_cfg['copyright_holders_html_path'] = $bb_cfg['html_path'] .'copyright_holders.html';  #
$bb_cfg['advert_html_path']            = $bb_cfg['html_path'] .'advert.html';  #

// Captcha
$bb_cfg['captcha'] = array(
	'disabled' => false,
	'secret_key' => 'secret_key',
	'img_url'    => './images/captcha/',           # without '/'
	'img_path'   => BB_PATH .'/images/captcha/',   # without '/'
);

// SEO
$bb_cfg['seo_link_home_page'] = false;

// Status of draft
$bb_cfg['status_of_draft'] = false;