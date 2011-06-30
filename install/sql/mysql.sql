-- phpMyAdmin SQL Dump
-- version 2.10.3
-- http://www.phpmyadmin.net
--
-- Хост: localhost
-- Время создания: Июн 17 2008 г., 13:37
-- Версия сервера: 5.0.51
-- Версия PHP: 5.2.6

SET SQL_MODE="NO_AUTO_VALUE_ON_ZERO";

--
-- Удаление старых таблиц
--

DROP TABLE IF EXISTS `bb_ads`;
DROP TABLE IF EXISTS `bb_attach_quota`;
DROP TABLE IF EXISTS `bb_attachments`;
DROP TABLE IF EXISTS `bb_attachments_config`;
DROP TABLE IF EXISTS `bb_attachments_desc`;
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
DROP TABLE IF EXISTS `bb_bt_torstat`;
DROP TABLE IF EXISTS `bb_bt_tracker`;
DROP TABLE IF EXISTS `bb_bt_tracker_snap`;
DROP TABLE IF EXISTS `bb_bt_user_settings`;
DROP TABLE IF EXISTS `bb_bt_users`;
DROP TABLE IF EXISTS `bb_captcha`;
DROP TABLE IF EXISTS `bb_categories`;
DROP TABLE IF EXISTS `bb_config`;
DROP TABLE IF EXISTS `bb_countries`;
DROP TABLE IF EXISTS `bb_cron`;
DROP TABLE IF EXISTS `bb_datastore`;
DROP TABLE IF EXISTS `bb_disallow`;
DROP TABLE IF EXISTS `bb_extension_groups`;
DROP TABLE IF EXISTS `bb_extensions`;
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
DROP TABLE IF EXISTS `bb_topic_templates`;
DROP TABLE IF EXISTS `bb_topics`;
DROP TABLE IF EXISTS `bb_topics_watch`;
DROP TABLE IF EXISTS `bb_user_group`;
DROP TABLE IF EXISTS `bb_users`;
DROP TABLE IF EXISTS `bb_vote_desc`;
DROP TABLE IF EXISTS `bb_vote_results`;
DROP TABLE IF EXISTS `bb_vote_voters`;
DROP TABLE IF EXISTS `bb_words`;
DROP TABLE IF EXISTS `buf_last_seeder`;
DROP TABLE IF EXISTS `buf_topic_view`;
--
-- Структура таблицы `bb_ads`
--

CREATE TABLE `bb_ads` (
  `ad_id` mediumint(8) unsigned NOT NULL auto_increment,
  `ad_block_ids` varchar(255) NOT NULL default '',
  `ad_start_time` datetime NOT NULL default '0000-00-00 00:00:00',
  `ad_active_days` smallint(6) NOT NULL default '0',
  `ad_status` tinyint(4) NOT NULL default '1',
  `ad_desc` varchar(255) NOT NULL default '',
  `ad_html` text NOT NULL,
  PRIMARY KEY  (`ad_id`)
) ENGINE=MyISAM DEFAULT CHARSET=utf8 AUTO_INCREMENT=1 ;

-- --------------------------------------------------------

--
-- Структура таблицы `bb_attachments`
--

CREATE TABLE `bb_attachments` (
  `attach_id` mediumint(8) unsigned NOT NULL default '0',
  `post_id` mediumint(8) unsigned NOT NULL default '0',
  `user_id_1` mediumint(8) NOT NULL default '0',
  PRIMARY KEY (`attach_id`, `post_id`)
) ENGINE=MyISAM DEFAULT CHARSET=utf8;

-- --------------------------------------------------------

--
-- Структура таблицы `bb_attachments_config`
--

CREATE TABLE `bb_attachments_config` (
  `config_name` varchar(255) NOT NULL default '',
  `config_value` varchar(255) NOT NULL default '',
  PRIMARY KEY  (`config_name`)
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

CREATE TABLE `bb_attachments_desc` (
  `attach_id` mediumint(8) unsigned NOT NULL auto_increment,
  `physical_filename` varchar(255) NOT NULL default '',
  `real_filename` varchar(255) NOT NULL default '',
  `download_count` mediumint(8) unsigned NOT NULL default '0',
  `comment` varchar(255) NOT NULL default '',
  `extension` varchar(100) NOT NULL default '',
  `mimetype` varchar(100) NOT NULL default '',
  `filesize` int(20) NOT NULL default '0',
  `filetime` int(11) NOT NULL default '0',
  `thumbnail` tinyint(1) NOT NULL default '0',
  `tracker_status` tinyint(1) NOT NULL default '0',
  PRIMARY KEY  (`attach_id`),
  KEY `filetime` (`filetime`),
  KEY `filesize` (`filesize`),
  KEY `physical_filename` (`physical_filename`(10))
) ENGINE=MyISAM DEFAULT CHARSET=utf8 AUTO_INCREMENT=1 ;

-- --------------------------------------------------------

--
-- Структура таблицы `bb_attach_quota`
--

CREATE TABLE `bb_attach_quota` (
  `user_id` mediumint(8) unsigned NOT NULL default '0',
  `group_id` mediumint(8) unsigned NOT NULL default '0',
  `quota_type` smallint(2) NOT NULL default '0',
  `quota_limit_id` mediumint(8) unsigned NOT NULL default '0',
  KEY `quota_type` (`quota_type`)
) ENGINE=MyISAM DEFAULT CHARSET=utf8;

-- --------------------------------------------------------

--
-- Структура таблицы `bb_auth_access`
--

CREATE TABLE `bb_auth_access` (
  `group_id` mediumint(8) NOT NULL default '0',
  `forum_id` smallint(5) unsigned NOT NULL default '0',
  `forum_perm` int(11) NOT NULL default '0',
  PRIMARY KEY  (`group_id`,`forum_id`),
  KEY `forum_id` (`forum_id`)
) ENGINE=MyISAM DEFAULT CHARSET=utf8;

-- --------------------------------------------------------

--
-- Структура таблицы `bb_auth_access_snap`
--

CREATE TABLE `bb_auth_access_snap` (
  `user_id` mediumint(9) NOT NULL default '0',
  `forum_id` smallint(6) NOT NULL default '0',
  `forum_perm` int(11) NOT NULL default '0',
  PRIMARY KEY  (`user_id`,`forum_id`)
) ENGINE=MyISAM DEFAULT CHARSET=utf8;

-- --------------------------------------------------------

--
-- Структура таблицы `bb_banlist`
--

CREATE TABLE `bb_banlist` (
  `ban_id` mediumint(8) unsigned NOT NULL auto_increment,
  `ban_userid` mediumint(8) NOT NULL default '0',
  `ban_ip` varchar(32) NOT NULL default '',
  `ban_email` varchar(255) NOT NULL default '',
  PRIMARY KEY  (`ban_id`),
  KEY `ban_ip_user_id` (`ban_ip`,`ban_userid`)
) ENGINE=MyISAM DEFAULT CHARSET=utf8 AUTO_INCREMENT=1 ;

-- --------------------------------------------------------

--
-- Структура таблицы `bb_bt_dlstatus_main`
--

CREATE TABLE `bb_bt_dlstatus_main` (
  `user_id` mediumint(9) NOT NULL default '0',
  `topic_id` mediumint(8) unsigned NOT NULL default '0',
  `user_status` tinyint(1) NOT NULL default '0',
  `last_modified_dlstatus` timestamp NOT NULL default CURRENT_TIMESTAMP on update CURRENT_TIMESTAMP,
  PRIMARY KEY  (`user_id`,`topic_id`),
  KEY `topic_id` (`topic_id`)
) ENGINE=MyISAM DEFAULT CHARSET=utf8;

-- --------------------------------------------------------

--
-- Структура таблицы `bb_bt_dlstatus_new`
--

CREATE TABLE `bb_bt_dlstatus_new` (
  `user_id` mediumint(9) NOT NULL default '0',
  `topic_id` mediumint(8) unsigned NOT NULL default '0',
  `user_status` tinyint(1) NOT NULL default '0',
  `last_modified_dlstatus` timestamp NOT NULL default CURRENT_TIMESTAMP on update CURRENT_TIMESTAMP,
  PRIMARY KEY  (`user_id`,`topic_id`),
  KEY `topic_id` (`topic_id`)
) ENGINE=MyISAM DEFAULT CHARSET=utf8;

-- --------------------------------------------------------

--
-- Структура таблицы `bb_bt_dlstatus_mrg`
--

CREATE TABLE `bb_bt_dlstatus_mrg` (
  `user_id` mediumint(9) NOT NULL default '0',
  `topic_id` mediumint(8) unsigned NOT NULL default '0',
  `user_status` tinyint(1) NOT NULL default '0',
  `last_modified_dlstatus` timestamp NOT NULL default CURRENT_TIMESTAMP on update CURRENT_TIMESTAMP,
  KEY `user_topic` (`user_id`,`topic_id`),
  KEY `topic_id` (`topic_id`)
) ENGINE=MRG_MyISAM DEFAULT CHARSET=utf8 UNION=(`bb_bt_dlstatus_main`,`bb_bt_dlstatus_new`);

-- --------------------------------------------------------

--
-- Структура таблицы `bb_bt_dlstatus_snap`
--

CREATE TABLE `bb_bt_dlstatus_snap` (
  `topic_id` mediumint(8) unsigned NOT NULL default '0',
  `dl_status` tinyint(4) NOT NULL default '0',
  `users_count` smallint(5) unsigned NOT NULL default '0',
  KEY `topic_id` (`topic_id`)
) ENGINE=MyISAM DEFAULT CHARSET=utf8;

-- --------------------------------------------------------

--
-- Структура таблицы `bb_bt_last_torstat`
--

CREATE TABLE `bb_bt_last_torstat` (
  `topic_id` mediumint(8) unsigned NOT NULL default '0',
  `user_id` mediumint(9) NOT NULL default '0',
  `dl_status` tinyint(1) NOT NULL default '0',
  `up_add` bigint(20) unsigned NOT NULL default '0',
  `down_add` bigint(20) unsigned NOT NULL default '0',
  `release_add` bigint(20) unsigned NOT NULL default '0',
  `bonus_add` bigint(20) unsigned NOT NULL default '0',
  `speed_up` bigint(20) unsigned NOT NULL default '0',
  `speed_down` bigint(20) unsigned NOT NULL default '0',
  PRIMARY KEY  USING BTREE (`topic_id`,`user_id`)
) ENGINE=MyISAM DEFAULT CHARSET=utf8;

-- --------------------------------------------------------

--
-- Структура таблицы `bb_bt_last_userstat`
--

CREATE TABLE `bb_bt_last_userstat` (
  `user_id` mediumint(9) NOT NULL default '0',
  `up_add` bigint(20) unsigned NOT NULL default '0',
  `down_add` bigint(20) unsigned NOT NULL default '0',
  `release_add` bigint(20) unsigned NOT NULL default '0',
  `bonus_add` bigint(20) unsigned NOT NULL default '0',
  `speed_up` bigint(20) unsigned NOT NULL default '0',
  `speed_down` bigint(20) unsigned NOT NULL default '0',
  PRIMARY KEY  (`user_id`)
) ENGINE=MyISAM DEFAULT CHARSET=utf8;

-- --------------------------------------------------------

--
-- Структура таблицы `bb_bt_torhelp`
--

CREATE TABLE `bb_bt_torhelp` (
  `user_id` mediumint(9) NOT NULL default '0',
  `topic_id_csv` text NOT NULL,
  PRIMARY KEY  (`user_id`)
) ENGINE=MyISAM DEFAULT CHARSET=utf8;

-- --------------------------------------------------------

--
-- Структура таблицы `bb_bt_torrents`
--

CREATE TABLE `bb_bt_torrents` (
  `info_hash` varbinary(20) NOT NULL,
  `post_id` mediumint(8) unsigned NOT NULL default '0',
  `poster_id` mediumint(9) NOT NULL default '0',
  `topic_id` mediumint(8) unsigned NOT NULL default '0',
  `forum_id` smallint(5) unsigned NOT NULL default '0',
  `attach_id` mediumint(8) unsigned NOT NULL default '0',
  `size` bigint(20) unsigned NOT NULL default '0',
  `reg_time` int(11) NOT NULL default '0',
  `call_seed_time` int(11) NOT NULL default '0',
  `complete_count` mediumint(8) unsigned NOT NULL default '0',
  `seeder_last_seen` int(11) NOT NULL default '0',
  `tor_status` tinyint(4) NOT NULL default '0',
  `checked_user_id` mediumint(8) NOT NULL default '0',
  `checked_time` int(11) NOT NULL default '0',
  `tor_type` TINYINT(1) NOT NULL DEFAULT '0',
  PRIMARY KEY  (`info_hash`),
  UNIQUE KEY `post_id` (`post_id`),
  UNIQUE KEY `topic_id` (`topic_id`),
  UNIQUE KEY `attach_id` (`attach_id`),
  KEY `reg_time` (`reg_time`),
  KEY `forum_id` (`forum_id`),
  KEY `poster_id` (`poster_id`)
) ENGINE=MyISAM DEFAULT CHARSET=utf8;

-- --------------------------------------------------------

--
-- Структура таблицы `bb_bt_torstat`
--

CREATE TABLE `bb_bt_torstat` (
  `topic_id` mediumint(8) unsigned NOT NULL default '0',
  `user_id` mediumint(9) NOT NULL default '0',
  `last_modified_torstat` timestamp NOT NULL default CURRENT_TIMESTAMP on update CURRENT_TIMESTAMP,
  `completed` tinyint(1) NOT NULL default '0',
  PRIMARY KEY  (`topic_id`,`user_id`)
) ENGINE=MyISAM DEFAULT CHARSET=utf8;

-- --------------------------------------------------------

--
-- Структура таблицы `bb_bt_tracker`
--

CREATE TABLE `bb_bt_tracker` (
  `peer_hash` varchar(32) character set utf8 collate utf8_bin NOT NULL default '',
  `topic_id` mediumint(8) unsigned NOT NULL default '0',
  `user_id` mediumint(9) NOT NULL default '0',
  `ip` char(8) character set utf8 collate utf8_bin NOT NULL default '0',
  `ipv6` varchar(32) DEFAULT NULL,
  `port` smallint(5) unsigned NOT NULL default '0',
  `seeder` tinyint(1) NOT NULL default '0',
  `releaser` tinyint(1) NOT NULL default '0',
  `tor_type` TINYINT(1) NOT NULL DEFAULT '0',
  `uploaded` bigint(20) unsigned NOT NULL default '0',
  `downloaded` bigint(20) unsigned NOT NULL default '0',
  `remain` bigint(20) unsigned NOT NULL default '0',
  `speed_up` mediumint(8) unsigned NOT NULL default '0',
  `speed_down` mediumint(8) unsigned NOT NULL default '0',
  `up_add` bigint(20) unsigned NOT NULL default '0',
  `down_add` bigint(20) unsigned NOT NULL default '0',
  `update_time` int(11) NOT NULL default '0',
  `xbt_error` varchar(200) DEFAULT NULL,
  `ul_gdc` bigint(20) unsigned NOT NULL DEFAULT '0',
  `ul_gdc_c` mediumint(9) unsigned NOT NULL DEFAULT '0',
  `ul_16k_c` mediumint(9) unsigned NOT NULL DEFAULT '0',
  `ul_eq_dl` mediumint(9) unsigned NOT NULL DEFAULT '0',
  PRIMARY KEY  (`peer_hash`),
  KEY `topic_id` (`topic_id`),
  KEY `user_id` (`user_id`)
) ENGINE=MyISAM DEFAULT CHARSET=utf8;

-- --------------------------------------------------------

--
-- Структура таблицы `bb_bt_tracker_snap`
--

CREATE TABLE `bb_bt_tracker_snap` (
  `topic_id` mediumint(8) unsigned NOT NULL default '0',
  `seeders` mediumint(8) unsigned NOT NULL default '0',
  `leechers` mediumint(8) unsigned NOT NULL default '0',
  `speed_up` int(10) unsigned NOT NULL default '0',
  `speed_down` int(10) unsigned NOT NULL default '0',
  PRIMARY KEY  (`topic_id`)
) ENGINE=MyISAM DEFAULT CHARSET=utf8;

-- --------------------------------------------------------

--
-- Структура таблицы `bb_bt_users`
--

CREATE TABLE `bb_bt_users` (
  `user_id` mediumint(9) NOT NULL default '0',
  `auth_key` char(10) character set utf8 collate utf8_bin NOT NULL default '',
  `u_up_total` bigint(20) unsigned NOT NULL default '0',
  `u_down_total` bigint(20) unsigned NOT NULL default '0',
  `u_up_release` bigint(20) unsigned NOT NULL default '0',
  `u_up_bonus` bigint(20) unsigned NOT NULL default '0',
  PRIMARY KEY  (`user_id`),
  UNIQUE KEY `auth_key` (`auth_key`)
) ENGINE=MyISAM DEFAULT CHARSET=utf8;

-- --------------------------------------------------------

--
-- Структура таблицы `bb_bt_user_settings`
--

CREATE TABLE `bb_bt_user_settings` (
  `user_id` mediumint(9) NOT NULL default '0',
  `tor_search_set` text NOT NULL,
  `last_modified` int(11) NOT NULL default '0',
  PRIMARY KEY  (`user_id`)
) ENGINE=MyISAM DEFAULT CHARSET=utf8;

-- --------------------------------------------------------

--
-- Структура таблицы `bb_captcha`
--

CREATE TABLE `bb_captcha` (
  `cap_id` int(10) NOT NULL,
  `cap_code` char(6) NOT NULL,
  `cap_expire` int(11) NOT NULL default '0',
  PRIMARY KEY  (`cap_id`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8;

-- --------------------------------------------------------

--
-- Структура таблицы `bb_categories`
--

CREATE TABLE `bb_categories` (
  `cat_id` smallint(5) unsigned NOT NULL auto_increment,
  `cat_title` varchar(100) NOT NULL default '',
  `cat_order` smallint(5) unsigned NOT NULL default '0',
  PRIMARY KEY  (`cat_id`),
  KEY `cat_order` (`cat_order`)
) ENGINE=MyISAM  DEFAULT CHARSET=utf8 AUTO_INCREMENT=2 ;

--
-- Дамп данных таблицы `bb_categories`
--

INSERT INTO `bb_categories` VALUES (1, 'Test category 1', 10);

-- --------------------------------------------------------

--
-- Структура таблицы `bb_config`
--

CREATE TABLE `bb_config` (
  `config_name` varchar(255) NOT NULL default '',
  `config_value` text NOT NULL,
  PRIMARY KEY  (`config_name`)
) ENGINE=MyISAM DEFAULT CHARSET=utf8;

--
-- Дамп данных таблицы `bb_config`
--

INSERT INTO `bb_config` VALUES ('allow_autologin', '1');
INSERT INTO `bb_config` VALUES ('allow_avatar_local', '0');
INSERT INTO `bb_config` VALUES ('allow_avatar_remote', '0');
INSERT INTO `bb_config` VALUES ('allow_avatar_upload', '1');
INSERT INTO `bb_config` VALUES ('allow_bbcode', '1');
INSERT INTO `bb_config` VALUES ('allow_namechange', '0');
INSERT INTO `bb_config` VALUES ('allow_sig', '1');
INSERT INTO `bb_config` VALUES ('allow_smilies', '1');
INSERT INTO `bb_config` VALUES ('avatar_filesize', '10000');
INSERT INTO `bb_config` VALUES ('avatar_gallery_path', 'images/avatars/gallery');
INSERT INTO `bb_config` VALUES ('avatar_max_height', '100');
INSERT INTO `bb_config` VALUES ('avatar_max_width', '100');
INSERT INTO `bb_config` VALUES ('avatar_path', 'images/avatars');
INSERT INTO `bb_config` VALUES ('board_disable', '0');
INSERT INTO `bb_config` VALUES ('board_email', 'board_email@yourdomain.com');
INSERT INTO `bb_config` VALUES ('board_email_form', '0');
INSERT INTO `bb_config` VALUES ('board_email_sig', 'Thanks, The Management');
INSERT INTO `bb_config` VALUES ('board_startdate', '1211477514');
INSERT INTO `bb_config` VALUES ('board_timezone', '0');
INSERT INTO `bb_config` VALUES ('bt_add_auth_key', '1');
INSERT INTO `bb_config` VALUES ('bt_add_comment', '');
INSERT INTO `bb_config` VALUES ('bt_add_publisher', 'YourSiteName');
INSERT INTO `bb_config` VALUES ('bt_allow_spmode_change', '1');
INSERT INTO `bb_config` VALUES ('bt_announce_url', 'http://yourdomain.com/bt/announce.php');
INSERT INTO `bb_config` VALUES ('bt_disable_dht', '1');
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
INSERT INTO `bb_config` VALUES ('enable_confirm', '1');
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
INSERT INTO `bb_config` VALUES ('site_desc', 'A _little_ text to describe your forum');
INSERT INTO `bb_config` VALUES ('sitename', 'yourdomain.com');
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
INSERT INTO `bb_config` VALUES ('active_ads', 'a:0:{}');
INSERT INTO `bb_config` VALUES ('report_subject_auth', '1');
INSERT INTO `bb_config` VALUES ('report_modules_cache', '1');
INSERT INTO `bb_config` VALUES ('report_hack_count', '0');
INSERT INTO `bb_config` VALUES ('report_notify', '0');
INSERT INTO `bb_config` VALUES ('report_list_admin', '0');
INSERT INTO `bb_config` VALUES ('report_new_window', '0');

-- --------------------------------------------------------

--
-- Структура таблицы `bb_countries`
--

CREATE TABLE `bb_countries` (
  `country_id` mediumint(3) NOT NULL auto_increment,
  `country_code` varchar(3) NOT NULL default '0',
  `country_code2` varchar(2) NOT NULL,
  `country_code3` varchar(3) NOT NULL,
  PRIMARY KEY  (`country_id`),
  KEY `country_code2` (`country_code2`),
  KEY `country_code3` (`country_code3`),
  KEY `country_code` (`country_code`)
) ENGINE=MyISAM  DEFAULT CHARSET=utf8 AUTO_INCREMENT=245 ;

--
-- Дамп данных таблицы `bb_countries`
--

INSERT INTO `bb_countries` (`country_id`, `country_code`, `country_code2`, `country_code3`) VALUES
(1, '036', 'AU', 'AUS'),
(2, '040', 'AT', 'AUT'),
(3, '031', 'AZ', 'AZE'),
(4, '248', 'AX', 'ALA'),
(5, '008', 'AL', 'ALB'),
(6, '012', 'DZ', 'DZA'),
(7, '581', 'UM', 'UMI'),
(8, '850', 'VI', 'VIR'),
(9, '016', 'AS', 'ASM'),
(10, '660', 'AI', 'AIA'),
(11, '024', 'AO', 'AGO'),
(12, '020', 'AD', 'AND'),
(13, '010', 'AQ', 'ATA'),
(14, '028', 'AG', 'ATG'),
(15, '032', 'AR', 'ARG'),
(16, '051', 'AM', 'ARM'),
(17, '533', 'AW', 'ABW'),
(18, '004', 'AF', 'AFG'),
(19, '044', 'BS', 'BHS'),
(20, '050', 'BD', 'BGD'),
(21, '052', 'BB', 'BRB'),
(22, '048', 'BH', 'BHR'),
(23, '084', 'BZ', 'BLZ'),
(24, '112', 'BY', 'BLR'),
(25, '056', 'BE', 'BEL'),
(26, '204', 'BJ', 'BEN'),
(27, '060', 'BM', 'BMU'),
(28, '100', 'BG', 'BGR'),
(29, '068', 'BO', 'BOL'),
(30, '070', 'BA', 'BIH'),
(31, '072', 'BW', 'BWA'),
(32, '076', 'BR', 'BRA'),
(33, '086', 'IO', 'IOT'),
(34, '092', 'VG', 'VGB'),
(35, '096', 'BN', 'BRN'),
(36, '854', 'BF', 'BFA'),
(37, '108', 'BI', 'BDI'),
(38, '064', 'BT', 'BTN'),
(39, '548', 'VU', 'VUT'),
(40, '336', 'VA', 'VAT'),
(41, '826', 'GB', 'GBR'),
(42, '348', 'HU', 'HUN'),
(43, '862', 'VE', 'VEN'),
(44, '626', 'TL', 'TLS'),
(45, '704', 'VN', 'VNM'),
(46, '266', 'GA', 'GAB'),
(47, '332', 'HT', 'HTI'),
(48, '328', 'GY', 'GUY'),
(49, '270', 'GM', 'GMB'),
(50, '288', 'GH', 'GHA'),
(51, '312', 'GP', 'GLP'),
(52, '320', 'GT', 'GTM'),
(53, '324', 'GN', 'GIN'),
(54, '624', 'GW', 'GNB'),
(55, '276', 'DE', 'DEU'),
(56, '292', 'GI', 'GIB'),
(57, '340', 'HN', 'HND'),
(58, '344', 'HK', 'HKG'),
(59, '308', 'GD', 'GRD'),
(60, '304', 'GL', 'GRL'),
(61, '300', 'GR', 'GRC'),
(62, '268', 'GE', 'GEO'),
(63, '316', 'GU', 'GUM'),
(64, '208', 'DK', 'DNK'),
(65, '180', 'CD', 'COD'),
(66, '262', 'DJ', 'DJI'),
(67, '212', 'DM', 'DMA'),
(68, '214', 'DO', 'DOM'),
(69, 'EU', 'EU', ''),
(70, '818', 'EG', 'EGY'),
(71, '894', 'ZM', 'ZMB'),
(72, '732', 'EH', 'ESH'),
(73, '716', 'ZW', 'ZWE'),
(74, '376', 'IL', 'ISR'),
(75, '356', 'IN', 'IND'),
(76, '360', 'ID', 'IDN'),
(77, '400', 'JO', 'JOR'),
(78, '368', 'IQ', 'IRQ'),
(79, '364', 'IR', 'IRN'),
(80, '372', 'IE', 'IRL'),
(81, '352', 'IS', 'ISL'),
(82, '724', 'ES', 'ESP'),
(83, '380', 'IT', 'ITA'),
(84, '887', 'YE', 'YEM'),
(85, '408', 'KP', 'PRK'),
(86, '132', 'CV', 'CPV'),
(87, '398', 'KZ', 'KAZ'),
(88, '136', 'KY', 'CYM'),
(89, '116', 'KH', 'KHM'),
(90, '120', 'CM', 'CMR'),
(91, '124', 'CA', 'CAN'),
(92, '634', 'QA', 'QAT'),
(93, '404', 'KE', 'KEN'),
(94, '196', 'CY', 'CYP'),
(95, '417', 'KG', 'KGZ'),
(96, '296', 'KI', 'KIR'),
(97, '156', 'CN', 'CHN'),
(98, '166', 'CC', 'CCK'),
(99, '170', 'CO', 'COL'),
(100, '174', 'KM', 'COM'),
(101, '188', 'CR', 'CRI'),
(102, '384', 'CI', 'CIV'),
(103, '192', 'CU', 'CUB'),
(104, '414', 'KW', 'KWT'),
(105, '418', 'LA', 'LAO'),
(106, '428', 'LV', 'LVA'),
(107, '426', 'LS', 'LSO'),
(108, '430', 'LR', 'LBR'),
(109, '422', 'LB', 'LBN'),
(110, '434', 'LY', 'LBY'),
(111, '440', 'LT', 'LTU'),
(112, '438', 'LI', 'LIE'),
(113, '442', 'LU', 'LUX'),
(114, '480', 'MU', 'MUS'),
(115, '478', 'MR', 'MRT'),
(116, '450', 'MG', 'MDG'),
(117, '175', 'YT', 'MYT'),
(118, '446', 'MO', 'MAC'),
(119, '807', 'MK', 'MKD'),
(120, '454', 'MW', 'MWI'),
(121, '458', 'MY', 'MYS'),
(122, '466', 'ML', 'MLI'),
(123, '462', 'MV', 'MDV'),
(124, '470', 'MT', 'MLT'),
(125, '504', 'MA', 'MAR'),
(126, '474', 'MQ', 'MTQ'),
(127, '584', 'MH', 'MHL'),
(128, '484', 'MX', 'MEX'),
(129, '508', 'MZ', 'MOZ'),
(130, '498', 'MD', 'MDA'),
(131, '492', 'MC', 'MCO'),
(132, '496', 'MN', 'MNG'),
(133, '500', 'MS', 'MSR'),
(134, '104', 'MM', 'MMR'),
(135, '516', 'NA', 'NAM'),
(136, '520', 'NR', 'NRU'),
(137, '524', 'NP', 'NPL'),
(138, '562', 'NE', 'NER'),
(139, '566', 'NG', 'NGA'),
(140, '530', 'AN', 'ANT'),
(141, '528', 'NL', 'NLD'),
(142, '558', 'NI', 'NIC'),
(143, '570', 'NU', 'NIU'),
(144, '540', 'NC', 'NCL'),
(145, '554', 'NZ', 'NZL'),
(146, '578', 'NO', 'NOR'),
(147, '784', 'AE', 'ARE'),
(148, '512', 'OM', 'OMN'),
(149, '162', 'CX', 'CXR'),
(150, '184', 'CK', 'COK'),
(151, '334', 'HM', 'HMD'),
(152, '586', 'PK', 'PAK'),
(153, '585', 'PW', 'PLW'),
(154, '275', 'PS', 'PSE'),
(155, '591', 'PA', 'PAN'),
(156, '598', 'PG', 'PNG'),
(157, '600', 'PY', 'PRY'),
(158, '604', 'PE', 'PER'),
(159, '612', 'PN', 'PCN'),
(160, '616', 'PL', 'POL'),
(161, '620', 'PT', 'PRT'),
(162, '630', 'PR', 'PRI'),
(163, '178', 'CG', 'COG'),
(164, '638', 'RE', 'REU'),
(165, '643', 'RU', 'RUS'),
(166, '646', 'RW', 'RWA'),
(167, '642', 'RO', 'ROU'),
(168, '840', 'US', 'USA'),
(169, '222', 'SV', 'SLV'),
(170, '882', 'WS', 'WSM'),
(171, '674', 'SM', 'SMR'),
(172, '678', 'ST', 'STP'),
(173, '682', 'SA', 'SAU'),
(174, '748', 'SZ', 'SWZ'),
(175, '744', 'SJ', 'SJM'),
(176, '580', 'MP', 'MNP'),
(177, '690', 'SC', 'SYC'),
(178, '686', 'SN', 'SEN'),
(179, '670', 'VC', 'VCT'),
(180, '659', 'KN', 'KNA'),
(181, '662', 'LC', 'LCA'),
(182, '666', 'PM', 'SPM'),
(183, '688', 'RS', 'SRB'),
(184, '891', 'CS', 'SCG'),
(185, '702', 'SG', 'SGP'),
(186, '760', 'SY', 'SYR'),
(187, '703', 'SK', 'SVK'),
(188, '705', 'SI', 'SVN'),
(189, '090', 'SB', 'SLB'),
(190, '706', 'SO', 'SOM'),
(191, '736', 'SD', 'SDN'),
(192, '740', 'SR', 'SUR'),
(193, '694', 'SL', 'SLE'),
(194, '810', 'SU', 'SUN'),
(195, '762', 'TJ', 'TJK'),
(196, '764', 'TH', 'THA'),
(197, '158', 'TW', 'TWN'),
(198, '834', 'TZ', 'TZA'),
(199, '768', 'TG', 'TGO'),
(200, '772', 'TK', 'TKL'),
(201, '776', 'TO', 'TON'),
(202, '780', 'TT', 'TTO'),
(203, '798', 'TV', 'TUV'),
(204, '788', 'TN', 'TUN'),
(205, '795', 'TM', 'TKM'),
(206, '792', 'TR', 'TUR'),
(207, '800', 'UG', 'UGA'),
(208, '860', 'UZ', 'UZB'),
(209, '804', 'UA', 'UKR'),
(210, '858', 'UY', 'URY'),
(211, '234', 'FO', 'FRO'),
(212, '583', 'FM', 'FSM'),
(213, '242', 'FJ', 'FJI'),
(214, '608', 'PH', 'PHL'),
(215, '246', 'FI', 'FIN'),
(216, '238', 'FK', 'FLK'),
(217, '250', 'FR', 'FRA'),
(218, '254', 'GF', 'GUF'),
(219, '258', 'PF', 'PYF'),
(220, '260', 'TF', 'ATF'),
(221, '191', 'HR', 'HRV'),
(222, '140', 'CF', 'CAF'),
(223, '148', 'TD', 'TCD'),
(224, '499', 'ME', 'MNE'),
(225, '203', 'CZ', 'CZE'),
(226, '152', 'CL', 'CHL'),
(227, '756', 'CH', 'CHE'),
(228, '752', 'SE', 'SWE'),
(229, '144', 'LK', 'LKA'),
(230, '218', 'EC', 'ECU'),
(231, '226', 'GQ', 'GNQ'),
(232, '232', 'ER', 'ERI'),
(233, '233', 'EE', 'EST'),
(234, '231', 'ET', 'ETH'),
(235, '710', 'ZA', 'ZAF'),
(236, '410', 'KR', 'KOR'),
(237, '239', 'GS', 'SGS'),
(238, '388', 'JM', 'JAM'),
(239, '392', 'JP', 'JPN'),
(240, '074', 'BV', 'BVT'),
(241, '574', 'NF', 'NFK'),
(242, '654', 'SH', 'SHN'),
(243, '796', 'TC', 'TCA'),
(244, '876', 'WF', 'WLF');

-- --------------------------------------------------------

--
-- Структура таблицы `bb_cron`
--

CREATE TABLE `bb_cron` (
  `cron_id` smallint(5) unsigned NOT NULL auto_increment,
  `cron_active` tinyint(4) NOT NULL default '1',
  `cron_title` char(120) NOT NULL default '',
  `cron_script` char(120) NOT NULL default '',
  `schedule` enum('hourly','daily','weekly','monthly','interval') NOT NULL default 'daily',
  `run_day` enum('1','2','3','4','5','6','7','8','9','10','11','12','13','14','15','16','17','18','19','20','21','22','23','24','25','26','27','28') default NULL,
  `run_time` time default '04:00:00',
  `run_order` tinyint(4) unsigned NOT NULL,
  `last_run` datetime NOT NULL default '0000-00-00 00:00:00',
  `next_run` datetime NOT NULL default '0000-00-00 00:00:00',
  `run_interval` time default NULL,
  `log_enabled` tinyint(1) NOT NULL default '0',
  `log_file` char(120) NOT NULL default '',
  `log_sql_queries` tinyint(4) NOT NULL default '0',
  `disable_board` tinyint(1) NOT NULL default '0',
  `run_counter` bigint(20) unsigned NOT NULL default '0',
  PRIMARY KEY  (`cron_id`),
  UNIQUE KEY `title` (`cron_title`),
  UNIQUE KEY `script` (`cron_script`)
) ENGINE=MyISAM  DEFAULT CHARSET=utf8 AUTO_INCREMENT=22 ;

--
-- Дамп данных таблицы `bb_cron`
--

INSERT INTO `bb_cron` VALUES (1, 0, 'Site backup', 'site_backup.php', 'daily', '1', '05:00:00', 10, '0000-00-00 00:00:00', '0000-00-00 00:00:00', NULL, 1, '', 0, 1, 0);
INSERT INTO `bb_cron` VALUES (2, 0, 'DB backup', 'db_backup.php', 'daily', '1', '05:00:00', 20, '0000-00-00 00:00:00', '0000-00-00 00:00:00', NULL, 1, '', 0, 1, 0);
INSERT INTO `bb_cron` VALUES (3, 1, 'Avatars cleanup', 'avatars_cleanup.php', 'weekly', '1', '05:00:00', 30, '2008-05-22 19:11:10', '2008-05-26 05:00:00', NULL, 1, '', 0, 1, 2);
INSERT INTO `bb_cron` VALUES (4, 1, 'Board maintenance', 'bb_maintenance.php', 'daily', NULL, '05:00:00', 40, '2008-05-22 19:11:14', '2008-05-23 05:00:00', NULL, 1, '', 0, 1, 2);
INSERT INTO `bb_cron` VALUES (5, 1, 'Prune forums', 'prune_forums.php', 'daily', NULL, '05:00:00', 50, '2008-05-22 19:11:17', '2008-05-23 05:00:00', NULL, 1, '', 0, 1, 2);
INSERT INTO `bb_cron` VALUES (6, 1, 'Prune topic moved stubs', 'prune_topic_moved.php', 'daily', NULL, '05:00:00', 60, '2008-05-22 19:11:20', '2008-05-23 05:00:00', NULL, 1, '', 0, 1, 2);
INSERT INTO `bb_cron` VALUES (7, 1, 'Logs cleanup', 'clean_log.php', 'daily', NULL, '05:00:00', 70, '2008-05-22 19:11:23', '2008-05-23 05:00:00', NULL, 1, '', 0, 1, 2);
INSERT INTO `bb_cron` VALUES (8, 1, 'Tracker maintenance', 'tr_maintenance.php', 'daily', NULL, '05:00:00', 90, '2008-05-22 19:11:26', '2008-05-23 05:00:00', NULL, 1, '', 0, 1, 2);
INSERT INTO `bb_cron` VALUES (9, 1, 'Clean dlstat', 'clean_dlstat.php', 'daily', NULL, '05:00:00', 100, '2008-05-22 19:11:29', '2008-05-23 05:00:00', NULL, 1, '', 0, 1, 2);
INSERT INTO `bb_cron` VALUES (10, 1, 'Prune inactive users', 'prune_inactive_users.php', 'daily', NULL, '05:00:00', 110, '2008-05-22 19:11:32', '2008-05-23 05:00:00', NULL, 1, '', 0, 1, 2);
INSERT INTO `bb_cron` VALUES (11, 1, 'Sessions cleanup', 'sessions_cleanup.php', 'interval', NULL, NULL, 255, '2008-05-22 19:18:07', '2008-05-22 19:21:07', '00:03:00', 0, '', 0, 0, 31);
INSERT INTO `bb_cron` VALUES (12, 1, 'DS update ''cat_forums''', 'ds_update_cat_forums.php', 'interval', NULL, NULL, 255, '2008-05-22 19:18:10', '2008-05-22 19:23:10', '00:05:00', 0, '', 0, 0, 24);
INSERT INTO `bb_cron` VALUES (13, 1, 'DS update ''stats''', 'ds_update_stats.php', 'interval', NULL, NULL, 255, '2008-05-22 19:11:46', '2008-05-22 19:21:46', '00:10:00', 0, '', 0, 0, 15);
INSERT INTO `bb_cron` VALUES (14, 1, 'Flash topic view', 'flash_topic_view.php', 'interval', NULL, NULL, 255, '2008-05-22 19:11:49', '2008-05-22 19:21:49', '00:10:00', 0, '', 0, 0, 15);
INSERT INTO `bb_cron` VALUES (15, 1, 'Clean search results', 'clean_search_results.php', 'interval', NULL, NULL, 255, '2008-05-22 19:11:52', '2008-05-22 19:21:52', '00:10:00', 0, '', 0, 0, 15);
INSERT INTO `bb_cron` VALUES (16, 1, 'Tracker cleanup and dlstat', 'tr_cleanup_and_dlstat.php', 'interval', NULL, NULL, 20, '2008-05-22 20:31:41', '2008-05-22 20:46:41', '00:15:00', 0, '', 0, 0, 14);
INSERT INTO `bb_cron` VALUES (17, 1, 'Make tracker snapshot', 'tr_make_snapshot.php', 'interval', NULL, NULL, 10, '2008-05-22 20:31:38', '2008-05-22 20:41:38', '00:10:00', 0, '', 0, 0, 16);
INSERT INTO `bb_cron` VALUES (18, 1, 'Seeder last seen', 'tr_update_seeder_last_seen.php', 'interval', NULL, NULL, 255, '2008-05-22 19:11:55', '2008-05-22 20:11:55', '01:00:00', 0, '', 0, 0, 5);
INSERT INTO `bb_cron` VALUES (19, 1, 'Captcha', 'captcha_gen_gc.php', 'interval', NULL, NULL, 255, '2008-05-22 19:11:58', '2008-05-23 01:11:58', '06:00:00', 0, '', 0, 0, 3);
INSERT INTO `bb_cron` VALUES (20, 1, 'Tracker dl-complete count', 'tr_complete_count.php', 'interval', NULL, NULL, 255, '2008-05-22 19:12:01', '2008-05-23 01:12:01', '06:00:00', 0, '', 0, 0, 3);
INSERT INTO `bb_cron` VALUES (21, 1, 'Cache garbage collector', 'cache_gc.php', 'interval', NULL, NULL, 255, '2008-05-22 19:18:13', '2008-05-22 19:23:13', '00:05:00', 0, '', 0, 0, 24);
INSERT INTO `bb_cron` VALUES (22, 1, 'Manage Antibroot', 'bb_manage_untrusted.php', 'interval', NULL, NULL, 255, '2008-05-22 19:18:13', '2008-05-22 19:23:13', '00:10:00', 0, '', 0, 0, 24);

-- --------------------------------------------------------

--
-- Структура таблицы `bb_datastore`
--

CREATE TABLE `bb_datastore` (
  `ds_title` varchar(255) NOT NULL default '',
  `ds_data` longtext NOT NULL,
  PRIMARY KEY  (`ds_title`)
) ENGINE=MyISAM DEFAULT CHARSET=utf8;

--
-- Дамп данных таблицы `bb_datastore`
--

INSERT INTO `bb_datastore` VALUES ('cat_forums', 'a:6:{s:15:"not_auth_forums";a:4:{s:10:"guest_view";s:0:"";s:10:"guest_read";s:0:"";s:9:"user_view";s:0:"";s:9:"user_read";s:0:"";}s:14:"tracker_forums";s:0:"";s:14:"cat_title_html";a:1:{i:1;s:15:"Test category 1";}s:15:"forum_name_html";a:1:{i:1;s:12:"Test Forum 1";}s:1:"c";a:1:{i:1;a:4:{s:6:"cat_id";s:1:"1";s:9:"cat_title";s:15:"Test category 1";s:9:"cat_order";s:2:"10";s:6:"forums";a:1:{i:0;s:1:"1";}}}s:1:"f";a:1:{i:1;a:20:{s:8:"forum_id";s:1:"1";s:6:"cat_id";s:1:"1";s:10:"forum_name";s:12:"Test Forum 1";s:10:"forum_desc";s:26:"This is just a test forum.";s:12:"forum_status";s:1:"0";s:11:"forum_posts";s:1:"1";s:12:"forum_topics";s:1:"1";s:9:"auth_view";s:1:"0";s:9:"auth_read";s:1:"0";s:9:"auth_post";s:1:"1";s:10:"auth_reply";s:1:"1";s:9:"auth_edit";s:1:"1";s:11:"auth_delete";s:1:"1";s:11:"auth_sticky";s:1:"3";s:13:"auth_announce";s:1:"3";s:9:"auth_vote";s:1:"1";s:15:"auth_pollcreate";s:1:"1";s:16:"auth_attachments";s:1:"1";s:13:"auth_download";s:1:"1";s:12:"forum_parent";s:1:"0";}}}');
INSERT INTO `bb_datastore` VALUES ('jumpbox', 'a:2:{s:5:"guest";s:241:"\n<select  id="jumpbox" onchange="window.location.href=''viewforum.php?f=''+this.value;" name="f" id="f">\n	<optgroup label="&nbsp;Test category 1">\n		<option class="root_forum" value="1">&nbsp;Test Forum 1&nbsp;</option>\n	</optgroup>\n</select>\n";s:4:"user";s:241:"\n<select  id="jumpbox" onchange="window.location.href=''viewforum.php?f=''+this.value;" name="f" id="f">\n	<optgroup label="&nbsp;Test category 1">\n		<option class="root_forum" value="1">&nbsp;Test Forum 1&nbsp;</option>\n	</optgroup>\n</select>\n";}');
INSERT INTO `bb_datastore` VALUES ('viewtopic_forum_select', 'a:1:{s:22:"viewtopic_forum_select";s:187:"\n<select  name="new_forum_id" id="new_forum_id">\n	<optgroup label="&nbsp;Test category 1">\n		<option class="root_forum" value="1">&nbsp;Test Forum 1&nbsp;</option>\n	</optgroup>\n</select>\n";}');
INSERT INTO `bb_datastore` VALUES ('latest_news', 'a:1:{i:0;a:3:{s:8:"topic_id";s:1:"1";s:10:"topic_time";s:9:"972086460";s:11:"topic_title";s:18:"Welcome to TorrentPier";}}');
INSERT INTO `bb_datastore` VALUES ('ads', 'a:0:{}');
INSERT INTO `bb_datastore` VALUES ('stats', 'a:4:{s:9:"usercount";s:1:"3";s:10:"newestuser";a:2:{s:7:"user_id";s:1:"2";s:8:"username";s:5:"admin";}s:9:"postcount";s:1:"1";s:10:"topiccount";s:1:"1";}');
INSERT INTO `bb_datastore` VALUES ('moderators', 'a:6:{s:10:"name_users";a:0:{}s:11:"name_groups";a:0:{}s:9:"mod_users";a:0:{}s:10:"mod_groups";a:0:{}s:10:"moderators";a:0:{}s:6:"admins";a:1:{i:2;s:5:"admin";}}');

-- --------------------------------------------------------

--
-- Структура таблицы `bb_disallow`
--

CREATE TABLE `bb_disallow` (
  `disallow_id` mediumint(8) unsigned NOT NULL auto_increment,
  `disallow_username` varchar(25) NOT NULL default '',
  PRIMARY KEY  (`disallow_id`)
) ENGINE=MyISAM DEFAULT CHARSET=utf8 AUTO_INCREMENT=1 ;

-- --------------------------------------------------------

--
-- Структура таблицы `bb_extensions`
--

CREATE TABLE `bb_extensions` (
  `ext_id` mediumint(8) unsigned NOT NULL auto_increment,
  `group_id` mediumint(8) unsigned NOT NULL default '0',
  `extension` varchar(100) NOT NULL default '',
  `comment` varchar(100) NOT NULL default '',
  PRIMARY KEY  (`ext_id`)
) ENGINE=MyISAM  DEFAULT CHARSET=utf8 AUTO_INCREMENT=30 ;

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

CREATE TABLE `bb_extension_groups` (
  `group_id` mediumint(8) NOT NULL auto_increment,
  `group_name` varchar(20) NOT NULL default '',
  `cat_id` tinyint(2) NOT NULL default '0',
  `allow_group` tinyint(1) NOT NULL default '0',
  `download_mode` tinyint(1) unsigned NOT NULL default '1',
  `upload_icon` varchar(100) NOT NULL default '',
  `max_filesize` int(20) NOT NULL default '0',
  `forum_permissions` text NOT NULL,
  PRIMARY KEY  (`group_id`)
) ENGINE=MyISAM  DEFAULT CHARSET=utf8 AUTO_INCREMENT=9 ;

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

CREATE TABLE `bb_forums` (
  `forum_id` smallint(5) unsigned NOT NULL auto_increment,
  `cat_id` smallint(5) unsigned NOT NULL default '0',
  `forum_name` varchar(150) NOT NULL default '',
  `forum_desc` text NOT NULL,
  `forum_status` tinyint(4) NOT NULL default '0',
  `forum_order` smallint(5) unsigned NOT NULL default '1',
  `forum_posts` mediumint(8) unsigned NOT NULL default '0',
  `forum_topics` mediumint(8) unsigned NOT NULL default '0',
  `forum_last_post_id` mediumint(8) unsigned NOT NULL default '0',
  `prune_days` smallint(5) unsigned NOT NULL default '0',
  `auth_view` tinyint(2) NOT NULL default '0',
  `auth_read` tinyint(2) NOT NULL default '0',
  `auth_post` tinyint(2) NOT NULL default '0',
  `auth_reply` tinyint(2) NOT NULL default '0',
  `auth_edit` tinyint(2) NOT NULL default '0',
  `auth_delete` tinyint(2) NOT NULL default '0',
  `auth_sticky` tinyint(2) NOT NULL default '0',
  `auth_announce` tinyint(2) NOT NULL default '0',
  `auth_vote` tinyint(2) NOT NULL default '0',
  `auth_pollcreate` tinyint(2) NOT NULL default '0',
  `auth_attachments` tinyint(2) NOT NULL default '0',
  `auth_download` tinyint(2) NOT NULL default '0',
  `allow_reg_tracker` tinyint(1) NOT NULL default '0',
  `allow_dl_topic` tinyint(1) NOT NULL default '0',
  `self_moderated` tinyint(1) NOT NULL default '0',
  `forum_parent` smallint(5) unsigned NOT NULL default '0',
  `show_on_index` tinyint(1) NOT NULL default '1',
  `forum_display_sort` tinyint(1) NOT NULL default '0',
  `forum_display_order` tinyint(1) NOT NULL default '0',
  `topic_tpl_id` smallint(6) NOT NULL default '0',
  PRIMARY KEY  (`forum_id`),
  KEY `forums_order` (`forum_order`),
  KEY `cat_id` (`cat_id`),
  KEY `forum_last_post_id` (`forum_last_post_id`),
  KEY `forum_parent` (`forum_parent`)
) ENGINE=MyISAM  DEFAULT CHARSET=utf8 AUTO_INCREMENT=2 ;

--
-- Дамп данных таблицы `bb_forums`
--

INSERT INTO `bb_forums` VALUES (1, 1, 'Test Forum 1', 'This is just a test forum.', 0, 10, 1, 1, 1, 0, 0, 0, 1, 1, 1, 1, 3, 3, 1, 1, 1, 1, 0, 0, 0, 0, 1, 0, 0, 0);

-- --------------------------------------------------------

--
-- Структура таблицы `bb_groups`
--

CREATE TABLE `bb_groups` (
  `group_id` mediumint(8) NOT NULL auto_increment,
  `group_type` tinyint(4) NOT NULL default '1',
  `group_name` varchar(40) NOT NULL default '',
  `group_description` varchar(255) NOT NULL default '',
  `group_moderator` mediumint(8) NOT NULL default '0',
  `group_single_user` tinyint(1) NOT NULL default '1',
  PRIMARY KEY  (`group_id`),
  KEY `group_single_user` (`group_single_user`)
) ENGINE=MyISAM DEFAULT CHARSET=utf8 AUTO_INCREMENT=1 ;

-- --------------------------------------------------------

--
-- Структура таблицы `bb_log`
--

CREATE TABLE `bb_log` (
  `log_type_id` mediumint(8) unsigned NOT NULL default '0',
  `log_user_id` mediumint(9) NOT NULL default '0',
  `log_username` varchar(25) NOT NULL default '',
  `log_user_ip` varchar(32) character set utf8 collate utf8_bin NOT NULL default '',
  `log_forum_id` smallint(5) unsigned NOT NULL default '0',
  `log_forum_id_new` smallint(5) unsigned NOT NULL default '0',
  `log_topic_id` mediumint(8) unsigned NOT NULL default '0',
  `log_topic_id_new` mediumint(8) unsigned NOT NULL default '0',
  `log_topic_title` varchar(250) NOT NULL default '',
  `log_topic_title_new` varchar(250) NOT NULL default '',
  `log_time` int(11) NOT NULL default '0',
  `log_msg` text NOT NULL,
  KEY `log_time` (`log_time`),
  FULLTEXT KEY `log_topic_title` (`log_topic_title`)
) ENGINE=MyISAM DEFAULT CHARSET=utf8;

-- --------------------------------------------------------

--
-- Структура таблицы `bb_posts`
--

CREATE TABLE `bb_posts` (
  `post_id` mediumint(8) unsigned NOT NULL auto_increment,
  `topic_id` mediumint(8) unsigned NOT NULL default '0',
  `forum_id` smallint(5) unsigned NOT NULL default '0',
  `poster_id` mediumint(8) NOT NULL default '0',
  `post_time` int(11) NOT NULL default '0',
  `poster_ip` char(32) character set utf8 collate utf8_bin NOT NULL default '',
  `post_username` varchar(25) NOT NULL default '',
  `enable_bbcode` tinyint(1) NOT NULL default '1',
  `enable_smilies` tinyint(1) NOT NULL default '1',
  `enable_sig` tinyint(1) NOT NULL default '1',
  `post_edit_time` int(11) NOT NULL default '0',
  `post_edit_count` smallint(5) unsigned NOT NULL default '0',
  `post_attachment` tinyint(1) NOT NULL default '0',
  `post_reported` tinyint(1) NOT NULL default '0',
  PRIMARY KEY  (`post_id`),
  KEY `topic_id` (`topic_id`),
  KEY `poster_id` (`poster_id`),
  KEY `post_time` (`post_time`),
  KEY `forum_id_post_time` (`forum_id`,`post_time`)
) ENGINE=MyISAM  DEFAULT CHARSET=utf8 AUTO_INCREMENT=2 ;

--
-- Дамп данных таблицы `bb_posts`
--

INSERT INTO `bb_posts` VALUES (1, 1, 1, 2, 972086460, '', '', 1, 1, 1, 0, 0, 0, 0);

-- --------------------------------------------------------

--
-- Структура таблицы `bb_posts_html`
--

CREATE TABLE `bb_posts_html` (
  `post_id` mediumint(9) NOT NULL default '0',
  `post_html_time` timestamp NOT NULL default CURRENT_TIMESTAMP on update CURRENT_TIMESTAMP,
  `post_html` mediumtext NOT NULL,
  PRIMARY KEY  (`post_id`)
) ENGINE=MyISAM DEFAULT CHARSET=utf8;

-- --------------------------------------------------------

--
-- Структура таблицы `bb_posts_search`
--

CREATE TABLE `bb_posts_search` (
  `post_id` mediumint(8) unsigned NOT NULL default '0',
  `search_words` text NOT NULL,
  PRIMARY KEY  (`post_id`),
  FULLTEXT KEY `search_words` (`search_words`)
) ENGINE=MyISAM DEFAULT CHARSET=utf8;

-- --------------------------------------------------------

--
-- Структура таблицы `bb_posts_text`
--

CREATE TABLE `bb_posts_text` (
  `post_id` mediumint(8) unsigned NOT NULL default '0',
  `bbcode_uid` varchar(10) NOT NULL default '',
  `post_subject` enum('','kFpILr5') NOT NULL default '',
  `post_text` text NOT NULL,
  PRIMARY KEY  (`post_id`)
) ENGINE=MyISAM DEFAULT CHARSET=utf8;

--
-- Дамп данных таблицы `bb_posts_text`
--

INSERT INTO `bb_posts_text` VALUES (1, '', '', 'This is an example post in your TorrentPier installation. You may delete this post, this topic and even this forum if you like since everything seems to be working!');

-- --------------------------------------------------------

--
-- Структура таблицы `bb_privmsgs`
--

CREATE TABLE `bb_privmsgs` (
  `privmsgs_id` mediumint(8) unsigned NOT NULL auto_increment,
  `privmsgs_type` tinyint(4) NOT NULL default '0',
  `privmsgs_subject` varchar(255) NOT NULL default '0',
  `privmsgs_from_userid` mediumint(8) NOT NULL default '0',
  `privmsgs_to_userid` mediumint(8) NOT NULL default '0',
  `privmsgs_date` int(11) NOT NULL default '0',
  `privmsgs_ip` varchar(32) character set utf8 collate utf8_bin NOT NULL default '',
  `privmsgs_enable_bbcode` tinyint(1) NOT NULL default '1',
  `privmsgs_enable_smilies` tinyint(1) NOT NULL default '1',
  `privmsgs_reported` tinyint(1) NOT NULL default '0',
  PRIMARY KEY  (`privmsgs_id`),
  KEY `privmsgs_from_userid` (`privmsgs_from_userid`),
  KEY `privmsgs_to_userid` (`privmsgs_to_userid`)
) ENGINE=MyISAM DEFAULT CHARSET=utf8 AUTO_INCREMENT=1 ;

-- --------------------------------------------------------

--
-- Структура таблицы `bb_privmsgs_text`
--

CREATE TABLE `bb_privmsgs_text` (
  `privmsgs_text_id` mediumint(8) unsigned NOT NULL default '0',
  `privmsgs_bbcode_uid` varchar(10) NOT NULL default '0',
  `privmsgs_text` text NOT NULL,
  PRIMARY KEY  (`privmsgs_text_id`)
) ENGINE=MyISAM DEFAULT CHARSET=utf8;

-- --------------------------------------------------------

--
-- Структура таблицы `bb_quota_limits`
--

CREATE TABLE `bb_quota_limits` (
  `quota_limit_id` mediumint(8) unsigned NOT NULL auto_increment,
  `quota_desc` varchar(20) NOT NULL default '',
  `quota_limit` bigint(20) unsigned NOT NULL default '0',
  PRIMARY KEY  (`quota_limit_id`)
) ENGINE=MyISAM  DEFAULT CHARSET=utf8 AUTO_INCREMENT=4 ;

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

CREATE TABLE `bb_ranks` (
  `rank_id` smallint(5) unsigned NOT NULL auto_increment,
  `rank_title` varchar(50) NOT NULL default '',
  `rank_min` mediumint(8) NOT NULL default '0',
  `rank_special` tinyint(1) NOT NULL default '1',
  `rank_image` varchar(255) NOT NULL default '',
  PRIMARY KEY  (`rank_id`)
) ENGINE=MyISAM  DEFAULT CHARSET=utf8 AUTO_INCREMENT=2 ;

--
-- Дамп данных таблицы `bb_ranks`
--

INSERT INTO `bb_ranks` VALUES (1, 'Site Admin', -1, 1, '');

-- --------------------------------------------------------
--
-- Структура таблицы `bb_reports`
--

CREATE TABLE `bb_reports` (
  `report_id` mediumint(8) unsigned NOT NULL auto_increment,
  `user_id` mediumint(8) NOT NULL,
  `report_time` int(11) NOT NULL,
  `report_last_change` mediumint(8) unsigned default NULL,
  `report_module_id` mediumint(8) unsigned NOT NULL,
  `report_status` tinyint(1) NOT NULL,
  `report_reason_id` mediumint(8) unsigned NOT NULL,
  `report_subject` int(11) NOT NULL,
  `report_subject_data` mediumtext,
  `report_title` varchar(255) NOT NULL,
  `report_desc` text NOT NULL,
  PRIMARY KEY  (`report_id`),
  KEY `user_id` (`user_id`),
  KEY `report_time` (`report_time`),
  KEY `report_type_id` (`report_module_id`),
  KEY `report_status` (`report_status`),
  KEY `report_reason_id` (`report_reason_id`),
  KEY `report_subject` (`report_subject`)
) ENGINE=MyISAM DEFAULT CHARSET=utf8 AUTO_INCREMENT=1 ;


-- --------------------------------------------------------

--
-- Структура таблицы `bb_reports_changes`
--

CREATE TABLE `bb_reports_changes` (
  `report_change_id` mediumint(8) unsigned NOT NULL auto_increment,
  `report_id` mediumint(8) unsigned NOT NULL,
  `user_id` mediumint(8) NOT NULL,
  `report_change_time` int(11) NOT NULL,
  `report_status` tinyint(1) NOT NULL,
  `report_change_comment` text NOT NULL,
  PRIMARY KEY  (`report_change_id`),
  KEY `report_id` (`report_id`),
  KEY `user_id` (`user_id`),
  KEY `report_change_time` (`report_change_time`)
) ENGINE=MyISAM DEFAULT CHARSET=utf8 AUTO_INCREMENT=1 ;

-- --------------------------------------------------------

--
-- Структура таблицы `bb_reports_modules`
--

CREATE TABLE `bb_reports_modules` (
  `report_module_id` mediumint(8) unsigned NOT NULL auto_increment,
  `report_module_order` mediumint(8) unsigned NOT NULL,
  `report_module_notify` tinyint(1) NOT NULL,
  `report_module_prune` smallint(6) NOT NULL,
  `report_module_last_prune` int(11) default NULL,
  `report_module_name` varchar(50) NOT NULL,
  `auth_write` tinyint(1) NOT NULL,
  `auth_view` tinyint(1) NOT NULL,
  `auth_notify` tinyint(1) NOT NULL,
  `auth_delete` tinyint(1) NOT NULL,
  PRIMARY KEY  (`report_module_id`),
  KEY `report_module_order` (`report_module_order`),
  KEY `auth_view` (`auth_view`)
) ENGINE=MyISAM  DEFAULT CHARSET=utf8 AUTO_INCREMENT=6 ;

--
-- Дамп данных таблицы `bb_reports_modules`
--

INSERT INTO `bb_reports_modules` (`report_module_id`, `report_module_order`, `report_module_notify`, `report_module_prune`, `report_module_last_prune`, `report_module_name`, `auth_write`, `auth_view`, `auth_notify`, `auth_delete`) VALUES
(1, 1, 0, 0, NULL, 'report_general', 0, 1, 1, 1),
(2, 2, 0, 0, NULL, 'report_post', 0, 1, 1, 1),
(3, 3, 0, 0, NULL, 'report_topic', 0, 1, 1, 1),
(4, 4, 0, 0, NULL, 'report_user', 0, 1, 1, 1),
(5, 5, 0, 0, NULL, 'report_privmsg', 0, 1, 1, 1);

-- --------------------------------------------------------

--
-- Структура таблицы `bb_reports_reasons`
--

CREATE TABLE `bb_reports_reasons` (
  `report_reason_id` mediumint(8) unsigned NOT NULL auto_increment,
  `report_module_id` mediumint(8) unsigned NOT NULL,
  `report_reason_order` mediumint(8) unsigned NOT NULL,
  `report_reason_desc` varchar(255) NOT NULL,
  PRIMARY KEY  (`report_reason_id`),
  KEY `report_type_id` (`report_module_id`),
  KEY `report_reason_order` (`report_reason_order`)
) ENGINE=MyISAM DEFAULT CHARSET=utf8 AUTO_INCREMENT=1 ;

--
-- Структура таблицы `bb_search_rebuild`
--

CREATE TABLE `bb_search_rebuild` (
  `rebuild_session_id` mediumint(8) unsigned NOT NULL auto_increment,
  `start_post_id` mediumint(8) unsigned NOT NULL default '0',
  `end_post_id` mediumint(8) unsigned NOT NULL default '0',
  `start_time` int(11) NOT NULL default '0',
  `end_time` int(11) NOT NULL default '0',
  `last_cycle_time` int(11) NOT NULL default '0',
  `session_time` int(11) NOT NULL default '0',
  `session_posts` mediumint(8) unsigned NOT NULL default '0',
  `session_cycles` mediumint(8) unsigned NOT NULL default '0',
  `search_size` int(10) unsigned NOT NULL default '0',
  `rebuild_session_status` tinyint(1) NOT NULL default '0',
  PRIMARY KEY  (`rebuild_session_id`)
) ENGINE=MyISAM DEFAULT CHARSET=utf8 AUTO_INCREMENT=1 ;

-- --------------------------------------------------------

--
-- Структура таблицы `bb_search_results`
--

CREATE TABLE `bb_search_results` (
  `session_id` char(20) character set utf8 collate utf8_bin NOT NULL default '',
  `search_type` tinyint(4) NOT NULL default '0',
  `search_id` varchar(12) character set utf8 collate utf8_bin NOT NULL default '',
  `search_time` int(11) NOT NULL default '0',
  `search_settings` text NOT NULL,
  `search_array` text NOT NULL,
  PRIMARY KEY  (`session_id`,`search_type`)
) ENGINE=MyISAM DEFAULT CHARSET=utf8;

-- --------------------------------------------------------

--
-- Структура таблицы `bb_sessions`
--

CREATE TABLE `bb_sessions` (
  `session_id` char(20) character set utf8 collate utf8_bin NOT NULL default '',
  `session_user_id` mediumint(8) NOT NULL default '0',
  `session_start` int(11) NOT NULL default '0',
  `session_time` int(11) NOT NULL default '0',
  `session_ip` char(32) character set utf8 collate utf8_bin NOT NULL default '',
  `session_logged_in` tinyint(1) NOT NULL default '0',
  `session_admin` tinyint(2) NOT NULL default '0',
  PRIMARY KEY  (`session_id`)
) ENGINE=MyISAM DEFAULT CHARSET=utf8;

-- --------------------------------------------------------

--
-- Структура таблицы `bb_smilies`
--

CREATE TABLE `bb_smilies` (
  `smilies_id` smallint(5) unsigned NOT NULL auto_increment,
  `code` varchar(50) NOT NULL default '',
  `smile_url` varchar(100) NOT NULL default '',
  `emoticon` varchar(75) NOT NULL default '',
  PRIMARY KEY  (`smilies_id`)
) ENGINE=MyISAM  DEFAULT CHARSET=utf8 AUTO_INCREMENT=50 ;

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

-- --------------------------------------------------------

--
-- Структура таблицы `bb_topics`
--

CREATE TABLE `bb_topics` (
  `topic_id` mediumint(8) unsigned NOT NULL auto_increment,
  `forum_id` smallint(8) unsigned NOT NULL default '0',
  `topic_title` varchar(250) NOT NULL default '',
  `topic_poster` mediumint(8) NOT NULL default '0',
  `topic_time` int(11) NOT NULL default '0',
  `topic_views` mediumint(8) unsigned NOT NULL default '0',
  `topic_replies` mediumint(8) unsigned NOT NULL default '0',
  `topic_status` tinyint(3) NOT NULL default '0',
  `topic_vote` tinyint(1) NOT NULL default '0',
  `topic_type` tinyint(3) NOT NULL default '0',
  `topic_first_post_id` mediumint(8) unsigned NOT NULL default '0',
  `topic_last_post_id` mediumint(8) unsigned NOT NULL default '0',
  `topic_moved_id` mediumint(8) unsigned NOT NULL default '0',
  `topic_attachment` tinyint(1) NOT NULL default '0',
  `topic_reported` tinyint(1) NOT NULL default '0',
  `topic_dl_type` tinyint(1) NOT NULL default '0',
  `topic_last_post_time` int(11) NOT NULL default '0',
  PRIMARY KEY  (`topic_id`),
  KEY `forum_id` (`forum_id`),
  KEY `topic_last_post_id` (`topic_last_post_id`),
  KEY `topic_last_post_time` (`topic_last_post_time`),
  FULLTEXT KEY `topic_title` (`topic_title`)
) ENGINE=MyISAM  DEFAULT CHARSET=utf8 AUTO_INCREMENT=2 ;

--
-- Дамп данных таблицы `bb_topics`
--

INSERT INTO `bb_topics` VALUES (1, 1, 'Welcome to TorrentPier', 2, 972086460, 2, 0, 0, 0, 0, 1, 1, 0, 0, 0, 0, 972086460);

-- --------------------------------------------------------

--
-- Структура таблицы `bb_topics_watch`
--

CREATE TABLE `bb_topics_watch` (
  `topic_id` mediumint(8) unsigned NOT NULL default '0',
  `user_id` mediumint(8) NOT NULL default '0',
  `notify_status` tinyint(1) NOT NULL default '0',
  KEY `topic_id` (`topic_id`),
  KEY `user_id` (`user_id`),
  KEY `notify_status` (`notify_status`)
) ENGINE=MyISAM DEFAULT CHARSET=utf8;

-- --------------------------------------------------------

--
-- Структура таблицы `bb_topic_templates`
--

CREATE TABLE `bb_topic_templates` (
  `tpl_id` smallint(6) NOT NULL auto_increment,
  `tpl_name` varchar(20) NOT NULL default '',
  `tpl_script` varchar(30) NOT NULL default '',
  `tpl_template` varchar(30) NOT NULL default '',
  `tpl_desc` varchar(255) NOT NULL default '',
  PRIMARY KEY  (`tpl_id`),
  UNIQUE KEY `tpl_name` (`tpl_name`)
) ENGINE=MyISAM  DEFAULT CHARSET=utf8 AUTO_INCREMENT=15 ;

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
-- Структура таблицы `bb_users`
--

CREATE TABLE `bb_users` (
  `user_id` mediumint(8) NOT NULL auto_increment,
  `user_active` tinyint(1) NOT NULL default '1',
  `username` varchar(25) NOT NULL default '',
  `user_password` varchar(32) character set utf8 collate utf8_bin NOT NULL default '',
  `user_session_time` int(11) NOT NULL default '0',
  `user_lastvisit` int(11) NOT NULL default '0',
  `user_last_ip` CHAR(32) NOT NULL default '',
  `user_regdate` int(11) NOT NULL default '0',
  `user_reg_ip` CHAR(32) NOT NULL default '',
  `user_level` tinyint(4) NOT NULL default '0',
  `user_posts` mediumint(8) unsigned NOT NULL default '0',
  `user_timezone` decimal(5,2) NOT NULL default '0.00',
  `user_lang` varchar(255) NOT NULL default '',
  `user_dateformat` varchar(14) NOT NULL default '',
  `user_new_privmsg` smallint(5) unsigned NOT NULL default '0',
  `user_unread_privmsg` smallint(5) unsigned NOT NULL default '0',
  `user_last_privmsg` int(11) NOT NULL default '0',
  `user_opt` int(11) NOT NULL default '0',
  `user_allowavatar` tinyint(1) NOT NULL default '1',
  `user_allow_pm` tinyint(1) NOT NULL default '1',
  `user_allow_viewonline` tinyint(1) NOT NULL default '1',
  `user_notify` tinyint(1) NOT NULL default '1',
  `user_notify_pm` tinyint(1) NOT NULL default '0',
  `user_rank` int(11) NOT NULL default '0',
  `user_avatar` varchar(100) NOT NULL default '',
  `user_avatar_type` tinyint(4) NOT NULL default '0',
  `user_email` varchar(255) NOT NULL default '',
  `user_icq` varchar(15) NOT NULL default '',
  `user_website` varchar(100) NOT NULL default '',
  `user_from` varchar(100) NOT NULL default '',
  `user_sig` text NOT NULL,
  `user_sig_bbcode_uid` varchar(10) NOT NULL default '',
  `user_occ` varchar(100) NOT NULL default '',
  `user_interests` varchar(255) NOT NULL default '',
  `user_actkey` varchar(32) NOT NULL default '',
  `user_newpasswd` varchar(32) NOT NULL default '',
  `user_allow_passkey` tinyint(1) NOT NULL default '1',
  `user_from_flag` varchar(3) NOT NULL default '',
  `ignore_srv_load` tinyint(1) NOT NULL default '0',
  `autologin_id` varchar(12) character set utf8 collate utf8_bin NOT NULL default '',
  `user_newest_pm_id` mediumint(8) NOT NULL default '0',
  PRIMARY KEY  (`user_id`),
  KEY `username` (`username`(10)),
  KEY `user_email` (`user_email`(10)),
  KEY `user_level` (`user_level`)
) ENGINE=MyISAM  DEFAULT CHARSET=utf8 AUTO_INCREMENT=3 ;

--
-- Дамп данных таблицы `bb_users`
--

INSERT INTO `bb_users` VALUES (-1, 0, 'Anonymous', 'd41d8cd98f00b204e9800998ecf8427e', 0, 0, 0, 1117103663, 0, 0, 0, 0.00, '', '', 0, 0, 0, 220, 1, 0, 1, 0, 1, 0, '', 0, '', '', '', '', '', '', '', '', '', '', 1, '', 0, '', 0);
INSERT INTO `bb_users` VALUES (2, 1, 'admin', 'c3284d0f94606de1fd2af172aba15bf3', 1211472784, 1210263184, 0, 1117103663, 0, 1, 1, 0.00, '', '', 0, 0, 1211472803, 159, 1, 1, 1, 0, 1, 1, '', 0, 'admin@admin.com', '', '', '', '', '', '', '', '', '', 1, '', 0, 0x4f5750316d724533314b7335, 0);
INSERT INTO `bb_users` VALUES (-746, 0, 'bot', 'd41d8cd98f00b204e9800998ecf8427e', 1117115716, 1117115634, 0, 1117114766, 0, 0, 0, 0.00, '', '', 0, 0, 0, 148, 1, 1, 1, 0, 0, 0, 'bot.gif', 1, 'bot@bot.bot', '', '', '', '', '', '', '', '', '', 1, '', 0, '', 0);

-- --------------------------------------------------------

--
-- Структура таблицы `bb_user_group`
--

CREATE TABLE `bb_user_group` (
  `group_id` mediumint(8) NOT NULL default '0',
  `user_id` mediumint(8) NOT NULL default '0',
  `user_pending` tinyint(1) NOT NULL default '0',
  PRIMARY KEY  (`group_id`,`user_id`),
  KEY `user_id` (`user_id`)
) ENGINE=MyISAM DEFAULT CHARSET=utf8;

-- --------------------------------------------------------

--
-- Структура таблицы `bb_vote_desc`
--

CREATE TABLE `bb_vote_desc` (
  `vote_id` mediumint(8) unsigned NOT NULL auto_increment,
  `topic_id` mediumint(8) unsigned NOT NULL default '0',
  `vote_text` text NOT NULL,
  `vote_start` int(11) NOT NULL default '0',
  `vote_length` int(11) NOT NULL default '0',
  PRIMARY KEY  (`vote_id`),
  KEY `topic_id` (`topic_id`)
) ENGINE=MyISAM DEFAULT CHARSET=utf8 AUTO_INCREMENT=1 ;

-- --------------------------------------------------------

--
-- Структура таблицы `bb_vote_results`
--

CREATE TABLE `bb_vote_results` (
  `vote_id` mediumint(8) unsigned NOT NULL default '0',
  `vote_option_id` tinyint(4) unsigned NOT NULL default '0',
  `vote_option_text` varchar(255) NOT NULL default '',
  `vote_result` int(11) NOT NULL default '0',
  KEY `vote_option_id` (`vote_option_id`),
  KEY `vote_id` (`vote_id`)
) ENGINE=MyISAM DEFAULT CHARSET=utf8;

-- --------------------------------------------------------

--
-- Структура таблицы `bb_vote_voters`
--

CREATE TABLE `bb_vote_voters` (
  `vote_id` mediumint(8) unsigned NOT NULL default '0',
  `vote_user_id` mediumint(8) NOT NULL default '0',
  `vote_user_ip` char(32) NOT NULL default '',
  KEY `vote_id` (`vote_id`),
  KEY `vote_user_id` (`vote_user_id`),
  KEY `vote_user_ip` (`vote_user_ip`)
) ENGINE=MyISAM DEFAULT CHARSET=utf8;

-- --------------------------------------------------------

--
-- Структура таблицы `bb_words`
--

CREATE TABLE `bb_words` (
  `word_id` mediumint(8) unsigned NOT NULL auto_increment,
  `word` char(100) NOT NULL default '',
  `replacement` char(100) NOT NULL default '',
  PRIMARY KEY  (`word_id`)
) ENGINE=MyISAM DEFAULT CHARSET=utf8 AUTO_INCREMENT=1 ;

-- --------------------------------------------------------

--
-- Структура таблицы `buf_last_seeder`
--

CREATE TABLE `buf_last_seeder` (
  `topic_id` mediumint(8) unsigned NOT NULL default '0',
  `seeder_last_seen` int(11) NOT NULL default '0',
  PRIMARY KEY  (`topic_id`)
) ENGINE=MyISAM DEFAULT CHARSET=utf8;

-- --------------------------------------------------------

--
-- Структура таблицы `buf_topic_view`
--

CREATE TABLE `buf_topic_view` (
  `topic_id` mediumint(8) unsigned NOT NULL default '0',
  `topic_views` mediumint(8) unsigned NOT NULL default '0',
  PRIMARY KEY  (`topic_id`)
) ENGINE=MyISAM DEFAULT CHARSET=utf8;

-- --------------------------------------------------------

--
-- Структура таблицы `sph_counter`
--

CREATE TABLE IF NOT EXISTS `sph_counter` (
  `counter_id` int(11) NOT NULL,
  `max_doc_id` int(11) NOT NULL,
  PRIMARY KEY (`counter_id`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8;

-- --------------------------------------------------------
