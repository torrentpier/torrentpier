SET SQL_MODE = "";

-- ----------------------------
-- Table structure for `bb_auth_access`
-- ----------------------------
DROP TABLE IF EXISTS `bb_auth_access`;
CREATE TABLE IF NOT EXISTS `bb_auth_access` (
  `group_id` mediumint(8) NOT NULL DEFAULT '0',
  `forum_id` smallint(5) unsigned NOT NULL DEFAULT '0',
  `forum_perm` int(11) NOT NULL DEFAULT '0',
  PRIMARY KEY (`group_id`,`forum_id`),
  KEY `forum_id` (`forum_id`)
) ENGINE=MyISAM DEFAULT CHARSET=utf8;

-- ----------------------------
-- Records of bb_auth_access
-- ----------------------------

-- ----------------------------
-- Table structure for `bb_auth_access_snap`
-- ----------------------------
DROP TABLE IF EXISTS `bb_auth_access_snap`;
CREATE TABLE IF NOT EXISTS `bb_auth_access_snap` (
  `user_id` mediumint(9) NOT NULL DEFAULT '0',
  `forum_id` smallint(6) NOT NULL DEFAULT '0',
  `forum_perm` int(11) NOT NULL DEFAULT '0',
  PRIMARY KEY (`user_id`,`forum_id`)
) ENGINE=MyISAM DEFAULT CHARSET=utf8;

-- ----------------------------
-- Records of bb_auth_access_snap
-- ----------------------------

-- ----------------------------
-- Table structure for `bb_banlist`
-- ----------------------------
DROP TABLE IF EXISTS `bb_banlist`;
CREATE TABLE IF NOT EXISTS `bb_banlist` (
  `ban_id` mediumint(8) unsigned NOT NULL AUTO_INCREMENT,
  `ban_userid` mediumint(8) NOT NULL DEFAULT '0',
  `ban_ip` varchar(32) NOT NULL DEFAULT '',
  `ban_email` varchar(255) NOT NULL DEFAULT '',
  PRIMARY KEY (`ban_id`),
  KEY `ban_ip_user_id` (`ban_ip`,`ban_userid`)
) ENGINE=MyISAM DEFAULT CHARSET=utf8;

-- ----------------------------
-- Records of bb_banlist
-- ----------------------------

-- ----------------------------
-- Table structure for `bb_bt_dlstatus`
-- ----------------------------
DROP TABLE IF EXISTS `bb_bt_dlstatus`;
CREATE TABLE IF NOT EXISTS `bb_bt_dlstatus` (
  `user_id` mediumint(9) NOT NULL DEFAULT '0',
  `topic_id` mediumint(8) unsigned NOT NULL DEFAULT '0',
  `user_status` tinyint(1) NOT NULL DEFAULT '0',
  `last_modified_dlstatus` timestamp NOT NULL DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
  PRIMARY KEY (`user_id`,`topic_id`),
  KEY `topic_id` (`topic_id`)
) ENGINE=MyISAM DEFAULT CHARSET=utf8;

-- ----------------------------
-- Records of bb_bt_dlstatus
-- ----------------------------

-- ----------------------------
-- Table structure for `bb_bt_dlstatus_snap`
-- ----------------------------
DROP TABLE IF EXISTS `bb_bt_dlstatus_snap`;
CREATE TABLE IF NOT EXISTS `bb_bt_dlstatus_snap` (
  `topic_id` mediumint(8) unsigned NOT NULL DEFAULT '0',
  `dl_status` tinyint(4) NOT NULL DEFAULT '0',
  `users_count` smallint(5) unsigned NOT NULL DEFAULT '0',
  KEY `topic_id` (`topic_id`)
) ENGINE=MyISAM DEFAULT CHARSET=utf8;

-- ----------------------------
-- Records of bb_bt_dlstatus_snap
-- ----------------------------

-- ----------------------------
-- Table structure for `bb_bt_last_torstat`
-- ----------------------------
DROP TABLE IF EXISTS `bb_bt_last_torstat`;
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
  PRIMARY KEY (`topic_id`,`user_id`) USING BTREE
) ENGINE=MyISAM DEFAULT CHARSET=utf8;

-- ----------------------------
-- Records of bb_bt_last_torstat
-- ----------------------------

-- ----------------------------
-- Table structure for `bb_bt_last_userstat`
-- ----------------------------
DROP TABLE IF EXISTS `bb_bt_last_userstat`;
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

-- ----------------------------
-- Records of bb_bt_last_userstat
-- ----------------------------

-- ----------------------------
-- Table structure for `bb_bt_torhelp`
-- ----------------------------
DROP TABLE IF EXISTS `bb_bt_torhelp`;
CREATE TABLE IF NOT EXISTS `bb_bt_torhelp` (
  `user_id` mediumint(9) NOT NULL DEFAULT '0',
  `topic_id_csv` text NOT NULL,
  PRIMARY KEY (`user_id`)
) ENGINE=MyISAM DEFAULT CHARSET=utf8;

-- ----------------------------
-- Records of bb_bt_torhelp
-- ----------------------------

-- ----------------------------
-- Table structure for `bb_bt_torrents`
-- ----------------------------
DROP TABLE IF EXISTS `bb_bt_torrents`;
CREATE TABLE IF NOT EXISTS `bb_bt_torrents` (
  `info_hash` varbinary(20) NOT NULL DEFAULT '',
  `poster_id` mediumint(9) NOT NULL DEFAULT '0',
  `topic_id` mediumint(8) unsigned NOT NULL DEFAULT '0',
  `forum_id` smallint(5) unsigned NOT NULL DEFAULT '0',
  `size` bigint(20) unsigned NOT NULL DEFAULT '0',
  `reg_time` int(11) NOT NULL DEFAULT '0',
  `call_seed_time` int(11) NOT NULL DEFAULT '0',
  `complete_count` mediumint(8) unsigned NOT NULL DEFAULT '0',
  `seeder_last_seen` int(11) NOT NULL DEFAULT '0',
  `tor_status` tinyint(4) NOT NULL DEFAULT '0',
  `checked_user_id` mediumint(8) NOT NULL DEFAULT '0',
  `checked_time` int(11) NOT NULL DEFAULT '0',
  `tor_type` tinyint(1) NOT NULL DEFAULT '0',
  `speed_up` int(11) NOT NULL DEFAULT '0',
  `speed_down` int(11) NOT NULL DEFAULT '0',
  PRIMARY KEY (`info_hash`),
  UNIQUE KEY `topic_id` (`topic_id`),
  KEY `reg_time` (`reg_time`),
  KEY `forum_id` (`forum_id`),
  KEY `poster_id` (`poster_id`)
) ENGINE=MyISAM DEFAULT CHARSET=utf8;

-- ----------------------------
-- Records of bb_bt_torrents
-- ----------------------------

-- ----------------------------
-- Table structure for `bb_bt_torstat`
-- ----------------------------
DROP TABLE IF EXISTS `bb_bt_torstat`;
CREATE TABLE IF NOT EXISTS `bb_bt_torstat` (
  `topic_id` mediumint(8) unsigned NOT NULL DEFAULT '0',
  `user_id` mediumint(9) NOT NULL DEFAULT '0',
  `last_modified_torstat` timestamp NOT NULL DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
  `completed` tinyint(1) NOT NULL DEFAULT '0',
  PRIMARY KEY (`topic_id`,`user_id`)
) ENGINE=MyISAM DEFAULT CHARSET=utf8;

-- ----------------------------
-- Records of bb_bt_torstat
-- ----------------------------

-- ----------------------------
-- Table structure for `bb_bt_tracker`
-- ----------------------------
DROP TABLE IF EXISTS `bb_bt_tracker`;
CREATE TABLE IF NOT EXISTS `bb_bt_tracker` (
  `peer_hash` varchar(32) CHARACTER SET utf8 COLLATE utf8_bin NOT NULL DEFAULT '',
  `topic_id` mediumint(8) unsigned NOT NULL DEFAULT '0',
  `peer_id` varchar(20) NOT NULL DEFAULT '0',
  `user_id` mediumint(9) NOT NULL DEFAULT '0',
  `ip` char(8) CHARACTER SET utf8 COLLATE utf8_bin NOT NULL DEFAULT '0',
  `ipv6` varchar(32) DEFAULT NULL,
  `port` smallint(5) unsigned NOT NULL DEFAULT '0',
  `client` varchar(51) NOT NULL DEFAULT 'Unknown',
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
  `complete_percent` bigint(20) NOT NULL DEFAULT '0',
  `complete` int(11) NOT NULL DEFAULT '0',
  PRIMARY KEY (`peer_hash`),
  KEY `topic_id` (`topic_id`),
  KEY `user_id` (`user_id`)
) ENGINE=MyISAM DEFAULT CHARSET=utf8;

-- ----------------------------
-- Records of bb_bt_tracker
-- ----------------------------

-- ----------------------------
-- Table structure for `bb_bt_tracker_snap`
-- ----------------------------
DROP TABLE IF EXISTS `bb_bt_tracker_snap`;
CREATE TABLE IF NOT EXISTS `bb_bt_tracker_snap` (
  `topic_id` mediumint(8) unsigned NOT NULL DEFAULT '0',
  `seeders` mediumint(8) unsigned NOT NULL DEFAULT '0',
  `leechers` mediumint(8) unsigned NOT NULL DEFAULT '0',
  `speed_up` int(10) unsigned NOT NULL DEFAULT '0',
  `speed_down` int(10) unsigned NOT NULL DEFAULT '0',
  PRIMARY KEY (`topic_id`)
) ENGINE=MyISAM DEFAULT CHARSET=utf8;

-- ----------------------------
-- Records of bb_bt_tracker_snap
-- ----------------------------

-- ----------------------------
-- Table structure for `bb_bt_users`
-- ----------------------------
DROP TABLE IF EXISTS `bb_bt_users`;
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

-- ----------------------------
-- Records of bb_bt_users
-- ----------------------------

-- ----------------------------
-- Table structure for `bb_bt_user_settings`
-- ----------------------------
DROP TABLE IF EXISTS `bb_bt_user_settings`;
CREATE TABLE IF NOT EXISTS `bb_bt_user_settings` (
  `user_id` mediumint(9) NOT NULL DEFAULT '0',
  `tor_search_set` text NOT NULL,
  `last_modified` int(11) NOT NULL DEFAULT '0',
  PRIMARY KEY (`user_id`)
) ENGINE=MyISAM DEFAULT CHARSET=utf8;

-- ----------------------------
-- Records of bb_bt_user_settings
-- ----------------------------

-- ----------------------------
-- Table structure for `bb_categories`
-- ----------------------------
DROP TABLE IF EXISTS `bb_categories`;
CREATE TABLE IF NOT EXISTS `bb_categories` (
  `cat_id` smallint(5) unsigned NOT NULL AUTO_INCREMENT,
  `cat_title` varchar(100) NOT NULL DEFAULT '',
  `cat_order` smallint(5) unsigned NOT NULL DEFAULT '0',
  PRIMARY KEY (`cat_id`),
  KEY `cat_order` (`cat_order`)
) ENGINE=MyISAM DEFAULT CHARSET=utf8;

-- ----------------------------
-- Records of bb_categories
-- ----------------------------
INSERT INTO `bb_categories` VALUES ('1', 'Ваша первая категория', '10');

-- ----------------------------
-- Table structure for `bb_config`
-- ----------------------------
DROP TABLE IF EXISTS `bb_config`;
CREATE TABLE IF NOT EXISTS `bb_config` (
  `config_name` varchar(255) NOT NULL DEFAULT '',
  `config_value` text NOT NULL,
  PRIMARY KEY (`config_name`)
) ENGINE=MyISAM DEFAULT CHARSET=utf8;

-- ----------------------------
-- Records of bb_config
-- ----------------------------
INSERT INTO `bb_config` VALUES ('allow_autologin', '1');
INSERT INTO `bb_config` VALUES ('allow_bbcode', '1');
INSERT INTO `bb_config` VALUES ('allow_namechange', '0');
INSERT INTO `bb_config` VALUES ('allow_sig', '1');
INSERT INTO `bb_config` VALUES ('allow_smilies', '1');
INSERT INTO `bb_config` VALUES ('board_disable', '0');
INSERT INTO `bb_config` VALUES ('board_startdate', UNIX_TIMESTAMP());
INSERT INTO `bb_config` VALUES ('board_timezone', '0');
INSERT INTO `bb_config` VALUES ('bonus_upload', '');
INSERT INTO `bb_config` VALUES ('bonus_upload_price', '');
INSERT INTO `bb_config` VALUES ('birthday_enabled', '1');
INSERT INTO `bb_config` VALUES ('birthday_max_age', '99');
INSERT INTO `bb_config` VALUES ('birthday_min_age', '10');
INSERT INTO `bb_config` VALUES ('birthday_check_day', '7');
INSERT INTO `bb_config` VALUES ('bt_allow_spmode_change', '1');
INSERT INTO `bb_config` VALUES ('bt_announce_url', 'https://demo.torrentpier.me/bt/announce.php');
INSERT INTO `bb_config` VALUES ('bt_disable_dht', '0');
INSERT INTO `bb_config` VALUES ('bt_del_addit_ann_urls', '1');
INSERT INTO `bb_config` VALUES ('bt_dl_list_only_1st_page', '1');
INSERT INTO `bb_config` VALUES ('bt_dl_list_only_count', '1');
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
INSERT INTO `bb_config` VALUES ('bt_unset_dltype_on_tor_unreg', '1');
INSERT INTO `bb_config` VALUES ('cron_last_check', '0');
INSERT INTO `bb_config` VALUES ('default_dateformat', 'Y-m-d H:i');
INSERT INTO `bb_config` VALUES ('default_lang', 'ru');
INSERT INTO `bb_config` VALUES ('flood_interval', '15');
INSERT INTO `bb_config` VALUES ('hot_threshold', '300');
INSERT INTO `bb_config` VALUES ('login_reset_time', '30');
INSERT INTO `bb_config` VALUES ('max_autologin_time', '10');
INSERT INTO `bb_config` VALUES ('max_login_attempts', '5');
INSERT INTO `bb_config` VALUES ('max_poll_options', '6');
INSERT INTO `bb_config` VALUES ('max_sig_chars', '255');
INSERT INTO `bb_config` VALUES ('posts_per_page', '15');
INSERT INTO `bb_config` VALUES ('prune_enable', '1');
INSERT INTO `bb_config` VALUES ('record_online_date', UNIX_TIMESTAMP());
INSERT INTO `bb_config` VALUES ('record_online_users', '0');
INSERT INTO `bb_config` VALUES ('seed_bonus_enabled', '1');
INSERT INTO `bb_config` VALUES ('seed_bonus_release', '');
INSERT INTO `bb_config` VALUES ('seed_bonus_points', '');
INSERT INTO `bb_config` VALUES ('seed_bonus_tor_size', '0');
INSERT INTO `bb_config` VALUES ('seed_bonus_user_regdate', '0');
INSERT INTO `bb_config` VALUES ('site_desc', 'A little text to describe your forum');
INSERT INTO `bb_config` VALUES ('sitemap_time', '');
INSERT INTO `bb_config` VALUES ('sitename', 'TorrentPier - Bittorrent-tracker engine');
INSERT INTO `bb_config` VALUES ('smilies_path', 'styles/images/smiles');
INSERT INTO `bb_config` VALUES ('static_sitemap', '');
INSERT INTO `bb_config` VALUES ('topics_per_page', '50');
INSERT INTO `bb_config` VALUES ('xs_use_cache', '1');
INSERT INTO `bb_config` VALUES ('cron_enabled', '1');
INSERT INTO `bb_config` VALUES ('cron_check_interval', '180');
INSERT INTO `bb_config` VALUES ('magnet_links_enabled', '1');
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
INSERT INTO `bb_config` VALUES ('whois_info', 'http://ip-whois.net/ip_geos.php?ip=');
INSERT INTO `bb_config` VALUES ('show_mod_index', '0');
INSERT INTO `bb_config` VALUES ('premod', '0');
INSERT INTO `bb_config` VALUES ('tor_comment', '1');
INSERT INTO `bb_config` VALUES ('terms', '');

-- ----------------------------
-- Table structure for `bb_cron`
-- ----------------------------
DROP TABLE IF EXISTS `bb_cron`;
CREATE TABLE IF NOT EXISTS `bb_cron` (
  `cron_id` smallint(5) unsigned NOT NULL AUTO_INCREMENT,
  `cron_active` tinyint(4) NOT NULL DEFAULT '1',
  `cron_title` char(120) NOT NULL DEFAULT '',
  `cron_script` char(120) NOT NULL DEFAULT '',
  `schedule` enum('hourly','daily','weekly','monthly','interval') NOT NULL DEFAULT 'daily',
  `run_day` enum('1','2','3','4','5','6','7','8','9','10','11','12','13','14','15','16','17','18','19','20','21','22','23','24','25','26','27','28') DEFAULT NULL,
  `run_time` time DEFAULT '04:00:00',
  `run_order` tinyint(4) unsigned NOT NULL DEFAULT '0',
  `last_run` datetime NOT NULL DEFAULT '1000-01-01 00:00:00',
  `next_run` datetime NOT NULL DEFAULT '1000-01-01 00:00:00',
  `run_interval` time DEFAULT NULL DEFAULT '0',
  `log_enabled` tinyint(1) NOT NULL DEFAULT '0',
  `log_file` char(120) NOT NULL DEFAULT '',
  `log_sql_queries` tinyint(4) NOT NULL DEFAULT '0',
  `disable_board` tinyint(1) NOT NULL DEFAULT '0',
  `run_counter` bigint(20) unsigned NOT NULL DEFAULT '0',
  PRIMARY KEY (`cron_id`),
  UNIQUE KEY `title` (`cron_title`),
  UNIQUE KEY `script` (`cron_script`)
) ENGINE=MyISAM DEFAULT CHARSET=utf8;

-- ----------------------------
-- Records of bb_cron
-- ----------------------------
INSERT INTO `bb_cron` VALUES ('', '1', 'Board maintenance', 'board_maintenance.php', 'daily', '', '05:00:00', '40', '', '', '', '1', '', '0', '1', '0');
INSERT INTO `bb_cron` VALUES ('', '1', 'Prune forums', 'prune_forums.php', 'daily', '', '05:00:00', '50', '', '', '', '1', '', '0', '1', '0');
INSERT INTO `bb_cron` VALUES ('', '1', 'Prune topic moved stubs', 'prune_topic_moved.php', 'daily', '', '05:00:00', '60', '', '', '', '1', '', '0', '1', '0');
INSERT INTO `bb_cron` VALUES ('', '1', 'Logs cleanup', 'clean_log.php', 'daily', '', '05:00:00', '70', '', '', '', '1', '', '0', '1', '0');
INSERT INTO `bb_cron` VALUES ('', '1', 'Tracker maintenance', 'tr_maintenance.php', 'daily', '', '05:00:00', '90', '', '', '', '1', '', '0', '1', '0');
INSERT INTO `bb_cron` VALUES ('', '1', 'Clean dlstat', 'clean_dlstat.php', 'daily', '', '05:00:00', '100', '', '', '', '1', '', '0', '1', '0');
INSERT INTO `bb_cron` VALUES ('', '1', 'Prune inactive users', 'prune_inactive_users.php', 'daily', '', '05:00:00', '110', '', '', '', '1', '', '0', '1', '0');
INSERT INTO `bb_cron` VALUES ('', '1', 'Sessions cleanup', 'sessions_cleanup.php', 'interval', '', '', '255', '', '', '00:03:00', '0', '', '0', '0', '0');
INSERT INTO `bb_cron` VALUES ('', '1', 'DS update cat_forums', 'ds_update_cat_forums.php', 'interval', '', '', '255', '', '', '00:05:00', '0', '', '0', '0', '0');
INSERT INTO `bb_cron` VALUES ('', '1', 'DS update stats', 'ds_update_stats.php', 'interval', '', '', '255', '', '', '00:10:00', '0', '', '0', '0', '0');
INSERT INTO `bb_cron` VALUES ('', '1', 'Flash topic view', 'flash_topic_view.php', 'interval', '', '', '255', '', '', '00:10:00', '0', '', '0', '0', '0');
INSERT INTO `bb_cron` VALUES ('', '1', 'Clean search results', 'clean_search_results.php', 'interval', '', '', '255', '', '', '00:10:00', '0', '', '0', '0', '0');
INSERT INTO `bb_cron` VALUES ('', '1', 'Tracker cleanup and dlstat', 'tr_cleanup_and_dlstat.php', 'interval', '', '', '20', '', '', '00:15:00', '0', '', '0', '0', '0');
INSERT INTO `bb_cron` VALUES ('', '1', 'Accrual seedbonus', 'tr_seed_bonus.php', 'interval', '', '', '25', '', '', '00:15:00', '0', '', '0', '0', '0');
INSERT INTO `bb_cron` VALUES ('', '1', 'Make tracker snapshot', 'tr_make_snapshot.php', 'interval', '', '', '10', '', '', '00:10:00', '0', '', '0', '0', '0');
INSERT INTO `bb_cron` VALUES ('', '1', 'Seeder last seen', 'tr_update_seeder_last_seen.php', 'interval', '', '', '255', '', '', '01:00:00', '0', '', '0', '0', '0');
INSERT INTO `bb_cron` VALUES ('', '1', 'Tracker dl-complete count', 'tr_complete_count.php', 'interval', '', '', '255', '', '', '06:00:00', '0', '', '0', '0', '0');
INSERT INTO `bb_cron` VALUES ('', '1', 'Sitemap update', 'sitemap.php', 'daily', '', '06:00:00', '30', '', '', '', '0', '', '0', '0', '0');
INSERT INTO `bb_cron` VALUES ('', '1', 'Update forums atom', 'update_forums_atom.php', 'interval', '', '', '255', '', '', '00:15:00', '0', '', '0', '0', '0');

-- ----------------------------
-- Table structure for `bb_disallow`
-- ----------------------------
DROP TABLE IF EXISTS `bb_disallow`;
CREATE TABLE IF NOT EXISTS `bb_disallow` (
  `disallow_id` mediumint(8) unsigned NOT NULL AUTO_INCREMENT,
  `disallow_username` varchar(25) NOT NULL DEFAULT '',
  PRIMARY KEY (`disallow_id`)
) ENGINE=MyISAM DEFAULT CHARSET=utf8;

-- ----------------------------
-- Records of bb_disallow
-- ----------------------------

-- ----------------------------
-- Table structure for `bb_forums`
-- ----------------------------
DROP TABLE IF EXISTS `bb_forums`;
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
  `forum_last_topic_time` INT NOT NULL,
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
  PRIMARY KEY (`forum_id`),
  KEY `forums_order` (`forum_order`),
  KEY `cat_id` (`cat_id`),
  KEY `forum_last_post_id` (`forum_last_post_id`),
  KEY `forum_parent` (`forum_parent`)
) ENGINE=MyISAM DEFAULT CHARSET=utf8;

-- ----------------------------
-- Records of bb_forums
-- ----------------------------
INSERT INTO `bb_forums` VALUES ('1', '1', 'Ваш первый форум', 'Описание вашего первого форума.', '0', '10', '1', '1', '1', '0', '0', '0', '0', '0', '1', '1', '1', '1', '3', '3', '1', '1', '1', '1', '0', '0', '0', '0', '1', '0', '0');

-- ----------------------------
-- Table structure for `bb_groups`
-- ----------------------------
DROP TABLE IF EXISTS `bb_groups`;
CREATE TABLE IF NOT EXISTS `bb_groups` (
  `group_id` mediumint(8) NOT NULL AUTO_INCREMENT,
  `avatar_ext_id` int(15) NOT NULL DEFAULT '0',
  `group_time` int(11) NOT NULL DEFAULT '0',
  `mod_time` int(11) NOT NULL DEFAULT '0',
  `group_type` tinyint(4) NOT NULL DEFAULT '1',
  `release_group` tinyint(4) NOT NULL DEFAULT '0',
  `group_name` varchar(40) NOT NULL DEFAULT '',
  `group_description` text NOT NULL,
  `group_signature` text NOT NULL,
  `group_moderator` mediumint(8) NOT NULL DEFAULT '0',
  `group_single_user` tinyint(1) NOT NULL DEFAULT '1',
  PRIMARY KEY (`group_id`),
  KEY `group_single_user` (`group_single_user`)
) ENGINE=MyISAM DEFAULT CHARSET=utf8;

-- ----------------------------
-- Records of bb_groups
-- ----------------------------

-- ----------------------------
-- Table structure for `bb_log`
-- ----------------------------
DROP TABLE IF EXISTS `bb_log`;
CREATE TABLE IF NOT EXISTS `bb_log` (
  `log_type_id` mediumint(8) unsigned NOT NULL DEFAULT '0',
  `log_user_id` mediumint(9) NOT NULL DEFAULT '0',
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

-- ----------------------------
-- Records of bb_log
-- ----------------------------

-- ----------------------------
-- Table structure for `bb_poll_users`
-- ----------------------------
DROP TABLE IF EXISTS `bb_poll_users`;
CREATE TABLE IF NOT EXISTS `bb_poll_users` (
  `topic_id` int(10) unsigned NOT NULL,
  `user_id` int(11) NOT NULL,
  `vote_ip` varchar(32) NOT NULL,
  `vote_dt` int(11) NOT NULL DEFAULT '0',
  PRIMARY KEY (`topic_id`,`user_id`)
) ENGINE=MyISAM DEFAULT CHARSET=utf8;

-- ----------------------------
-- Records of bb_poll_users
-- ----------------------------

-- ----------------------------
-- Table structure for `bb_poll_votes`
-- ----------------------------
DROP TABLE IF EXISTS `bb_poll_votes`;
CREATE TABLE IF NOT EXISTS `bb_poll_votes` (
  `topic_id` int(10) unsigned NOT NULL,
  `vote_id` tinyint(4) unsigned NOT NULL,
  `vote_text` varchar(255) NOT NULL,
  `vote_result` mediumint(8) unsigned NOT NULL,
  PRIMARY KEY (`topic_id`,`vote_id`)
) ENGINE=MyISAM DEFAULT CHARSET=utf8;

-- ----------------------------
-- Records of bb_poll_votes
-- ----------------------------

-- ----------------------------
-- Table structure for `bb_posts`
-- ----------------------------
DROP TABLE IF EXISTS `bb_posts`;
CREATE TABLE IF NOT EXISTS `bb_posts` (
  `post_id` mediumint(8) unsigned NOT NULL AUTO_INCREMENT,
  `topic_id` mediumint(8) unsigned NOT NULL DEFAULT '0',
  `forum_id` smallint(5) unsigned NOT NULL DEFAULT '0',
  `poster_id` mediumint(8) NOT NULL DEFAULT '0',
  `post_time` int(11) NOT NULL DEFAULT '0',
  `poster_ip` char(32) CHARACTER SET utf8 COLLATE utf8_bin NOT NULL DEFAULT '',
  `poster_rg_id` mediumint(8) NOT NULL DEFAULT '0',
  `attach_rg_sig` tinyint(4) NOT NULL DEFAULT '0',
  `post_username` varchar(25) NOT NULL DEFAULT '',
  `post_edit_time` int(11) NOT NULL DEFAULT '0',
  `post_edit_count` smallint(5) unsigned NOT NULL DEFAULT '0',
  `user_post` tinyint(1) NOT NULL DEFAULT '1',
  `mc_comment` text NOT NULL,
  `mc_type` tinyint(1) NOT NULL DEFAULT '0',
  `mc_user_id` mediumint(8) NOT NULL DEFAULT '0',
  PRIMARY KEY (`post_id`),
  KEY `topic_id` (`topic_id`),
  KEY `poster_id` (`poster_id`),
  KEY `post_time` (`post_time`),
  KEY `forum_id_post_time` (`forum_id`,`post_time`)
) ENGINE=MyISAM DEFAULT CHARSET=utf8;

-- ----------------------------
-- Records of bb_posts
-- ----------------------------
INSERT INTO `bb_posts` VALUES ('1', '1', '1', '2', UNIX_TIMESTAMP(), '', '0', '0', '', '0', '0', '1', '', '0', '0');

-- ----------------------------
-- Table structure for `bb_posts_html`
-- ----------------------------
DROP TABLE IF EXISTS `bb_posts_html`;
CREATE TABLE IF NOT EXISTS `bb_posts_html` (
  `post_id` mediumint(9) NOT NULL DEFAULT '0',
  `post_html_time` timestamp NOT NULL DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
  `post_html` mediumtext NOT NULL DEFAULT '',
  PRIMARY KEY (`post_id`)
) ENGINE=MyISAM DEFAULT CHARSET=utf8;

-- ----------------------------
-- Records of bb_posts_html
-- ----------------------------

-- ----------------------------
-- Table structure for `bb_posts_search`
-- ----------------------------
DROP TABLE IF EXISTS `bb_posts_search`;
CREATE TABLE IF NOT EXISTS `bb_posts_search` (
  `post_id` mediumint(8) unsigned NOT NULL DEFAULT '0',
  `search_words` text NOT NULL,
  PRIMARY KEY (`post_id`),
  FULLTEXT KEY `search_words` (`search_words`)
) ENGINE=MyISAM DEFAULT CHARSET=utf8;

-- ----------------------------
-- Records of bb_posts_search
-- ----------------------------

-- ----------------------------
-- Table structure for `bb_posts_text`
-- ----------------------------
DROP TABLE IF EXISTS `bb_posts_text`;
CREATE TABLE IF NOT EXISTS `bb_posts_text` (
  `post_id` mediumint(8) unsigned NOT NULL DEFAULT '0',
  `post_text` text NOT NULL,
  PRIMARY KEY (`post_id`)
) ENGINE=MyISAM DEFAULT CHARSET=utf8;

-- ----------------------------
-- Records of bb_posts_text
-- ----------------------------
INSERT INTO `bb_posts_text` VALUES ('1', 'Благодарим вас за установку новой версии TorrentPier!\n\nЧто делать дальше? Сперва настройте ваш сайт в администраторском разделе. Измените базовые опции: заголовок сайта, число сообщений на страницу, часовой пояс, язык по-умолчанию, настройки сидбонусов, дней рождения и т.д. Создайте несколько форумов, а также не забудьте переименовать или удалить этот. Обязательно настройте возможность создания релизов в созданных вами разделах и добавьте [url=https://torrentpier.me/threads/25867/]шаблоны оформления раздач[/url] для них. Если у вас возникнут вопросы или потребность в дополнительных модификациях, [url=https://torrentpier.me/]посетите наш форум[/url].\n\nТакже напоминаем, что у проекта TorrentPier есть несколько сайтов, которые могут оказаться полезны для вас:\n[list]\n[*]Форум: https://torrentpier.me/\n[*]Демо-версия: https://demo.torrentpier.me/\n[*]Инструкция: https://faq.torrentpier.me/\n[*]Центр загрузки: https://get.torrentpier.me/\n[*]Перевод на другие языки: http://translate.torrentpier.me/\n[/list]\nНе забудьте добавить их себе в закладки и регулярно проверять наличие новых версий движка на нашем форуме, для своевременного обновления.\n\nНе сомневаемся, вам под силу создать самый лучший трекер. Удачи!');

-- ----------------------------
-- Table structure for `bb_privmsgs`
-- ----------------------------
DROP TABLE IF EXISTS `bb_privmsgs`;
CREATE TABLE IF NOT EXISTS `bb_privmsgs` (
  `privmsgs_id` mediumint(8) unsigned NOT NULL AUTO_INCREMENT,
  `privmsgs_type` tinyint(4) NOT NULL DEFAULT '0',
  `privmsgs_subject` varchar(255) NOT NULL DEFAULT '0',
  `privmsgs_from_userid` mediumint(8) NOT NULL DEFAULT '0',
  `privmsgs_to_userid` mediumint(8) NOT NULL DEFAULT '0',
  `privmsgs_date` int(11) NOT NULL DEFAULT '0',
  `privmsgs_ip` varchar(32) CHARACTER SET utf8 COLLATE utf8_bin NOT NULL DEFAULT '',
  PRIMARY KEY (`privmsgs_id`),
  KEY `privmsgs_from_userid` (`privmsgs_from_userid`),
  KEY `privmsgs_to_userid` (`privmsgs_to_userid`)
) ENGINE=MyISAM DEFAULT CHARSET=utf8;

-- ----------------------------
-- Records of bb_privmsgs
-- ----------------------------

-- ----------------------------
-- Table structure for `bb_privmsgs_text`
-- ----------------------------
DROP TABLE IF EXISTS `bb_privmsgs_text`;
CREATE TABLE IF NOT EXISTS `bb_privmsgs_text` (
  `privmsgs_text_id` mediumint(8) unsigned NOT NULL DEFAULT '0',
  `privmsgs_text` text NOT NULL,
  PRIMARY KEY (`privmsgs_text_id`)
) ENGINE=MyISAM DEFAULT CHARSET=utf8;

-- ----------------------------
-- Records of bb_privmsgs_text
-- ----------------------------

-- ----------------------------
-- Table structure for `bb_ranks`
-- ----------------------------
DROP TABLE IF EXISTS `bb_ranks`;
CREATE TABLE IF NOT EXISTS `bb_ranks` (
  `rank_id` smallint(5) unsigned NOT NULL AUTO_INCREMENT,
  `rank_title` varchar(50) NOT NULL DEFAULT '',
  `rank_min` mediumint(8) NOT NULL DEFAULT '0',
  `rank_special` tinyint(1) NOT NULL DEFAULT '1',
  `rank_image` varchar(255) NOT NULL DEFAULT '',
  `rank_style` varchar(255) NOT NULL DEFAULT '',
  PRIMARY KEY (`rank_id`)
) ENGINE=MyISAM DEFAULT CHARSET=utf8;

-- ----------------------------
-- Records of bb_ranks
-- ----------------------------
INSERT INTO `bb_ranks` VALUES ('', 'Администратор', '-1', '1', 'styles/images/ranks/admin.png', 'colorAdmin');

-- ----------------------------
-- Table structure for `bb_search_rebuild`
-- ----------------------------
DROP TABLE IF EXISTS `bb_search_rebuild`;
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

-- ----------------------------
-- Records of bb_search_rebuild
-- ----------------------------

-- ----------------------------
-- Table structure for `bb_search_results`
-- ----------------------------
DROP TABLE IF EXISTS `bb_search_results`;
CREATE TABLE IF NOT EXISTS `bb_search_results` (
  `session_id` char(20) CHARACTER SET utf8 COLLATE utf8_bin NOT NULL DEFAULT '',
  `search_type` tinyint(4) NOT NULL DEFAULT '0',
  `search_id` varchar(12) CHARACTER SET utf8 COLLATE utf8_bin NOT NULL DEFAULT '',
  `search_time` int(11) NOT NULL DEFAULT '0',
  `search_settings` text NOT NULL,
  `search_array` text NOT NULL,
  PRIMARY KEY (`session_id`,`search_type`)
) ENGINE=MyISAM DEFAULT CHARSET=utf8;

-- ----------------------------
-- Records of bb_search_results
-- ----------------------------

-- ----------------------------
-- Table structure for `bb_sessions`
-- ----------------------------
DROP TABLE IF EXISTS `bb_sessions`;
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

-- ----------------------------
-- Records of bb_sessions
-- ----------------------------

-- ----------------------------
-- Table structure for `bb_smilies`
-- ----------------------------
DROP TABLE IF EXISTS `bb_smilies`;
CREATE TABLE IF NOT EXISTS `bb_smilies` (
  `smilies_id` smallint(5) unsigned NOT NULL AUTO_INCREMENT,
  `code` varchar(50) NOT NULL DEFAULT '',
  `smile_url` varchar(100) NOT NULL DEFAULT '',
  `emoticon` varchar(75) NOT NULL DEFAULT '',
  PRIMARY KEY (`smilies_id`)
) ENGINE=MyISAM DEFAULT CHARSET=utf8;

-- ----------------------------
-- Records of bb_smilies
-- ----------------------------
INSERT INTO `bb_smilies` VALUES ('', ':aa:', 'aa.gif', 'aa');
INSERT INTO `bb_smilies` VALUES ('', ':ab:', 'ab.gif', 'ab');
INSERT INTO `bb_smilies` VALUES ('', ':ac:', 'ac.gif', 'ac');
INSERT INTO `bb_smilies` VALUES ('', ':ae:', 'ae.gif', 'ae');
INSERT INTO `bb_smilies` VALUES ('', ':af:', 'af.gif', 'af');
INSERT INTO `bb_smilies` VALUES ('', ':ag:', 'ag.gif', 'ag');
INSERT INTO `bb_smilies` VALUES ('', ':ah:', 'ah.gif', 'ah');
INSERT INTO `bb_smilies` VALUES ('', ':ai:', 'ai.gif', 'ai');
INSERT INTO `bb_smilies` VALUES ('', ':aj:', 'aj.gif', 'aj');
INSERT INTO `bb_smilies` VALUES ('', ':ak:', 'ak.gif', 'ak');
INSERT INTO `bb_smilies` VALUES ('', ':al:', 'al.gif', 'al');
INSERT INTO `bb_smilies` VALUES ('', ':am:', 'am.gif', 'am');
INSERT INTO `bb_smilies` VALUES ('', ':an:', 'an.gif', 'an');
INSERT INTO `bb_smilies` VALUES ('', ':ao:', 'ao.gif', 'ao');
INSERT INTO `bb_smilies` VALUES ('', ':ap:', 'ap.gif', 'ap');
INSERT INTO `bb_smilies` VALUES ('', ':aq:', 'aq.gif', 'aq');
INSERT INTO `bb_smilies` VALUES ('', ':ar:', 'ar.gif', 'ar');
INSERT INTO `bb_smilies` VALUES ('', ':as:', 'as.gif', 'as');
INSERT INTO `bb_smilies` VALUES ('', ':at:', 'at.gif', 'at');
INSERT INTO `bb_smilies` VALUES ('', ':au:', 'au.gif', 'au');
INSERT INTO `bb_smilies` VALUES ('', ':av:', 'av.gif', 'av');
INSERT INTO `bb_smilies` VALUES ('', ':aw:', 'aw.gif', 'aw');
INSERT INTO `bb_smilies` VALUES ('', ':ax:', 'ax.gif', 'ax');
INSERT INTO `bb_smilies` VALUES ('', ':ay:', 'ay.gif', 'ay');
INSERT INTO `bb_smilies` VALUES ('', ':az:', 'az.gif', 'az');
INSERT INTO `bb_smilies` VALUES ('', ':ba:', 'ba.gif', 'ba');
INSERT INTO `bb_smilies` VALUES ('', ':bb:', 'bb.gif', 'bb');
INSERT INTO `bb_smilies` VALUES ('', ':bc:', 'bc.gif', 'bc');
INSERT INTO `bb_smilies` VALUES ('', ':bd:', 'bd.gif', 'bd');
INSERT INTO `bb_smilies` VALUES ('', ':be:', 'be.gif', 'be');
INSERT INTO `bb_smilies` VALUES ('', ':bf:', 'bf.gif', 'bf');
INSERT INTO `bb_smilies` VALUES ('', ':bg:', 'bg.gif', 'bg');
INSERT INTO `bb_smilies` VALUES ('', ':bh:', 'bh.gif', 'bh');
INSERT INTO `bb_smilies` VALUES ('', ':bi:', 'bi.gif', 'bi');
INSERT INTO `bb_smilies` VALUES ('', ':bj:', 'bj.gif', 'bj');
INSERT INTO `bb_smilies` VALUES ('', ':bk:', 'bk.gif', 'bk');
INSERT INTO `bb_smilies` VALUES ('', ':bl:', 'bl.gif', 'bl');
INSERT INTO `bb_smilies` VALUES ('', ':bm:', 'bm.gif', 'bm');
INSERT INTO `bb_smilies` VALUES ('', ':bn:', 'bn.gif', 'bn');
INSERT INTO `bb_smilies` VALUES ('', ':bo:', 'bo.gif', 'bo');
INSERT INTO `bb_smilies` VALUES ('', ':bp:', 'bp.gif', 'bp');
INSERT INTO `bb_smilies` VALUES ('', ':bq:', 'bq.gif', 'bq');
INSERT INTO `bb_smilies` VALUES ('', ':br:', 'br.gif', 'br');
INSERT INTO `bb_smilies` VALUES ('', ':bs:', 'bs.gif', 'bs');
INSERT INTO `bb_smilies` VALUES ('', ':bt:', 'bt.gif', 'bt');
INSERT INTO `bb_smilies` VALUES ('', ':bu:', 'bu.gif', 'bu');
INSERT INTO `bb_smilies` VALUES ('', ':bv:', 'bv.gif', 'bv');
INSERT INTO `bb_smilies` VALUES ('', ':bw:', 'bw.gif', 'bw');
INSERT INTO `bb_smilies` VALUES ('', ':bx:', 'bx.gif', 'bx');
INSERT INTO `bb_smilies` VALUES ('', ':by:', 'by.gif', 'by');
INSERT INTO `bb_smilies` VALUES ('', ':bz:', 'bz.gif', 'bz');
INSERT INTO `bb_smilies` VALUES ('', ':ca:', 'ca.gif', 'ca');
INSERT INTO `bb_smilies` VALUES ('', ':cb:', 'cb.gif', 'cb');
INSERT INTO `bb_smilies` VALUES ('', ':cc:', 'cc.gif', 'cc');
INSERT INTO `bb_smilies` VALUES ('', ':сd:', 'сd.gif', 'сd');

-- ----------------------------
-- Table structure for `bb_topics`
-- ----------------------------
DROP TABLE IF EXISTS `bb_topics`;
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
  `topic_dl_type` tinyint(1) NOT NULL DEFAULT '0',
  `attach_ext_id` tinyint(4) NOT NULL DEFAULT '0',
  `attach_dl_cnt` mediumint(8) unsigned NOT NULL DEFAULT '0',
  `filesize` mediumint(8) unsigned NOT NULL DEFAULT '0',
  `topic_last_post_time` int(11) NOT NULL DEFAULT '0',
  `topic_show_first_post` tinyint(1) unsigned NOT NULL DEFAULT '0',
  PRIMARY KEY (`topic_id`),
  KEY `forum_id` (`forum_id`),
  KEY `topic_last_post_id` (`topic_last_post_id`),
  KEY `topic_last_post_time` (`topic_last_post_time`),
  FULLTEXT KEY `topic_title` (`topic_title`)
) ENGINE=MyISAM DEFAULT CHARSET=utf8;

-- ----------------------------
-- Records of bb_topics
-- ----------------------------
INSERT INTO `bb_topics` VALUES ('1', '1', 'Добро пожаловать в TorrentPier', '2', UNIX_TIMESTAMP(), '2', '0', '0', '0', '0', '1', '1', '0', '0', '0', '0', '0', UNIX_TIMESTAMP(), '0');

-- ----------------------------
-- Table structure for `bb_topics_watch`
-- ----------------------------
DROP TABLE IF EXISTS `bb_topics_watch`;
CREATE TABLE IF NOT EXISTS `bb_topics_watch` (
  `topic_id` mediumint(8) unsigned NOT NULL DEFAULT '0',
  `user_id` mediumint(8) NOT NULL DEFAULT '0',
  `notify_status` tinyint(1) NOT NULL DEFAULT '0',
  KEY `topic_id` (`topic_id`),
  KEY `user_id` (`user_id`),
  KEY `notify_status` (`notify_status`)
) ENGINE=MyISAM DEFAULT CHARSET=utf8;

-- ----------------------------
-- Records of bb_topics_watch
-- ----------------------------

-- ----------------------------
-- Table structure for `bb_topic_tpl`
-- ----------------------------
DROP TABLE IF EXISTS `bb_topic_tpl`;
CREATE TABLE IF NOT EXISTS `bb_topic_tpl` (
  `tpl_id` smallint(6) NOT NULL AUTO_INCREMENT,
  `tpl_name` varchar(60) NOT NULL DEFAULT '',
  `tpl_src_form` text NOT NULL,
  `tpl_src_title` text NOT NULL,
  `tpl_src_msg` text NOT NULL,
  `tpl_comment` text NOT NULL,
  `tpl_rules_post_id` int(10) unsigned NOT NULL DEFAULT '0',
  `tpl_last_edit_tm` int(11) NOT NULL DEFAULT '0',
  `tpl_last_edit_by` int(11) NOT NULL DEFAULT '0',
  PRIMARY KEY (`tpl_id`),
  UNIQUE KEY `tpl_name` (`tpl_name`)
) ENGINE=MyISAM DEFAULT CHARSET=utf8;

-- ----------------------------
-- Records of bb_topic_tpl
-- ----------------------------

-- ----------------------------
-- Table structure for `bb_users`
-- ----------------------------
DROP TABLE IF EXISTS `bb_users`;
CREATE TABLE IF NOT EXISTS `bb_users` (
  `user_id` mediumint(8) NOT NULL AUTO_INCREMENT,
  `user_active` tinyint(1) NOT NULL DEFAULT '1',
  `username` varchar(25) NOT NULL DEFAULT '',
  `user_password` varchar(32) CHARACTER SET utf8 COLLATE utf8_bin NOT NULL DEFAULT '',
  `user_session_time` int(11) NOT NULL DEFAULT '0',
  `user_lastvisit` int(11) NOT NULL DEFAULT '0',
  `user_last_ip` char(32) NOT NULL DEFAULT '',
  `user_regdate` int(11) NOT NULL DEFAULT '0',
  `user_reg_ip` char(32) NOT NULL DEFAULT '',
  `user_level` tinyint(4) NOT NULL DEFAULT '0',
  `user_posts` mediumint(8) unsigned NOT NULL DEFAULT '0',
  `user_timezone` decimal(5,2) NOT NULL DEFAULT '0.00',
  `user_lang` varchar(255) NOT NULL DEFAULT 'ru',
  `user_new_privmsg` smallint(5) unsigned NOT NULL DEFAULT '0',
  `user_unread_privmsg` smallint(5) unsigned NOT NULL DEFAULT '0',
  `user_last_privmsg` int(11) NOT NULL DEFAULT '0',
  `user_opt` int(11) NOT NULL DEFAULT '0',
  `user_rank` int(11) NOT NULL DEFAULT '0',
  `avatar_ext_id` tinyint(4) NOT NULL DEFAULT '0',
  `user_gender` tinyint(1) NOT NULL DEFAULT '0',
  `user_birthday` date NOT NULL DEFAULT '1000-01-01',
  `user_email` varchar(255) NOT NULL DEFAULT '',
  `user_skype` varchar(32) NOT NULL DEFAULT '',
  `user_twitter` varchar(15) NOT NULL DEFAULT '',
  `user_icq` varchar(15) NOT NULL DEFAULT '',
  `user_website` varchar(100) NOT NULL DEFAULT '',
  `user_from` varchar(100) NOT NULL DEFAULT '',
  `user_sig` text NOT NULL,
  `user_occ` varchar(100) NOT NULL DEFAULT '',
  `user_interests` varchar(255) NOT NULL DEFAULT '',
  `user_actkey` varchar(32) NOT NULL DEFAULT '',
  `user_newpasswd` varchar(32) NOT NULL DEFAULT '',
  `autologin_id` varchar(12) CHARACTER SET utf8 COLLATE utf8_bin NOT NULL DEFAULT '',
  `user_newest_pm_id` mediumint(8) NOT NULL DEFAULT '0',
  `user_points` float(16,2) NOT NULL DEFAULT '0.00',
  `tpl_name` varchar(255) NOT NULL DEFAULT 'default',
  PRIMARY KEY (`user_id`),
  KEY `username` (`username`(10)),
  KEY `user_email` (`user_email`(10)),
  KEY `user_level` (`user_level`)
) ENGINE=MyISAM DEFAULT CHARSET=utf8;

-- ----------------------------
-- Records of bb_users
-- ----------------------------
INSERT INTO `bb_users` VALUES ('-1', '0', 'Guest', 'd41d8cd98f00b204e9800998ecf8427e', '0', '0', '0', UNIX_TIMESTAMP(), '0', '0', '0', '', '', '0', '0', '0', '0', '0', '0', '0', '1000-01-01', '', '', '', '', '', '', '', '', '', '', '', '', '0', '0.00', 'default');
INSERT INTO `bb_users` VALUES ('-746', '0', 'bot', 'd41d8cd98f00b204e9800998ecf8427e', '0', '0', '0', UNIX_TIMESTAMP(), '0', '0', '0', '', '', '0', '0', '0', '144', '0', '0', '0', '1000-01-01', 'bot@torrentpier.me', '', '', '', '', '', '', '', '', '', '', '', '0', '0.00', 'default');
INSERT INTO `bb_users` VALUES ('2', '1', 'admin', 'c3284d0f94606de1fd2af172aba15bf3', '0', '0', '0', UNIX_TIMESTAMP(), '0', '1', '1', '', '', '0', '0', '0', '304', '1', '0', '0', '1000-01-01', 'admin@torrentpier.me', '', '', '', '', '', '', '', '', '', '', '', '0', '0.00', 'default');

-- ----------------------------
-- Table structure for `bb_user_group`
-- ----------------------------
DROP TABLE IF EXISTS `bb_user_group`;
CREATE TABLE IF NOT EXISTS `bb_user_group` (
  `group_id` mediumint(8) NOT NULL DEFAULT '0',
  `user_id` mediumint(8) NOT NULL DEFAULT '0',
  `user_pending` tinyint(1) NOT NULL DEFAULT '0',
  `user_time` int(11) NOT NULL DEFAULT '0',
  PRIMARY KEY (`group_id`,`user_id`),
  KEY `user_id` (`user_id`)
) ENGINE=MyISAM DEFAULT CHARSET=utf8;

-- ----------------------------
-- Records of bb_user_group
-- ----------------------------

-- ----------------------------
-- Table structure for `bb_words`
-- ----------------------------
DROP TABLE IF EXISTS `bb_words`;
CREATE TABLE IF NOT EXISTS `bb_words` (
  `word_id` mediumint(8) unsigned NOT NULL AUTO_INCREMENT,
  `word` char(100) NOT NULL DEFAULT '',
  `replacement` char(100) NOT NULL DEFAULT '',
  PRIMARY KEY (`word_id`)
) ENGINE=MyISAM DEFAULT CHARSET=utf8;

-- ----------------------------
-- Records of bb_words
-- ----------------------------

-- ----------------------------
-- Table structure for `buf_last_seeder`
-- ----------------------------
DROP TABLE IF EXISTS `buf_last_seeder`;
CREATE TABLE IF NOT EXISTS `buf_last_seeder` (
  `topic_id` mediumint(8) unsigned NOT NULL DEFAULT '0',
  `seeder_last_seen` int(11) NOT NULL DEFAULT '0',
  PRIMARY KEY (`topic_id`)
) ENGINE=MyISAM DEFAULT CHARSET=utf8;

-- ----------------------------
-- Records of buf_last_seeder
-- ----------------------------

-- ----------------------------
-- Table structure for `buf_topic_view`
-- ----------------------------
DROP TABLE IF EXISTS `buf_topic_view`;
CREATE TABLE IF NOT EXISTS `buf_topic_view` (
  `topic_id` mediumint(8) unsigned NOT NULL DEFAULT '0',
  `topic_views` mediumint(8) unsigned NOT NULL DEFAULT '0',
  PRIMARY KEY (`topic_id`)
) ENGINE=MyISAM DEFAULT CHARSET=utf8;

-- ----------------------------
-- Records of buf_topic_view
-- ----------------------------