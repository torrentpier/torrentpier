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

if (!defined('BB_ROOT')) {
    die(basename(__FILE__));
}

$domain_name = 'torrentpier.me'; // enter here your primary domain name of your site
$domain_name = (!empty($_SERVER['SERVER_NAME'])) ? $_SERVER['SERVER_NAME'] : $domain_name;

$config = [

    // Debug (twig etc.)
    'debug' => true,

    // Increase number after changing js or css
    'js_ver' => 1,
    'css_ver' => 1,

    // Version info
    'tp_version' => '2.2.0',
    'tp_release_date' => '**/**/2016',
    'tp_release_state' => 'ALPHA',

    'services' => [
        'cache' => [
            'adapter' => \TorrentPier\Cache\FileAdapter::class,
            'options' => [
                'directory' => __DIR__ . '/../internal_data/cache',
                'prefix' => 'hash string'
            ],
        ],
        /*'cache' => [
            'adapter' => \TorrentPier\Cache\MemoryAdapter::class,
            'options' => [
                'servers' => [
                    'host' => '127.0.0.1',
                    'port' => 11211,
                    'weight' => 100,
                ],
            ],
        ],*/

        // Database
        'db' => [
            'debug' => '{self.debug}',
            'driver' => 'Pdo_Mysql',
            'hostname' => '127.0.0.1',
            'database' => 'tp_220',
            'username' => 'user',
            'password' => 'pass',
            'charset' => 'utf8'
        ],

        // Sphinx
        'sphinx' => [
            'debug' => '{self.debug}',
            'driver' => '{self.db.driver}',
            'hostname' => '{self.db.hostname}',
            'username' => 'user',
            'password' => 'pass',
            'port' => 9306,
            'charset' => 'utf8'
        ],

        // Twig
        'twig' => [
            'dir_templates' => __DIR__ . '/../templates/default',
            'dir_cache' => __DIR__ . '/../internal_data/cache',
        ],

        // Translation
        'translator' => [
            'dir_cache' => __DIR__ . '/../internal_data/cache',
            'resources' => [
                [
                    'resource' => __DIR__ . '/../messages/ru.php',
                    'locale' => 'ru',
                ],
                [
                    'resource' => __DIR__ . '/../messages/en.php',
                    'locale' => 'en',
                ]
            ]
        ],

        // Log
        'log' => [
            'handlers' => [
                function () {
                    return new \Monolog\Handler\StreamHandler(
                        __DIR__ . '/../internal_data/log/app.log',
                        \Monolog\Logger::DEBUG
                    );
                }
            ]
        ],
    ],

    // Aliases
    // TODO: удалить
    'db_alias' => [
        'log' => 'db', // BB_LOG
        'search' => 'db', // BB_TOPIC_SEARCH
        'sres' => 'db', // BB_BT_USER_SETTINGS, BB_SEARCH_RESULTS
        'u_ses' => 'db', // BB_USER_SES, BB_USER_LASTVISIT
        'dls' => 'db', // BB_BT_DLS_*
        'ip' => 'db', // BB_POSTS_IP
        'ut' => 'db', // BB_TOPICS_USER_POSTED
        'pm' => 'db', // BB_PRIVMSGS, BB_PRIVMSGS_TEXT
        'pt' => 'db', // BB_POSTS_TEXT
    ],

    // Cache
    'cache' => [
        'db_dir' => realpath(BB_ROOT) . '/internal_data/cache/filecache/',
        'prefix' => 'tp_', // Префикс кеша ('tp_')
        'memcache' => [
            'host' => '127.0.0.1',
            'port' => 11211,
            'pconnect' => true,
            'con_required' => true,
        ],
        'redis' => [
            'host' => '127.0.0.1',
            'port' => 6379,
            'con_required' => true,
        ]
    ],

    // Datastore
    // Available datastore types: memcache, sqlite, redis, apc, xcache  (default filecache)
    // TODO: удалить
    'datastore_type' => 'filecache',

    // Server
    'server_name' => $domain_name,                                                     // The domain name from which this board runs
    'server_port' => (!empty($_SERVER['SERVER_PORT'])) ? $_SERVER['SERVER_PORT'] : 80, // The port your server is running on
    'script_path' => '/',                                                              // The path where FORUM is located relative to the domain name

    // GZip
    'gzip_compress' => true, // compress output

    // Tracker
    'announce_interval' => 2400,  // Announce interval (default: 1800)
    'passkey_key' => 'uk',  // Passkey key name in GET request
    'ignore_reported_ip' => false, // Ignore IP reported by client
    'verify_reported_ip' => true,  // Verify IP reported by client against $_SERVER['HTTP_X_FORWARDED_FOR']
    'allow_internal_ip' => false, // Allow internal IP (10.xx.. etc.)

    // Ocelot
    'ocelot' => [
        'enabled' => false,
        'host' => $domain_name,
        'port' => 2710,
        'url' => "http://$domain_name:2710/", // with '/'
        'secret' => 'some_10_chars',             // 10 chars
        'stats' => 'some_10_chars',             // 10 chars
    ],

    // FAQ url help link
    'how_to_download_url_help' => 'viewtopic.php?t=1', // Как скачивать?
    'what_is_torrent_url_help' => 'viewtopic.php?t=2', // Что такое торрент?
    'ratio_url_help' => 'viewtopic.php?t=3', // Рейтинг и ограничения
    'search_help_url' => 'viewtopic.php?t=4', // Помощь по поиску

    // Torrents
    'bt_min_ratio_allow_dl_tor' => 0.3, // 0 - disable
    'bt_min_ratio_warning' => 0.6, // 0 - disable

    'show_dl_status_in_search' => true,
    'show_dl_status_in_forum' => true,
    'allow_dl_list_names_mode' => true,

    // Days to keep torrent registered
    'seeder_last_seen_days_keep' => 0, // сколько дней назад был сид последний раз
    'seeder_never_seen_days_keep' => 0, // сколько дней имеется статус "Сида не было никогда"

    // DL-Status (days to keep user's dlstatus records)
    'dl_will_days_keep' => 360,
    'dl_down_days_keep' => 180,
    'dl_complete_days_keep' => 180,
    'dl_cancel_days_keep' => 30,

    // Tor-Stats
    'torstat_days_keep' => 60, // days to keep user's per-torrent stats

    // Tor-Help
    'torhelp_enabled' => false, // find dead torrents (without seeder) that user might help seeding

    // URL's
    'ajax_url' => 'ajax.php',    # "http://{$_SERVER['SERVER_NAME']}/ajax.php"
    'dl_url' => 'dl.php?t=',   # "http://{$domain_name}/dl.php"
    'login_url' => 'login.php',   # "http://{$domain_name}/login.php"
    'posting_url' => 'posting.php', # "http://{$domain_name}/posting.php"
    'pm_url' => 'privmsg.php', # "http://{$domain_name}/privmsg.php"

    // Language
    'charset' => 'utf8', // page charset
    'lang' => [
        'ru' => [
            'name' => 'Русский',
            'locale' => 'ru_RU.UTF-8',
            'encoding' => 'UTF-8',
        ],
        'uk' => [
            'name' => 'Український',
            'locale' => 'uk_UA.UTF-8',
            'encoding' => 'UTF-8',
        ],
        'en' => [
            'name' => 'English',
            'locale' => 'en_US.UTF-8',
            'encoding' => 'UTF-8',
        ],
    ],

    // Templates
    'templates' => [
        // TODO: новый шаблонизатор
        'default' => 'Стандартный',
    ],

    'tpl_name' => 'default',
    'stylesheet' => 'main.css',

    'show_sidebar1_on_every_page' => false,
    'show_sidebar2_on_every_page' => false,

    // Cookie
    'cookie_domain' => in_array($domain_name, array(getenv('SERVER_ADDR'), 'localhost')) ? '' : ".$domain_name",
    'cookie_secure' => (isset($_SERVER['HTTP_X_FORWARDED_PROTO']) == 'https') ? 1 : 0,
    'cookie_prefix' => 'bb_', // 'bb_'

    // Sessions
    // TODO: заменить + глобальный объект user
    'session_update_intrv' => 180,    // sec
    'user_session_duration' => 1800,   // sec
    'admin_session_duration' => 6 * 3600, // sec
    'user_session_gc_ttl' => 1800,   // number of seconds that a staled session entry may remain in sessions table
    'session_cache_gc_ttl' => 1200,   // sec
    'max_last_visit_days' => 14,     // days
    'last_visit_update_intrv' => 3600,   // sec

    // Registration
    'invalid_logins' => 5,     // Количество неверных попыток ввода пароля, перед выводом проверки капчей
    'new_user_reg_disabled' => false, // Запретить регистрацию новых учетных записей
    'unique_ip' => false, // Запретить регистрацию нескольких учетных записей с одного ip
    'new_user_reg_restricted' => false, // Ограничить регистрацию новых пользователей по времени с 01:00 до 17:00
    'reg_email_activation' => true,  // Требовать активацию учетной записи по email

    // Email
    // TODO: заменить
    'emailer_disabled' => false,
    'smtp_delivery' => false, // send email via a named server instead of the local mail function
    'smtp_ssl' => false, // use ssl connect
    'smtp_host' => '',    // SMTP server host
    'smtp_port' => 25,    // SMTP server port
    'smtp_username' => '',    // enter a username if your SMTP server requires it
    'smtp_password' => '',    // enter a password if your SMTP server requires it
    'smtp' => [
        'name' => 'yandex.ru',
        'host' => 'smtp.yandex.ru',
        'port' => 465,
        'connection_class' => 'login',
        'connection_config' => [
            'username' => '',
            'password' => '',
            'ssl' => 'ssl',
        ],
    ],

    'board_email' => "noreply@$domain_name", // admin email address
    'board_email_form' => false,                  // can users send email to each other via board
    'board_email_sig' => '',                     // this text will be attached to all emails the board sends
    'board_email_sitename' => $domain_name,           // sitename used in all emails header

    'topic_notify_enabled' => true,
    'pm_notify_enabled' => true,
    'group_send_email' => true,
    'email_change_disabled' => false, // disable changing email by user

    'tech_admin_email' => "admin@$domain_name", // email for sending error reports
    'abuse_email' => "abuse@$domain_name",
    'adv_email' => "adv@$domain_name",

    // Special users
    'dbg_users' => [
        #user_id => 'name',
        2 => 'admin',
    ],
    'unlimited_users' => [
        #user_id => 'name',
        2 => 'admin',
    ],
    'super_admins' => [
        #user_id => 'name',
        2 => 'admin',
    ],

    // Date format
    'date_format' => 'Y-m-d',

    // Subforums
    'sf_on_first_page_only' => true,

    // Forums
    'allowed_topics_per_page' => [50, 100, 150, 200, 250, 300],

    // Topics
    'show_quick_reply' => true,
    'show_rank_text' => false,
    'show_rank_image' => true,
    'show_poster_joined' => true,
    'show_poster_posts' => true,
    'show_poster_from' => true,
    'show_bot_nick' => false,
    'text_buttons' => false, // replace EDIT, QUOTE... images with text links
    'parse_ed2k_links' => true,  // make ed2k links clickable
    'post_date_format' => 'd-M-Y H:i',
    'ext_link_new_win' => true,  // open external links in new window

    'topic_moved_days_keep' => 7, // remove topic moved links after xx days (or FALSE to disable)
    'allowed_posts_per_page' => [15, 30, 50, 100],
    'user_signature_start' => '<div class="signature"><br />_________________<br />',
    'user_signature_end' => '</div>', // Это позволит использовать html теги, которые требуют закрытия. Например <table> или <font color>

    // Posts
    'use_posts_cache' => true,
    'posts_cache_days_keep' => 14,
    'max_post_length' => 120000,
    'use_ajax_posts' => true,

    // Search
    'sphinx_enabled' => false, // if false mysql by default
    'sphinx_topic_titles_host' => '127.0.0.1',
    'sphinx_topic_titles_port' => 3312,
    'sphinx_config_path' => realpath("../install/sphinx/sphinx.conf"),
    'disable_ft_search_in_posts' => false, // disable searching in post bodies
    'disable_search_for_guest' => true,
    'allow_search_in_bool_mode' => true,
    'max_search_words_per_post' => 200,
    'search_min_word_len' => 3,
    'search_max_word_len' => 35,
    'limit_max_search_results' => false,
    'spam_filter_file_path' => '', // BB_PATH .'/misc/spam_filter_words.txt';

    // Posting
    'prevent_multiposting' => true, // replace "reply" with "edit last msg" if user (not admin or mod) is last topic poster
    'max_smilies' => 10,   // Максимальное число смайлов в посте (0 - без ограничения)

    // PM
    'privmsg_disable' => false, // отключить систему личных сообщений на форуме
    'max_outgoing_pm_cnt' => 10,    // ограничение на кол. одновременных исходящих лс (для замедления рассылки спама)
    'max_inbox_privmsgs' => 500,   // максимальное число сообщений в папке входящие (удалить)
    'max_savebox_privmsgs' => 500,   // максимальное число сообщений в папке сохраненные (удалить)
    'max_sentbox_privmsgs' => 500,   // максимальное число сообщений в папке отправленные (удалить)
    'pm_days_keep' => 180,   // время хранения ЛС

    // Actions log
    'log_days_keep' => 90,

    // Users
    'color_nick' => true, // Окраска ников пользователей по user_rank
    'user_not_activated_days_keep' => 7,    // "not activated" == "not finished registration"
    'user_not_active_days_keep' => 180,  // inactive users but only with no posts

    // Groups
    'group_members_per_page' => 50,

    // Tidy
    'tidy_post' => (!in_array('tidy', get_loaded_extensions())) ? false : true,

    // Misc
    'mem_on_start' => (MEM_USAGE) ? memory_get_usage() : 0,
    'translate_dates' => true, // in displaying time
    'use_word_censor' => true,

    'last_visit_date_format' => 'd-M H:i',
    'last_post_date_format' => 'd-M-y H:i',
    'poll_max_days' => 180, // сколько дней с момента создания темы опрос будет активным

    'allow_change' => [
        'language' => true,
        'dateformat' => true,
    ],

    'trash_forum_id' => 0, // (int) 7

    'first_logon_redirect_url' => 'index.php',
    'terms_and_conditions_url' => 'terms.php',
    'tor_help_links' => 'terms.php',

    'user_agreement_url' => 'info.php?show=user_agreement',
    'copyright_holders_url' => 'info.php?show=copyright_holders',
    'advert_url' => 'info.php?show=advert',

    // Extensions
    'file_id_ext' => [
        1 => 'gif',
        2 => 'gz',
        3 => 'jpg',
        4 => 'png',
        5 => 'rar',
        6 => 'tar',
        7 => 'tiff',
        8 => 'torrent',
        9 => 'zip',
    ],

    // Attachments
    'attach' => [
        'upload_path' => DATA_DIR . 'torrent_files', // путь к директории с torrent файлами
        'max_size' => 5 * 1024 * 1024,            // максимальный размер файла в байтах
    ],

    'tor_forums_allowed_ext' => ['torrent', 'zip', 'rar'], // для разделов с раздачами
    'gen_forums_allowed_ext' => ['torrent', 'zip', 'rar'], // для обычных разделов

    // Avatars
    'avatars' => [
        'allowed_ext' => ['gif', 'jpg', 'jpeg', 'png'], // разрешенные форматы файлов
        'bot_avatar' => 'gallery/bot.gif',          // аватара бота
        'max_size' => 100 * 1024,                 // размер аватары в байтах
        'max_height' => 100,                        // высота аватара в px
        'max_width' => 100,                        // ширина аватара в px
        'no_avatar' => 'gallery/noavatar.png',     // дефолтная аватара
        'upload_path' => BB_ROOT . 'data/avatars/',  // путь к директории с аватарами
        'up_allowed' => true,                       // разрешить загрузку аватар
    ],

    // Group avatars
    'group_avatars' => [
        'allowed_ext' => ['gif', 'jpg', 'jpeg', 'png'], // разрешенные форматы файлов
        'max_size' => 300 * 1024,                 // размер аватары в байтах
        'max_height' => 300,                        // высота аватара в px
        'max_width' => 300,                        // ширина аватара в px
        'no_avatar' => 'gallery/noavatar.png',     // дефолтная аватара
        'upload_path' => BB_ROOT . 'data/avatars/',  // путь к директории с аватарами
        'up_allowed' => true,                       // разрешить загрузку аватар
    ],

    // Captcha
    // Get a Google reCAPTCHA API Key: https://www.google.com/recaptcha/admin
    'captcha' => [
        'disabled' => false,
        'public_key' => '', // your public key
        'secret_key' => '', // your secret key
        'theme' => 'light', // light or dark
    ],

    // Atom feed
    'atom' => [
        'path' => INT_DATA_DIR . 'atom',  // without ending slash
        'url' => './internal_data/atom',// without ending slash
    ],

    // Nofollow
    'nofollow' => [
        'disabled' => false,
        'allowed_url' => [$domain_name], // 'allowed.site', 'www.allowed.site'
    ],

    // Page settings
    'page' => [
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
    ],

    // Tracker settings
    'tracker' => [
        'autoclean' => true,
        'off' => false,
        'off_reason' => 'temporarily disabled',
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
        'gold_silver_enabled' => true,
        'retracker' => true,
        'retracker_host' => 'http://retracker.local/announce',
        'freeleech' => false,
        'guest_tracker' => true,
    ],

    // Ratio settings
    // Don't change the order of ratios (from 0 to 1)
    // rating < 0.4 -- allow only 1 torrent for leeching
    // rating < 0.5 -- only 2
    // rating < 0.6 -- only 3
    // rating > 0.6 -- depend on your tracker config limits (in "ACP - Tracker Config - Limits")
    'rating' => [
        '0.4' => 1,
        '0.5' => 2,
        '0.6' => 3,
    ],

    // Иконки статусов раздач
    'tor_icons' => [
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
    ],

    // Запрет на скачивание
    'tor_frozen' => [
        TOR_CHECKING => true,
        TOR_CLOSED => true,
        TOR_CLOSED_CPHOLD => true,
        TOR_CONSUMED => true,
        TOR_DUP => true,
        TOR_NO_DESC => true,
        TOR_PREMOD => true,
    ],

    // Разрешение на скачку автором, если закрыто на скачивание.
    'tor_frozen_author_download' => [
        TOR_CHECKING => true,
        TOR_NO_DESC => true,
        TOR_PREMOD => true,
    ],

    // Запрет на редактирование головного сообщения
    'tor_cannot_edit' => [
        TOR_CHECKING => true,
        TOR_CLOSED => true,
        TOR_CONSUMED => true,
        TOR_DUP => true,
    ],

    // Запрет на создание новых раздач если стоит статус недооформлено/неоформлено/сомнительно
    'tor_cannot_new' => [TOR_NEED_EDIT, TOR_NO_DESC, TOR_DOUBTFUL],

    // Разрешение на ответ релизера, если раздача исправлена.
    'tor_reply' => [TOR_NEED_EDIT, TOR_NO_DESC, TOR_DOUBTFUL],

    // Если такой статус у релиза, то статистика раздачи будет скрыта
    'tor_no_tor_act' => [
        TOR_CLOSED => true,
        TOR_DUP => true,
        TOR_CLOSED_CPHOLD => true,
        TOR_CONSUMED => true,
    ],

    // Vote graphic length defines the maximum length of a vote result graphic, ie. 100% = this length
    'vote_graphic_length' => 205,
    'privmsg_graphic_length' => 175,
    'topic_left_column_witdh' => 150,

    // Images auto-resize
    'post_img_width_decr' => 52,
    'attach_img_width_decr' => 130,
];

return $config;
