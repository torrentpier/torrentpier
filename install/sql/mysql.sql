-- phpMyAdmin SQL Dump
-- version 3.4.10.1
-- http://www.phpmyadmin.net
--
-- Хост: localhost
-- Время создания: Мар 08 2012 г., 16:00
-- Версия сервера: 5.5.20
-- Версия PHP: 5.3.10

SET SQL_MODE="NO_AUTO_VALUE_ON_ZERO";

--
-- Удаление старых таблиц
--

DROP TABLE IF EXISTS `bb_ads`;
DROP TABLE IF EXISTS `bb_attachments`;
DROP TABLE IF EXISTS `bb_attachments_config`;
DROP TABLE IF EXISTS `bb_attachments_desc`;
DROP TABLE IF EXISTS `bb_attach_quota`;
DROP TABLE IF EXISTS `bb_auth_access`;
DROP TABLE IF EXISTS `bb_auth_access_snap`;
DROP TABLE IF EXISTS `bb_banlist`;
DROP TABLE IF EXISTS `bb_bt_dlstatus_main`;
DROP TABLE IF EXISTS `bb_bt_dlstatus_mrg`;
DROP TABLE IF EXISTS `bb_bt_dlstatus_new`;
DROP TABLE IF EXISTS `bb_bt_dlstatus_snap`;
DROP TABLE IF EXISTS `bb_bt_last_torstat`;
DROP TABLE IF EXISTS `bb_bt_last_userstat`;
DROP TABLE IF EXISTS `bb_bt_torhelp`;
DROP TABLE IF EXISTS `bb_bt_torrents`;
DROP TABLE IF EXISTS `bb_bt_torrents_del`;
DROP TABLE IF EXISTS `bb_bt_torstat`;
DROP TABLE IF EXISTS `bb_bt_tor_dl_stat`;
DROP TABLE IF EXISTS `bb_bt_tracker`;
DROP TABLE IF EXISTS `bb_bt_tracker_snap`;
DROP TABLE IF EXISTS `bb_bt_users`;
DROP TABLE IF EXISTS `bb_bt_user_settings`;
DROP TABLE IF EXISTS `bb_captcha`;
DROP TABLE IF EXISTS `bb_categories`;
DROP TABLE IF EXISTS `bb_config`;
DROP TABLE IF EXISTS `bb_cron`;
DROP TABLE IF EXISTS `bb_disallow`;
DROP TABLE IF EXISTS `bb_extensions`;
DROP TABLE IF EXISTS `bb_extension_groups`;
DROP TABLE IF EXISTS `bb_forums`;
DROP TABLE IF EXISTS `bb_groups`;
DROP TABLE IF EXISTS `bb_log`;
DROP TABLE IF EXISTS `bb_posts`;
DROP TABLE IF EXISTS `bb_posts_html`;
DROP TABLE IF EXISTS `bb_posts_search`;
DROP TABLE IF EXISTS `bb_posts_text`;
DROP TABLE IF EXISTS `bb_privmsgs`;
DROP TABLE IF EXISTS `bb_privmsgs_text`;
DROP TABLE IF EXISTS `bb_quota_limits`;
DROP TABLE IF EXISTS `bb_ranks`;
DROP TABLE IF EXISTS `bb_reports`;
DROP TABLE IF EXISTS `bb_reports_changes`;
DROP TABLE IF EXISTS `bb_reports_modules`;
DROP TABLE IF EXISTS `bb_reports_reasons`;
DROP TABLE IF EXISTS `bb_search_rebuild`;
DROP TABLE IF EXISTS `bb_search_results`;
DROP TABLE IF EXISTS `bb_sessions`;
DROP TABLE IF EXISTS `bb_smilies`;
DROP TABLE IF EXISTS `bb_topics`;
DROP TABLE IF EXISTS `bb_topics_watch`;
DROP TABLE IF EXISTS `bb_topic_templates`;
DROP TABLE IF EXISTS `bb_topic_tpl`;
DROP TABLE IF EXISTS `bb_users`;
DROP TABLE IF EXISTS `bb_user_group`;
DROP TABLE IF EXISTS `bb_vote_desc`;
DROP TABLE IF EXISTS `bb_vote_results`;
DROP TABLE IF EXISTS `bb_vote_voters`;
DROP TABLE IF EXISTS `bb_words`;
DROP TABLE IF EXISTS `buf_last_seeder`;
DROP TABLE IF EXISTS `buf_topic_view`;
DROP TABLE IF EXISTS `sph_counter`;
DROP TABLE IF EXISTS `xbt_announce_log`;
DROP TABLE IF EXISTS `xbt_config`;
DROP TABLE IF EXISTS `xbt_deny_from_hosts`;
DROP TABLE IF EXISTS `xbt_files_users`;
DROP TABLE IF EXISTS `xbt_scrape_log`;

--
-- Структура таблицы `bb_ads`
--

CREATE TABLE IF NOT EXISTS `bb_ads` (
  `ad_id` mediumint(8) unsigned NOT NULL AUTO_INCREMENT,
  `ad_block_ids` varchar(255) NOT NULL DEFAULT '',
  `ad_start_time` datetime NOT NULL DEFAULT '0000-00-00 00:00:00',
  `ad_active_days` smallint(6) NOT NULL DEFAULT '0',
  `ad_status` tinyint(4) NOT NULL DEFAULT '1',
  `ad_desc` varchar(255) NOT NULL DEFAULT '',
  `ad_html` text NOT NULL,
  PRIMARY KEY (`ad_id`)
) ENGINE=MyISAM DEFAULT CHARSET=utf8;

-- --------------------------------------------------------

--
-- Структура таблицы `bb_attachments`
--

CREATE TABLE IF NOT EXISTS `bb_attachments` (
  `attach_id` mediumint(8) unsigned NOT NULL DEFAULT '0',
  `post_id` mediumint(8) unsigned NOT NULL DEFAULT '0',
  `user_id_1` mediumint(8) NOT NULL DEFAULT '0',
  PRIMARY KEY (`attach_id`,`post_id`)
) ENGINE=MyISAM DEFAULT CHARSET=utf8;

-- --------------------------------------------------------

--
-- Структура таблицы `bb_attachments_config`
--

CREATE TABLE IF NOT EXISTS `bb_attachments_config` (
  `config_name` varchar(255) NOT NULL DEFAULT '',
  `config_value` varchar(255) NOT NULL DEFAULT '',
  PRIMARY KEY (`config_name`)
) ENGINE=MyISAM DEFAULT CHARSET=utf8;

--
-- Дамп данных таблицы `bb_attachments_config`
--

INSERT INTO `bb_attachments_config` VALUES ('upload_dir', 'files');
INSERT INTO `bb_attachments_config` VALUES ('upload_img', 'images/icon_clip.gif');
INSERT INTO `bb_attachments_config` VALUES ('topic_icon', 'images/icon_clip.gif');
INSERT INTO `bb_attachments_config` VALUES ('display_order', '0');
INSERT INTO `bb_attachments_config` VALUES ('max_filesize', '262144');
INSERT INTO `bb_attachments_config` VALUES ('attachment_quota', '52428800');
INSERT INTO `bb_attachments_config` VALUES ('max_filesize_pm', '262144');
INSERT INTO `bb_attachments_config` VALUES ('max_attachments', '1');
INSERT INTO `bb_attachments_config` VALUES ('max_attachments_pm', '1');
INSERT INTO `bb_attachments_config` VALUES ('disable_mod', '0');
INSERT INTO `bb_attachments_config` VALUES ('allow_pm_attach', '1');
INSERT INTO `bb_attachments_config` VALUES ('allow_ftp_upload', '0');
INSERT INTO `bb_attachments_config` VALUES ('attach_version', '2.3.14');
INSERT INTO `bb_attachments_config` VALUES ('default_upload_quota', '0');
INSERT INTO `bb_attachments_config` VALUES ('default_pm_quota', '0');
INSERT INTO `bb_attachments_config` VALUES ('ftp_server', '');
INSERT INTO `bb_attachments_config` VALUES ('ftp_path', '');
INSERT INTO `bb_attachments_config` VALUES ('download_path', '');
INSERT INTO `bb_attachments_config` VALUES ('ftp_user', '');
INSERT INTO `bb_attachments_config` VALUES ('ftp_pass', '');
INSERT INTO `bb_attachments_config` VALUES ('ftp_pasv_mode', '1');
INSERT INTO `bb_attachments_config` VALUES ('img_display_inlined', '1');
INSERT INTO `bb_attachments_config` VALUES ('img_max_width', '200');
INSERT INTO `bb_attachments_config` VALUES ('img_max_height', '200');
INSERT INTO `bb_attachments_config` VALUES ('img_link_width', '0');
INSERT INTO `bb_attachments_config` VALUES ('img_link_height', '0');
INSERT INTO `bb_attachments_config` VALUES ('img_create_thumbnail', '0');
INSERT INTO `bb_attachments_config` VALUES ('img_min_thumb_filesize', '12000');
INSERT INTO `bb_attachments_config` VALUES ('img_imagick', '/usr/bin/convert');
INSERT INTO `bb_attachments_config` VALUES ('use_gd2', '1');
INSERT INTO `bb_attachments_config` VALUES ('wma_autoplay', '0');
INSERT INTO `bb_attachments_config` VALUES ('flash_autoplay', '0');

-- --------------------------------------------------------

--
-- Структура таблицы `bb_attachments_desc`
--

CREATE TABLE IF NOT EXISTS `bb_attachments_desc` (
  `attach_id` mediumint(8) unsigned NOT NULL AUTO_INCREMENT,
  `physical_filename` varchar(255) NOT NULL DEFAULT '',
  `real_filename` varchar(255) NOT NULL DEFAULT '',
  `download_count` mediumint(8) unsigned NOT NULL DEFAULT '0',
  `comment` varchar(255) NOT NULL DEFAULT '',
  `extension` varchar(100) NOT NULL DEFAULT '',
  `mimetype` varchar(100) NOT NULL DEFAULT '',
  `filesize` int(20) NOT NULL DEFAULT '0',
  `filetime` int(11) NOT NULL DEFAULT '0',
  `thumbnail` tinyint(1) NOT NULL DEFAULT '0',
  `tracker_status` tinyint(1) NOT NULL DEFAULT '0',
  PRIMARY KEY (`attach_id`),
  KEY `filetime` (`filetime`),
  KEY `filesize` (`filesize`),
  KEY `physical_filename` (`physical_filename`(10))
) ENGINE=MyISAM DEFAULT CHARSET=utf8;

-- --------------------------------------------------------

--
-- Структура таблицы `bb_attach_quota`
--

CREATE TABLE IF NOT EXISTS `bb_attach_quota` (
  `user_id` mediumint(8) unsigned NOT NULL DEFAULT '0',
  `group_id` mediumint(8) unsigned NOT NULL DEFAULT '0',
  `quota_type` smallint(2) NOT NULL DEFAULT '0',
  `quota_limit_id` mediumint(8) unsigned NOT NULL DEFAULT '0',
  KEY `quota_type` (`quota_type`)
) ENGINE=MyISAM DEFAULT CHARSET=utf8;

-- --------------------------------------------------------

--
-- Структура таблицы `bb_auth_access`
--

CREATE TABLE IF NOT EXISTS `bb_auth_access` (
  `group_id` mediumint(8) NOT NULL DEFAULT '0',
  `forum_id` smallint(5) unsigned NOT NULL DEFAULT '0',
  `forum_perm` int(11) NOT NULL DEFAULT '0',
  PRIMARY KEY (`group_id`,`forum_id`),
  KEY `forum_id` (`forum_id`)
) ENGINE=MyISAM DEFAULT CHARSET=utf8;

-- --------------------------------------------------------

--
-- Структура таблицы `bb_auth_access_snap`
--

CREATE TABLE IF NOT EXISTS `bb_auth_access_snap` (
  `user_id` mediumint(9) NOT NULL DEFAULT '0',
  `forum_id` smallint(6) NOT NULL DEFAULT '0',
  `forum_perm` int(11) NOT NULL DEFAULT '0',
  PRIMARY KEY (`user_id`,`forum_id`)
) ENGINE=MyISAM DEFAULT CHARSET=utf8;

-- --------------------------------------------------------

--
-- Структура таблицы `bb_banlist`
--

CREATE TABLE IF NOT EXISTS `bb_banlist` (
  `ban_id` mediumint(8) unsigned NOT NULL AUTO_INCREMENT,
  `ban_userid` mediumint(8) NOT NULL DEFAULT '0',
  `ban_ip` varchar(32) NOT NULL DEFAULT '',
  `ban_email` varchar(255) NOT NULL DEFAULT '',
  PRIMARY KEY (`ban_id`),
  KEY `ban_ip_user_id` (`ban_ip`,`ban_userid`)
) ENGINE=MyISAM DEFAULT CHARSET=utf8;

-- --------------------------------------------------------

--
-- Структура таблицы `bb_bt_dlstatus_main`
--

CREATE TABLE IF NOT EXISTS `bb_bt_dlstatus_main` (
  `user_id` mediumint(9) NOT NULL DEFAULT '0',
  `topic_id` mediumint(8) unsigned NOT NULL DEFAULT '0',
  `user_status` tinyint(1) NOT NULL DEFAULT '0',
  `last_modified_dlstatus` timestamp NOT NULL DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
  PRIMARY KEY (`user_id`,`topic_id`),
  KEY `topic_id` (`topic_id`)
) ENGINE=MyISAM DEFAULT CHARSET=utf8;

-- --------------------------------------------------------

--
-- Структура таблицы `bb_bt_dlstatus_mrg`
--

CREATE TABLE IF NOT EXISTS `bb_bt_dlstatus_mrg` (
  `user_id` mediumint(9) NOT NULL DEFAULT '0',
  `topic_id` mediumint(8) unsigned NOT NULL DEFAULT '0',
  `user_status` tinyint(1) NOT NULL DEFAULT '0',
  `last_modified_dlstatus` timestamp NOT NULL DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
  KEY `user_topic` (`user_id`,`topic_id`),
  KEY `topic_id` (`topic_id`)
) ENGINE=MRG_MyISAM DEFAULT CHARSET=utf8 UNION=(`bb_bt_dlstatus_main`,`bb_bt_dlstatus_new`);

-- --------------------------------------------------------

--
-- Структура таблицы `bb_bt_dlstatus_new`
--

CREATE TABLE IF NOT EXISTS `bb_bt_dlstatus_new` (
  `user_id` mediumint(9) NOT NULL DEFAULT '0',
  `topic_id` mediumint(8) unsigned NOT NULL DEFAULT '0',
  `user_status` tinyint(1) NOT NULL DEFAULT '0',
  `last_modified_dlstatus` timestamp NOT NULL DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
  PRIMARY KEY (`user_id`,`topic_id`),
  KEY `topic_id` (`topic_id`)
) ENGINE=MyISAM DEFAULT CHARSET=utf8;

-- --------------------------------------------------------

--
-- Структура таблицы `bb_bt_dlstatus_snap`
--

CREATE TABLE IF NOT EXISTS `bb_bt_dlstatus_snap` (
  `topic_id` mediumint(8) unsigned NOT NULL DEFAULT '0',
  `dl_status` tinyint(4) NOT NULL DEFAULT '0',
  `users_count` smallint(5) unsigned NOT NULL DEFAULT '0',
  KEY `topic_id` (`topic_id`)
) ENGINE=MyISAM DEFAULT CHARSET=utf8;

-- --------------------------------------------------------

--
-- Структура таблицы `bb_bt_last_torstat`
--

CREATE TABLE IF NOT EXISTS `bb_bt_last_torstat` (
  `topic_id` mediumint(8) unsigned NOT NULL DEFAULT '0',
  `user_id` mediumint(9) NOT NULL DEFAULT '0',
  `dl_status` tinyint(1) NOT NULL DEFAULT '0',
  `up_add` bigint(20) unsigned NOT NULL DEFAULT '0',
  `down_add` bigint(20) unsigned NOT NULL DEFAULT '0',
  `release_add` bigint(20) unsigned NOT NULL DEFAULT '0',
  `bonus_add` bigint(20) unsigned NOT NULL DEFAULT '0',
  `speed_up` bigint(20) unsigned NOT NULL DEFAULT '0',
  `speed_down` bigint(20) unsigned NOT NULL DEFAULT '0',
  PRIMARY KEY USING BTREE (`topic_id`,`user_id`)
) ENGINE=MyISAM DEFAULT CHARSET=utf8;

-- --------------------------------------------------------

--
-- Структура таблицы `bb_bt_last_userstat`
--

CREATE TABLE IF NOT EXISTS `bb_bt_last_userstat` (
  `user_id` mediumint(9) NOT NULL DEFAULT '0',
  `up_add` bigint(20) unsigned NOT NULL DEFAULT '0',
  `down_add` bigint(20) unsigned NOT NULL DEFAULT '0',
  `release_add` bigint(20) unsigned NOT NULL DEFAULT '0',
  `bonus_add` bigint(20) unsigned NOT NULL DEFAULT '0',
  `speed_up` bigint(20) unsigned NOT NULL DEFAULT '0',
  `speed_down` bigint(20) unsigned NOT NULL DEFAULT '0',
  PRIMARY KEY (`user_id`)
) ENGINE=MyISAM DEFAULT CHARSET=utf8;

-- --------------------------------------------------------

--
-- Структура таблицы `bb_bt_torhelp`
--

CREATE TABLE IF NOT EXISTS `bb_bt_torhelp` (
  `user_id` mediumint(9) NOT NULL DEFAULT '0',
  `topic_id_csv` text NOT NULL,
  PRIMARY KEY (`user_id`)
) ENGINE=MyISAM DEFAULT CHARSET=utf8;

-- --------------------------------------------------------

--
-- Структура таблицы `bb_bt_torrents`
--

CREATE TABLE IF NOT EXISTS `bb_bt_torrents` (
  `info_hash` varbinary(20) NOT NULL,
  `post_id` mediumint(8) unsigned NOT NULL DEFAULT '0',
  `poster_id` mediumint(9) NOT NULL DEFAULT '0',
  `topic_id` mediumint(8) unsigned NOT NULL DEFAULT '0',
  `forum_id` smallint(5) unsigned NOT NULL DEFAULT '0',
  `attach_id` mediumint(8) unsigned NOT NULL DEFAULT '0',
  `size` bigint(20) unsigned NOT NULL DEFAULT '0',
  `reg_time` int(11) NOT NULL DEFAULT '0',
  `call_seed_time` int(11) NOT NULL DEFAULT '0',
  `complete_count` mediumint(8) unsigned NOT NULL DEFAULT '0',
  `seeder_last_seen` int(11) NOT NULL DEFAULT '0',
  `tor_status` tinyint(4) NOT NULL DEFAULT '0',
  `checked_user_id` mediumint(8) NOT NULL DEFAULT '0',
  `checked_time` int(11) NOT NULL DEFAULT '0',
  `tor_type` tinyint(1) NOT NULL DEFAULT '0',
  `speed_up` int(11) NOT NULL,
  `speed_down` int(11) NOT NULL,
  PRIMARY KEY (`info_hash`),
  UNIQUE KEY `post_id` (`post_id`),
  UNIQUE KEY `topic_id` (`topic_id`),
  UNIQUE KEY `attach_id` (`attach_id`),
  KEY `reg_time` (`reg_time`),
  KEY `forum_id` (`forum_id`),
  KEY `poster_id` (`poster_id`)
) ENGINE=MyISAM DEFAULT CHARSET=utf8;

-- --------------------------------------------------------

--
-- Структура таблицы `bb_bt_torrents_del`
--

CREATE TABLE IF NOT EXISTS `bb_bt_torrents_del` (
  `topic_id` mediumint(8) unsigned NOT NULL,
  `info_hash` varbinary(20) NOT NULL,
  `is_del` tinyint(4) NOT NULL DEFAULT '1',
  `dl_percent` tinyint(4) NOT NULL DEFAULT '100',
  PRIMARY KEY (`topic_id`)
) ENGINE=MyISAM DEFAULT CHARSET=utf8;

-- --------------------------------------------------------

--
-- Структура таблицы `bb_bt_torstat`
--

CREATE TABLE IF NOT EXISTS `bb_bt_torstat` (
  `topic_id` mediumint(8) unsigned NOT NULL DEFAULT '0',
  `user_id` mediumint(9) NOT NULL DEFAULT '0',
  `last_modified_torstat` timestamp NOT NULL DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
  `completed` tinyint(1) NOT NULL DEFAULT '0',
  PRIMARY KEY (`topic_id`,`user_id`)
) ENGINE=MyISAM DEFAULT CHARSET=utf8;

-- --------------------------------------------------------

--
-- Структура таблицы `bb_bt_tor_dl_stat`
--

CREATE TABLE IF NOT EXISTS `bb_bt_tor_dl_stat` (
  `topic_id` mediumint(8) unsigned NOT NULL DEFAULT '0',
  `user_id` mediumint(9) NOT NULL DEFAULT '0',
  `attach_id` mediumint(8) unsigned NOT NULL DEFAULT '0',
  `t_up_total` bigint(20) unsigned NOT NULL DEFAULT '0',
  `t_down_total` bigint(20) unsigned NOT NULL DEFAULT '0',
  `t_bonus_total` bigint(20) unsigned NOT NULL DEFAULT '0',
  PRIMARY KEY (`topic_id`,`user_id`)
) ENGINE=MyISAM DEFAULT CHARSET=utf8;

-- --------------------------------------------------------

--
-- Структура таблицы `bb_bt_tracker`
--

CREATE TABLE IF NOT EXISTS `bb_bt_tracker` (
  `peer_hash` varchar(32) CHARACTER SET utf8 COLLATE utf8_bin NOT NULL DEFAULT '',
  `topic_id` mediumint(8) unsigned NOT NULL DEFAULT '0',
  `peer_id` varchar(20) NOT NULL,
  `user_id` mediumint(9) NOT NULL DEFAULT '0',
  `ip` char(8) CHARACTER SET utf8 COLLATE utf8_bin NOT NULL DEFAULT '0',
  `ipv6` varchar(32) DEFAULT NULL,
  `port` smallint(5) unsigned NOT NULL DEFAULT '0',
  `seeder` tinyint(1) NOT NULL DEFAULT '0',
  `releaser` tinyint(1) NOT NULL DEFAULT '0',
  `tor_type` tinyint(1) NOT NULL DEFAULT '0',
  `uploaded` bigint(20) unsigned NOT NULL DEFAULT '0',
  `downloaded` bigint(20) unsigned NOT NULL DEFAULT '0',
  `remain` bigint(20) unsigned NOT NULL DEFAULT '0',
  `speed_up` mediumint(8) unsigned NOT NULL DEFAULT '0',
  `speed_down` mediumint(8) unsigned NOT NULL DEFAULT '0',
  `up_add` bigint(20) unsigned NOT NULL DEFAULT '0',
  `down_add` bigint(20) unsigned NOT NULL DEFAULT '0',
  `update_time` int(11) NOT NULL DEFAULT '0',
  `xbt_error` varchar(200) DEFAULT NULL,
  `ul_gdc` bigint(20) unsigned NOT NULL DEFAULT '0',
  `ul_gdc_c` mediumint(9) unsigned NOT NULL DEFAULT '0',
  `ul_16k_c` mediumint(9) unsigned NOT NULL DEFAULT '0',
  `ul_eq_dl` mediumint(9) unsigned NOT NULL DEFAULT '0',
  `complete_percent` bigint(20) NOT NULL DEFAULT '0',
  PRIMARY KEY (`peer_hash`),
  KEY `topic_id` (`topic_id`),
  KEY `user_id` (`user_id`)
) ENGINE=MyISAM DEFAULT CHARSET=utf8;

-- --------------------------------------------------------

--
-- Структура таблицы `bb_bt_tracker_snap`
--

CREATE TABLE IF NOT EXISTS `bb_bt_tracker_snap` (
  `topic_id` mediumint(8) unsigned NOT NULL DEFAULT '0',
  `seeders` mediumint(8) unsigned NOT NULL DEFAULT '0',
  `leechers` mediumint(8) unsigned NOT NULL DEFAULT '0',
  `speed_up` int(10) unsigned NOT NULL DEFAULT '0',
  `speed_down` int(10) unsigned NOT NULL DEFAULT '0',
  PRIMARY KEY (`topic_id`)
) ENGINE=MyISAM DEFAULT CHARSET=utf8;

-- --------------------------------------------------------

--
-- Структура таблицы `bb_bt_users`
--

CREATE TABLE IF NOT EXISTS `bb_bt_users` (
  `user_id` mediumint(9) NOT NULL DEFAULT '0',
  `auth_key` char(10) CHARACTER SET utf8 COLLATE utf8_bin NOT NULL DEFAULT '',
  `u_up_total` bigint(20) unsigned NOT NULL DEFAULT '0',
  `u_down_total` bigint(20) unsigned NOT NULL DEFAULT '0',
  `u_up_release` bigint(20) unsigned NOT NULL DEFAULT '0',
  `u_up_bonus` bigint(20) unsigned NOT NULL DEFAULT '0',
  `up_today` bigint(20) unsigned NOT NULL DEFAULT '0',
  `down_today` bigint(20) unsigned NOT NULL DEFAULT '0',
  `up_release_today` bigint(20) unsigned NOT NULL DEFAULT '0',
  `up_bonus_today` bigint(20) unsigned NOT NULL DEFAULT '0',
  `points_today` float(16,2) unsigned NOT NULL DEFAULT '0.00',
  `up_yesterday` bigint(20) unsigned NOT NULL DEFAULT '0',
  `down_yesterday` bigint(20) unsigned NOT NULL DEFAULT '0',
  `up_release_yesterday` bigint(20) unsigned NOT NULL DEFAULT '0',
  `up_bonus_yesterday` bigint(20) unsigned NOT NULL DEFAULT '0',
  `points_yesterday` float(16,2) unsigned NOT NULL DEFAULT '0.00',
  PRIMARY KEY (`user_id`),
  UNIQUE KEY `auth_key` (`auth_key`)
) ENGINE=MyISAM DEFAULT CHARSET=utf8;

-- --------------------------------------------------------

--
-- Структура таблицы `bb_bt_user_settings`
--

CREATE TABLE IF NOT EXISTS `bb_bt_user_settings` (
  `user_id` mediumint(9) NOT NULL DEFAULT '0',
  `tor_search_set` text NOT NULL,
  `last_modified` int(11) NOT NULL DEFAULT '0',
  PRIMARY KEY (`user_id`)
) ENGINE=MyISAM DEFAULT CHARSET=utf8;

-- --------------------------------------------------------

--
-- Структура таблицы `bb_captcha`
--

CREATE TABLE IF NOT EXISTS `bb_captcha` (
  `cap_id` int(10) NOT NULL,
  `cap_code` char(6) NOT NULL,
  `cap_expire` int(11) NOT NULL DEFAULT '0',
  PRIMARY KEY (`cap_id`)
) ENGINE=MyISAM DEFAULT CHARSET=utf8;

-- --------------------------------------------------------

--
-- Структура таблицы `bb_categories`
--

CREATE TABLE IF NOT EXISTS `bb_categories` (
  `cat_id` smallint(5) unsigned NOT NULL AUTO_INCREMENT,
  `cat_title` varchar(100) NOT NULL DEFAULT '',
  `cat_order` smallint(5) unsigned NOT NULL DEFAULT '0',
  PRIMARY KEY (`cat_id`),
  KEY `cat_order` (`cat_order`)
) ENGINE=MyISAM  DEFAULT CHARSET=utf8;

--
-- Дамп данных таблицы `bb_categories`
--

INSERT INTO `bb_categories` VALUES (1, 'Ваша первая категория', 10);

-- --------------------------------------------------------

--
-- Структура таблицы `bb_config`
--

CREATE TABLE IF NOT EXISTS `bb_config` (
  `config_name` varchar(255) NOT NULL DEFAULT '',
  `config_value` text NOT NULL,
  PRIMARY KEY (`config_name`)
) ENGINE=MyISAM DEFAULT CHARSET=utf8;

--
-- Дамп данных таблицы `bb_config`
--

INSERT INTO `bb_config` VALUES ('allow_autologin', '1');
INSERT INTO `bb_config` VALUES ('allow_avatar_local', '1');
INSERT INTO `bb_config` VALUES ('allow_avatar_remote', '0');
INSERT INTO `bb_config` VALUES ('allow_avatar_upload', '1');
INSERT INTO `bb_config` VALUES ('allow_bbcode', '1');
INSERT INTO `bb_config` VALUES ('allow_namechange', '0');
INSERT INTO `bb_config` VALUES ('allow_sig', '1');
INSERT INTO `bb_config` VALUES ('allow_smilies', '1');
INSERT INTO `bb_config` VALUES ('avatar_filesize', '100000');
INSERT INTO `bb_config` VALUES ('avatar_gallery_path', 'images/avatars/gallery');
INSERT INTO `bb_config` VALUES ('avatar_max_height', '100');
INSERT INTO `bb_config` VALUES ('avatar_max_width', '100');
INSERT INTO `bb_config` VALUES ('avatar_path', 'images/avatars');
INSERT INTO `bb_config` VALUES ('board_disable', '0');
INSERT INTO `bb_config` VALUES ('board_email', 'board_email@yourdomain.com');
INSERT INTO `bb_config` VALUES ('board_email_form', '0');
INSERT INTO `bb_config` VALUES ('board_email_sig', 'Thanks, The Management');
INSERT INTO `bb_config` VALUES ('board_startdate', '');
INSERT INTO `bb_config` VALUES ('board_timezone', '0');
INSERT INTO `bb_config` VALUES ('bonus_upload', '');
INSERT INTO `bb_config` VALUES ('bonus_upload_price', '');
INSERT INTO `bb_config` VALUES ('birthday_enabled', '1');
INSERT INTO `bb_config` VALUES ('birthday_max_age', '99');
INSERT INTO `bb_config` VALUES ('birthday_min_age', '10');
INSERT INTO `bb_config` VALUES ('birthday_check_day', '7');
INSERT INTO `bb_config` VALUES ('bt_add_auth_key', '1');
INSERT INTO `bb_config` VALUES ('bt_add_comment', '');
INSERT INTO `bb_config` VALUES ('bt_add_publisher', 'YourSiteName');
INSERT INTO `bb_config` VALUES ('bt_allow_spmode_change', '1');
INSERT INTO `bb_config` VALUES ('bt_announce_url', 'http://yourdomain.com/bt/announce.php');
INSERT INTO `bb_config` VALUES ('bt_disable_dht', '0');
INSERT INTO `bb_config` VALUES ('bt_check_announce_url', '0');
INSERT INTO `bb_config` VALUES ('bt_del_addit_ann_urls', '1');
INSERT INTO `bb_config` VALUES ('bt_dl_list_only_1st_page', '1');
INSERT INTO `bb_config` VALUES ('bt_dl_list_only_count', '1');
INSERT INTO `bb_config` VALUES ('bt_gen_passkey_on_reg', '1');
INSERT INTO `bb_config` VALUES ('bt_newtopic_auto_reg', '1');
INSERT INTO `bb_config` VALUES ('bt_replace_ann_url', '1');
INSERT INTO `bb_config` VALUES ('bt_search_bool_mode', '1');
INSERT INTO `bb_config` VALUES ('bt_set_dltype_on_tor_reg', '1');
INSERT INTO `bb_config` VALUES ('bt_show_dl_but_cancel', '1');
INSERT INTO `bb_config` VALUES ('bt_show_dl_but_compl', '1');
INSERT INTO `bb_config` VALUES ('bt_show_dl_but_down', '0');
INSERT INTO `bb_config` VALUES ('bt_show_dl_but_will', '1');
INSERT INTO `bb_config` VALUES ('bt_show_dl_list', '0');
INSERT INTO `bb_config` VALUES ('bt_show_dl_list_buttons', '1');
INSERT INTO `bb_config` VALUES ('bt_show_dl_stat_on_index', '1');
INSERT INTO `bb_config` VALUES ('bt_show_ip_only_moder', '1');
INSERT INTO `bb_config` VALUES ('bt_show_peers', '1');
INSERT INTO `bb_config` VALUES ('bt_show_peers_mode', '1');
INSERT INTO `bb_config` VALUES ('bt_show_port_only_moder', '1');
INSERT INTO `bb_config` VALUES ('bt_tor_browse_only_reg', '0');
INSERT INTO `bb_config` VALUES ('bt_unset_dltype_on_tor_unreg', '0');
INSERT INTO `bb_config` VALUES ('config_id', '1');
INSERT INTO `bb_config` VALUES ('cron_last_check', '1211477514');
INSERT INTO `bb_config` VALUES ('default_dateformat', 'Y-m-d H:i');
INSERT INTO `bb_config` VALUES ('default_lang', 'russian');
INSERT INTO `bb_config` VALUES ('flood_interval', '15');
INSERT INTO `bb_config` VALUES ('hot_threshold', '300');
INSERT INTO `bb_config` VALUES ('login_reset_time', '30');
INSERT INTO `bb_config` VALUES ('max_autologin_time', '10');
INSERT INTO `bb_config` VALUES ('max_inbox_privmsgs', '200');
INSERT INTO `bb_config` VALUES ('max_login_attempts', '5');
INSERT INTO `bb_config` VALUES ('max_poll_options', '6');
INSERT INTO `bb_config` VALUES ('max_savebox_privmsgs', '50');
INSERT INTO `bb_config` VALUES ('max_sentbox_privmsgs', '25');
INSERT INTO `bb_config` VALUES ('max_sig_chars', '255');
INSERT INTO `bb_config` VALUES ('posts_per_page', '15');
INSERT INTO `bb_config` VALUES ('privmsg_disable', '0');
INSERT INTO `bb_config` VALUES ('prune_enable', '1');
INSERT INTO `bb_config` VALUES ('record_online_date', '1211477508');
INSERT INTO `bb_config` VALUES ('record_online_users', '2');
INSERT INTO `bb_config` VALUES ('require_activation', '0');
INSERT INTO `bb_config` VALUES ('sendmail_fix', '0');
INSERT INTO `bb_config` VALUES ('seed_bonus_enabled', '1');
INSERT INTO `bb_config` VALUES ('seed_bonus_release', '');
INSERT INTO `bb_config` VALUES ('seed_bonus_points', '');
INSERT INTO `bb_config` VALUES ('seed_bonus_tor_size', '0');
INSERT INTO `bb_config` VALUES ('seed_bonus_user_regdate', '0');
INSERT INTO `bb_config` VALUES ('site_desc', 'A _little_ text to describe your forum');
INSERT INTO `bb_config` VALUES ('sitename', 'TorrentPier II - Torrent Tracker');
INSERT INTO `bb_config` VALUES ('smilies_path', 'images/smiles');
INSERT INTO `bb_config` VALUES ('smtp_delivery', '0');
INSERT INTO `bb_config` VALUES ('smtp_host', '');
INSERT INTO `bb_config` VALUES ('smtp_password', '');
INSERT INTO `bb_config` VALUES ('smtp_username', '');
INSERT INTO `bb_config` VALUES ('topics_per_page', '50');
INSERT INTO `bb_config` VALUES ('version', '.0.22');
INSERT INTO `bb_config` VALUES ('xs_add_comments', '0');
INSERT INTO `bb_config` VALUES ('xs_auto_compile', '1');
INSERT INTO `bb_config` VALUES ('xs_auto_recompile', '1');
INSERT INTO `bb_config` VALUES ('xs_php', 'php');
INSERT INTO `bb_config` VALUES ('xs_shownav', '17');
INSERT INTO `bb_config` VALUES ('xs_template_time', '0');
INSERT INTO `bb_config` VALUES ('xs_use_cache', '1');
INSERT INTO `bb_config` VALUES ('xs_version', '8');
INSERT INTO `bb_config` VALUES ('active_ads', '');
INSERT INTO `bb_config` VALUES ('report_subject_auth', '1');
INSERT INTO `bb_config` VALUES ('report_modules_cache', '1');
INSERT INTO `bb_config` VALUES ('report_hack_count', '0');
INSERT INTO `bb_config` VALUES ('report_notify', '0');
INSERT INTO `bb_config` VALUES ('report_list_admin', '0');
INSERT INTO `bb_config` VALUES ('report_new_window', '0');
INSERT INTO `bb_config` VALUES ('torrent_pass_private_key', 'вставить_из_конфига_XBTT');
INSERT INTO `bb_config` VALUES ('cron_enabled', '1');
INSERT INTO `bb_config` VALUES ('cron_check_interval', '300');
INSERT INTO `bb_config` VALUES ('reports_enabled', '1');
INSERT INTO `bb_config` VALUES ('gallery_enabled', '1');
INSERT INTO `bb_config` VALUES ('pic_dir', 'pictures/');
INSERT INTO `bb_config` VALUES ('pic_max_size', '3');
INSERT INTO `bb_config` VALUES ('auto_delete_posted_pics', '1');
INSERT INTO `bb_config` VALUES ('magnet_links_enabled', '1');
INSERT INTO `bb_config` VALUES ('no_avatar', 'images/avatars/gallery/noavatar.png');
INSERT INTO `bb_config` VALUES ('gender', '1');
INSERT INTO `bb_config` VALUES ('callseed', '0');
INSERT INTO `bb_config` VALUES ('tor_stats', '1');
INSERT INTO `bb_config` VALUES ('show_latest_news', '1');
INSERT INTO `bb_config` VALUES ('max_news_title', '50');
INSERT INTO `bb_config` VALUES ('latest_news_count', '5');
INSERT INTO `bb_config` VALUES ('latest_news_forum_id', '1');
INSERT INTO `bb_config` VALUES ('show_network_news', '1');
INSERT INTO `bb_config` VALUES ('max_net_title', '50');
INSERT INTO `bb_config` VALUES ('network_news_count', '5');
INSERT INTO `bb_config` VALUES ('network_news_forum_id', '2');
INSERT INTO `bb_config` VALUES ('whois_info', 'http://ip-whois.net/ip_geo.php?ip=');
INSERT INTO `bb_config` VALUES ('show_mod_index', '1');
INSERT INTO `bb_config` VALUES ('premod', '0');
INSERT INTO `bb_config` VALUES ('new_tpls', '1');
INSERT INTO `bb_config` VALUES ('tor_comment', '1');

-- --------------------------------------------------------

--
-- Структура таблицы `bb_cron`
--

CREATE TABLE IF NOT EXISTS `bb_cron` (
  `cron_id` smallint(5) unsigned NOT NULL AUTO_INCREMENT,
  `cron_active` tinyint(4) NOT NULL DEFAULT '1',
  `cron_title` char(120) NOT NULL DEFAULT '',
  `cron_script` char(120) NOT NULL DEFAULT '',
  `schedule` enum('hourly','daily','weekly','monthly','interval') NOT NULL DEFAULT 'daily',
  `run_day` enum('1','2','3','4','5','6','7','8','9','10','11','12','13','14','15','16','17','18','19','20','21','22','23','24','25','26','27','28') DEFAULT NULL,
  `run_time` time DEFAULT '04:00:00',
  `run_order` tinyint(4) unsigned NOT NULL,
  `last_run` datetime NOT NULL DEFAULT '0000-00-00 00:00:00',
  `next_run` datetime NOT NULL DEFAULT '0000-00-00 00:00:00',
  `run_interval` time DEFAULT NULL,
  `log_enabled` tinyint(1) NOT NULL DEFAULT '0',
  `log_file` char(120) NOT NULL DEFAULT '',
  `log_sql_queries` tinyint(4) NOT NULL DEFAULT '0',
  `disable_board` tinyint(1) NOT NULL DEFAULT '0',
  `run_counter` bigint(20) unsigned NOT NULL DEFAULT '0',
  PRIMARY KEY (`cron_id`),
  UNIQUE KEY `title` (`cron_title`),
  UNIQUE KEY `script` (`cron_script`)
) ENGINE=MyISAM  DEFAULT CHARSET=utf8;

--
-- Дамп данных таблицы `bb_cron`
--

INSERT INTO `bb_cron` VALUES (1, 0, 'Site backup', 'site_backup.php', 'daily', '1', '05:00:00', 10, '0000-00-00 00:00:00', '0000-00-00 00:00:00', NULL, 1, '', 0, 1, 0);
INSERT INTO `bb_cron` VALUES (2, 0, 'DB backup', 'db_backup.php', 'daily', '1', '05:00:00', 20, '0000-00-00 00:00:00', '0000-00-00 00:00:00', NULL, 1, '', 0, 1, 0);
INSERT INTO `bb_cron` VALUES (3, 1, 'Avatars cleanup', 'avatars_cleanup.php', 'weekly', '1', '05:00:00', 30, '0000-00-00 00:00:00', '0000-00-00 00:00:00', NULL, 1, '', 0, 1, 0);
INSERT INTO `bb_cron` VALUES (4, 1, 'Board maintenance', 'bb_maintenance.php', 'daily', NULL, '05:00:00', 40, '0000-00-00 00:00:00', '0000-00-00 00:00:00', NULL, 1, '', 0, 1, 0);
INSERT INTO `bb_cron` VALUES (5, 1, 'Prune forums', 'prune_forums.php', 'daily', NULL, '05:00:00', 50, '0000-00-00 00:00:00', '0000-00-00 00:00:00', NULL, 1, '', 0, 1, 0);
INSERT INTO `bb_cron` VALUES (6, 1, 'Prune topic moved stubs', 'prune_topic_moved.php', 'daily', NULL, '05:00:00', 60, '0000-00-00 00:00:00', '0000-00-00 00:00:00', NULL, 1, '', 0, 1, 0);
INSERT INTO `bb_cron` VALUES (7, 1, 'Logs cleanup', 'clean_log.php', 'daily', NULL, '05:00:00', 70, '0000-00-00 00:00:00', '0000-00-00 00:00:00', NULL, 1, '', 0, 1, 0);
INSERT INTO `bb_cron` VALUES (8, 1, 'Tracker maintenance', 'tr_maintenance.php', 'daily', NULL, '05:00:00', 90, '0000-00-00 00:00:00', '0000-00-00 00:00:00', NULL, 1, '', 0, 1, 0);
INSERT INTO `bb_cron` VALUES (9, 1, 'Clean dlstat', 'clean_dlstat.php', 'daily', NULL, '05:00:00', 100, '0000-00-00 00:00:00', '0000-00-00 00:00:00', NULL, 1, '', 0, 1, 0);
INSERT INTO `bb_cron` VALUES (10, 1, 'Prune inactive users', 'prune_inactive_users.php', 'daily', NULL, '05:00:00', 110, '0000-00-00 00:00:00', '0000-00-00 00:00:00', NULL, 1, '', 0, 1, 0);
INSERT INTO `bb_cron` VALUES (11, 1, 'Sessions cleanup', 'sessions_cleanup.php', 'interval', NULL, NULL, 255, '0000-00-00 00:00:00', '0000-00-00 00:00:00', '00:03:00', 0, '', 0, 0, 0);
INSERT INTO `bb_cron` VALUES (12, 1, 'DS update ''cat_forums''', 'ds_update_cat_forums.php', 'interval', NULL, NULL, 255, '0000-00-00 00:00:00', '0000-00-00 00:00:00', '00:05:00', 0, '', 0, 0, 0);
INSERT INTO `bb_cron` VALUES (13, 1, 'DS update ''stats''', 'ds_update_stats.php', 'interval', NULL, NULL, 255, '0000-00-00 00:00:00', '0000-00-00 00:00:00', '00:10:00', 0, '', 0, 0, 0);
INSERT INTO `bb_cron` VALUES (14, 1, 'Flash topic view', 'flash_topic_view.php', 'interval', NULL, NULL, 255, '0000-00-00 00:00:00', '0000-00-00 00:00:00', '00:10:00', 0, '', 0, 0, 0);
INSERT INTO `bb_cron` VALUES (15, 1, 'Clean search results', 'clean_search_results.php', 'interval', NULL, NULL, 255, '0000-00-00 00:00:00', '0000-00-00 00:00:00', '00:10:00', 0, '', 0, 0, 0);
INSERT INTO `bb_cron` VALUES (16, 1, 'Tracker cleanup and dlstat', 'tr_cleanup_and_dlstat.php', 'interval', NULL, NULL, 20, '0000-00-00 00:00:00', '0000-00-00 00:00:00', '00:15:00', 0, '', 0, 0, 0);
INSERT INTO `bb_cron` VALUES (17, 1, 'Make tracker snapshot', 'tr_make_snapshot.php', 'interval', NULL, NULL, 10, '0000-00-00 00:00:00', '0000-00-00 00:00:00', '00:10:00', 0, '', 0, 0, 0);
INSERT INTO `bb_cron` VALUES (18, 1, 'Seeder last seen', 'tr_update_seeder_last_seen.php', 'interval', NULL, NULL, 255, '0000-00-00 00:00:00', '0000-00-00 00:00:00', '01:00:00', 0, '', 0, 0, 0);
INSERT INTO `bb_cron` VALUES (19, 1, 'Captcha', 'captcha_gen_gc.php', 'daily', NULL, '05:00:00', 120, '0000-00-00 00:00:00', '0000-00-00 00:00:00', NULL, 0, '', 0, 0, 0);
INSERT INTO `bb_cron` VALUES (20, 1, 'Tracker dl-complete count', 'tr_complete_count.php', 'interval', NULL, NULL, 255, '0000-00-00 00:00:00', '0000-00-00 00:00:00', '06:00:00', 0, '', 0, 0, 0);
INSERT INTO `bb_cron` VALUES (21, 1, 'Cache garbage collector', 'cache_gc.php', 'interval', NULL, NULL, 255, '0000-00-00 00:00:00', '0000-00-00 00:00:00', '00:05:00', 0, '', 0, 0, 0);

-- --------------------------------------------------------

--
-- Структура таблицы `bb_disallow`
--

CREATE TABLE IF NOT EXISTS `bb_disallow` (
  `disallow_id` mediumint(8) unsigned NOT NULL AUTO_INCREMENT,
  `disallow_username` varchar(25) NOT NULL DEFAULT '',
  PRIMARY KEY (`disallow_id`)
) ENGINE=MyISAM DEFAULT CHARSET=utf8;

-- --------------------------------------------------------

--
-- Структура таблицы `bb_extensions`
--

CREATE TABLE IF NOT EXISTS `bb_extensions` (
  `ext_id` mediumint(8) unsigned NOT NULL AUTO_INCREMENT,
  `group_id` mediumint(8) unsigned NOT NULL DEFAULT '0',
  `extension` varchar(100) NOT NULL DEFAULT '',
  `comment` varchar(100) NOT NULL DEFAULT '',
  PRIMARY KEY (`ext_id`)
) ENGINE=MyISAM  DEFAULT CHARSET=utf8;

--
-- Дамп данных таблицы `bb_extensions`
--

INSERT INTO `bb_extensions` VALUES (1, 1, 'gif', '');
INSERT INTO `bb_extensions` VALUES (2, 1, 'png', '');
INSERT INTO `bb_extensions` VALUES (3, 1, 'jpeg', '');
INSERT INTO `bb_extensions` VALUES (4, 1, 'jpg', '');
INSERT INTO `bb_extensions` VALUES (5, 1, 'tif', '');
INSERT INTO `bb_extensions` VALUES (6, 1, 'tga', '');
INSERT INTO `bb_extensions` VALUES (7, 2, 'gtar', '');
INSERT INTO `bb_extensions` VALUES (8, 2, 'gz', '');
INSERT INTO `bb_extensions` VALUES (9, 2, 'tar', '');
INSERT INTO `bb_extensions` VALUES (10, 2, 'zip', '');
INSERT INTO `bb_extensions` VALUES (11, 2, 'rar', '');
INSERT INTO `bb_extensions` VALUES (12, 2, 'ace', '');
INSERT INTO `bb_extensions` VALUES (13, 3, 'txt', '');
INSERT INTO `bb_extensions` VALUES (14, 3, 'c', '');
INSERT INTO `bb_extensions` VALUES (15, 3, 'h', '');
INSERT INTO `bb_extensions` VALUES (16, 3, 'cpp', '');
INSERT INTO `bb_extensions` VALUES (17, 3, 'hpp', '');
INSERT INTO `bb_extensions` VALUES (18, 3, 'diz', '');
INSERT INTO `bb_extensions` VALUES (19, 4, 'xls', '');
INSERT INTO `bb_extensions` VALUES (20, 4, 'doc', '');
INSERT INTO `bb_extensions` VALUES (21, 4, 'dot', '');
INSERT INTO `bb_extensions` VALUES (22, 4, 'pdf', '');
INSERT INTO `bb_extensions` VALUES (23, 4, 'ai', '');
INSERT INTO `bb_extensions` VALUES (24, 4, 'ps', '');
INSERT INTO `bb_extensions` VALUES (25, 4, 'ppt', '');
INSERT INTO `bb_extensions` VALUES (26, 5, 'rm', '');
INSERT INTO `bb_extensions` VALUES (27, 6, 'wma', '');
INSERT INTO `bb_extensions` VALUES (28, 7, 'swf', '');
INSERT INTO `bb_extensions` VALUES (29, 8, 'torrent', '');

-- --------------------------------------------------------

--
-- Структура таблицы `bb_extension_groups`
--

CREATE TABLE IF NOT EXISTS `bb_extension_groups` (
  `group_id` mediumint(8) NOT NULL AUTO_INCREMENT,
  `group_name` varchar(20) NOT NULL DEFAULT '',
  `cat_id` tinyint(2) NOT NULL DEFAULT '0',
  `allow_group` tinyint(1) NOT NULL DEFAULT '0',
  `download_mode` tinyint(1) unsigned NOT NULL DEFAULT '1',
  `upload_icon` varchar(100) NOT NULL DEFAULT '',
  `max_filesize` int(20) NOT NULL DEFAULT '0',
  `forum_permissions` text NOT NULL,
  PRIMARY KEY (`group_id`)
) ENGINE=MyISAM  DEFAULT CHARSET=utf8;

--
-- Дамп данных таблицы `bb_extension_groups`
--

INSERT INTO `bb_extension_groups` VALUES (1, 'Images', 1, 1, 1, '', 262144, '');
INSERT INTO `bb_extension_groups` VALUES (2, 'Archives', 0, 1, 1, '', 262144, '');
INSERT INTO `bb_extension_groups` VALUES (3, 'Plain Text', 0, 0, 1, '', 262144, '');
INSERT INTO `bb_extension_groups` VALUES (4, 'Documents', 0, 0, 1, '', 262144, '');
INSERT INTO `bb_extension_groups` VALUES (5, 'Real Media', 0, 0, 2, '', 262144, '');
INSERT INTO `bb_extension_groups` VALUES (6, 'Streams', 2, 0, 1, '', 262144, '');
INSERT INTO `bb_extension_groups` VALUES (7, 'Flash Files', 3, 0, 1, '', 262144, '');
INSERT INTO `bb_extension_groups` VALUES (8, 'Torrent', 0, 1, 1, '', 122880, '');

-- --------------------------------------------------------

--
-- Структура таблицы `bb_forums`
--

CREATE TABLE IF NOT EXISTS `bb_forums` (
  `forum_id` smallint(5) unsigned NOT NULL AUTO_INCREMENT,
  `cat_id` smallint(5) unsigned NOT NULL DEFAULT '0',
  `forum_name` varchar(150) NOT NULL DEFAULT '',
  `forum_desc` text NOT NULL,
  `forum_status` tinyint(4) NOT NULL DEFAULT '0',
  `forum_order` smallint(5) unsigned NOT NULL DEFAULT '1',
  `forum_posts` mediumint(8) unsigned NOT NULL DEFAULT '0',
  `forum_topics` mediumint(8) unsigned NOT NULL DEFAULT '0',
  `forum_last_post_id` mediumint(8) unsigned NOT NULL DEFAULT '0',
  `forum_tpl_id` smallint(6) NOT NULL DEFAULT '0',
  `prune_days` smallint(5) unsigned NOT NULL DEFAULT '0',
  `auth_view` tinyint(2) NOT NULL DEFAULT '0',
  `auth_read` tinyint(2) NOT NULL DEFAULT '0',
  `auth_post` tinyint(2) NOT NULL DEFAULT '0',
  `auth_reply` tinyint(2) NOT NULL DEFAULT '0',
  `auth_edit` tinyint(2) NOT NULL DEFAULT '0',
  `auth_delete` tinyint(2) NOT NULL DEFAULT '0',
  `auth_sticky` tinyint(2) NOT NULL DEFAULT '0',
  `auth_announce` tinyint(2) NOT NULL DEFAULT '0',
  `auth_vote` tinyint(2) NOT NULL DEFAULT '0',
  `auth_pollcreate` tinyint(2) NOT NULL DEFAULT '0',
  `auth_attachments` tinyint(2) NOT NULL DEFAULT '0',
  `auth_download` tinyint(2) NOT NULL DEFAULT '0',
  `allow_reg_tracker` tinyint(1) NOT NULL DEFAULT '0',
  `allow_porno_topic` tinyint(1) NOT NULL DEFAULT '0',
  `self_moderated` tinyint(1) NOT NULL DEFAULT '0',
  `forum_parent` smallint(5) unsigned NOT NULL DEFAULT '0',
  `show_on_index` tinyint(1) NOT NULL DEFAULT '1',
  `forum_display_sort` tinyint(1) NOT NULL DEFAULT '0',
  `forum_display_order` tinyint(1) NOT NULL DEFAULT '0',
  `topic_tpl_id` smallint(6) NOT NULL DEFAULT '0',
  PRIMARY KEY (`forum_id`),
  KEY `forums_order` (`forum_order`),
  KEY `cat_id` (`cat_id`),
  KEY `forum_last_post_id` (`forum_last_post_id`),
  KEY `forum_parent` (`forum_parent`)
) ENGINE=MyISAM  DEFAULT CHARSET=utf8;

--
-- Дамп данных таблицы `bb_forums`
--

INSERT INTO `bb_forums` VALUES (1, 1, 'Ваш первый форум', 'Описание вашего первого форума.', 0, 10, 1, 1, 1, 0, 0, 0, 0, 1, 1, 1, 1, 3, 3, 1, 1, 1, 1, 0, 0, 0, 0, 1, 0, 0, 0);

-- --------------------------------------------------------

--
-- Структура таблицы `bb_groups`
--

CREATE TABLE IF NOT EXISTS `bb_groups` (
  `group_id` mediumint(8) NOT NULL AUTO_INCREMENT,
  `group_time` int(11) NOT NULL DEFAULT '0',
  `group_type` tinyint(4) NOT NULL DEFAULT '1',
  `group_name` varchar(40) NOT NULL DEFAULT '',
  `group_description` varchar(255) NOT NULL DEFAULT '',
  `group_moderator` mediumint(8) NOT NULL DEFAULT '0',
  `group_single_user` tinyint(1) NOT NULL DEFAULT '1',
  PRIMARY KEY (`group_id`),
  KEY `group_single_user` (`group_single_user`)
) ENGINE=MyISAM DEFAULT CHARSET=utf8;

-- --------------------------------------------------------

--
-- Структура таблицы `bb_log`
--

CREATE TABLE IF NOT EXISTS `bb_log` (
  `log_type_id` mediumint(8) unsigned NOT NULL DEFAULT '0',
  `log_user_id` mediumint(9) NOT NULL DEFAULT '0',
  `log_username` varchar(25) NOT NULL DEFAULT '',
  `log_user_ip` varchar(32) CHARACTER SET utf8 COLLATE utf8_bin NOT NULL DEFAULT '',
  `log_forum_id` smallint(5) unsigned NOT NULL DEFAULT '0',
  `log_forum_id_new` smallint(5) unsigned NOT NULL DEFAULT '0',
  `log_topic_id` mediumint(8) unsigned NOT NULL DEFAULT '0',
  `log_topic_id_new` mediumint(8) unsigned NOT NULL DEFAULT '0',
  `log_topic_title` varchar(250) NOT NULL DEFAULT '',
  `log_topic_title_new` varchar(250) NOT NULL DEFAULT '',
  `log_time` int(11) NOT NULL DEFAULT '0',
  `log_msg` text NOT NULL,
  KEY `log_time` (`log_time`),
  FULLTEXT KEY `log_topic_title` (`log_topic_title`)
) ENGINE=MyISAM DEFAULT CHARSET=utf8;

-- --------------------------------------------------------

--
-- Структура таблицы `bb_posts`
--

CREATE TABLE IF NOT EXISTS `bb_posts` (
  `post_id` mediumint(8) unsigned NOT NULL AUTO_INCREMENT,
  `topic_id` mediumint(8) unsigned NOT NULL DEFAULT '0',
  `forum_id` smallint(5) unsigned NOT NULL DEFAULT '0',
  `poster_id` mediumint(8) NOT NULL DEFAULT '0',
  `post_time` int(11) NOT NULL DEFAULT '0',
  `poster_ip` char(32) CHARACTER SET utf8 COLLATE utf8_bin NOT NULL DEFAULT '',
  `post_username` varchar(25) NOT NULL DEFAULT '',
  `post_edit_time` int(11) NOT NULL DEFAULT '0',
  `post_edit_count` smallint(5) unsigned NOT NULL DEFAULT '0',
  `post_attachment` tinyint(1) NOT NULL DEFAULT '0',
  `post_reported` tinyint(1) NOT NULL DEFAULT '0',
  `user_post` tinyint(1) NOT NULL DEFAULT '1',
  `post_mod_comment` TEXT NOT NULL DEFAULT  '',
  `post_mod_comment_type` TINYINT( 1 ) NOT NULL DEFAULT  '0',
  `post_mc_mod_id` mediumint(8) NOT NULL,
  `post_mc_mod_name` varchar(25) NOT NULL DEFAULT '',
  PRIMARY KEY (`post_id`),
  KEY `topic_id` (`topic_id`),
  KEY `poster_id` (`poster_id`),
  KEY `post_time` (`post_time`),
  KEY `forum_id_post_time` (`forum_id`,`post_time`)
) ENGINE=MyISAM  DEFAULT CHARSET=utf8;

--
-- Дамп данных таблицы `bb_posts`
--

INSERT INTO `bb_posts` VALUES (1, 1, 1, 2, 1309421220, '', '', 0, 0, 0, 0, 1, '', 0, 0, '');

-- --------------------------------------------------------

--
-- Структура таблицы `bb_posts_html`
--

CREATE TABLE IF NOT EXISTS `bb_posts_html` (
  `post_id` mediumint(9) NOT NULL DEFAULT '0',
  `post_html_time` timestamp NOT NULL DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
  `post_html` mediumtext NOT NULL,
  PRIMARY KEY (`post_id`)
) ENGINE=MyISAM DEFAULT CHARSET=utf8;

-- --------------------------------------------------------

--
-- Структура таблицы `bb_posts_search`
--

CREATE TABLE IF NOT EXISTS `bb_posts_search` (
  `post_id` mediumint(8) unsigned NOT NULL DEFAULT '0',
  `search_words` text NOT NULL,
  PRIMARY KEY (`post_id`),
  FULLTEXT KEY `search_words` (`search_words`)
) ENGINE=MyISAM DEFAULT CHARSET=utf8;

-- --------------------------------------------------------

--
-- Структура таблицы `bb_posts_text`
--

CREATE TABLE IF NOT EXISTS `bb_posts_text` (
  `post_id` mediumint(8) unsigned NOT NULL DEFAULT '0',
  `post_text` text NOT NULL,
  PRIMARY KEY (`post_id`)
) ENGINE=MyISAM DEFAULT CHARSET=utf8;

--
-- Дамп данных таблицы `bb_posts_text`
--

INSERT INTO `bb_posts_text` VALUES (1, '[list]\n[*]Переделан поиск по топикам с выбором типа [none, mysql, sphinx] (только в tracker.php)\n[*]Удалены bbcode_uid и переход на class.bbcode\n[*]Изменён метод и способ кеширования (memcache, sqlite, db_sqlite, redis, eaccelerator, apc, xcache, filecache)\n[*]Изменён способ подключения к БД и отказ от глобальной переменной $db\n[*]Улучшенный дебагер\n[*]Полностью переписан и упрощен файл регистрации\n[*]Переписана капча (в том числе при быстром ответе у гостя)\n[*]Добавлена возможность выбора типа анонсера (xbt или php)\n[*]Удаление файлов torrent.php и torstatus.php и перенос их функций в ajax\n[*]Ajax цитирование, изменение, редактирование, удаление сообщений\n[/list]');

-- --------------------------------------------------------

--
-- Структура таблицы `bb_privmsgs`
--

CREATE TABLE IF NOT EXISTS `bb_privmsgs` (
  `privmsgs_id` mediumint(8) unsigned NOT NULL AUTO_INCREMENT,
  `privmsgs_type` tinyint(4) NOT NULL DEFAULT '0',
  `privmsgs_subject` varchar(255) NOT NULL DEFAULT '0',
  `privmsgs_from_userid` mediumint(8) NOT NULL DEFAULT '0',
  `privmsgs_to_userid` mediumint(8) NOT NULL DEFAULT '0',
  `privmsgs_date` int(11) NOT NULL DEFAULT '0',
  `privmsgs_ip` varchar(32) CHARACTER SET utf8 COLLATE utf8_bin NOT NULL DEFAULT '',
  `privmsgs_reported` tinyint(1) NOT NULL DEFAULT '0',
  PRIMARY KEY (`privmsgs_id`),
  KEY `privmsgs_from_userid` (`privmsgs_from_userid`),
  KEY `privmsgs_to_userid` (`privmsgs_to_userid`)
) ENGINE=MyISAM DEFAULT CHARSET=utf8;

-- --------------------------------------------------------

--
-- Структура таблицы `bb_privmsgs_text`
--

CREATE TABLE IF NOT EXISTS `bb_privmsgs_text` (
  `privmsgs_text_id` mediumint(8) unsigned NOT NULL DEFAULT '0',
  `privmsgs_text` text NOT NULL,
  PRIMARY KEY (`privmsgs_text_id`)
) ENGINE=MyISAM DEFAULT CHARSET=utf8;

-- --------------------------------------------------------

--
-- Структура таблицы `bb_quota_limits`
--

CREATE TABLE IF NOT EXISTS `bb_quota_limits` (
  `quota_limit_id` mediumint(8) unsigned NOT NULL AUTO_INCREMENT,
  `quota_desc` varchar(20) NOT NULL DEFAULT '',
  `quota_limit` bigint(20) unsigned NOT NULL DEFAULT '0',
  PRIMARY KEY (`quota_limit_id`)
) ENGINE=MyISAM  DEFAULT CHARSET=utf8;

--
-- Дамп данных таблицы `bb_quota_limits`
--

INSERT INTO `bb_quota_limits` VALUES (1, 'Low', 262144);
INSERT INTO `bb_quota_limits` VALUES (2, 'Medium', 10485760);
INSERT INTO `bb_quota_limits` VALUES (3, 'High', 15728640);

-- --------------------------------------------------------

--
-- Структура таблицы `bb_ranks`
--

CREATE TABLE IF NOT EXISTS `bb_ranks` (
  `rank_id` smallint(5) unsigned NOT NULL AUTO_INCREMENT,
  `rank_title` varchar(50) NOT NULL DEFAULT '',
  `rank_min` mediumint(8) NOT NULL DEFAULT '0',
  `rank_special` tinyint(1) NOT NULL DEFAULT '1',
  `rank_image` varchar(255) NOT NULL DEFAULT '',
  `rank_style` varchar(255) NOT NULL DEFAULT '',
  PRIMARY KEY (`rank_id`)
) ENGINE=MyISAM  DEFAULT CHARSET=utf8;

--
-- Дамп данных таблицы `bb_ranks`
--

INSERT INTO `bb_ranks` VALUES (1, 'Администратор', -1, 1, 'images/ranks/admin.png', 'colorAdmin');

-- --------------------------------------------------------

--
-- Структура таблицы `bb_reports`
--

CREATE TABLE IF NOT EXISTS `bb_reports` (
  `report_id` mediumint(8) unsigned NOT NULL AUTO_INCREMENT,
  `user_id` mediumint(8) NOT NULL,
  `report_time` int(11) NOT NULL,
  `report_last_change` mediumint(8) unsigned DEFAULT NULL,
  `report_module_id` mediumint(8) unsigned NOT NULL,
  `report_status` tinyint(1) NOT NULL,
  `report_reason_id` mediumint(8) unsigned NOT NULL,
  `report_subject` int(11) NOT NULL,
  `report_subject_data` mediumtext,
  `report_title` varchar(255) NOT NULL,
  `report_desc` text NOT NULL,
  PRIMARY KEY (`report_id`),
  KEY `user_id` (`user_id`),
  KEY `report_time` (`report_time`),
  KEY `report_type_id` (`report_module_id`),
  KEY `report_status` (`report_status`),
  KEY `report_reason_id` (`report_reason_id`),
  KEY `report_subject` (`report_subject`)
) ENGINE=MyISAM DEFAULT CHARSET=utf8;

-- --------------------------------------------------------

--
-- Структура таблицы `bb_reports_changes`
--

CREATE TABLE IF NOT EXISTS `bb_reports_changes` (
  `report_change_id` mediumint(8) unsigned NOT NULL AUTO_INCREMENT,
  `report_id` mediumint(8) unsigned NOT NULL,
  `user_id` mediumint(8) NOT NULL,
  `report_change_time` int(11) NOT NULL,
  `report_status` tinyint(1) NOT NULL,
  `report_change_comment` text NOT NULL,
  PRIMARY KEY (`report_change_id`),
  KEY `report_id` (`report_id`),
  KEY `user_id` (`user_id`),
  KEY `report_change_time` (`report_change_time`)
) ENGINE=MyISAM DEFAULT CHARSET=utf8;

-- --------------------------------------------------------

--
-- Структура таблицы `bb_reports_modules`
--

CREATE TABLE IF NOT EXISTS `bb_reports_modules` (
  `report_module_id` mediumint(8) unsigned NOT NULL AUTO_INCREMENT,
  `report_module_order` mediumint(8) unsigned NOT NULL,
  `report_module_notify` tinyint(1) NOT NULL,
  `report_module_prune` smallint(6) NOT NULL,
  `report_module_last_prune` int(11) DEFAULT NULL,
  `report_module_name` varchar(50) NOT NULL,
  `auth_write` tinyint(1) NOT NULL,
  `auth_view` tinyint(1) NOT NULL,
  `auth_notify` tinyint(1) NOT NULL,
  `auth_delete` tinyint(1) NOT NULL,
  PRIMARY KEY (`report_module_id`),
  KEY `report_module_order` (`report_module_order`),
  KEY `auth_view` (`auth_view`)
) ENGINE=MyISAM  DEFAULT CHARSET=utf8;

--
-- Дамп данных таблицы `bb_reports_modules`
--

INSERT INTO `bb_reports_modules` VALUES (1, 1, 0, 0, NULL, 'report_general', 0, 1, 1, 1);
INSERT INTO `bb_reports_modules` VALUES (2, 2, 0, 0, NULL, 'report_post', 0, 1, 1, 1);
INSERT INTO `bb_reports_modules` VALUES (3, 3, 0, 0, NULL, 'report_topic', 0, 1, 1, 1);
INSERT INTO `bb_reports_modules` VALUES (4, 4, 0, 0, NULL, 'report_user', 0, 1, 1, 1);
INSERT INTO `bb_reports_modules` VALUES (5, 5, 0, 0, NULL, 'report_privmsg', 0, 1, 1, 1);

-- --------------------------------------------------------

--
-- Структура таблицы `bb_reports_reasons`
--

CREATE TABLE IF NOT EXISTS `bb_reports_reasons` (
  `report_reason_id` mediumint(8) unsigned NOT NULL AUTO_INCREMENT,
  `report_module_id` mediumint(8) unsigned NOT NULL,
  `report_reason_order` mediumint(8) unsigned NOT NULL,
  `report_reason_desc` varchar(255) NOT NULL,
  PRIMARY KEY (`report_reason_id`),
  KEY `report_type_id` (`report_module_id`),
  KEY `report_reason_order` (`report_reason_order`)
) ENGINE=MyISAM DEFAULT CHARSET=utf8;

-- --------------------------------------------------------

--
-- Структура таблицы `bb_search_rebuild`
--

CREATE TABLE IF NOT EXISTS `bb_search_rebuild` (
  `rebuild_session_id` mediumint(8) unsigned NOT NULL AUTO_INCREMENT,
  `start_post_id` mediumint(8) unsigned NOT NULL DEFAULT '0',
  `end_post_id` mediumint(8) unsigned NOT NULL DEFAULT '0',
  `start_time` int(11) NOT NULL DEFAULT '0',
  `end_time` int(11) NOT NULL DEFAULT '0',
  `last_cycle_time` int(11) NOT NULL DEFAULT '0',
  `session_time` int(11) NOT NULL DEFAULT '0',
  `session_posts` mediumint(8) unsigned NOT NULL DEFAULT '0',
  `session_cycles` mediumint(8) unsigned NOT NULL DEFAULT '0',
  `search_size` int(10) unsigned NOT NULL DEFAULT '0',
  `rebuild_session_status` tinyint(1) NOT NULL DEFAULT '0',
  PRIMARY KEY (`rebuild_session_id`)
) ENGINE=MyISAM DEFAULT CHARSET=utf8;

-- --------------------------------------------------------

--
-- Структура таблицы `bb_search_results`
--

CREATE TABLE IF NOT EXISTS `bb_search_results` (
  `session_id` char(20) CHARACTER SET utf8 COLLATE utf8_bin NOT NULL DEFAULT '',
  `search_type` tinyint(4) NOT NULL DEFAULT '0',
  `search_id` varchar(12) CHARACTER SET utf8 COLLATE utf8_bin NOT NULL DEFAULT '',
  `search_time` int(11) NOT NULL DEFAULT '0',
  `search_settings` text NOT NULL,
  `search_array` text NOT NULL,
  PRIMARY KEY (`session_id`,`search_type`)
) ENGINE=MyISAM DEFAULT CHARSET=utf8;

-- --------------------------------------------------------

--
-- Структура таблицы `bb_sessions`
--

CREATE TABLE IF NOT EXISTS `bb_sessions` (
  `session_id` char(20) CHARACTER SET utf8 COLLATE utf8_bin NOT NULL DEFAULT '',
  `session_user_id` mediumint(8) NOT NULL DEFAULT '0',
  `session_start` int(11) NOT NULL DEFAULT '0',
  `session_time` int(11) NOT NULL DEFAULT '0',
  `session_ip` char(32) CHARACTER SET utf8 COLLATE utf8_bin NOT NULL DEFAULT '',
  `session_logged_in` tinyint(1) NOT NULL DEFAULT '0',
  `session_admin` tinyint(2) NOT NULL DEFAULT '0',
  PRIMARY KEY (`session_id`)
) ENGINE=MyISAM DEFAULT CHARSET=utf8;

-- --------------------------------------------------------

--
-- Структура таблицы `bb_smilies`
--

CREATE TABLE IF NOT EXISTS `bb_smilies` (
  `smilies_id` smallint(5) unsigned NOT NULL AUTO_INCREMENT,
  `code` varchar(50) NOT NULL DEFAULT '',
  `smile_url` varchar(100) NOT NULL DEFAULT '',
  `emoticon` varchar(75) NOT NULL DEFAULT '',
  PRIMARY KEY (`smilies_id`)
) ENGINE=MyISAM  DEFAULT CHARSET=utf8;

--
-- Дамп данных таблицы `bb_smilies`
--

INSERT INTO `bb_smilies` VALUES (1, ':aa:', 'aa.gif', 'aa');
INSERT INTO `bb_smilies` VALUES (2, ':ab:', 'ab.gif', 'ab');
INSERT INTO `bb_smilies` VALUES (3, ':ac:', 'ac.gif', 'ac');
INSERT INTO `bb_smilies` VALUES (4, ':ad:', 'ad.gif', 'ad');
INSERT INTO `bb_smilies` VALUES (5, ':ae:', 'ae.gif', 'ae');
INSERT INTO `bb_smilies` VALUES (6, ':af:', 'af.gif', 'af');
INSERT INTO `bb_smilies` VALUES (7, ':ag:', 'ag.gif', 'ag');
INSERT INTO `bb_smilies` VALUES (8, ':ah:', 'ah.gif', 'ah');
INSERT INTO `bb_smilies` VALUES (9, ':ai:', 'ai.gif', 'ai');
INSERT INTO `bb_smilies` VALUES (10, ':aj:', 'aj.gif', 'aj');
INSERT INTO `bb_smilies` VALUES (11, ':ak:', 'ak.gif', 'ak');
INSERT INTO `bb_smilies` VALUES (12, ':al:', 'al.gif', 'al');
INSERT INTO `bb_smilies` VALUES (13, ':am:', 'am.gif', 'am');
INSERT INTO `bb_smilies` VALUES (14, ':an:', 'an.gif', 'an');
INSERT INTO `bb_smilies` VALUES (15, ':ao:', 'ao.gif', 'ao');
INSERT INTO `bb_smilies` VALUES (16, ':ap:', 'ap.gif', 'ap');
INSERT INTO `bb_smilies` VALUES (17, ':aq:', 'aq.gif', 'aq');
INSERT INTO `bb_smilies` VALUES (18, ':ar:', 'ar.gif', 'ar');
INSERT INTO `bb_smilies` VALUES (19, ':as:', 'as.gif', 'as');
INSERT INTO `bb_smilies` VALUES (20, ':at:', 'at.gif', 'at');
INSERT INTO `bb_smilies` VALUES (21, ':au:', 'au.gif', 'au');
INSERT INTO `bb_smilies` VALUES (22, ':av:', 'av.gif', 'av');
INSERT INTO `bb_smilies` VALUES (23, ':aw:', 'aw.gif', 'aw');
INSERT INTO `bb_smilies` VALUES (24, ':ax:', 'ax.gif', 'ax');
INSERT INTO `bb_smilies` VALUES (25, ':ay:', 'ay.gif', 'ay');
INSERT INTO `bb_smilies` VALUES (26, ':az:', 'az.gif', 'az');
INSERT INTO `bb_smilies` VALUES (27, ':ba:', 'ba.gif', 'ba');
INSERT INTO `bb_smilies` VALUES (28, ':bb:', 'bb.gif', 'bb');
INSERT INTO `bb_smilies` VALUES (29, ':bc:', 'bc.gif', 'bc');
INSERT INTO `bb_smilies` VALUES (30, ':bd:', 'bd.gif', 'bd');
INSERT INTO `bb_smilies` VALUES (31, ':be:', 'be.gif', 'be');
INSERT INTO `bb_smilies` VALUES (32, ':bf:', 'bf.gif', 'bf');
INSERT INTO `bb_smilies` VALUES (33, ':bg:', 'bg.gif', 'bg');
INSERT INTO `bb_smilies` VALUES (34, ':bh:', 'bh.gif', 'bh');
INSERT INTO `bb_smilies` VALUES (35, ':bi:', 'bi.gif', 'bi');
INSERT INTO `bb_smilies` VALUES (36, ':bj:', 'bj.gif', 'bj');
INSERT INTO `bb_smilies` VALUES (37, ':bk:', 'bk.gif', 'bk');
INSERT INTO `bb_smilies` VALUES (38, ':bl:', 'bl.gif', 'bl');
INSERT INTO `bb_smilies` VALUES (39, ':bm:', 'bm.gif', 'bm');
INSERT INTO `bb_smilies` VALUES (40, ':bn:', 'bn.gif', 'bn');
INSERT INTO `bb_smilies` VALUES (41, ':bo:', 'bo.gif', 'bo');
INSERT INTO `bb_smilies` VALUES (42, ':bp:', 'bp.gif', 'bp');
INSERT INTO `bb_smilies` VALUES (43, ':bq:', 'bq.gif', 'bq');
INSERT INTO `bb_smilies` VALUES (44, ':br:', 'br.gif', 'br');
INSERT INTO `bb_smilies` VALUES (45, ':bs:', 'bs.gif', 'bs');
INSERT INTO `bb_smilies` VALUES (46, ':bt:', 'bt.gif', 'bt');
INSERT INTO `bb_smilies` VALUES (47, ':bu:', 'bu.gif', 'bu');
INSERT INTO `bb_smilies` VALUES (48, ':bv:', 'bv.gif', 'bv');
INSERT INTO `bb_smilies` VALUES (49, ':bw:', 'bw.gif', 'bw');
INSERT INTO `bb_smilies` VALUES (50, ':bx:', 'bx.gif', 'bx');
INSERT INTO `bb_smilies` VALUES (51, ':by:', 'by.gif', 'by');
INSERT INTO `bb_smilies` VALUES (52, ':bz:', 'bz.gif', 'bz');
INSERT INTO `bb_smilies` VALUES (53, ':ca:', 'ca.gif', 'ca');
INSERT INTO `bb_smilies` VALUES (54, ':cb:', 'cb.gif', 'cb');
INSERT INTO `bb_smilies` VALUES (55, ':cc:', 'cc.gif', 'cc');
INSERT INTO `bb_smilies` VALUES (56, ':cd:', 'cd.gif', 'cd');

-- --------------------------------------------------------

--
-- Структура таблицы `bb_topics`
--

CREATE TABLE IF NOT EXISTS `bb_topics` (
  `topic_id` mediumint(8) unsigned NOT NULL AUTO_INCREMENT,
  `forum_id` smallint(8) unsigned NOT NULL DEFAULT '0',
  `topic_title` varchar(250) NOT NULL DEFAULT '',
  `topic_poster` mediumint(8) NOT NULL DEFAULT '0',
  `topic_time` int(11) NOT NULL DEFAULT '0',
  `topic_views` mediumint(8) unsigned NOT NULL DEFAULT '0',
  `topic_replies` mediumint(8) unsigned NOT NULL DEFAULT '0',
  `topic_status` tinyint(3) NOT NULL DEFAULT '0',
  `topic_vote` tinyint(1) NOT NULL DEFAULT '0',
  `topic_type` tinyint(3) NOT NULL DEFAULT '0',
  `topic_first_post_id` mediumint(8) unsigned NOT NULL DEFAULT '0',
  `topic_last_post_id` mediumint(8) unsigned NOT NULL DEFAULT '0',
  `topic_moved_id` mediumint(8) unsigned NOT NULL DEFAULT '0',
  `topic_attachment` tinyint(1) NOT NULL DEFAULT '0',
  `topic_reported` tinyint(1) NOT NULL DEFAULT '0',
  `topic_dl_type` tinyint(1) NOT NULL DEFAULT '0',
  `topic_last_post_time` int(11) NOT NULL DEFAULT '0',
  `topic_show_first_post` tinyint(1) unsigned NOT NULL DEFAULT '0',
  `is_draft` TINYINT(1) UNSIGNED NOT NULL DEFAULT '0',
  PRIMARY KEY (`topic_id`),
  KEY `forum_id` (`forum_id`),
  KEY `topic_last_post_id` (`topic_last_post_id`),
  KEY `topic_last_post_time` (`topic_last_post_time`),
  FULLTEXT KEY `topic_title` (`topic_title`)
) ENGINE=MyISAM  DEFAULT CHARSET=utf8;

--
-- Дамп данных таблицы `bb_topics`
--

INSERT INTO `bb_topics` VALUES (1, 1, 'Добро пожаловать в TorrentPier II', 2, 1309421220, 2, 0, 0, 0, 0, 1, 1, 0, 0, 0, 0, 972086460, 0);

-- --------------------------------------------------------

--
-- Структура таблицы `bb_topics_watch`
--

CREATE TABLE IF NOT EXISTS `bb_topics_watch` (
  `topic_id` mediumint(8) unsigned NOT NULL DEFAULT '0',
  `user_id` mediumint(8) NOT NULL DEFAULT '0',
  `notify_status` tinyint(1) NOT NULL DEFAULT '0',
  KEY `topic_id` (`topic_id`),
  KEY `user_id` (`user_id`),
  KEY `notify_status` (`notify_status`)
) ENGINE=MyISAM DEFAULT CHARSET=utf8;

-- --------------------------------------------------------

--
-- Структура таблицы `bb_topic_templates`
--

CREATE TABLE IF NOT EXISTS `bb_topic_templates` (
  `tpl_id` smallint(6) NOT NULL AUTO_INCREMENT,
  `tpl_name` varchar(20) NOT NULL DEFAULT '',
  `tpl_script` varchar(30) NOT NULL DEFAULT '',
  `tpl_template` varchar(30) NOT NULL DEFAULT '',
  `tpl_desc` varchar(255) NOT NULL DEFAULT '',
  PRIMARY KEY (`tpl_id`),
  UNIQUE KEY `tpl_name` (`tpl_name`)
) ENGINE=MyISAM  DEFAULT CHARSET=utf8;

--
-- Дамп данных таблицы `bb_topic_templates`
--

INSERT INTO `bb_topic_templates` VALUES (1, 'video', 'video', 'video', 'Video (basic)');
INSERT INTO `bb_topic_templates` VALUES (2, 'video_home', 'video', 'video_home', 'Video (home)');
INSERT INTO `bb_topic_templates` VALUES (3, 'video_simple', 'video', 'video_simple', 'Video (simple)');
INSERT INTO `bb_topic_templates` VALUES (4, 'video_lesson', 'video', 'video_lesson', 'Video (lesson)');
INSERT INTO `bb_topic_templates` VALUES (5, 'games', 'games', 'games', 'Games');
INSERT INTO `bb_topic_templates` VALUES (6, 'games_ps', 'games', 'games_ps', 'Games PS/PS2');
INSERT INTO `bb_topic_templates` VALUES (7, 'games_psp', 'games', 'games_psp', 'Games PSP');
INSERT INTO `bb_topic_templates` VALUES (8, 'games_xbox', 'games', 'games_xbox', 'Games XBOX');
INSERT INTO `bb_topic_templates` VALUES (9, 'progs', 'progs', 'progs', 'Programs');
INSERT INTO `bb_topic_templates` VALUES (10, 'progs_mac', 'progs', 'progs_mac', 'Programs Mac OS');
INSERT INTO `bb_topic_templates` VALUES (11, 'music', 'music', 'music', 'Music');
INSERT INTO `bb_topic_templates` VALUES (12, 'books', 'books', 'books', 'Books');
INSERT INTO `bb_topic_templates` VALUES (13, 'audiobooks', 'audiobooks', 'audiobooks', 'Audiobooks');
INSERT INTO `bb_topic_templates` VALUES (14, 'sport', 'sport', 'sport', 'Sport');

-- --------------------------------------------------------

--
-- Структура таблицы `bb_topic_tpl`
--

CREATE TABLE `bb_topic_tpl` (
  `tpl_id` smallint(6) NOT NULL auto_increment,
  `tpl_name` varchar(60) NOT NULL default '',
  `tpl_src_form` text NOT NULL,
  `tpl_src_title` text NOT NULL,
  `tpl_src_msg` text NOT NULL,
  `tpl_comment` text NOT NULL,
  `tpl_rules_post_id` int(10) unsigned NOT NULL default '0',
  `tpl_last_edit_tm` int(11) NOT NULL default '0',
  `tpl_last_edit_by` int(11) NOT NULL default '0',
  PRIMARY KEY  (`tpl_id`),
  UNIQUE KEY `tpl_name` (`tpl_name`)
) ENGINE=MyISAM  DEFAULT CHARSET=utf8;

-- --------------------------------------------------------

--
-- Структура таблицы `bb_users`
--

CREATE TABLE IF NOT EXISTS `bb_users` (
  `user_id` mediumint(8) NOT NULL AUTO_INCREMENT,
  `user_active` tinyint(1) NOT NULL DEFAULT '1',
  `username` varchar(25) NOT NULL DEFAULT '',
  `user_password` varchar(32) CHARACTER SET utf8 COLLATE utf8_bin NOT NULL DEFAULT '',
  `user_session_time` int(11) NOT NULL DEFAULT '0',
  `user_lastvisit` int(11) NOT NULL DEFAULT '0',
  `user_last_ip` char(32) NOT NULL DEFAULT '',
  `user_regdate` int(11) NOT NULL,
  `user_reg_ip` char(32) NOT NULL DEFAULT '',
  `user_level` tinyint(4) NOT NULL DEFAULT '0',
  `user_posts` mediumint(8) unsigned NOT NULL DEFAULT '0',
  `user_timezone` decimal(5,2) NOT NULL DEFAULT '0.00',
  `user_lang` varchar(255) NOT NULL DEFAULT 'russian',
  `user_new_privmsg` smallint(5) unsigned NOT NULL DEFAULT '0',
  `user_unread_privmsg` smallint(5) unsigned NOT NULL DEFAULT '0',
  `user_last_privmsg` int(11) NOT NULL DEFAULT '0',
  `user_opt` int(11) NOT NULL DEFAULT '0',
  `user_rank` int(11) NOT NULL DEFAULT '0',
  `user_avatar` varchar(100) NOT NULL DEFAULT '',
  `user_avatar_type` tinyint(4) NOT NULL DEFAULT '0',
  `user_gender` tinyint(1) NOT NULL DEFAULT '0',
  `user_birthday` int(11) NOT NULL DEFAULT '0',
  `user_next_birthday_greeting` int(11) NOT NULL DEFAULT '0',
  `user_email` varchar(255) NOT NULL DEFAULT '',
  `user_skype` varchar(32) NOT NULL DEFAULT '',
  `user_icq` varchar(15) NOT NULL DEFAULT '',
  `user_website` varchar(100) NOT NULL DEFAULT '',
  `user_from` varchar(100) NOT NULL DEFAULT '',
  `user_sig` text NOT NULL,
  `user_occ` varchar(100) NOT NULL DEFAULT '',
  `user_interests` varchar(255) NOT NULL DEFAULT '',
  `user_actkey` varchar(32) NOT NULL DEFAULT '',
  `user_newpasswd` varchar(32) NOT NULL DEFAULT '',
  `ignore_srv_load` tinyint(1) NOT NULL DEFAULT '0',
  `autologin_id` varchar(12) CHARACTER SET utf8 COLLATE utf8_bin NOT NULL DEFAULT '',
  `user_newest_pm_id` mediumint(8) NOT NULL DEFAULT '0',
  `user_points` float(16,2) NOT NULL DEFAULT '0.00',
  PRIMARY KEY (`user_id`),
  KEY `username` (`username`(10)),
  KEY `user_email` (`user_email`(10)),
  KEY `user_level` (`user_level`)
) ENGINE=MyISAM  DEFAULT CHARSET=utf8;

--
-- Дамп данных таблицы `bb_users`
--

INSERT INTO `bb_users` VALUES (-1, 0, 'Anonymous', 'd41d8cd98f00b204e9800998ecf8427e', 0, 0, '0', 0, '0', 0, 5, 0.00, '', 0, 0, 0, 0, 0, '', 0, 0, 0, 0, '', '', '', '', '', '', '', '', '', '', 0, '', 0, 0);
INSERT INTO `bb_users` VALUES (2, 1, 'admin', 'c3284d0f94606de1fd2af172aba15bf3', 0, 0, '0', 0, '0', 1, 1, 4.00, '', 0, 0, 0, 304, 1, '', 1, 0, 0, 0, 'admin@admin.com', '', '', '', '', '', '', '', '', '', 0, '', 0, 0);
INSERT INTO `bb_users` VALUES (-746, 0, 'bot', 'd41d8cd98f00b204e9800998ecf8427e', 0, 0, '0', 0, '0', 0, 0, 0.00, '', 0, 0, 0, 144, 0, 'bot.gif', 1, 0, 0, 0, 'bot@bot.bot', '', '', '', '', '', '', '', '', '', 0, '', 0, 0);

-- --------------------------------------------------------

--
-- Структура таблицы `bb_user_group`
--

CREATE TABLE IF NOT EXISTS `bb_user_group` (
  `group_id` mediumint(8) NOT NULL DEFAULT '0',
  `user_id` mediumint(8) NOT NULL DEFAULT '0',
  `user_pending` tinyint(1) NOT NULL DEFAULT '0',
  `user_time` int(11) NOT NULL,
  PRIMARY KEY (`group_id`,`user_id`),
  KEY `user_id` (`user_id`)
) ENGINE=MyISAM DEFAULT CHARSET=utf8;

-- --------------------------------------------------------

--
-- Структура таблицы `bb_vote_desc`
--

CREATE TABLE IF NOT EXISTS `bb_vote_desc` (
  `vote_id` mediumint(8) unsigned NOT NULL AUTO_INCREMENT,
  `topic_id` mediumint(8) unsigned NOT NULL DEFAULT '0',
  `vote_text` text NOT NULL,
  `vote_start` int(11) NOT NULL DEFAULT '0',
  `vote_length` int(11) NOT NULL DEFAULT '0',
  PRIMARY KEY (`vote_id`),
  KEY `topic_id` (`topic_id`)
) ENGINE=MyISAM DEFAULT CHARSET=utf8;

-- --------------------------------------------------------

--
-- Структура таблицы `bb_vote_results`
--

CREATE TABLE IF NOT EXISTS `bb_vote_results` (
  `vote_id` mediumint(8) unsigned NOT NULL DEFAULT '0',
  `vote_option_id` tinyint(4) unsigned NOT NULL DEFAULT '0',
  `vote_option_text` varchar(255) NOT NULL DEFAULT '',
  `vote_result` int(11) NOT NULL DEFAULT '0',
  KEY `vote_option_id` (`vote_option_id`),
  KEY `vote_id` (`vote_id`)
) ENGINE=MyISAM DEFAULT CHARSET=utf8;

-- --------------------------------------------------------

--
-- Структура таблицы `bb_vote_voters`
--

CREATE TABLE IF NOT EXISTS `bb_vote_voters` (
  `vote_id` mediumint(8) unsigned NOT NULL DEFAULT '0',
  `vote_user_id` mediumint(8) NOT NULL DEFAULT '0',
  `vote_user_ip` char(32) NOT NULL DEFAULT '',
  KEY `vote_id` (`vote_id`),
  KEY `vote_user_id` (`vote_user_id`),
  KEY `vote_user_ip` (`vote_user_ip`)
) ENGINE=MyISAM DEFAULT CHARSET=utf8;

-- --------------------------------------------------------

--
-- Структура таблицы `bb_words`
--

CREATE TABLE IF NOT EXISTS `bb_words` (
  `word_id` mediumint(8) unsigned NOT NULL AUTO_INCREMENT,
  `word` char(100) NOT NULL DEFAULT '',
  `replacement` char(100) NOT NULL DEFAULT '',
  PRIMARY KEY (`word_id`)
) ENGINE=MyISAM DEFAULT CHARSET=utf8;

-- --------------------------------------------------------

--
-- Структура таблицы `buf_last_seeder`
--

CREATE TABLE IF NOT EXISTS `buf_last_seeder` (
  `topic_id` mediumint(8) unsigned NOT NULL DEFAULT '0',
  `seeder_last_seen` int(11) NOT NULL DEFAULT '0',
  PRIMARY KEY (`topic_id`)
) ENGINE=MyISAM DEFAULT CHARSET=utf8;

-- --------------------------------------------------------

--
-- Структура таблицы `buf_topic_view`
--

CREATE TABLE IF NOT EXISTS `buf_topic_view` (
  `topic_id` mediumint(8) unsigned NOT NULL DEFAULT '0',
  `topic_views` mediumint(8) unsigned NOT NULL DEFAULT '0',
  PRIMARY KEY (`topic_id`)
) ENGINE=MyISAM DEFAULT CHARSET=utf8;

-- --------------------------------------------------------

--
-- Структура таблицы `sph_counter`
--

CREATE TABLE IF NOT EXISTS `sph_counter` (
  `counter_id` int(11) NOT NULL,
  `max_doc_id` int(11) NOT NULL,
  PRIMARY KEY (`counter_id`)
) ENGINE=MyISAM DEFAULT CHARSET=utf8;

-- --------------------------------------------------------

--
-- Структура таблицы `xbt_announce_log`
--

CREATE TABLE IF NOT EXISTS `xbt_announce_log` (
  `id` int(11) NOT NULL AUTO_INCREMENT,
  `ipa` int(10) unsigned NOT NULL DEFAULT '0',
  `port` int(11) NOT NULL DEFAULT '0',
  `event` int(11) NOT NULL DEFAULT '0',
  `info_hash` blob NOT NULL,
  `peer_id` blob NOT NULL,
  `downloaded` bigint(20) NOT NULL DEFAULT '0',
  `left0` bigint(20) NOT NULL DEFAULT '0',
  `uploaded` bigint(20) NOT NULL DEFAULT '0',
  `uid` int(11) NOT NULL DEFAULT '0',
  `mtime` int(11) NOT NULL DEFAULT '0',
  PRIMARY KEY (`id`)
) ENGINE=MyISAM DEFAULT CHARSET=utf8;

-- --------------------------------------------------------

--
-- Структура таблицы `xbt_config`
--

CREATE TABLE IF NOT EXISTS `xbt_config` (
  `name` varchar(255) NOT NULL DEFAULT '',
  `value` varchar(255) NOT NULL DEFAULT '',
  PRIMARY KEY (`name`)
) ENGINE=MyISAM DEFAULT CHARSET=utf8;

-- --------------------------------------------------------

--
-- Структура таблицы `xbt_deny_from_hosts`
--

CREATE TABLE IF NOT EXISTS `xbt_deny_from_hosts` (
  `begin` int(11) NOT NULL DEFAULT '0',
  `end` int(11) NOT NULL DEFAULT '0'
) ENGINE=MyISAM DEFAULT CHARSET=utf8;

-- --------------------------------------------------------

--
-- Структура таблицы `xbt_files_users`
--

CREATE TABLE IF NOT EXISTS `xbt_files_users` (
  `fid` int(11) NOT NULL DEFAULT '0',
  `uid` int(11) NOT NULL DEFAULT '0',
  `active` tinyint(4) NOT NULL DEFAULT '0',
  `announced` int(11) NOT NULL DEFAULT '0',
  `completed` int(11) NOT NULL DEFAULT '0',
  `downloaded` bigint(20) NOT NULL DEFAULT '0',
  `left` bigint(20) NOT NULL DEFAULT '0',
  `uploaded` bigint(20) NOT NULL DEFAULT '0',
  `mtime` int(11) NOT NULL DEFAULT '0',
  UNIQUE KEY `fid` (`fid`,`uid`),
  KEY `uid` (`uid`)
) ENGINE=MyISAM DEFAULT CHARSET=utf8;

-- --------------------------------------------------------

--
-- Структура таблицы `xbt_scrape_log`
--

CREATE TABLE IF NOT EXISTS `xbt_scrape_log` (
  `id` int(11) NOT NULL AUTO_INCREMENT,
  `ipa` int(11) NOT NULL DEFAULT '0',
  `info_hash` blob,
  `uid` int(11) NOT NULL DEFAULT '0',
  `mtime` int(11) NOT NULL DEFAULT '0',
  PRIMARY KEY (`id`)
) ENGINE=MyISAM DEFAULT CHARSET=utf8;
