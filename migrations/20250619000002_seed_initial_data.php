<?php
/**
 * TorrentPier Initial Data Seeding Migration
 * Seeds essential data for fresh installations
 */

use Phinx\Migration\AbstractMigration;

class SeedInitialData extends AbstractMigration
{
    public function up()
    {
        // Seed all essential data
        $this->seedCategories();
        $this->seedForums();
        $this->seedUsers();
        $this->seedBtUsers();
        $this->seedConfiguration();
        $this->seedCronJobs();
        $this->seedExtensions();
        $this->seedSmilies();
        $this->seedRanks();
        $this->seedQuotaLimits();
        $this->seedDisallowedUsernames();
        $this->seedAttachmentConfig();
        $this->seedTopicsAndPosts();
        $this->seedTopicWatch();
    }

    private function seedCategories()
    {
        $this->table('bb_categories')->insert([
            [
                'cat_id' => 1,
                'cat_title' => 'Your first category',
                'cat_order' => 10
            ]
        ])->saveData();
    }

    private function seedForums()
    {
        $this->table('bb_forums')->insert([
            [
                'forum_id' => 1,
                'cat_id' => 1,
                'forum_name' => 'Your first forum',
                'forum_desc' => 'Description of the forum.',
                'forum_status' => 0,
                'forum_order' => 10,
                'forum_posts' => 1,
                'forum_topics' => 1,
                'forum_last_post_id' => 1,
                'forum_tpl_id' => 0,
                'prune_days' => 0,
                'auth_view' => 0,
                'auth_read' => 0,
                'auth_post' => 1,
                'auth_reply' => 1,
                'auth_edit' => 1,
                'auth_delete' => 1,
                'auth_sticky' => 3,
                'auth_announce' => 3,
                'auth_vote' => 1,
                'auth_pollcreate' => 1,
                'auth_attachments' => 1,
                'auth_download' => 1,
                'allow_reg_tracker' => 0,
                'allow_porno_topic' => 0,
                'self_moderated' => 0,
                'forum_parent' => 0,
                'show_on_index' => 1,
                'forum_display_sort' => 0,
                'forum_display_order' => 0
            ]
        ])->saveData();
    }

    private function seedUsers()
    {
        $this->table('bb_users')->insert([
            [
                'user_id' => -1,
                'user_active' => 0,
                'username' => 'Guest',
                'user_password' => '$2y$10$sfZSmqPio8mxxFQLRRXaFuVMkFKZARRz/RzqddfYByN3M53.CEe.O',
                'user_session_time' => 0,
                'user_lastvisit' => 0,
                'user_last_ip' => '0',
                'user_regdate' => time(),
                'user_reg_ip' => '0',
                'user_level' => 0,
                'user_posts' => 0,
                'user_timezone' => 0.00,
                'user_lang' => 'en',
                'user_new_privmsg' => 0,
                'user_unread_privmsg' => 0,
                'user_last_privmsg' => 0,
                'user_opt' => 0,
                'user_rank' => 0,
                'avatar_ext_id' => 0,
                'user_gender' => 0,
                'user_birthday' => '1900-01-01',
                'user_email' => '',
                'user_skype' => '',
                'user_twitter' => '',
                'user_icq' => '',
                'user_website' => '',
                'user_from' => '',
                'user_sig' => '',
                'user_occ' => '',
                'user_interests' => '',
                'user_actkey' => '',
                'user_newpasswd' => '',
                'autologin_id' => '',
                'user_newest_pm_id' => 0,
                'user_points' => 0.00,
                'tpl_name' => 'default'
            ],
            [
                'user_id' => -746,
                'user_active' => 0,
                'username' => 'bot',
                'user_password' => '$2y$10$sfZSmqPio8mxxFQLRRXaFuVMkFKZARRz/RzqddfYByN3M53.CEe.O',
                'user_session_time' => 0,
                'user_lastvisit' => 0,
                'user_last_ip' => '0',
                'user_regdate' => time(),
                'user_reg_ip' => '0',
                'user_level' => 0,
                'user_posts' => 0,
                'user_timezone' => 0.00,
                'user_lang' => 'en',
                'user_new_privmsg' => 0,
                'user_unread_privmsg' => 0,
                'user_last_privmsg' => 0,
                'user_opt' => 144,
                'user_rank' => 0,
                'avatar_ext_id' => 0,
                'user_gender' => 0,
                'user_birthday' => '1900-01-01',
                'user_email' => 'bot@torrentpier.com',
                'user_skype' => '',
                'user_twitter' => '',
                'user_icq' => '',
                'user_website' => '',
                'user_from' => '',
                'user_sig' => '',
                'user_occ' => '',
                'user_interests' => '',
                'user_actkey' => '',
                'user_newpasswd' => '',
                'autologin_id' => '',
                'user_newest_pm_id' => 0,
                'user_points' => 0.00,
                'tpl_name' => 'default'
            ],
            [
                'user_id' => 2,
                'user_active' => 1,
                'username' => 'admin',
                'user_password' => '$2y$10$QeekUGqdfMO0yp7AT7la8OhgbiNBoJ627BO38MdS1h5kY7oX6UUKu',
                'user_session_time' => 0,
                'user_lastvisit' => 0,
                'user_last_ip' => '0',
                'user_regdate' => time(),
                'user_reg_ip' => '0',
                'user_level' => 1,
                'user_posts' => 1,
                'user_timezone' => 0.00,
                'user_lang' => 'en',
                'user_new_privmsg' => 0,
                'user_unread_privmsg' => 0,
                'user_last_privmsg' => 0,
                'user_opt' => 304,
                'user_rank' => 1,
                'avatar_ext_id' => 0,
                'user_gender' => 0,
                'user_birthday' => '1900-01-01',
                'user_email' => 'admin@torrentpier.com',
                'user_skype' => '',
                'user_twitter' => '',
                'user_icq' => '',
                'user_website' => '',
                'user_from' => '',
                'user_sig' => '',
                'user_occ' => '',
                'user_interests' => '',
                'user_actkey' => '',
                'user_newpasswd' => '',
                'autologin_id' => '',
                'user_newest_pm_id' => 0,
                'user_points' => 0.00,
                'tpl_name' => 'default'
            ]
        ])->saveData();
    }

    private function seedBtUsers()
    {
        $this->table('bb_bt_users')->insert([
            [
                'user_id' => -1,
                'auth_key' => substr(md5(rand()), 0, 20)
            ],
            [
                'user_id' => -746,
                'auth_key' => substr(md5(rand()), 0, 20)
            ],
            [
                'user_id' => 2,
                'auth_key' => substr(md5(rand()), 0, 20)
            ]
        ])->saveData();
    }

    private function seedConfiguration()
    {
        $currentTime = time();
        
        $configs = [
            ['config_name' => 'allow_autologin', 'config_value' => '1'],
            ['config_name' => 'allow_bbcode', 'config_value' => '1'],
            ['config_name' => 'allow_namechange', 'config_value' => '0'],
            ['config_name' => 'allow_sig', 'config_value' => '1'],
            ['config_name' => 'allow_smilies', 'config_value' => '1'],
            ['config_name' => 'board_disable', 'config_value' => '0'],
            ['config_name' => 'board_startdate', 'config_value' => (string)$currentTime],
            ['config_name' => 'board_timezone', 'config_value' => '0'],
            ['config_name' => 'bonus_upload', 'config_value' => ''],
            ['config_name' => 'bonus_upload_price', 'config_value' => ''],
            ['config_name' => 'birthday_enabled', 'config_value' => '1'],
            ['config_name' => 'birthday_max_age', 'config_value' => '99'],
            ['config_name' => 'birthday_min_age', 'config_value' => '10'],
            ['config_name' => 'birthday_check_day', 'config_value' => '7'],
            ['config_name' => 'bt_add_auth_key', 'config_value' => '1'],
            ['config_name' => 'bt_allow_spmode_change', 'config_value' => '1'],
            ['config_name' => 'bt_announce_url', 'config_value' => 'https://localhost/bt/announce.php'],
            ['config_name' => 'bt_disable_dht', 'config_value' => '0'],
            ['config_name' => 'bt_check_announce_url', 'config_value' => '0'],
            ['config_name' => 'bt_del_addit_ann_urls', 'config_value' => '1'],
            ['config_name' => 'bt_dl_list_only_1st_page', 'config_value' => '1'],
            ['config_name' => 'bt_dl_list_only_count', 'config_value' => '1'],
            ['config_name' => 'bt_newtopic_auto_reg', 'config_value' => '1'],
            ['config_name' => 'bt_replace_ann_url', 'config_value' => '1'],
            ['config_name' => 'bt_search_bool_mode', 'config_value' => '1'],
            ['config_name' => 'bt_set_dltype_on_tor_reg', 'config_value' => '1'],
            ['config_name' => 'bt_show_dl_but_cancel', 'config_value' => '1'],
            ['config_name' => 'bt_show_dl_but_compl', 'config_value' => '1'],
            ['config_name' => 'bt_show_dl_but_down', 'config_value' => '0'],
            ['config_name' => 'bt_show_dl_but_will', 'config_value' => '1'],
            ['config_name' => 'bt_show_dl_list', 'config_value' => '0'],
            ['config_name' => 'bt_show_dl_list_buttons', 'config_value' => '1'],
            ['config_name' => 'bt_show_dl_stat_on_index', 'config_value' => '1'],
            ['config_name' => 'bt_show_ip_only_moder', 'config_value' => '1'],
            ['config_name' => 'bt_show_peers', 'config_value' => '1'],
            ['config_name' => 'bt_show_peers_mode', 'config_value' => '1'],
            ['config_name' => 'bt_show_port_only_moder', 'config_value' => '1'],
            ['config_name' => 'bt_tor_browse_only_reg', 'config_value' => '0'],
            ['config_name' => 'bt_unset_dltype_on_tor_unreg', 'config_value' => '1'],
            ['config_name' => 'cron_last_check', 'config_value' => '0'],
            ['config_name' => 'default_dateformat', 'config_value' => 'Y-m-d H:i'],
            ['config_name' => 'default_lang', 'config_value' => 'en'],
            ['config_name' => 'flood_interval', 'config_value' => '15'],
            ['config_name' => 'hot_threshold', 'config_value' => '300'],
            ['config_name' => 'login_reset_time', 'config_value' => '30'],
            ['config_name' => 'max_autologin_time', 'config_value' => '10'],
            ['config_name' => 'max_login_attempts', 'config_value' => '5'],
            ['config_name' => 'max_poll_options', 'config_value' => '6'],
            ['config_name' => 'max_sig_chars', 'config_value' => '255'],
            ['config_name' => 'posts_per_page', 'config_value' => '15'],
            ['config_name' => 'prune_enable', 'config_value' => '1'],
            ['config_name' => 'record_online_date', 'config_value' => (string)$currentTime],
            ['config_name' => 'record_online_users', 'config_value' => '0'],
            ['config_name' => 'seed_bonus_enabled', 'config_value' => '1'],
            ['config_name' => 'seed_bonus_release', 'config_value' => ''],
            ['config_name' => 'seed_bonus_points', 'config_value' => ''],
            ['config_name' => 'seed_bonus_tor_size', 'config_value' => '0'],
            ['config_name' => 'seed_bonus_user_regdate', 'config_value' => '0'],
            ['config_name' => 'site_desc', 'config_value' => 'Bull-powered BitTorrent tracker engine'],
            ['config_name' => 'sitemap_time', 'config_value' => ''],
            ['config_name' => 'sitename', 'config_value' => 'TorrentPier'],
            ['config_name' => 'smilies_path', 'config_value' => 'styles/images/smiles'],
            ['config_name' => 'static_sitemap', 'config_value' => ''],
            ['config_name' => 'topics_per_page', 'config_value' => '50'],
            ['config_name' => 'xs_use_cache', 'config_value' => '1'],
            ['config_name' => 'cron_check_interval', 'config_value' => '180'],
            ['config_name' => 'magnet_links_enabled', 'config_value' => '1'],
            ['config_name' => 'magnet_links_for_guests', 'config_value' => '0'],
            ['config_name' => 'gender', 'config_value' => '1'],
            ['config_name' => 'callseed', 'config_value' => '0'],
            ['config_name' => 'tor_stats', 'config_value' => '1'],
            ['config_name' => 'show_latest_news', 'config_value' => '1'],
            ['config_name' => 'max_news_title', 'config_value' => '50'],
            ['config_name' => 'latest_news_count', 'config_value' => '5'],
            ['config_name' => 'latest_news_forum_id', 'config_value' => '1'],
            ['config_name' => 'show_network_news', 'config_value' => '1'],
            ['config_name' => 'max_net_title', 'config_value' => '50'],
            ['config_name' => 'network_news_count', 'config_value' => '5'],
            ['config_name' => 'network_news_forum_id', 'config_value' => '2'],
            ['config_name' => 'whois_info', 'config_value' => 'https://whatismyipaddress.com/ip/'],
            ['config_name' => 'show_mod_index', 'config_value' => '0'],
            ['config_name' => 'premod', 'config_value' => '0'],
            ['config_name' => 'tor_comment', 'config_value' => '1'],
            ['config_name' => 'terms', 'config_value' => ''],
            ['config_name' => 'show_board_start_index', 'config_value' => '1']
        ];

        $this->table('bb_config')->insert($configs)->saveData();
    }

    private function seedCronJobs()
    {
        $cronJobs = [
            [
                'cron_active' => 1,
                'cron_title' => 'Attach maintenance',
                'cron_script' => 'attach_maintenance.php',
                'schedule' => 'daily',
                'run_day' => null,
                'run_time' => '05:00:00',
                'run_order' => 40,
                'last_run' => '1900-01-01 00:00:00',
                'next_run' => '1900-01-01 00:00:00',
                'run_interval' => null,
                'log_enabled' => 0,
                'log_file' => '',
                'log_sql_queries' => 0,
                'disable_board' => 1,
                'run_counter' => 0
            ],
            [
                'cron_active' => 1,
                'cron_title' => 'Board maintenance',
                'cron_script' => 'board_maintenance.php',
                'schedule' => 'daily',
                'run_day' => null,
                'run_time' => '05:00:00',
                'run_order' => 40,
                'last_run' => '1900-01-01 00:00:00',
                'next_run' => '1900-01-01 00:00:00',
                'run_interval' => null,
                'log_enabled' => 0,
                'log_file' => '',
                'log_sql_queries' => 0,
                'disable_board' => 1,
                'run_counter' => 0
            ],
            [
                'cron_active' => 1,
                'cron_title' => 'Prune forums',
                'cron_script' => 'prune_forums.php',
                'schedule' => 'daily',
                'run_day' => null,
                'run_time' => '05:00:00',
                'run_order' => 50,
                'last_run' => '1900-01-01 00:00:00',
                'next_run' => '1900-01-01 00:00:00',
                'run_interval' => null,
                'log_enabled' => 0,
                'log_file' => '',
                'log_sql_queries' => 0,
                'disable_board' => 1,
                'run_counter' => 0
            ],
            [
                'cron_active' => 1,
                'cron_title' => 'Tracker maintenance',
                'cron_script' => 'tr_maintenance.php',
                'schedule' => 'daily',
                'run_day' => null,
                'run_time' => '05:00:00',
                'run_order' => 90,
                'last_run' => '1900-01-01 00:00:00',
                'next_run' => '1900-01-01 00:00:00',
                'run_interval' => null,
                'log_enabled' => 0,
                'log_file' => '',
                'log_sql_queries' => 0,
                'disable_board' => 1,
                'run_counter' => 0
            ],
            [
                'cron_active' => 1,
                'cron_title' => 'Sessions cleanup',
                'cron_script' => 'sessions_cleanup.php',
                'schedule' => 'interval',
                'run_day' => null,
                'run_time' => '04:00:00',
                'run_order' => 255,
                'last_run' => '1900-01-01 00:00:00',
                'next_run' => '1900-01-01 00:00:00',
                'run_interval' => '00:03:00',
                'log_enabled' => 0,
                'log_file' => '',
                'log_sql_queries' => 0,
                'disable_board' => 0,
                'run_counter' => 0
            ],
            [
                'cron_active' => 1,
                'cron_title' => 'Tracker cleanup and dlstat',
                'cron_script' => 'tr_cleanup_and_dlstat.php',
                'schedule' => 'interval',
                'run_day' => null,
                'run_time' => '04:00:00',
                'run_order' => 20,
                'last_run' => '1900-01-01 00:00:00',
                'next_run' => '1900-01-01 00:00:00',
                'run_interval' => '00:15:00',
                'log_enabled' => 0,
                'log_file' => '',
                'log_sql_queries' => 0,
                'disable_board' => 0,
                'run_counter' => 0
            ],
            [
                'cron_active' => 1,
                'cron_title' => 'Make tracker snapshot',
                'cron_script' => 'tr_make_snapshot.php',
                'schedule' => 'interval',
                'run_day' => null,
                'run_time' => '04:00:00',
                'run_order' => 10,
                'last_run' => '1900-01-01 00:00:00',
                'next_run' => '1900-01-01 00:00:00',
                'run_interval' => '00:10:00',
                'log_enabled' => 0,
                'log_file' => '',
                'log_sql_queries' => 0,
                'disable_board' => 0,
                'run_counter' => 0
            ]
        ];

        $this->table('bb_cron')->insert($cronJobs)->saveData();
    }

    private function seedExtensions()
    {
        // Extension groups
        $groups = [
            ['group_name' => 'Images', 'cat_id' => 1, 'allow_group' => 1, 'download_mode' => 1, 'upload_icon' => '', 'max_filesize' => 262144, 'forum_permissions' => ''],
            ['group_name' => 'Archives', 'cat_id' => 0, 'allow_group' => 1, 'download_mode' => 1, 'upload_icon' => '', 'max_filesize' => 262144, 'forum_permissions' => ''],
            ['group_name' => 'Plain text', 'cat_id' => 0, 'allow_group' => 1, 'download_mode' => 1, 'upload_icon' => '', 'max_filesize' => 262144, 'forum_permissions' => ''],
            ['group_name' => 'Documents', 'cat_id' => 0, 'allow_group' => 1, 'download_mode' => 1, 'upload_icon' => '', 'max_filesize' => 262144, 'forum_permissions' => ''],
            ['group_name' => 'Real media', 'cat_id' => 0, 'allow_group' => 0, 'download_mode' => 2, 'upload_icon' => '', 'max_filesize' => 262144, 'forum_permissions' => ''],
            ['group_name' => 'Torrent', 'cat_id' => 0, 'allow_group' => 1, 'download_mode' => 1, 'upload_icon' => '', 'max_filesize' => 262144, 'forum_permissions' => '']
        ];

        $this->table('bb_extension_groups')->insert($groups)->saveData();

        // Extensions
        $extensions = [
            ['group_id' => 1, 'extension' => 'gif', 'comment' => ''],
            ['group_id' => 1, 'extension' => 'png', 'comment' => ''],
            ['group_id' => 1, 'extension' => 'jpeg', 'comment' => ''],
            ['group_id' => 1, 'extension' => 'jpg', 'comment' => ''],
            ['group_id' => 1, 'extension' => 'webp', 'comment' => ''],
            ['group_id' => 1, 'extension' => 'avif', 'comment' => ''],
            ['group_id' => 1, 'extension' => 'bmp', 'comment' => ''],
            ['group_id' => 2, 'extension' => 'gtar', 'comment' => ''],
            ['group_id' => 2, 'extension' => 'gz', 'comment' => ''],
            ['group_id' => 2, 'extension' => 'tar', 'comment' => ''],
            ['group_id' => 2, 'extension' => 'zip', 'comment' => ''],
            ['group_id' => 2, 'extension' => 'rar', 'comment' => ''],
            ['group_id' => 2, 'extension' => 'ace', 'comment' => ''],
            ['group_id' => 2, 'extension' => '7z', 'comment' => ''],
            ['group_id' => 3, 'extension' => 'txt', 'comment' => ''],
            ['group_id' => 3, 'extension' => 'c', 'comment' => ''],
            ['group_id' => 3, 'extension' => 'h', 'comment' => ''],
            ['group_id' => 3, 'extension' => 'cpp', 'comment' => ''],
            ['group_id' => 3, 'extension' => 'hpp', 'comment' => ''],
            ['group_id' => 3, 'extension' => 'diz', 'comment' => ''],
            ['group_id' => 3, 'extension' => 'm3u', 'comment' => ''],
            ['group_id' => 4, 'extension' => 'xls', 'comment' => ''],
            ['group_id' => 4, 'extension' => 'doc', 'comment' => ''],
            ['group_id' => 4, 'extension' => 'dot', 'comment' => ''],
            ['group_id' => 4, 'extension' => 'pdf', 'comment' => ''],
            ['group_id' => 4, 'extension' => 'ai', 'comment' => ''],
            ['group_id' => 4, 'extension' => 'ps', 'comment' => ''],
            ['group_id' => 4, 'extension' => 'ppt', 'comment' => ''],
            ['group_id' => 5, 'extension' => 'rm', 'comment' => ''],
            ['group_id' => 6, 'extension' => 'torrent', 'comment' => '']
        ];

        $this->table('bb_extensions')->insert($extensions)->saveData();
    }

    private function seedSmilies()
    {
        $smilies = [
            ['code' => ':aa:', 'smile_url' => 'aa.gif', 'emoticon' => 'aa'],
            ['code' => ':ab:', 'smile_url' => 'ab.gif', 'emoticon' => 'ab'],
            ['code' => ':ac:', 'smile_url' => 'ac.gif', 'emoticon' => 'ac'],
            ['code' => ':ae:', 'smile_url' => 'ae.gif', 'emoticon' => 'ae'],
            ['code' => ':af:', 'smile_url' => 'af.gif', 'emoticon' => 'af'],
            ['code' => ':ag:', 'smile_url' => 'ag.gif', 'emoticon' => 'ag'],
            ['code' => ':ah:', 'smile_url' => 'ah.gif', 'emoticon' => 'ah'],
            ['code' => ':ai:', 'smile_url' => 'ai.gif', 'emoticon' => 'ai'],
            ['code' => ':aj:', 'smile_url' => 'aj.gif', 'emoticon' => 'aj'],
            ['code' => ':ak:', 'smile_url' => 'ak.gif', 'emoticon' => 'ak']
        ];

        $this->table('bb_smilies')->insert($smilies)->saveData();
    }

    private function seedRanks()
    {
        $this->table('bb_ranks')->insert([
            [
                'rank_title' => 'Administrator',
                'rank_image' => 'styles/images/ranks/admin.png',
                'rank_style' => 'colorAdmin'
            ]
        ])->saveData();
    }

    private function seedQuotaLimits()
    {
        $quotas = [
            ['quota_desc' => 'Low', 'quota_limit' => 262144],
            ['quota_desc' => 'Medium', 'quota_limit' => 10485760],
            ['quota_desc' => 'High', 'quota_limit' => 15728640]
        ];

        $this->table('bb_quota_limits')->insert($quotas)->saveData();
    }

    private function seedDisallowedUsernames()
    {
        $disallowed = [
            ['disallow_username' => 'torrentpier*'],
            ['disallow_username' => 'tracker*'],
            ['disallow_username' => 'forum*'],
            ['disallow_username' => 'torrent*'],
            ['disallow_username' => 'admin*']
        ];

        $this->table('bb_disallow')->insert($disallowed)->saveData();
    }

    private function seedAttachmentConfig()
    {
        $attachConfig = [
            ['config_name' => 'upload_dir', 'config_value' => 'data/uploads'],
            ['config_name' => 'upload_img', 'config_value' => 'styles/images/icon_clip.gif'],
            ['config_name' => 'topic_icon', 'config_value' => 'styles/images/icon_clip.gif'],
            ['config_name' => 'display_order', 'config_value' => '0'],
            ['config_name' => 'max_filesize', 'config_value' => '262144'],
            ['config_name' => 'attachment_quota', 'config_value' => '52428800'],
            ['config_name' => 'max_filesize_pm', 'config_value' => '262144'],
            ['config_name' => 'max_attachments', 'config_value' => '1'],
            ['config_name' => 'max_attachments_pm', 'config_value' => '1'],
            ['config_name' => 'disable_mod', 'config_value' => '0'],
            ['config_name' => 'allow_pm_attach', 'config_value' => '1'],
            ['config_name' => 'default_upload_quota', 'config_value' => '0'],
            ['config_name' => 'default_pm_quota', 'config_value' => '0'],
            ['config_name' => 'img_display_inlined', 'config_value' => '1'],
            ['config_name' => 'img_max_width', 'config_value' => '2000'],
            ['config_name' => 'img_max_height', 'config_value' => '2000'],
            ['config_name' => 'img_link_width', 'config_value' => '600'],
            ['config_name' => 'img_link_height', 'config_value' => '400'],
            ['config_name' => 'img_create_thumbnail', 'config_value' => '1'],
            ['config_name' => 'img_min_thumb_filesize', 'config_value' => '12000']
        ];

        $this->table('bb_attachments_config')->insert($attachConfig)->saveData();
    }

    private function seedTopicsAndPosts()
    {
        $currentTime = time();

        // Create welcome topic
        $this->table('bb_topics')->insert([
            [
                'topic_id' => 1,
                'forum_id' => 1,
                'topic_title' => 'Welcome to TorrentPier Cattle',
                'topic_poster' => 2,
                'topic_time' => $currentTime,
                'topic_views' => 0,
                'topic_replies' => 0,
                'topic_status' => 0,
                'topic_vote' => 0,
                'topic_type' => 0,
                'topic_first_post_id' => 1,
                'topic_last_post_id' => 1,
                'topic_moved_id' => 0,
                'topic_attachment' => 0,
                'topic_dl_type' => 0,
                'topic_last_post_time' => $currentTime,
                'topic_show_first_post' => 0,
                'topic_allow_robots' => 1
            ]
        ])->saveData();

        // Create welcome post
        $this->table('bb_posts')->insert([
            [
                'post_id' => 1,
                'topic_id' => 1,
                'forum_id' => 1,
                'poster_id' => 2,
                'post_time' => $currentTime,
                'poster_ip' => '0',
                'poster_rg_id' => 0,
                'attach_rg_sig' => 0,
                'post_username' => '',
                'post_edit_time' => 0,
                'post_edit_count' => 0,
                'post_attachment' => 0,
                'user_post' => 1,
                'mc_comment' => '',
                'mc_type' => 0,
                'mc_user_id' => 0
            ]
        ])->saveData();

        // Create welcome post text
        $welcomeText = "Thank you for installing the new — TorrentPier Cattle!\n\n" .
                      "What to do next? First of all configure your site in the administration panel (link in the bottom).\n\n" .
                      "Change main options: site description, number of messages per topic, time zone, language by default, seed-bonus options, birthdays etc... " .
                      "Create a couple of forums, delete or change this one. Change settings of categories to allow registration of torrents, change announcer url. " .
                      "If you will have questions or want additional modifications of the engine, [url=https://torrentpier.com/]visit our forum[/url] " .
                      "(you can use english, we will try to help in any case).\n\n" .
                      "If you want to help with the translations: [url=https://crowdin.com/project/torrentpier]Crowdin[/url].\n\n" .
                      "Our GitHub organization: [url=https://github.com/torrentpier]https://github.com/torrentpier[/url].\n" .
                      "Our SourceForge repository: [url=https://sourceforge.net/projects/torrentpier-engine]https://sourceforge.net/projects/torrentpier-engine[/url].\n" .
                      "Our demo website: [url=https://torrentpier.duckdns.org]https://torrentpier.duckdns.org[/url].\n\n" .
                      "We are sure that you will be able to create the best tracker available!\n" .
                      "Good luck! 😉";

        $this->table('bb_posts_text')->insert([
            [
                'post_id' => 1,
                'post_text' => $welcomeText
            ]
        ])->saveData();
    }

    private function seedTopicWatch()
    {
        $this->table('bb_topics_watch')->insert([
            [
                'topic_id' => 1,
                'user_id' => 2,
                'notify_status' => 1
            ]
        ])->saveData();
    }

    public function down()
    {
        // Clean all seeded data
        $tables = [
            'bb_topics_watch', 'bb_posts_text', 'bb_posts', 'bb_topics',
            'bb_attachments_config', 'bb_disallow', 'bb_quota_limits',
            'bb_ranks', 'bb_smilies', 'bb_extensions', 'bb_extension_groups',
            'bb_cron', 'bb_config', 'bb_bt_users', 'bb_users', 'bb_forums', 'bb_categories'
        ];

        foreach ($tables as $table) {
            $this->execute("DELETE FROM {$table}");
        }
    }
}