<?php

/**
 * Script versions
 * Domain name
 * Version info
 * Database
   - Charset
   - Config
   - Aliases
 * Cache
   - Config
   - Datastore
 * Server
   - Cloudflare
   - GZip
 * Tracker
 * Ocelot
 * FAQ url
 * Torrents
   - Ratio limits
   - Seeding torrents limit
   - DL-Status (days to keep)
   - Tor-Stats (days to keep)
   - Tor-Help
 * Path
 * URL's
 * Language
 * Templates
 * Cookie
 * Sessions
 * Registration
 * Email
 * Debug
 * Special users (dbg_users, unlimited_users, super_admins)
 * LOG
 * Error reporting
 * Triggers
 * Date format

 * Subforums
 * Forums
 * Topics
 * Posts
 * Search
 * Posting
 * PM
 * Actions log
 * Users
 * Groups

 * Tidy
 * Ads
 * Attachments
 * Avatars
 * Group avatars
 * Misc
 * Captcha
 * Atom feed
 * Nofollow
 * Local config
**/

if (!defined('BB_ROOT')) die(basename(__FILE__));

$bb_cfg = $tr_cfg = $page_cfg = array();

// Increase number after changing js or css
$bb_cfg['js_ver'] = $bb_cfg['css_ver'] = 1;

// Primary domain name
$domain_name = 'torrentpier.me'; // enter here your primary domain name of your site
$domain_name = (!empty($_SERVER['SERVER_NAME'])) ? $_SERVER['SERVER_NAME'] : $domain_name;

// Version info
$bb_cfg['tp_version'] = '2.1.3';
$bb_cfg['tp_release_date'] = '24-10-2014';
$bb_cfg['tp_release_state'] = 'ALPHA';

// Database
$charset  = 'utf8';
$pconnect = false;

// Настройка баз данных ['db']['srv_name'] => (array) srv_cfg;
// порядок параметров srv_cfg (хост, название базы, пользователь, пароль, charset, pconnect);
$bb_cfg['db'] = array(
	'db1' => array('localhost', 'dbase', 'user', 'pass', $charset, $pconnect),
	//'db2' => array('localhost2', 'dbase2', 'user2', 'pass2', $charset, $pconnect),
	//'db3' => array('localhost3', 'dbase3', 'user2', 'pass3', $charset, $pconnect),
);

$bb_cfg['db_alias'] = array(
//	'alias'  => 'srv_name'
#	db1
	'log'    => 'db1', // BB_LOG
	'search' => 'db1', // BB_TOPIC_SEARCH
	'sres'   => 'db1', // BB_BT_USER_SETTINGS, BB_SEARCH_RESULTS
	'u_ses'  => 'db1', // BB_USER_SES, BB_USER_LASTVISIT
#	db2
	'dls'    => 'db1', // BB_BT_DLS_*
	'ip'     => 'db1', // BB_POSTS_IP
	'ut'     => 'db1', // BB_TOPICS_USER_POSTED
#	db3
	'cap'    => 'db1', // BB_CAPTCHA
	'pm'     => 'db1', // BB_PRIVMSGS, BB_PRIVMSGS_TEXT
	'pt'     => 'db1', // BB_POSTS_TEXT
);

// Cache
$bb_cfg['cache']['pconnect'] = true;
$bb_cfg['cache']['db_dir']   = realpath(BB_ROOT) .'/internal_data/cache/filecache/';
$bb_cfg['cache']['prefix']   = 'tp_';  // Префикс кеша ('tp_')
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

// Available cache types: memcache, sqlite, redis, apc, xcache (default of filecache)
# name => array( (string) type, (array) cfg )
$bb_cfg['cache']['engines'] = array(
	'bb_cache'      => array('filecache', array()),
	'bb_config'     => array('filecache', array()),
	'tr_cache'      => array('filecache', array()),
	'session_cache' => array('filecache', array()),
	'bb_cap_sid'    => array('filecache', array()),
	'bb_login_err'  => array('filecache', array()),
	'bb_poll_data'  => array('filecache', array()),
);
// Datastore
// Available datastore types: memcache, sqlite, redis, apc, xcache  (default filecache)
$bb_cfg['datastore_type'] = 'filecache';

// Server
$bb_cfg['server_name'] = $domain_name;                                                     // The domain name from which this board runs
$bb_cfg['server_port'] = (!empty($_SERVER['SERVER_PORT'])) ? $_SERVER['SERVER_PORT'] : 80; // The port your server is running on
$bb_cfg['script_path'] = '/';                                                              // The path where FORUM is located relative to the domain name

// Cloudflare
if (isset($_SERVER['HTTP_CF_CONNECTING_IP']))
{
	$_SERVER['REMOTE_ADDR'] = $_SERVER['HTTP_CF_CONNECTING_IP'];
}

// GZip
$bb_cfg['gzip_compress']      = true;              // compress output

// Tracker
$bb_cfg['announce_interval']  = 2400;              // Announce interval (default: 1800)
$bb_cfg['passkey_key']        = 'uk';              // Passkey key name in GET request
$bb_cfg['ignore_reported_ip'] = false;             // Ignore IP reported by client
$bb_cfg['verify_reported_ip'] = true;              // Verify IP reported by client against $_SERVER['HTTP_X_FORWARDED_FOR']
$bb_cfg['allow_internal_ip']  = false;             // Allow internal IP (10.xx.. etc.)

// Ocelot
$bb_cfg['ocelot'] = array(
	'enabled' => false,
	'host'    => $domain_name,
	'port'    => 34000,
	'url'     => "http://$domain_name:34000/", // with '/'
	'secret'  => 'some_10_chars',              // 10 chars
	'stats'   => 'some_10_chars',              // 10 chars
);

// FAQ url help link
$bb_cfg['how_to_download_url_help']  = 'viewtopic.php?t=1'; // Как скачивать?
$bb_cfg['what_is_torrent_url_help']  = 'viewtopic.php?t=2'; // Что такое торрент?
$bb_cfg['ratio_url_help']            = 'viewtopic.php?t=3'; // Рейтинг и ограничения
$bb_cfg['search_help_url']           = 'viewtopic.php?t=4'; // Помощь по поиску

// Torrents
$bb_cfg['bt_min_ratio_allow_dl_tor'] = 0.3;        // 0 - disable
$bb_cfg['bt_min_ratio_warning']      = 0.6;        // 0 - disable

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
	'retracker'             => true,
	'retracker_host'        => 'http://retracker.local/announce',
);

$bb_cfg['show_dl_status_in_search'] = true;
$bb_cfg['show_dl_status_in_forum']  = true;

$bb_cfg['show_tor_info_in_dl_list'] = true;
$bb_cfg['allow_dl_list_names_mode'] = true;

$bb_cfg['torrent_name_style'] = true; // use torrent name style [yoursite.com].txxx.torrent
$bb_cfg['tor_help_links']     = 'terms.php';

// Сколько дней сохранять торрент зарегистрированным / Days to keep torrent registered, if:
$bb_cfg['seeder_last_seen_days_keep']  = 0; // сколько дней назад был сид последний раз
$bb_cfg['seeder_never_seen_days_keep'] = 0; // сколько дней имеется статус "Сида не было никогда"

// Ratio limits
define('TR_RATING_LIMITS', true);        // ON/OFF
define('MIN_DL_FOR_RATIO', 10737418240); // 10 GB in bytes, 0 - disable

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

// DL-Status (days to keep user's dlstatus records)
$bb_cfg['dl_will_days_keep']     = 360;
$bb_cfg['dl_down_days_keep']     = 180;
$bb_cfg['dl_complete_days_keep'] = 180;
$bb_cfg['dl_cancel_days_keep']   = 30;

// Tor-Stats
$bb_cfg['torstat_days_keep']     = 60;    // days to keep user's per-torrent stats

// Tor-Help
$bb_cfg['torhelp_enabled']       = false; // find dead torrents (without seeder) that user might help seeding

$page_cfg['show_torhelp'] = array(
#	BB_SCRIPT => true
	'index'   => true,
	'tracker' => true,
);

// Path (trailing slash '/' at the end: XX_PATH - without, XX_DIR - with)
define('BB_PATH',       realpath(BB_ROOT)                    );
define('ADMIN_DIR',     BB_PATH .'/admin/'                   );
define('DATA_DIR',      BB_PATH .'/data/'                    );
define('INT_DATA_DIR',  BB_PATH .'/internal_data/'           );
define('AJAX_HTML_DIR', BB_ROOT .'/internal_data/ajax_html/' );
define('CACHE_DIR',     BB_PATH .'/internal_data/cache/'     );
define('LOG_DIR',       BB_PATH .'/internal_data/log/'       );
define('SITEMAP_DIR',   BB_PATH .'/internal_data/sitemap/'   );
define('TRIGGERS_DIR',  BB_PATH .'/internal_data/triggers/'  );
define('AJAX_DIR',      BB_ROOT .'/library/ajax/'            );
define('ATTACH_DIR',    BB_PATH .'/library/attach_mod/'      );
define('CFG_DIR',       BB_PATH .'/library/config/'          );
define('INC_DIR',       BB_PATH .'/library/includes/'        );
define('CLASS_DIR',     BB_PATH .'/library/includes/classes/');
define('UCP_DIR',       BB_PATH .'/library/includes/ucp/'    );
define('LANG_ROOT_DIR', BB_PATH .'/library/language/'        );
define('IMAGES_DIR',    BB_PATH .'/styles/images/'           );
define('TEMPLATES_DIR', BB_PATH .'/styles/templates/'        );

// URL's
$bb_cfg['ajax_url']    = 'ajax.php';     #  "http://{$_SERVER['SERVER_NAME']}/ajax.php"
$bb_cfg['login_url']   = 'login.php';    #  "http://{$domain_name}/login.php"
$bb_cfg['posting_url'] = 'posting.php';  #  "http://{$domain_name}/posting.php"
$bb_cfg['pm_url']      = 'privmsg.php';  #  "http://{$domain_name}/privmsg.php"

// Language
$bb_cfg['charset']       = 'utf8'; // page charset
$bb_cfg['auto_language'] = true;   // select user-preferred language automatically

if (!empty($_SERVER['HTTP_ACCEPT_LANGUAGE']) && $bb_cfg['auto_language'])
{
	$user_lang = substr($_SERVER['HTTP_ACCEPT_LANGUAGE'], 0, 2);
	if (file_exists(LANG_ROOT_DIR . $user_lang .'/'))
	{
		$bb_cfg['default_lang_dir'] = LANG_ROOT_DIR . $user_lang .'/';
		$bb_cfg['default_lang'] = $user_lang;
	}
	else
	{
		$bb_cfg['default_lang_dir'] = LANG_ROOT_DIR .'en/';
		$bb_cfg['default_lang'] = 'en';
	}
}
else
{
	if (isset($bb_cfg['default_lang']) && file_exists(LANG_ROOT_DIR . $bb_cfg['default_lang'] .'/'))
	{
		$bb_cfg['default_lang_dir'] = LANG_ROOT_DIR . $bb_cfg['default_lang'] .'/';
	}
	else
	{
		$bb_cfg['default_lang_dir'] = LANG_ROOT_DIR .'en/';
	}
}

$bb_cfg['languages'] = array(
//	'folder' => 'Name',
	'ru'     => 'Русский',
	'uk'     => 'Український',
	'en'     => 'English',
);

// Templates
define('ADMIN_TPL_DIR', TEMPLATES_DIR .'/admin/');

$bb_cfg['templates'] = array(
//	'folder'  => 'Name',
	'default' => 'Стандартный',
);

$bb_cfg['tpl_name']   = 'default';
$bb_cfg['stylesheet'] = 'main.css';

$bb_cfg['show_sidebar1_on_every_page'] = false;
$bb_cfg['show_sidebar2_on_every_page'] = false;

$page_cfg['show_sidebar1'] = array(
#	BB_SCRIPT => true
	'index'   => true,
);
$page_cfg['show_sidebar2'] = array(
#	BB_SCRIPT => true
	'index'   => true,
);

// Cookie
$bb_cfg['cookie_domain'] = in_array($domain_name, array(getenv('SERVER_ADDR'), 'localhost')) ? '' : ".$domain_name";
$bb_cfg['cookie_secure'] = (!empty($_SERVER['HTTPS']) ? 1 : 0);
$bb_cfg['cookie_prefix'] = 'bb_'; // 'bb_'

// Sessions
$bb_cfg['session_update_intrv']    = 180;          // sec
$bb_cfg['user_session_duration']   = 1800;         // sec
$bb_cfg['admin_session_duration']  = 6*3600;       // sec
$bb_cfg['user_session_gc_ttl']     = 1800;         // number of seconds that a staled session entry may remain in sessions table
$bb_cfg['session_cache_gc_ttl']    = 1200;         // sec
$bb_cfg['max_last_visit_days']     = 14;           // days
$bb_cfg['last_visit_update_intrv'] = 3600;         // sec

// Registration
$bb_cfg['invalid_logins']          = 5;            // Количество неверных попыток ввода пароля, перед выводом проверки капчей
$bb_cfg['new_user_reg_disabled']   = false;        // Запретить регистрацию новых учетных записей
$bb_cfg['unique_ip']               = false;        // Запретить регистрацию нескольких учетных записей с одного ip
$bb_cfg['new_user_reg_restricted'] = false;        // Ограничить регистрацию новых пользователей по времени с 01:00 до 17:00
$bb_cfg['reg_email_activation']    = false;        // Требовать активацию учетной записи по email

// Email
$bb_cfg['emailer_disabled']        = false;

$bb_cfg['smtp_delivery']           = false; // send email via a named server instead of the local mail function
$bb_cfg['smtp_ssl']                = false; // use ssl connect
$bb_cfg['smtp_host']               = '';    // SMTP server host
$bb_cfg['smtp_port']               = 25;    // SMTP server port
$bb_cfg['smtp_username']           = '';    // enter a username if your SMTP server requires it
$bb_cfg['smtp_password']           = '';    // enter a password if your SMTP server requires it

$bb_cfg['board_email']             = "noreply@$domain_name"; // admin email address
$bb_cfg['board_email_form']        = false;        // can users send email to each other via board
$bb_cfg['board_email_sig']         = '';           // this text will be attached to all emails the board sends
$bb_cfg['board_email_sitename']    = $domain_name; // sitename used in all emails header

$bb_cfg['topic_notify_enabled']    = true;
$bb_cfg['pm_notify_enabled']       = true;
$bb_cfg['group_send_email']        = true;
$bb_cfg['email_change_disabled']   = false;        // disable changing email by user

$bb_cfg['tech_admin_email']        = "admin@$domain_name"; // email for sending error reports
$bb_cfg['abuse_email']             = "abuse@$domain_name";
$bb_cfg['adv_email']               = "adv@$domain_name";

// Debug
define('DBG_LOG',              false);    // enable forum debug (off on production)
define('DBG_TRACKER',          false);    // enable tracker debug (off on production)
define('COOKIE_DBG',           'bb_dbg'); // debug cookie name
define('SQL_DEBUG',            true);     // enable forum sql & cache debug
define('SQL_LOG_ERRORS',       true);     // all SQL_xxx options enabled only if SQL_DEBUG == TRUE
define('SQL_CALC_QUERY_TIME',  true);     // for stats
define('SQL_LOG_SLOW_QUERIES', true);     // log sql slow queries
define('SQL_SLOW_QUERY_TIME',  10);       // slow query in seconds
define('SQL_PREPEND_SRC_COMM', false);    // prepend source file comment to sql query

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
ini_set('error_reporting', E_ALL);
ini_set('display_errors',  0);
ini_set('log_errors',      1);
ini_set('error_log',       LOG_DIR .'php_err.log');

// Check some variable
// Magic quotes
if (get_magic_quotes_gpc()) die('Set magic_quotes off');
// JSON
if (!function_exists('json_encode')) die('Json_encode not installed');

// Triggers
define('BB_ENABLED',   TRIGGERS_DIR .'$on');
define('BB_DISABLED',  TRIGGERS_DIR .'$off');
define('CRON_ALLOWED', TRIGGERS_DIR .'cron_allowed');
define('CRON_RUNNING', TRIGGERS_DIR .'cron_running');

// Date format
$bb_cfg['date_format']             = 'Y-m-d';

// Subforums
$bb_cfg['sf_on_first_page_only']   = true;

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
$bb_cfg['text_buttons']       = false;             // replace EDIT, QUOTE... images with text links
$bb_cfg['parse_ed2k_links']   = true;              // make ed2k links clickable
$bb_cfg['post_date_format']   = 'd-M-Y H:i';
$bb_cfg['ext_link_new_win']   = true;              // open external links in new window

$bb_cfg['topic_moved_days_keep']  = 7;              // remove topic moved links after xx days (or FALSE to disable)

$bb_cfg['allowed_posts_per_page'] = array(15, 30, 50, 100);
$bb_cfg['user_signature_start']   = '<div class="signature"><br />_________________<br />';
$bb_cfg['user_signature_end']     = '</div>';        // Это позволит использовать html теги, которые требуют закрытия. Например <table> или <font color>

// Posts
$bb_cfg['use_posts_cache']       = true;           // if you switch from ON to OFF, you need to TRUNCATE `bb_posts_html` table
$bb_cfg['posts_cache_days_keep'] = 14;
$bb_cfg['max_post_length']       = 120000;         // bytes
$bb_cfg['use_ajax_posts']        = true;

// Search
$bb_cfg['search_engine_type']         = 'mysql';   // none, mysql, sphinx
$bb_cfg['sphinx_topic_titles_host']   = '127.0.0.1';
$bb_cfg['sphinx_topic_titles_port']   = 3312;
$bb_cfg['sphinx_config_path']         = realpath("../install/sphinx/sphinx.conf");
$bb_cfg['disable_ft_search_in_posts'] = false;     // disable searching in post bodies
$bb_cfg['disable_search_for_guest']   = true;
$bb_cfg['allow_search_in_bool_mode']  = true;
$bb_cfg['max_search_words_per_post']  = 200;
$bb_cfg['search_min_word_len']        = 3;
$bb_cfg['search_max_word_len']        = 35;
$bb_cfg['limit_max_search_results']   = false;
$bb_cfg['spam_filter_file_path']      = '';        // BB_PATH .'/misc/spam_filter_words.txt';
$bb_cfg['autocorrect_wkl']            = true;      // autocorrect wrong keyboard layout

// Posting
$bb_cfg['prevent_multiposting']  = true;           // replace "reply" with "edit last msg" if user (not admin or mod) is last topic poster
$bb_cfg['max_smilies']           = 10;             // Максимальное число смайлов в посте (0 - без ограничения)

// PM
$bb_cfg['privmsg_disable']      = false;           // отключить систему личных сообщений на форуме
$bb_cfg['max_outgoing_pm_cnt']  = 10;              // ограничение на кол. одновременных исходящих лс (для замедления рассылки спама)
$bb_cfg['max_inbox_privmsgs']   = 200;             // максимальное число сообщений в папке входящие
$bb_cfg['max_savebox_privmsgs'] = 25;              // максимальное число сообщений в папке сохраненные
$bb_cfg['max_sentbox_privmsgs'] = 50;              // максимальное число сообщений в папке отправленные
$bb_cfg['pm_days_keep']         = 180;             // время хранения ЛС

// Actions log
$bb_cfg['log_days_keep'] = 90;

// Users
$bb_cfg['color_nick']                   = true;    // Окраска ников пользователей по user_rank
$bb_cfg['user_not_activated_days_keep'] = 7;       // "not activated" == "not finished registration"
$bb_cfg['user_not_active_days_keep']    = 180;     // inactive users but only with no posts

// Groups
$bb_cfg['group_members_per_page']       = 50;

// Tidy
$bb_cfg['tidy_post'] = (!in_array('tidy', get_loaded_extensions())) ? false : true;

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

// Attachments
$bb_cfg['attach'] = array(
	'upload_path'           => DATA_DIR . 'torrent_files', // without '/'
	'max_size'              => 250*1024,                   // bytes
);

$bb_cfg['file_id_ext'] = array(
	1  => 'gif',
	2  => 'gz',
	3  => 'jpg',
	4  => 'png',
	5  => 'rar',
	6  => 'tar',
	7  => 'tiff',
	8  => 'torrent',
	9  => 'zip',
);

$bb_cfg['tor_forums_allowed_ext'] = array('torrent', 'zip', 'rar'); // для разделов с раздачами
$bb_cfg['gen_forums_allowed_ext'] = array('zip', 'rar');            // для обычных разделов

// Avatars
$bb_cfg['avatars'] = array(
	'allowed_ext' => array('gif','jpg','jpeg','png'), // разрешенные форматы файлов
	'bot_avatar'  => 'gallery/bot.gif',               // аватара бота
	'max_size'    => 100*1024,                        // размер аватары в байтах
	'max_height'  => 100,                             // высота аватара в px
	'max_width'   => 100,                             // ширина аватара в px
	'no_avatar'   => 'gallery/noavatar.png',          // дефолтная аватара
	'upload_path' => BB_ROOT . 'data/avatars/',       // путь к директории с аватарами
	'up_allowed'  => true,                            // разрешить загрузку аватар
);

// Group avatars
$bb_cfg['group_avatars'] = array(
	'allowed_ext' => array('gif','jpg','jpeg','png'), // разрешенные форматы файлов
	'max_size'    => 300*1024,                        // размер аватары в байтах
	'max_height'  => 300,                             // высота аватара в px
	'max_width'   => 300,                             // ширина аватара в px
	'no_avatar'   => 'gallery/noavatar.png',          // дефолтная аватара
	'upload_path' => BB_ROOT . 'data/avatars/',       // путь к директории с аватарами
	'up_allowed'  => true,                            // разрешить загрузку аватар
);

// Misc
define('MEM_USAGE', function_exists('memory_get_usage'));

$bb_cfg['mem_on_start'] = (MEM_USAGE) ? memory_get_usage() : 0;

$bb_cfg['translate_dates'] = true; // in displaying time
$bb_cfg['use_word_censor'] = true;

$bb_cfg['last_visit_date_format'] = 'd-M H:i';
$bb_cfg['last_post_date_format']  = 'd-M-y H:i';
$bb_cfg['poll_max_days']          = 180; // сколько дней с момента создания темы опрос будет активным

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

$bb_cfg['trash_forum_id'] = 0; // (int) 7

$bb_cfg['first_logon_redirect_url'] = 'index.php';
$bb_cfg['terms_and_conditions_url'] = 'terms.php';

$bb_cfg['user_agreement_url']       = 'info.php?show=user_agreement';
$bb_cfg['copyright_holders_url']    = 'info.php?show=copyright_holders';
$bb_cfg['advert_url']               = 'info.php?show=advert';

// Captcha
$bb_cfg['captcha'] = array(
	'disabled'   => false,
	'secret_key' => 'secret_key',
	'img_path'   => INT_DATA_DIR .'captcha/',   # with '/'
	'img_url'    => './internal_data/captcha/', # with '/'
);

// Atom feed
$bb_cfg['atom'] = array(
	'path' => INT_DATA_DIR .'atom',   # without '/'
	'url'  => './internal_data/atom', # without '/'
);

// Nofollow
$bb_cfg['nofollow'] = array(
	'disabled'    => false,
	'allowed_url' => array($domain_name), // 'allowed.site', 'www.allowed.site'
);

// Local config
if (file_exists(BB_ROOT. '/library/config.local.php'))
{
	include_once(BB_ROOT. '/library/config.local.php');
}

define('BB_CFG_LOADED', true);