SET SQL_MODE = "";

-- ----------------------------
-- Table structure for `bb_attachments`
-- ----------------------------
DROP TABLE IF EXISTS `bb_attachments`;
CREATE TABLE IF NOT EXISTS `bb_attachments`
(
  `attach_id` MEDIUMINT(8) UNSIGNED NOT NULL DEFAULT '0',
  `post_id`   MEDIUMINT(8) UNSIGNED NOT NULL DEFAULT '0',
  `user_id_1` MEDIUMINT(8)          NOT NULL DEFAULT '0',
  PRIMARY KEY (`attach_id`, `post_id`)
)
  ENGINE = MyISAM
  DEFAULT CHARSET = utf8mb4
  COLLATE = utf8mb4_unicode_ci;

-- ----------------------------
-- Records of bb_attachments
-- ----------------------------

-- ----------------------------
-- Table structure for `bb_attachments_config`
-- ----------------------------
DROP TABLE IF EXISTS `bb_attachments_config`;
CREATE TABLE IF NOT EXISTS `bb_attachments_config`
(
  `config_name`  VARCHAR(155) NOT NULL DEFAULT '',
  `config_value` VARCHAR(255) NOT NULL DEFAULT '',
  PRIMARY KEY (`config_name`)
)
  ENGINE = MyISAM
  DEFAULT CHARSET = utf8mb4
  COLLATE = utf8mb4_unicode_ci;

-- ----------------------------
-- Records of bb_attachments_config
-- ----------------------------
INSERT INTO `bb_attachments_config`
VALUES ('upload_dir', 'data/uploads'),
       ('upload_img', 'styles/images/icon_clip.gif'),
       ('topic_icon', 'styles/images/icon_clip.gif'),
       ('display_order', '0'),
       ('max_filesize', '262144'),
       ('attachment_quota', '52428800'),
       ('max_filesize_pm', '262144'),
       ('max_attachments', '1'),
       ('max_attachments_pm', '1'),
       ('disable_mod', '0'),
       ('allow_pm_attach', '1'),
       ('default_upload_quota', '0'),
       ('default_pm_quota', '0'),
       ('img_display_inlined', '1'),
       ('img_max_width', '2000'),
       ('img_max_height', '2000'),
       ('img_link_width', '600'),
       ('img_link_height', '400'),
       ('img_create_thumbnail', '1'),
       ('img_min_thumb_filesize', '12000');

-- ----------------------------
-- Table structure for `bb_attachments_desc`
-- ----------------------------
DROP TABLE IF EXISTS `bb_attachments_desc`;
CREATE TABLE IF NOT EXISTS `bb_attachments_desc`
(
  `attach_id`         MEDIUMINT(8) UNSIGNED NOT NULL AUTO_INCREMENT,
  `physical_filename` VARCHAR(255)          NOT NULL DEFAULT '',
  `real_filename`     VARCHAR(255)          NOT NULL DEFAULT '',
  `download_count`    MEDIUMINT(8) UNSIGNED NOT NULL DEFAULT '0',
  `comment`           VARCHAR(255)          NOT NULL DEFAULT '',
  `extension`         VARCHAR(100)          NOT NULL DEFAULT '',
  `mimetype`          VARCHAR(100)          NOT NULL DEFAULT '',
  `filesize`          INT(20)               NOT NULL DEFAULT '0',
  `filetime`          INT(11)               NOT NULL DEFAULT '0',
  `thumbnail`         TINYINT(1)            NOT NULL DEFAULT '0',
  `tracker_status`    TINYINT(1)            NOT NULL DEFAULT '0',
  PRIMARY KEY (`attach_id`),
  KEY `filetime` (`filetime`),
  KEY `filesize` (`filesize`),
  KEY `physical_filename` (`physical_filename`(10))
)
  ENGINE = MyISAM
  DEFAULT CHARSET = utf8mb4
  COLLATE = utf8mb4_unicode_ci;

-- ----------------------------
-- Records of bb_attachments_desc
-- ----------------------------

-- ----------------------------
-- Table structure for `bb_attach_quota`
-- ----------------------------
DROP TABLE IF EXISTS `bb_attach_quota`;
CREATE TABLE IF NOT EXISTS `bb_attach_quota`
(
  `user_id`        MEDIUMINT(8) UNSIGNED NOT NULL DEFAULT '0',
  `group_id`       MEDIUMINT(8) UNSIGNED NOT NULL DEFAULT '0',
  `quota_type`     SMALLINT(2)           NOT NULL DEFAULT '0',
  `quota_limit_id` MEDIUMINT(8) UNSIGNED NOT NULL DEFAULT '0',
  KEY `quota_type` (`quota_type`)
)
  ENGINE = MyISAM
  DEFAULT CHARSET = utf8mb4
  COLLATE = utf8mb4_unicode_ci;

-- ----------------------------
-- Records of bb_attach_quota
-- ----------------------------

-- ----------------------------
-- Table structure for `bb_auth_access`
-- ----------------------------
DROP TABLE IF EXISTS `bb_auth_access`;
CREATE TABLE IF NOT EXISTS `bb_auth_access`
(
  `group_id`   MEDIUMINT(8)         NOT NULL DEFAULT '0',
  `forum_id`   SMALLINT(5) UNSIGNED NOT NULL DEFAULT '0',
  `forum_perm` INT(11)              NOT NULL DEFAULT '0',
  PRIMARY KEY (`group_id`, `forum_id`),
  KEY `forum_id` (`forum_id`)
)
  ENGINE = MyISAM
  DEFAULT CHARSET = utf8mb4
  COLLATE = utf8mb4_unicode_ci;

-- ----------------------------
-- Records of bb_auth_access
-- ----------------------------

-- ----------------------------
-- Table structure for `bb_auth_access_snap`
-- ----------------------------
DROP TABLE IF EXISTS `bb_auth_access_snap`;
CREATE TABLE IF NOT EXISTS `bb_auth_access_snap`
(
  `user_id`    MEDIUMINT(9) NOT NULL DEFAULT '0',
  `forum_id`   SMALLINT(6)  NOT NULL DEFAULT '0',
  `forum_perm` INT(11)      NOT NULL DEFAULT '0',
  PRIMARY KEY (`user_id`, `forum_id`)
)
  ENGINE = MyISAM
  DEFAULT CHARSET = utf8mb4
  COLLATE = utf8mb4_unicode_ci;

-- ----------------------------
-- Records of bb_auth_access_snap
-- ----------------------------

-- ----------------------------
-- Table structure for `bb_banlist`
-- ----------------------------
DROP TABLE IF EXISTS `bb_banlist`;
CREATE TABLE IF NOT EXISTS `bb_banlist`
(
  `ban_id`     MEDIUMINT(8) UNSIGNED NOT NULL AUTO_INCREMENT,
  `ban_userid` MEDIUMINT(8)          NOT NULL DEFAULT '0',
  `ban_reason` VARCHAR(255)          NOT NULL DEFAULT '',
  PRIMARY KEY (`ban_id`, `ban_userid`)
)
  ENGINE = MyISAM
  DEFAULT CHARSET = utf8mb4
  COLLATE = utf8mb4_unicode_ci;

-- ----------------------------
-- Records of bb_banlist
-- ----------------------------

-- ----------------------------
-- Table structure for `bb_bt_dlstatus`
-- ----------------------------
DROP TABLE IF EXISTS `bb_bt_dlstatus`;
CREATE TABLE IF NOT EXISTS `bb_bt_dlstatus`
(
  `user_id`                MEDIUMINT(9)          NOT NULL DEFAULT '0',
  `topic_id`               MEDIUMINT(8) UNSIGNED NOT NULL DEFAULT '0',
  `user_status`            TINYINT(1)            NOT NULL DEFAULT '0',
  `last_modified_dlstatus` TIMESTAMP             NOT NULL DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
  PRIMARY KEY (`user_id`, `topic_id`),
  KEY `topic_id` (`topic_id`)
)
  ENGINE = MyISAM
  DEFAULT CHARSET = utf8mb4
  COLLATE = utf8mb4_unicode_ci;

-- ----------------------------
-- Records of bb_bt_dlstatus
-- ----------------------------

-- ----------------------------
-- Table structure for `bb_bt_dlstatus_snap`
-- ----------------------------
DROP TABLE IF EXISTS `bb_bt_dlstatus_snap`;
CREATE TABLE IF NOT EXISTS `bb_bt_dlstatus_snap`
(
  `topic_id`    MEDIUMINT(8) UNSIGNED NOT NULL DEFAULT '0',
  `dl_status`   TINYINT(4)            NOT NULL DEFAULT '0',
  `users_count` SMALLINT(5) UNSIGNED  NOT NULL DEFAULT '0',
  KEY `topic_id` (`topic_id`)
)
  ENGINE = MyISAM
  DEFAULT CHARSET = utf8mb4
  COLLATE = utf8mb4_unicode_ci;

-- ----------------------------
-- Records of bb_bt_dlstatus_snap
-- ----------------------------

-- ----------------------------
-- Table structure for `bb_bt_last_torstat`
-- ----------------------------
DROP TABLE IF EXISTS `bb_bt_last_torstat`;
CREATE TABLE IF NOT EXISTS `bb_bt_last_torstat`
(
  `topic_id`    MEDIUMINT(8) UNSIGNED NOT NULL DEFAULT '0',
  `user_id`     MEDIUMINT(9)          NOT NULL DEFAULT '0',
  `dl_status`   TINYINT(1)            NOT NULL DEFAULT '0',
  `up_add`      BIGINT(20) UNSIGNED   NOT NULL DEFAULT '0',
  `down_add`    BIGINT(20) UNSIGNED   NOT NULL DEFAULT '0',
  `release_add` BIGINT(20) UNSIGNED   NOT NULL DEFAULT '0',
  `bonus_add`   BIGINT(20) UNSIGNED   NOT NULL DEFAULT '0',
  `speed_up`    BIGINT(20) UNSIGNED   NOT NULL DEFAULT '0',
  `speed_down`  BIGINT(20) UNSIGNED   NOT NULL DEFAULT '0',
  PRIMARY KEY (`topic_id`, `user_id`) USING BTREE
)
  ENGINE = MyISAM
  DEFAULT CHARSET = utf8mb4
  COLLATE = utf8mb4_unicode_ci;

-- ----------------------------
-- Records of bb_bt_last_torstat
-- ----------------------------

-- ----------------------------
-- Table structure for `bb_bt_last_userstat`
-- ----------------------------
DROP TABLE IF EXISTS `bb_bt_last_userstat`;
CREATE TABLE IF NOT EXISTS `bb_bt_last_userstat`
(
  `user_id`     MEDIUMINT(9)        NOT NULL DEFAULT '0',
  `up_add`      BIGINT(20) UNSIGNED NOT NULL DEFAULT '0',
  `down_add`    BIGINT(20) UNSIGNED NOT NULL DEFAULT '0',
  `release_add` BIGINT(20) UNSIGNED NOT NULL DEFAULT '0',
  `bonus_add`   BIGINT(20) UNSIGNED NOT NULL DEFAULT '0',
  `speed_up`    BIGINT(20) UNSIGNED NOT NULL DEFAULT '0',
  `speed_down`  BIGINT(20) UNSIGNED NOT NULL DEFAULT '0',
  PRIMARY KEY (`user_id`)
)
  ENGINE = MyISAM
  DEFAULT CHARSET = utf8mb4
  COLLATE = utf8mb4_unicode_ci;

-- ----------------------------
-- Records of bb_bt_last_userstat
-- ----------------------------

-- ----------------------------
-- Table structure for `bb_bt_torhelp`
-- ----------------------------
DROP TABLE IF EXISTS `bb_bt_torhelp`;
CREATE TABLE IF NOT EXISTS `bb_bt_torhelp`
(
  `user_id`      MEDIUMINT(9) NOT NULL DEFAULT '0',
  `topic_id_csv` TEXT         NOT NULL,
  PRIMARY KEY (`user_id`)
)
  ENGINE = MyISAM
  DEFAULT CHARSET = utf8mb4
  COLLATE = utf8mb4_unicode_ci;

-- ----------------------------
-- Records of bb_bt_torhelp
-- ----------------------------

-- ----------------------------
-- Table structure for `bb_bt_torrents`
-- ----------------------------
DROP TABLE IF EXISTS `bb_bt_torrents`;
CREATE TABLE IF NOT EXISTS `bb_bt_torrents`
(
  `info_hash`        VARBINARY(20)         NOT NULL DEFAULT '',
  `info_hash_v2`     VARBINARY(32)         NOT NULL DEFAULT '',
  `post_id`          MEDIUMINT(8) UNSIGNED NOT NULL DEFAULT '0',
  `poster_id`        MEDIUMINT(9)          NOT NULL DEFAULT '0',
  `topic_id`         MEDIUMINT(8) UNSIGNED NOT NULL DEFAULT '0',
  `forum_id`         SMALLINT(5) UNSIGNED  NOT NULL DEFAULT '0',
  `attach_id`        MEDIUMINT(8) UNSIGNED NOT NULL DEFAULT '0',
  `size`             BIGINT(20) UNSIGNED   NOT NULL DEFAULT '0',
  `reg_time`         INT(11)               NOT NULL DEFAULT '0',
  `call_seed_time`   INT(11)               NOT NULL DEFAULT '0',
  `complete_count`   MEDIUMINT(8) UNSIGNED NOT NULL DEFAULT '0',
  `seeder_last_seen` INT(11)               NOT NULL DEFAULT '0',
  `tor_status`       TINYINT(4)            NOT NULL DEFAULT '0',
  `checked_user_id`  MEDIUMINT(8)          NOT NULL DEFAULT '0',
  `checked_time`     INT(11)               NOT NULL DEFAULT '0',
  `tor_type`         TINYINT(1)            NOT NULL DEFAULT '0',
  `speed_up`         INT(11)               NOT NULL DEFAULT '0',
  `speed_down`       INT(11)               NOT NULL DEFAULT '0',
  `last_seeder_id`   MEDIUMINT(8)          NOT NULL DEFAULT '0',
  PRIMARY KEY (`topic_id`),
  UNIQUE KEY `post_id` (`post_id`),
  UNIQUE KEY `topic_id` (`topic_id`),
  UNIQUE KEY `attach_id` (`attach_id`),
  KEY `reg_time` (`reg_time`),
  KEY `forum_id` (`forum_id`),
  KEY `poster_id` (`poster_id`)
)
  ENGINE = MyISAM
  DEFAULT CHARSET = utf8mb4
  COLLATE = utf8mb4_unicode_ci;

-- ----------------------------
-- Records of bb_bt_torrents
-- ----------------------------

-- ----------------------------
-- Table structure for `bb_bt_torstat`
-- ----------------------------
DROP TABLE IF EXISTS `bb_bt_torstat`;
CREATE TABLE IF NOT EXISTS `bb_bt_torstat`
(
  `topic_id`              MEDIUMINT(8) UNSIGNED NOT NULL DEFAULT '0',
  `user_id`               MEDIUMINT(9)          NOT NULL DEFAULT '0',
  `last_modified_torstat` TIMESTAMP             NOT NULL DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
  `completed`             TINYINT(1)            NOT NULL DEFAULT '0',
  PRIMARY KEY (`topic_id`, `user_id`)
)
  ENGINE = MyISAM
  DEFAULT CHARSET = utf8mb4
  COLLATE = utf8mb4_unicode_ci;

-- ----------------------------
-- Records of bb_bt_torstat
-- ----------------------------

-- ----------------------------
-- Table structure for `bb_bt_tor_dl_stat`
-- ----------------------------
DROP TABLE IF EXISTS `bb_bt_tor_dl_stat`;
CREATE TABLE IF NOT EXISTS `bb_bt_tor_dl_stat`
(
  `topic_id`      MEDIUMINT(8) UNSIGNED NOT NULL DEFAULT '0',
  `user_id`       MEDIUMINT(9)          NOT NULL DEFAULT '0',
  `attach_id`     MEDIUMINT(8) UNSIGNED NOT NULL DEFAULT '0',
  `t_up_total`    BIGINT(20) UNSIGNED   NOT NULL DEFAULT '0',
  `t_down_total`  BIGINT(20) UNSIGNED   NOT NULL DEFAULT '0',
  `t_bonus_total` BIGINT(20) UNSIGNED   NOT NULL DEFAULT '0',
  PRIMARY KEY (`topic_id`, `user_id`)
)
  ENGINE = MyISAM
  DEFAULT CHARSET = utf8mb4
  COLLATE = utf8mb4_unicode_ci;

-- ----------------------------
-- Records of bb_bt_tor_dl_stat
-- ----------------------------

-- ----------------------------
-- Table structure for `bb_bt_tracker`
-- ----------------------------
DROP TABLE IF EXISTS `bb_bt_tracker`;
CREATE TABLE IF NOT EXISTS `bb_bt_tracker`
(
  `peer_hash`        VARCHAR(32)
                       CHARACTER SET utf8
                         COLLATE utf8_bin  NOT NULL DEFAULT '',
  `topic_id`         MEDIUMINT(8) UNSIGNED NOT NULL DEFAULT '0',
  `peer_id`          VARCHAR(20)           NOT NULL DEFAULT '0',
  `user_id`          MEDIUMINT(9)          NOT NULL DEFAULT '0',
  `ip`               VARCHAR(42)                    DEFAULT NULL,
  `ipv6`             VARCHAR(42)                    DEFAULT NULL,
  `port`             SMALLINT(5) UNSIGNED  NOT NULL DEFAULT '0',
  `seeder`           TINYINT(1)            NOT NULL DEFAULT '0',
  `releaser`         TINYINT(1)            NOT NULL DEFAULT '0',
  `tor_type`         TINYINT(1)            NOT NULL DEFAULT '0',
  `uploaded`         BIGINT(20) UNSIGNED   NOT NULL DEFAULT '0',
  `downloaded`       BIGINT(20) UNSIGNED   NOT NULL DEFAULT '0',
  `remain`           BIGINT(20) UNSIGNED   NOT NULL DEFAULT '0',
  `speed_up`         INT(11) UNSIGNED      NOT NULL DEFAULT '0',
  `speed_down`       INT(11) UNSIGNED      NOT NULL DEFAULT '0',
  `up_add`           BIGINT(20) UNSIGNED   NOT NULL DEFAULT '0',
  `down_add`         BIGINT(20) UNSIGNED   NOT NULL DEFAULT '0',
  `update_time`      INT(11)               NOT NULL DEFAULT '0',
  `complete_percent` BIGINT(20)            NOT NULL DEFAULT '0',
  `complete`         TINYINT(1)            NOT NULL DEFAULT '0',
  PRIMARY KEY (`peer_hash`),
  KEY `topic_id` (`topic_id`),
  KEY `user_id` (`user_id`)
)
  ENGINE = MyISAM
  DEFAULT CHARSET = utf8mb4
  COLLATE = utf8mb4_unicode_ci;

-- ----------------------------
-- Records of bb_bt_tracker
-- ----------------------------

-- ----------------------------
-- Table structure for `bb_bt_tracker_snap`
-- ----------------------------
DROP TABLE IF EXISTS `bb_bt_tracker_snap`;
CREATE TABLE IF NOT EXISTS `bb_bt_tracker_snap`
(
  `topic_id`   MEDIUMINT(8) UNSIGNED NOT NULL DEFAULT '0',
  `seeders`    MEDIUMINT(8) UNSIGNED NOT NULL DEFAULT '0',
  `leechers`   MEDIUMINT(8) UNSIGNED NOT NULL DEFAULT '0',
  `speed_up`   INT(11) UNSIGNED      NOT NULL DEFAULT '0',
  `speed_down` INT(11) UNSIGNED      NOT NULL DEFAULT '0',
  `completed`  INT(10)               NOT NULL DEFAULT '0',
  PRIMARY KEY (`topic_id`)
)
  ENGINE = MyISAM
  DEFAULT CHARSET = utf8mb4
  COLLATE = utf8mb4_unicode_ci;

-- ----------------------------
-- Records of bb_bt_tracker_snap
-- ----------------------------

-- ----------------------------
-- Table structure for `bb_bt_users`
-- ----------------------------
DROP TABLE IF EXISTS `bb_bt_users`;
CREATE TABLE IF NOT EXISTS `bb_bt_users`
(
  `user_id`              MEDIUMINT(9)          NOT NULL DEFAULT '0',
  `auth_key`             CHAR(20)
                           CHARACTER SET utf8
                             COLLATE utf8_bin  NOT NULL DEFAULT '',
  `u_up_total`           BIGINT(20) UNSIGNED   NOT NULL DEFAULT '0',
  `u_down_total`         BIGINT(20) UNSIGNED   NOT NULL DEFAULT '0',
  `u_up_release`         BIGINT(20) UNSIGNED   NOT NULL DEFAULT '0',
  `u_up_bonus`           BIGINT(20) UNSIGNED   NOT NULL DEFAULT '0',
  `up_today`             BIGINT(20) UNSIGNED   NOT NULL DEFAULT '0',
  `down_today`           BIGINT(20) UNSIGNED   NOT NULL DEFAULT '0',
  `up_release_today`     BIGINT(20) UNSIGNED   NOT NULL DEFAULT '0',
  `up_bonus_today`       BIGINT(20) UNSIGNED   NOT NULL DEFAULT '0',
  `points_today`         FLOAT(16, 2) UNSIGNED NOT NULL DEFAULT '0.00',
  `up_yesterday`         BIGINT(20) UNSIGNED   NOT NULL DEFAULT '0',
  `down_yesterday`       BIGINT(20) UNSIGNED   NOT NULL DEFAULT '0',
  `up_release_yesterday` BIGINT(20) UNSIGNED   NOT NULL DEFAULT '0',
  `up_bonus_yesterday`   BIGINT(20) UNSIGNED   NOT NULL DEFAULT '0',
  `points_yesterday`     FLOAT(16, 2) UNSIGNED NOT NULL DEFAULT '0.00',
  `ratio_nulled`         tinyint(1)            NOT NULL DEFAULT '0',
  PRIMARY KEY (`user_id`),
  UNIQUE KEY `auth_key` (`auth_key`)
)
  ENGINE = MyISAM
  DEFAULT CHARSET = utf8mb4
  COLLATE = utf8mb4_unicode_ci;

-- ----------------------------
-- Records of bb_bt_users
-- ----------------------------
INSERT INTO `bb_bt_users` (user_id, auth_key)
VALUES ('-1', SUBSTRING(MD5(RAND()), 1, 20)),
       ('-746', SUBSTRING(MD5(RAND()), 1, 20)),
       ('2', SUBSTRING(MD5(RAND()), 1, 20));

-- ----------------------------
-- Table structure for `bb_bt_user_settings`
-- ----------------------------
DROP TABLE IF EXISTS `bb_bt_user_settings`;
CREATE TABLE IF NOT EXISTS `bb_bt_user_settings`
(
  `user_id`        MEDIUMINT(9) NOT NULL DEFAULT '0',
  `tor_search_set` TEXT         NOT NULL,
  `last_modified`  INT(11)      NOT NULL DEFAULT '0',
  PRIMARY KEY (`user_id`)
)
  ENGINE = MyISAM
  DEFAULT CHARSET = utf8mb4
  COLLATE = utf8mb4_unicode_ci;

-- ----------------------------
-- Records of bb_bt_user_settings
-- ----------------------------

-- ----------------------------
-- Table structure for `bb_categories`
-- ----------------------------
DROP TABLE IF EXISTS `bb_categories`;
CREATE TABLE IF NOT EXISTS `bb_categories`
(
  `cat_id`    SMALLINT(5) UNSIGNED NOT NULL AUTO_INCREMENT,
  `cat_title` VARCHAR(100)         NOT NULL DEFAULT '',
  `cat_order` SMALLINT(5) UNSIGNED NOT NULL DEFAULT '0',
  PRIMARY KEY (`cat_id`),
  KEY `cat_order` (`cat_order`)
)
  ENGINE = MyISAM
  DEFAULT CHARSET = utf8mb4
  COLLATE = utf8mb4_unicode_ci;

-- ----------------------------
-- Records of bb_categories
-- ----------------------------
INSERT INTO `bb_categories`
VALUES ('1', 'Your first category', '10');

-- ----------------------------
-- Table structure for `bb_config`
-- ----------------------------
DROP TABLE IF EXISTS `bb_config`;
CREATE TABLE IF NOT EXISTS `bb_config`
(
  `config_name`  VARCHAR(155) NOT NULL DEFAULT '',
  `config_value` TEXT         NOT NULL,
  PRIMARY KEY (`config_name`)
)
  ENGINE = MyISAM
  DEFAULT CHARSET = utf8mb4
  COLLATE = utf8mb4_unicode_ci;

-- ----------------------------
-- Records of bb_config
-- ----------------------------
INSERT INTO `bb_config`
VALUES ('allow_autologin', '1'),
       ('allow_bbcode', '1'),
       ('allow_namechange', '0'),
       ('allow_sig', '1'),
       ('allow_smilies', '1'),
       ('board_disable', '0'),
       ('board_startdate', UNIX_TIMESTAMP()),
       ('board_timezone', '0'),
       ('bonus_upload', ''),
       ('bonus_upload_price', ''),
       ('birthday_enabled', '1'),
       ('birthday_max_age', '99'),
       ('birthday_min_age', '10'),
       ('birthday_check_day', '7'),
       ('bt_add_auth_key', '1'),
       ('bt_allow_spmode_change', '1'),
       ('bt_announce_url', 'https://localhost/bt/announce.php'),
       ('bt_disable_dht', '0'),
       ('bt_check_announce_url', '0'),
       ('bt_del_addit_ann_urls', '1'),
       ('bt_dl_list_only_1st_page', '1'),
       ('bt_dl_list_only_count', '1'),
       ('bt_newtopic_auto_reg', '1'),
       ('bt_replace_ann_url', '1'),
       ('bt_search_bool_mode', '1'),
       ('bt_set_dltype_on_tor_reg', '1'),
       ('bt_show_dl_but_cancel', '1'),
       ('bt_show_dl_but_compl', '1'),
       ('bt_show_dl_but_down', '0'),
       ('bt_show_dl_but_will', '1'),
       ('bt_show_dl_list', '0'),
       ('bt_show_dl_list_buttons', '1'),
       ('bt_show_dl_stat_on_index', '1'),
       ('bt_show_ip_only_moder', '1'),
       ('bt_show_peers', '1'),
       ('bt_show_peers_mode', '1'),
       ('bt_show_port_only_moder', '1'),
       ('bt_tor_browse_only_reg', '0'),
       ('bt_unset_dltype_on_tor_unreg', '1'),
       ('cron_last_check', '0'),
       ('default_dateformat', 'Y-m-d H:i'),
       ('default_lang', 'en'),
       ('flood_interval', '15'),
       ('hot_threshold', '300'),
       ('login_reset_time', '30'),
       ('max_autologin_time', '10'),
       ('max_login_attempts', '5'),
       ('max_poll_options', '6'),
       ('max_sig_chars', '255'),
       ('posts_per_page', '15'),
       ('prune_enable', '1'),
       ('record_online_date', UNIX_TIMESTAMP()),
       ('record_online_users', '0'),
       ('seed_bonus_enabled', '1'),
       ('seed_bonus_release', ''),
       ('seed_bonus_points', ''),
       ('seed_bonus_tor_size', '0'),
       ('seed_bonus_user_regdate', '0'),
       ('site_desc', 'Bull-powered BitTorrent tracker engine'),
       ('sitemap_time', ''),
       ('sitename', 'TorrentPier'),
       ('smilies_path', 'styles/images/smiles'),
       ('static_sitemap', ''),
       ('topics_per_page', '50'),
       ('xs_use_cache', '1'),
       ('cron_check_interval', '180'),
       ('magnet_links_enabled', '1'),
       ('magnet_links_for_guests', '0'),
       ('gender', '1'),
       ('callseed', '0'),
       ('tor_stats', '1'),
       ('show_latest_news', '1'),
       ('max_news_title', '50'),
       ('latest_news_count', '5'),
       ('latest_news_forum_id', '1'),
       ('show_network_news', '1'),
       ('max_net_title', '50'),
       ('network_news_count', '5'),
       ('network_news_forum_id', '2'),
       ('whois_info', 'https://whatismyipaddress.com/ip/'),
       ('show_mod_index', '0'),
       ('premod', '0'),
       ('tor_comment', '1'),
       ('terms', ''),
       ('show_board_start_index', '1');

-- ----------------------------
-- Table structure for `bb_cron`
-- ----------------------------
DROP TABLE IF EXISTS `bb_cron`;
CREATE TABLE IF NOT EXISTS `bb_cron`
(
  `cron_id`         SMALLINT(5) UNSIGNED                                      NOT NULL AUTO_INCREMENT,
  `cron_active`     TINYINT(4)                                                NOT NULL                                                                                                   DEFAULT '1',
  `cron_title`      CHAR(120)                                                 NOT NULL                                                                                                   DEFAULT '',
  `cron_script`     CHAR(120)                                                 NOT NULL                                                                                                   DEFAULT '',
  `schedule`        ENUM ('hourly', 'daily', 'weekly', 'monthly', 'interval') NOT NULL                                                                                                   DEFAULT 'daily',
  `run_day`         ENUM ('1', '2', '3', '4', '5', '6', '7', '8', '9', '10', '11', '12', '13', '14', '15', '16', '17', '18', '19', '20', '21', '22', '23', '24', '25', '26', '27', '28') DEFAULT NULL,
  `run_time`        TIME                                                                                                                                                                 DEFAULT '04:00:00',
  `run_order`       TINYINT(4) UNSIGNED                                       NOT NULL                                                                                                   DEFAULT '0',
  `last_run`        DATETIME                                                  NOT NULL                                                                                                   DEFAULT '1900-01-01 00:00:00',
  `next_run`        DATETIME                                                  NOT NULL                                                                                                   DEFAULT '1900-01-01 00:00:00',
  `run_interval`    TIME                                                                                                                                                                 DEFAULT NULL DEFAULT '0',
  `log_enabled`     TINYINT(1)                                                NOT NULL                                                                                                   DEFAULT '0',
  `log_file`        CHAR(120)                                                 NOT NULL                                                                                                   DEFAULT '',
  `log_sql_queries` TINYINT(4)                                                NOT NULL                                                                                                   DEFAULT '0',
  `disable_board`   TINYINT(1)                                                NOT NULL                                                                                                   DEFAULT '0',
  `run_counter`     BIGINT(20) UNSIGNED                                       NOT NULL                                                                                                   DEFAULT '0',
  PRIMARY KEY (`cron_id`),
  UNIQUE KEY `title` (`cron_title`),
  UNIQUE KEY `script` (`cron_script`)
)
  ENGINE = MyISAM
  DEFAULT CHARSET = utf8mb4
  COLLATE = utf8mb4_unicode_ci;

-- ----------------------------
-- Records of bb_cron
-- ----------------------------
INSERT INTO `bb_cron` (`cron_active`, `cron_title`, `cron_script`, `schedule`, `run_day`, `run_time`, `run_order`,
                       `last_run`, `next_run`, `run_interval`, `log_enabled`, `log_file`, `log_sql_queries`,
                       `disable_board`, `run_counter`)
VALUES ('1', 'Attach maintenance', 'attach_maintenance.php', 'daily', '', '05:00:00', '40', '', '', '', '0', '', '0',
        '1', '0'),
       ('1', 'Board maintenance', 'board_maintenance.php', 'daily', '', '05:00:00', '40', '', '', '', '0', '', '0', '1',
        '0'),
       ('1', 'Prune forums', 'prune_forums.php', 'daily', '', '05:00:00', '50', '', '', '', '0', '', '0', '1', '0'),
       ('1', 'Prune topic moved stubs', 'prune_topic_moved.php', 'daily', '', '05:00:00', '60', '', '', '', '0', '',
        '0',
        '1', '0'),
       ('1', 'Logs cleanup', 'clean_log.php', 'daily', '', '05:00:00', '70', '', '', '', '0', '', '0', '1', '0'),
       ('1', 'PM cleanup', 'clean_pm.php', 'daily', '', '05:00:00', '70', '', '', '', '0', '', '0', '1', '0'),
       ('1', 'Tracker maintenance', 'tr_maintenance.php', 'daily', '', '05:00:00', '90', '', '', '', '0', '', '0', '1',
        '0'),
       ('1', 'Clean dlstat', 'clean_dlstat.php', 'daily', '', '05:00:00', '100', '', '', '', '0', '', '0', '1', '0'),
       ('1', 'Prune inactive users', 'prune_inactive_users.php', 'daily', '', '05:00:00', '110', '', '', '', '0', '',
        '0', '1', '0'),
       ('1', 'Sessions cleanup', 'sessions_cleanup.php', 'interval', '', '', '255', '', '', '00:03:00', '0', '', '0',
        '0', '0'),
       ('1', 'DS update cat_forums', 'ds_update_cat_forums.php', 'interval', '', '', '255', '', '', '00:05:00', '0', '',
        '0', '0', '0'),
       ('1', 'DS update stats', 'ds_update_stats.php', 'interval', '', '', '255', '', '', '00:10:00', '0', '', '0', '0',
        '0'),
       ('1', 'Flash topic view', 'flash_topic_view.php', 'interval', '', '', '255', '', '', '00:10:00', '0', '', '0',
        '0', '0'),
       ('1', 'Clean search results', 'clean_search_results.php', 'interval', '', '', '255', '', '', '00:10:00', '0', '',
        '0', '0', '0'),
       ('1', 'Tracker cleanup and dlstat', 'tr_cleanup_and_dlstat.php', 'interval', '', '', '20', '', '', '00:15:00',
        '0', '', '0', '0', '0'),
       ('1', 'Accrual seedbonus', 'tr_seed_bonus.php', 'interval', '', '', '25', '', '', '00:10:00', '0', '', '0', '0',
        '0'),
       ('1', 'Make tracker snapshot', 'tr_make_snapshot.php', 'interval', '', '', '10', '', '', '00:10:00', '0', '',
        '0',
        '0', '0'),
       ('1', 'Seeder last seen', 'tr_update_seeder_last_seen.php', 'interval', '', '', '255', '', '', '01:00:00', '0',
        '', '0', '0', '0'),
       ('1', 'Tracker dl-complete count', 'tr_complete_count.php', 'interval', '', '', '255', '', '', '06:00:00', '0',
        '', '0', '0', '0'),
       ('1', 'Sitemap update', 'sitemap.php', 'daily', '', '06:00:00', '30', '', '', '', '0', '', '0', '0', '0'),
       ('1', 'Update forums atom', 'update_forums_atom.php', 'interval', '', '', '255', '', '', '00:15:00', '0', '',
        '0',
        '0', '0'),
       ('1', 'Demo mode', 'demo_mode.php', 'daily', '', '05:00:00', '255', '', '', '', '1', 'demo_mode_cron', '1', '1',
        '0');

-- ----------------------------
-- Table structure for `bb_disallow`
-- ----------------------------
DROP TABLE IF EXISTS `bb_disallow`;
CREATE TABLE IF NOT EXISTS `bb_disallow`
(
  `disallow_id`       MEDIUMINT(8) UNSIGNED NOT NULL AUTO_INCREMENT,
  `disallow_username` VARCHAR(25)           NOT NULL DEFAULT '',
  PRIMARY KEY (`disallow_id`)
)
  ENGINE = MyISAM
  DEFAULT CHARSET = utf8mb4
  COLLATE = utf8mb4_unicode_ci;

-- ----------------------------
-- Records of bb_disallow
-- ----------------------------
INSERT INTO `bb_disallow` (`disallow_id`, `disallow_username`)
VALUES ('1', 'torrentpier*'),
       ('2', 'tracker*'),
       ('3', 'forum*'),
       ('4', 'torrent*'),
       ('5', 'admin*');

-- ----------------------------
-- Table structure for `bb_extensions`
-- ----------------------------
DROP TABLE IF EXISTS `bb_extensions`;
CREATE TABLE IF NOT EXISTS `bb_extensions`
(
  `ext_id`    MEDIUMINT(8) UNSIGNED NOT NULL AUTO_INCREMENT,
  `group_id`  MEDIUMINT(8) UNSIGNED NOT NULL DEFAULT '0',
  `extension` VARCHAR(100)          NOT NULL DEFAULT '',
  `comment`   VARCHAR(100)          NOT NULL DEFAULT '',
  PRIMARY KEY (`ext_id`)
)
  ENGINE = MyISAM
  DEFAULT CHARSET = utf8mb4
  COLLATE = utf8mb4_unicode_ci;

-- ----------------------------
-- Records of bb_extensions
-- ----------------------------
INSERT INTO `bb_extensions` (`group_id`, `extension`, `comment`)
VALUES ('1', 'gif', ''),
       ('1', 'png', ''),
       ('1', 'jpeg', ''),
       ('1', 'jpg', ''),
       ('1', 'webp', ''),
       ('1', 'avif', ''),
       ('1', 'bmp', ''),
       ('2', 'gtar', ''),
       ('2', 'gz', ''),
       ('2', 'tar', ''),
       ('2', 'zip', ''),
       ('2', 'rar', ''),
       ('2', 'ace', ''),
       ('2', '7z', ''),
       ('3', 'txt', ''),
       ('3', 'c', ''),
       ('3', 'h', ''),
       ('3', 'cpp', ''),
       ('3', 'hpp', ''),
       ('3', 'diz', ''),
       ('3', 'm3u', ''),
       ('4', 'xls', ''),
       ('4', 'doc', ''),
       ('4', 'dot', ''),
       ('4', 'pdf', ''),
       ('4', 'ai', ''),
       ('4', 'ps', ''),
       ('4', 'ppt', ''),
       ('5', 'rm', ''),
       ('6', 'torrent', '');

-- ----------------------------
-- Table structure for `bb_extension_groups`
-- ----------------------------
DROP TABLE IF EXISTS `bb_extension_groups`;
CREATE TABLE IF NOT EXISTS `bb_extension_groups`
(
  `group_id`          MEDIUMINT(8)        NOT NULL AUTO_INCREMENT,
  `group_name`        VARCHAR(20)         NOT NULL DEFAULT '',
  `cat_id`            TINYINT(2)          NOT NULL DEFAULT '0',
  `allow_group`       TINYINT(1)          NOT NULL DEFAULT '0',
  `download_mode`     TINYINT(1) UNSIGNED NOT NULL DEFAULT '1',
  `upload_icon`       VARCHAR(100)        NOT NULL DEFAULT '',
  `max_filesize`      INT(20)             NOT NULL DEFAULT '0',
  `forum_permissions` TEXT                NOT NULL,
  PRIMARY KEY (`group_id`)
)
  ENGINE = MyISAM
  DEFAULT CHARSET = utf8mb4
  COLLATE = utf8mb4_unicode_ci;

-- ----------------------------
-- Records of bb_extension_groups
-- ----------------------------
INSERT INTO `bb_extension_groups` (`group_name`, `cat_id`, `allow_group`, `download_mode`, `upload_icon`,
                                   `max_filesize`, `forum_permissions`)
VALUES ('Images', '1', '1', '1', '', '262144', ''),
       ('Archives', '0', '1', '1', '', '262144', ''),
       ('Plain text', '0', '1', '1', '', '262144', ''),
       ('Documents', '0', '1', '1', '', '262144', ''),
       ('Real media', '0', '0', '2', '', '262144', ''),
       ('Torrent', '0', '1', '1', '', '262144', '');

-- ----------------------------
-- Table structure for `bb_forums`
-- ----------------------------
DROP TABLE IF EXISTS `bb_forums`;
CREATE TABLE IF NOT EXISTS `bb_forums`
(
  `forum_id`            SMALLINT(5) UNSIGNED  NOT NULL AUTO_INCREMENT,
  `cat_id`              SMALLINT(5) UNSIGNED  NOT NULL DEFAULT '0',
  `forum_name`          VARCHAR(150)          NOT NULL DEFAULT '',
  `forum_desc`          TEXT                  NOT NULL,
  `forum_status`        TINYINT(4)            NOT NULL DEFAULT '0',
  `forum_order`         SMALLINT(5) UNSIGNED  NOT NULL DEFAULT '1',
  `forum_posts`         MEDIUMINT(8) UNSIGNED NOT NULL DEFAULT '0',
  `forum_topics`        MEDIUMINT(8) UNSIGNED NOT NULL DEFAULT '0',
  `forum_last_post_id`  MEDIUMINT(8) UNSIGNED NOT NULL DEFAULT '0',
  `forum_tpl_id`        SMALLINT(6)           NOT NULL DEFAULT '0',
  `prune_days`          SMALLINT(5) UNSIGNED  NOT NULL DEFAULT '0',
  `auth_view`           TINYINT(2)            NOT NULL DEFAULT '0',
  `auth_read`           TINYINT(2)            NOT NULL DEFAULT '0',
  `auth_post`           TINYINT(2)            NOT NULL DEFAULT '0',
  `auth_reply`          TINYINT(2)            NOT NULL DEFAULT '0',
  `auth_edit`           TINYINT(2)            NOT NULL DEFAULT '0',
  `auth_delete`         TINYINT(2)            NOT NULL DEFAULT '0',
  `auth_sticky`         TINYINT(2)            NOT NULL DEFAULT '0',
  `auth_announce`       TINYINT(2)            NOT NULL DEFAULT '0',
  `auth_vote`           TINYINT(2)            NOT NULL DEFAULT '0',
  `auth_pollcreate`     TINYINT(2)            NOT NULL DEFAULT '0',
  `auth_attachments`    TINYINT(2)            NOT NULL DEFAULT '0',
  `auth_download`       TINYINT(2)            NOT NULL DEFAULT '0',
  `allow_reg_tracker`   TINYINT(1)            NOT NULL DEFAULT '0',
  `allow_porno_topic`   TINYINT(1)            NOT NULL DEFAULT '0',
  `self_moderated`      TINYINT(1)            NOT NULL DEFAULT '0',
  `forum_parent`        SMALLINT(5) UNSIGNED  NOT NULL DEFAULT '0',
  `show_on_index`       TINYINT(1)            NOT NULL DEFAULT '1',
  `forum_display_sort`  TINYINT(1)            NOT NULL DEFAULT '0',
  `forum_display_order` TINYINT(1)            NOT NULL DEFAULT '0',
  PRIMARY KEY (`forum_id`),
  KEY `forums_order` (`forum_order`),
  KEY `cat_id` (`cat_id`),
  KEY `forum_last_post_id` (`forum_last_post_id`),
  KEY `forum_parent` (`forum_parent`)
)
  ENGINE = MyISAM
  DEFAULT CHARSET = utf8mb4
  COLLATE = utf8mb4_unicode_ci;

-- ----------------------------
-- Records of bb_forums
-- ----------------------------
INSERT INTO `bb_forums`
VALUES ('1', '1', 'Your first forum', 'Description of the forum.', '0', '10', '1', '1', '1', '0', '0', '0', '0',
        '1',
        '1', '1', '1',
        '3', '3', '1',
        '1', '1', '1',
        '0', '0', '0', '0', '1', '0', '0');

-- ----------------------------
-- Table structure for `bb_groups`
-- ----------------------------
DROP TABLE IF EXISTS `bb_groups`;
CREATE TABLE IF NOT EXISTS `bb_groups`
(
  `group_id`          MEDIUMINT(8) NOT NULL AUTO_INCREMENT,
  `avatar_ext_id`     INT(15)      NOT NULL DEFAULT '0',
  `group_time`        INT(11)      NOT NULL DEFAULT '0',
  `mod_time`          INT(11)      NOT NULL DEFAULT '0',
  `group_type`        TINYINT(4)   NOT NULL DEFAULT '1',
  `release_group`     TINYINT(4)   NOT NULL DEFAULT '0',
  `group_name`        VARCHAR(40)  NOT NULL DEFAULT '',
  `group_description` TEXT         NOT NULL DEFAULT '',
  `group_signature`   TEXT         NOT NULL DEFAULT '',
  `group_moderator`   MEDIUMINT(8) NOT NULL DEFAULT '0',
  `group_single_user` TINYINT(1)   NOT NULL DEFAULT '1',
  PRIMARY KEY (`group_id`),
  KEY `group_single_user` (`group_single_user`)
)
  ENGINE = MyISAM
  DEFAULT CHARSET = utf8mb4
  COLLATE = utf8mb4_unicode_ci;

-- ----------------------------
-- Records of bb_groups
-- ----------------------------

-- ----------------------------
-- Table structure for `bb_log`
-- ----------------------------
DROP TABLE IF EXISTS `bb_log`;
CREATE TABLE IF NOT EXISTS `bb_log`
(
  `log_type_id`         MEDIUMINT(8) UNSIGNED NOT NULL DEFAULT '0',
  `log_user_id`         MEDIUMINT(9)          NOT NULL DEFAULT '0',
  `log_user_ip`         VARCHAR(42)           NOT NULL DEFAULT '0',
  `log_forum_id`        SMALLINT(5) UNSIGNED  NOT NULL DEFAULT '0',
  `log_forum_id_new`    SMALLINT(5) UNSIGNED  NOT NULL DEFAULT '0',
  `log_topic_id`        MEDIUMINT(8) UNSIGNED NOT NULL DEFAULT '0',
  `log_topic_id_new`    MEDIUMINT(8) UNSIGNED NOT NULL DEFAULT '0',
  `log_topic_title`     VARCHAR(250)          NOT NULL DEFAULT '',
  `log_topic_title_new` VARCHAR(250)          NOT NULL DEFAULT '',
  `log_time`            INT(11)               NOT NULL DEFAULT '0',
  `log_msg`             TEXT                  NOT NULL,
  KEY `log_time` (`log_time`),
  FULLTEXT KEY `log_topic_title` (`log_topic_title`)
)
  ENGINE = MyISAM
  DEFAULT CHARSET = utf8mb4
  COLLATE = utf8mb4_unicode_ci;

-- ----------------------------
-- Records of bb_log
-- ----------------------------

-- ----------------------------
-- Table structure for `bb_poll_users`
-- ----------------------------
DROP TABLE IF EXISTS `bb_poll_users`;
CREATE TABLE IF NOT EXISTS `bb_poll_users`
(
  `topic_id` INT(10) UNSIGNED NOT NULL,
  `user_id`  MEDIUMINT(8)     NOT NULL,
  `vote_ip`  VARCHAR(42)      NOT NULL DEFAULT '0',
  `vote_dt`  INT(11)          NOT NULL DEFAULT '0',
  PRIMARY KEY (`topic_id`, `user_id`)
)
  ENGINE = MyISAM
  DEFAULT CHARSET = utf8mb4
  COLLATE = utf8mb4_unicode_ci;

-- ----------------------------
-- Records of bb_poll_users
-- ----------------------------

-- ----------------------------
-- Table structure for `bb_poll_votes`
-- ----------------------------
DROP TABLE IF EXISTS `bb_poll_votes`;
CREATE TABLE IF NOT EXISTS `bb_poll_votes`
(
  `topic_id`    INT(10) UNSIGNED      NOT NULL,
  `vote_id`     TINYINT(4) UNSIGNED   NOT NULL,
  `vote_text`   VARCHAR(255)          NOT NULL,
  `vote_result` MEDIUMINT(8) UNSIGNED NOT NULL,
  PRIMARY KEY (`topic_id`, `vote_id`)
)
  ENGINE = MyISAM
  DEFAULT CHARSET = utf8mb4
  COLLATE = utf8mb4_unicode_ci;

-- ----------------------------
-- Records of bb_poll_votes
-- ----------------------------

-- ----------------------------
-- Table structure for `bb_posts`
-- ----------------------------
DROP TABLE IF EXISTS `bb_posts`;
CREATE TABLE IF NOT EXISTS `bb_posts`
(
  `post_id`         MEDIUMINT(8) UNSIGNED NOT NULL AUTO_INCREMENT,
  `topic_id`        MEDIUMINT(8) UNSIGNED NOT NULL DEFAULT '0',
  `forum_id`        SMALLINT(5) UNSIGNED  NOT NULL DEFAULT '0',
  `poster_id`       MEDIUMINT(8)          NOT NULL DEFAULT '0',
  `post_time`       INT(11)               NOT NULL DEFAULT '0',
  `poster_ip`       VARCHAR(42)           NOT NULL DEFAULT '0',
  `poster_rg_id`    MEDIUMINT(8)          NOT NULL DEFAULT '0',
  `attach_rg_sig`   TINYINT(4)            NOT NULL DEFAULT '0',
  `post_username`   VARCHAR(25)           NOT NULL DEFAULT '',
  `post_edit_time`  INT(11)               NOT NULL DEFAULT '0',
  `post_edit_count` SMALLINT(5) UNSIGNED  NOT NULL DEFAULT '0',
  `post_attachment` TINYINT(1)            NOT NULL DEFAULT '0',
  `user_post`       TINYINT(1)            NOT NULL DEFAULT '1',
  `mc_comment`      TEXT                  NOT NULL DEFAULT '',
  `mc_type`         TINYINT(1)            NOT NULL DEFAULT '0',
  `mc_user_id`      MEDIUMINT(8)          NOT NULL DEFAULT '0',
  PRIMARY KEY (`post_id`),
  KEY `topic_id` (`topic_id`),
  KEY `poster_id` (`poster_id`),
  KEY `post_time` (`post_time`),
  KEY `forum_id_post_time` (`forum_id`, `post_time`)
)
  ENGINE = MyISAM
  DEFAULT CHARSET = utf8mb4
  COLLATE = utf8mb4_unicode_ci;

-- ----------------------------
-- Records of bb_posts
-- ----------------------------
INSERT INTO `bb_posts`
VALUES ('1', '1', '1', '2', UNIX_TIMESTAMP(), '0', '0', '0', '', '0', '0', '0', '1', '', '0', '0');

-- ----------------------------
-- Table structure for `bb_posts_html`
-- ----------------------------
DROP TABLE IF EXISTS `bb_posts_html`;
CREATE TABLE IF NOT EXISTS `bb_posts_html`
(
  `post_id`        MEDIUMINT(9) NOT NULL DEFAULT '0',
  `post_html_time` TIMESTAMP    NOT NULL DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
  `post_html`      MEDIUMTEXT   NOT NULL DEFAULT '',
  PRIMARY KEY (`post_id`)
)
  ENGINE = MyISAM
  DEFAULT CHARSET = utf8mb4
  COLLATE = utf8mb4_unicode_ci;

-- ----------------------------
-- Records of bb_posts_html
-- ----------------------------

-- ----------------------------
-- Table structure for `bb_posts_search`
-- ----------------------------
DROP TABLE IF EXISTS `bb_posts_search`;
CREATE TABLE IF NOT EXISTS `bb_posts_search`
(
  `post_id`      MEDIUMINT(8) UNSIGNED NOT NULL DEFAULT '0',
  `search_words` TEXT                  NOT NULL,
  PRIMARY KEY (`post_id`),
  FULLTEXT KEY `search_words` (`search_words`)
)
  ENGINE = MyISAM
  DEFAULT CHARSET = utf8mb4
  COLLATE = utf8mb4_unicode_ci;

-- ----------------------------
-- Records of bb_posts_search
-- ----------------------------

-- ----------------------------
-- Table structure for `bb_posts_text`
-- ----------------------------
DROP TABLE IF EXISTS `bb_posts_text`;
CREATE TABLE IF NOT EXISTS `bb_posts_text`
(
  `post_id`   MEDIUMINT(8) UNSIGNED NOT NULL DEFAULT '0',
  `post_text` MEDIUMTEXT            NOT NULL,
  PRIMARY KEY (`post_id`)
)
  ENGINE = MyISAM
  DEFAULT CHARSET = utf8mb4
  COLLATE = utf8mb4_unicode_ci;

-- ----------------------------
-- Records of bb_posts_text
-- ----------------------------
INSERT INTO `bb_posts_text`
VALUES ('1',
        'Thank you for installing the new — TorrentPier Cattle!\n\nWhat to do next? First of all configure your site in the administration panel (link in the bottom).\n\nChange main options: site description, number of messages per topic, time zone, language by default, seed-bonus options, birthdays etc... Create a couple of forums, delete or change this one. Change settings of categories to allow registration of torrents, change announcer url. If you will have questions or want additional modifications of the engine, [url=https://torrentpier.com/]visit our forum[/url] (you can use english, we will try to help in any case).\n\nIf you want to help with the translations: [url=https://crowdin.com/project/torrentpier]Crowdin[/url].\n\nOur GitHub organization: [url=https://github.com/torrentpier]https://github.com/torrentpier[/url].\nOur SourceForge repository: [url=https://sourceforge.net/projects/torrentpier-engine]https://sourceforge.net/projects/torrentpier-engine[/url].\nOur demo website: [url=https://torrentpier.duckdns.org]https://torrentpier.duckdns.org[/url].\n\nWe are sure that you will be able to create the best tracker available!\nGood luck! 😉');

-- ----------------------------
-- Table structure for `bb_privmsgs`
-- ----------------------------
DROP TABLE IF EXISTS `bb_privmsgs`;
CREATE TABLE IF NOT EXISTS `bb_privmsgs`
(
  `privmsgs_id`          MEDIUMINT(8) UNSIGNED NOT NULL AUTO_INCREMENT,
  `privmsgs_type`        TINYINT(4)            NOT NULL DEFAULT '0',
  `privmsgs_subject`     VARCHAR(255)          NOT NULL DEFAULT '',
  `privmsgs_from_userid` MEDIUMINT(8)          NOT NULL DEFAULT '0',
  `privmsgs_to_userid`   MEDIUMINT(8)          NOT NULL DEFAULT '0',
  `privmsgs_date`        INT(11)               NOT NULL DEFAULT '0',
  `privmsgs_ip`          VARCHAR(42)           NOT NULL DEFAULT '0',
  PRIMARY KEY (`privmsgs_id`),
  KEY `privmsgs_from_userid` (`privmsgs_from_userid`),
  KEY `privmsgs_to_userid` (`privmsgs_to_userid`)
)
  ENGINE = MyISAM
  DEFAULT CHARSET = utf8mb4
  COLLATE = utf8mb4_unicode_ci;

-- ----------------------------
-- Records of bb_privmsgs
-- ----------------------------

-- ----------------------------
-- Table structure for `bb_privmsgs_text`
-- ----------------------------
DROP TABLE IF EXISTS `bb_privmsgs_text`;
CREATE TABLE IF NOT EXISTS `bb_privmsgs_text`
(
  `privmsgs_text_id` MEDIUMINT(8) UNSIGNED NOT NULL DEFAULT '0',
  `privmsgs_text`    MEDIUMTEXT            NOT NULL,
  PRIMARY KEY (`privmsgs_text_id`)
)
  ENGINE = MyISAM
  DEFAULT CHARSET = utf8mb4
  COLLATE = utf8mb4_unicode_ci;

-- ----------------------------
-- Records of bb_privmsgs_text
-- ----------------------------

-- ----------------------------
-- Table structure for `bb_quota_limits`
-- ----------------------------
DROP TABLE IF EXISTS `bb_quota_limits`;
CREATE TABLE IF NOT EXISTS `bb_quota_limits`
(
  `quota_limit_id` MEDIUMINT(8) UNSIGNED NOT NULL AUTO_INCREMENT,
  `quota_desc`     VARCHAR(20)           NOT NULL DEFAULT '',
  `quota_limit`    BIGINT(20) UNSIGNED   NOT NULL DEFAULT '0',
  PRIMARY KEY (`quota_limit_id`)
)
  ENGINE = MyISAM
  DEFAULT CHARSET = utf8mb4
  COLLATE = utf8mb4_unicode_ci;

-- ----------------------------
-- Records of bb_quota_limits
-- ----------------------------
INSERT INTO `bb_quota_limits` (`quota_desc`, `quota_limit`)
VALUES ('Low', '262144'),
       ('Medium', '10485760'),
       ('High', '15728640');

-- ----------------------------
-- Table structure for `bb_ranks`
-- ----------------------------
DROP TABLE IF EXISTS `bb_ranks`;
CREATE TABLE IF NOT EXISTS `bb_ranks`
(
  `rank_id`    SMALLINT(5) UNSIGNED NOT NULL AUTO_INCREMENT,
  `rank_title` VARCHAR(50)          NOT NULL DEFAULT '',
  `rank_image` VARCHAR(255)         NOT NULL DEFAULT '',
  `rank_style` VARCHAR(255)         NOT NULL DEFAULT '',
  PRIMARY KEY (`rank_id`)
)
  ENGINE = MyISAM
  DEFAULT CHARSET = utf8mb4
  COLLATE = utf8mb4_unicode_ci;

-- ----------------------------
-- Records of bb_ranks
-- ----------------------------
INSERT INTO `bb_ranks` (`rank_title`, `rank_image`, `rank_style`)
VALUES ('Administrator', 'styles/images/ranks/admin.png', 'colorAdmin');

-- ----------------------------
-- Table structure for `bb_search_rebuild`
-- ----------------------------
DROP TABLE IF EXISTS `bb_search_rebuild`;
CREATE TABLE IF NOT EXISTS `bb_search_rebuild`
(
  `rebuild_session_id`     MEDIUMINT(8) UNSIGNED NOT NULL AUTO_INCREMENT,
  `start_post_id`          MEDIUMINT(8) UNSIGNED NOT NULL DEFAULT '0',
  `end_post_id`            MEDIUMINT(8) UNSIGNED NOT NULL DEFAULT '0',
  `start_time`             INT(11)               NOT NULL DEFAULT '0',
  `end_time`               INT(11)               NOT NULL DEFAULT '0',
  `last_cycle_time`        INT(11)               NOT NULL DEFAULT '0',
  `session_time`           INT(11)               NOT NULL DEFAULT '0',
  `session_posts`          MEDIUMINT(8) UNSIGNED NOT NULL DEFAULT '0',
  `session_cycles`         MEDIUMINT(8) UNSIGNED NOT NULL DEFAULT '0',
  `search_size`            INT(10) UNSIGNED      NOT NULL DEFAULT '0',
  `rebuild_session_status` TINYINT(1)            NOT NULL DEFAULT '0',
  PRIMARY KEY (`rebuild_session_id`)
)
  ENGINE = MyISAM
  DEFAULT CHARSET = utf8mb4
  COLLATE = utf8mb4_unicode_ci;

-- ----------------------------
-- Records of bb_search_rebuild
-- ----------------------------

-- ----------------------------
-- Table structure for `bb_search_results`
-- ----------------------------
DROP TABLE IF EXISTS `bb_search_results`;
CREATE TABLE IF NOT EXISTS `bb_search_results`
(
  `session_id`      CHAR(255)
                      CHARACTER SET utf8
                        COLLATE utf8_bin NOT NULL DEFAULT '',
  `search_type`     TINYINT(4)           NOT NULL DEFAULT '0',
  `search_id`       VARCHAR(255)
                      CHARACTER SET utf8
                        COLLATE utf8_bin NOT NULL DEFAULT '',
  `search_time`     INT(11)              NOT NULL DEFAULT '0',
  `search_settings` TEXT                 NOT NULL,
  `search_array`    TEXT                 NOT NULL,
  PRIMARY KEY (`session_id`, `search_type`)
)
  ENGINE = MyISAM
  DEFAULT CHARSET = utf8mb4
  COLLATE = utf8mb4_unicode_ci;

-- ----------------------------
-- Records of bb_search_results
-- ----------------------------

-- ----------------------------
-- Table structure for `bb_sessions`
-- ----------------------------
DROP TABLE IF EXISTS `bb_sessions`;
CREATE TABLE IF NOT EXISTS `bb_sessions`
(
  `session_id`        CHAR(255)
                        CHARACTER SET utf8
                          COLLATE utf8_bin NOT NULL DEFAULT '',
  `session_user_id`   MEDIUMINT(8)         NOT NULL DEFAULT '0',
  `session_start`     INT(11)              NOT NULL DEFAULT '0',
  `session_time`      INT(11)              NOT NULL DEFAULT '0',
  `session_ip`        VARCHAR(42)          NOT NULL DEFAULT '0',
  `session_logged_in` TINYINT(1)           NOT NULL DEFAULT '0',
  `session_admin`     TINYINT(2)           NOT NULL DEFAULT '0',
  PRIMARY KEY (`session_id`)
)
  ENGINE = MyISAM
  DEFAULT CHARSET = utf8mb4
  COLLATE = utf8mb4_unicode_ci;

-- ----------------------------
-- Records of bb_sessions
-- ----------------------------

-- ----------------------------
-- Table structure for `bb_smilies`
-- ----------------------------
DROP TABLE IF EXISTS `bb_smilies`;
CREATE TABLE IF NOT EXISTS `bb_smilies`
(
  `smilies_id` SMALLINT(5) UNSIGNED NOT NULL AUTO_INCREMENT,
  `code`       VARCHAR(50)          NOT NULL DEFAULT '',
  `smile_url`  VARCHAR(100)         NOT NULL DEFAULT '',
  `emoticon`   VARCHAR(75)          NOT NULL DEFAULT '',
  PRIMARY KEY (`smilies_id`)
)
  ENGINE = MyISAM
  DEFAULT CHARSET = utf8mb4
  COLLATE = utf8mb4_unicode_ci;

-- ----------------------------
-- Records of bb_smilies
-- ----------------------------
INSERT INTO `bb_smilies` (`code`, `smile_url`, `emoticon`)
VALUES (':aa:', 'aa.gif', 'aa'),
       (':ab:', 'ab.gif', 'ab'),
       (':ac:', 'ac.gif', 'ac'),
       (':ae:', 'ae.gif', 'ae'),
       (':af:', 'af.gif', 'af'),
       (':ag:', 'ag.gif', 'ag'),
       (':ah:', 'ah.gif', 'ah'),
       (':ai:', 'ai.gif', 'ai'),
       (':aj:', 'aj.gif', 'aj'),
       (':ak:', 'ak.gif', 'ak'),
       (':al:', 'al.gif', 'al'),
       (':am:', 'am.gif', 'am'),
       (':an:', 'an.gif', 'an'),
       (':ao:', 'ao.gif', 'ao'),
       (':ap:', 'ap.gif', 'ap'),
       (':aq:', 'aq.gif', 'aq'),
       (':ar:', 'ar.gif', 'ar'),
       (':as:', 'as.gif', 'as'),
       (':at:', 'at.gif', 'at'),
       (':au:', 'au.gif', 'au'),
       (':av:', 'av.gif', 'av'),
       (':aw:', 'aw.gif', 'aw'),
       (':ax:', 'ax.gif', 'ax'),
       (':ay:', 'ay.gif', 'ay'),
       (':az:', 'az.gif', 'az'),
       (':ba:', 'ba.gif', 'ba'),
       (':bb:', 'bb.gif', 'bb'),
       (':bc:', 'bc.gif', 'bc'),
       (':bd:', 'bd.gif', 'bd'),
       (':be:', 'be.gif', 'be'),
       (':bf:', 'bf.gif', 'bf'),
       (':bg:', 'bg.gif', 'bg'),
       (':bh:', 'bh.gif', 'bh'),
       (':bi:', 'bi.gif', 'bi'),
       (':bj:', 'bj.gif', 'bj'),
       (':bk:', 'bk.gif', 'bk'),
       (':bl:', 'bl.gif', 'bl'),
       (':bm:', 'bm.gif', 'bm'),
       (':bn:', 'bn.gif', 'bn'),
       (':bo:', 'bo.gif', 'bo'),
       (':bp:', 'bp.gif', 'bp'),
       (':bq:', 'bq.gif', 'bq'),
       (':br:', 'br.gif', 'br'),
       (':bs:', 'bs.gif', 'bs'),
       (':bt:', 'bt.gif', 'bt'),
       (':bu:', 'bu.gif', 'bu'),
       (':bv:', 'bv.gif', 'bv'),
       (':bw:', 'bw.gif', 'bw'),
       (':bx:', 'bx.gif', 'bx'),
       (':by:', 'by.gif', 'by'),
       (':bz:', 'bz.gif', 'bz'),
       (':ca:', 'ca.gif', 'ca'),
       (':cb:', 'cb.gif', 'cb'),
       (':cc:', 'cc.gif', 'cc'),
       (':cd:', 'cd.gif', 'cd');

-- ----------------------------
-- Table structure for `bb_topics`
-- ----------------------------
DROP TABLE IF EXISTS `bb_topics`;
CREATE TABLE IF NOT EXISTS `bb_topics`
(
  `topic_id`              MEDIUMINT(8) UNSIGNED NOT NULL AUTO_INCREMENT,
  `forum_id`              SMALLINT(8) UNSIGNED  NOT NULL DEFAULT '0',
  `topic_title`           VARCHAR(250)          NOT NULL DEFAULT '',
  `topic_poster`          MEDIUMINT(8)          NOT NULL DEFAULT '0',
  `topic_time`            INT(11)               NOT NULL DEFAULT '0',
  `topic_views`           MEDIUMINT(8) UNSIGNED NOT NULL DEFAULT '0',
  `topic_replies`         MEDIUMINT(8) UNSIGNED NOT NULL DEFAULT '0',
  `topic_status`          TINYINT(3)            NOT NULL DEFAULT '0',
  `topic_vote`            TINYINT(1)            NOT NULL DEFAULT '0',
  `topic_type`            TINYINT(3)            NOT NULL DEFAULT '0',
  `topic_first_post_id`   MEDIUMINT(8) UNSIGNED NOT NULL DEFAULT '0',
  `topic_last_post_id`    MEDIUMINT(8) UNSIGNED NOT NULL DEFAULT '0',
  `topic_moved_id`        MEDIUMINT(8) UNSIGNED NOT NULL DEFAULT '0',
  `topic_attachment`      TINYINT(1)            NOT NULL DEFAULT '0',
  `topic_dl_type`         TINYINT(1)            NOT NULL DEFAULT '0',
  `topic_last_post_time`  INT(11)               NOT NULL DEFAULT '0',
  `topic_show_first_post` TINYINT(1) UNSIGNED   NOT NULL DEFAULT '0',
  `topic_allow_robots`    TINYINT(1) UNSIGNED   NOT NULL DEFAULT '0',
  PRIMARY KEY (`topic_id`),
  KEY `forum_id` (`forum_id`),
  KEY `topic_last_post_id` (`topic_last_post_id`),
  KEY `topic_last_post_time` (`topic_last_post_time`),
  FULLTEXT KEY `topic_title` (`topic_title`)
)
  ENGINE = MyISAM
  DEFAULT CHARSET = utf8mb4
  COLLATE = utf8mb4_unicode_ci;

-- ----------------------------
-- Records of bb_topics
-- ----------------------------
INSERT INTO `bb_topics`
VALUES ('1', '1', 'Welcome to TorrentPier Cattle', '2', UNIX_TIMESTAMP(), '0', '0', '0', '0', '0', '1', '1',
        '0',
        '0',
        '0', UNIX_TIMESTAMP(), '0', '1');

-- ----------------------------
-- Table structure for `bb_topics_watch`
-- ----------------------------
DROP TABLE IF EXISTS `bb_topics_watch`;
CREATE TABLE IF NOT EXISTS `bb_topics_watch`
(
  `topic_id`      MEDIUMINT(8) UNSIGNED NOT NULL DEFAULT '0',
  `user_id`       MEDIUMINT(8)          NOT NULL DEFAULT '0',
  `notify_status` TINYINT(1)            NOT NULL DEFAULT '0',
  KEY `topic_id` (`topic_id`),
  KEY `user_id` (`user_id`),
  KEY `notify_status` (`notify_status`)
)
  ENGINE = MyISAM
  DEFAULT CHARSET = utf8mb4
  COLLATE = utf8mb4_unicode_ci;

-- ----------------------------
-- Records of bb_topics_watch
-- ----------------------------
INSERT INTO `bb_topics_watch`
VALUES ('1', '2', '1');

-- ----------------------------
-- Table structure for `bb_topic_tpl`
-- ----------------------------
DROP TABLE IF EXISTS `bb_topic_tpl`;
CREATE TABLE IF NOT EXISTS `bb_topic_tpl`
(
  `tpl_id`            SMALLINT(6)      NOT NULL AUTO_INCREMENT,
  `tpl_name`          VARCHAR(60)      NOT NULL DEFAULT '',
  `tpl_src_form`      TEXT             NOT NULL,
  `tpl_src_title`     TEXT             NOT NULL,
  `tpl_src_msg`       TEXT             NOT NULL,
  `tpl_comment`       TEXT             NOT NULL,
  `tpl_rules_post_id` INT(10) UNSIGNED NOT NULL DEFAULT '0',
  `tpl_last_edit_tm`  INT(11)          NOT NULL DEFAULT '0',
  `tpl_last_edit_by`  INT(11)          NOT NULL DEFAULT '0',
  PRIMARY KEY (`tpl_id`),
  UNIQUE KEY `tpl_name` (`tpl_name`)
)
  ENGINE = MyISAM
  DEFAULT CHARSET = utf8mb4
  COLLATE = utf8mb4_unicode_ci;

-- ----------------------------
-- Records of bb_topic_tpl
-- ----------------------------

-- ----------------------------
-- Table structure for `bb_users`
-- ----------------------------
DROP TABLE IF EXISTS `bb_users`;
CREATE TABLE IF NOT EXISTS `bb_users`
(
  `user_id`             MEDIUMINT(8)          NOT NULL AUTO_INCREMENT,
  `user_active`         TINYINT(1)            NOT NULL DEFAULT '1',
  `username`            VARCHAR(255)          NOT NULL DEFAULT '',
  `user_password`       VARCHAR(255)
                          CHARACTER SET utf8
                            COLLATE utf8_bin  NOT NULL DEFAULT '',
  `user_session_time`   INT(11)               NOT NULL DEFAULT '0',
  `user_lastvisit`      INT(11)               NOT NULL DEFAULT '0',
  `user_last_ip`        VARCHAR(42)           NOT NULL DEFAULT '0',
  `user_regdate`        INT(11)               NOT NULL DEFAULT '0',
  `user_reg_ip`         VARCHAR(42)           NOT NULL DEFAULT '0',
  `user_level`          TINYINT(4)            NOT NULL DEFAULT '0',
  `user_posts`          MEDIUMINT(8) UNSIGNED NOT NULL DEFAULT '0',
  `user_timezone`       DECIMAL(5, 2)         NOT NULL DEFAULT '0.00',
  `user_lang`           VARCHAR(255)          NOT NULL DEFAULT 'en',
  `user_new_privmsg`    SMALLINT(5) UNSIGNED  NOT NULL DEFAULT '0',
  `user_unread_privmsg` SMALLINT(5) UNSIGNED  NOT NULL DEFAULT '0',
  `user_last_privmsg`   INT(11)               NOT NULL DEFAULT '0',
  `user_opt`            INT(11)               NOT NULL DEFAULT '0',
  `user_rank`           INT(11)               NOT NULL DEFAULT '0',
  `avatar_ext_id`       TINYINT(4)            NOT NULL DEFAULT '0',
  `user_gender`         TINYINT(1)            NOT NULL DEFAULT '0',
  `user_birthday`       DATE                  NOT NULL DEFAULT '1900-01-01',
  `user_email`          VARCHAR(255)          NOT NULL DEFAULT '',
  `user_skype`          VARCHAR(32)           NOT NULL DEFAULT '',
  `user_twitter`        VARCHAR(15)           NOT NULL DEFAULT '',
  `user_icq`            VARCHAR(15)           NOT NULL DEFAULT '',
  `user_website`        VARCHAR(100)          NOT NULL DEFAULT '',
  `user_from`           VARCHAR(100)          NOT NULL DEFAULT '',
  `user_sig`            TEXT                  NOT NULL DEFAULT '',
  `user_occ`            VARCHAR(100)          NOT NULL DEFAULT '',
  `user_interests`      VARCHAR(255)          NOT NULL DEFAULT '',
  `user_actkey`         VARCHAR(255)          NOT NULL DEFAULT '',
  `user_newpasswd`      VARCHAR(255)          NOT NULL DEFAULT '',
  `autologin_id`        VARCHAR(255)
                          CHARACTER SET utf8
                            COLLATE utf8_bin  NOT NULL DEFAULT '',
  `user_newest_pm_id`   MEDIUMINT(8)          NOT NULL DEFAULT '0',
  `user_points`         FLOAT(16, 2)          NOT NULL DEFAULT '0.00',
  `tpl_name`            VARCHAR(255)          NOT NULL DEFAULT 'default',
  PRIMARY KEY (`user_id`),
  KEY `username` (`username`(10)),
  KEY `user_email` (`user_email`(10)),
  KEY `user_level` (`user_level`)
)
  ENGINE = MyISAM
  DEFAULT CHARSET = utf8mb4
  COLLATE = utf8mb4_unicode_ci;

-- ----------------------------
-- Records of bb_users
-- ----------------------------
INSERT INTO `bb_users`
VALUES ('-1', '0', 'Guest', '$2y$10$sfZSmqPio8mxxFQLRRXaFuVMkFKZARRz/RzqddfYByN3M53.CEe.O', '0', '0',
        '0', UNIX_TIMESTAMP(), '0', '0', '0', '',
        'en', '0',
        '0', '0',
        '0', '0',
        '0', '0',
        '1900-01-01',
        '', '', '', '', '', '', '', '', '', '', '', '', '0', '0.00', 'default'),
       ('-746', '0', 'bot', '$2y$10$sfZSmqPio8mxxFQLRRXaFuVMkFKZARRz/RzqddfYByN3M53.CEe.O', '0', '0',
        '0', UNIX_TIMESTAMP(), '0', '0', '0', '',
        'en', '0',
        '0', '0',
        '144', '0',
        '0', '0',
        '1900-01-01',
        'bot@torrentpier.com', '', '', '', '', '', '', '', '', '', '', '', '0', '0.00', 'default'),
       ('2', '1', 'admin', '$2y$10$QeekUGqdfMO0yp7AT7la8OhgbiNBoJ627BO38MdS1h5kY7oX6UUKu', '0', '0',
        '0', UNIX_TIMESTAMP(), '0', '1', '1', '', 'en',
        '0',
        '0', '0',
        '304', '1',
        '0', '0',
        '1900-01-01',
        'admin@torrentpier.com', '', '', '', '', '', '', '', '', '', '', '', '0', '0.00', 'default');

-- ----------------------------
-- Table structure for `bb_user_group`
-- ----------------------------
DROP TABLE IF EXISTS `bb_user_group`;
CREATE TABLE IF NOT EXISTS `bb_user_group`
(
  `group_id`     MEDIUMINT(8) NOT NULL DEFAULT '0',
  `user_id`      MEDIUMINT(8) NOT NULL DEFAULT '0',
  `user_pending` TINYINT(1)   NOT NULL DEFAULT '0',
  `user_time`    INT(11)      NOT NULL DEFAULT '0',
  PRIMARY KEY (`group_id`, `user_id`),
  KEY `user_id` (`user_id`)
)
  ENGINE = MyISAM
  DEFAULT CHARSET = utf8mb4
  COLLATE = utf8mb4_unicode_ci;

-- ----------------------------
-- Records of bb_user_group
-- ----------------------------

-- ----------------------------
-- Table structure for `bb_words`
-- ----------------------------
DROP TABLE IF EXISTS `bb_words`;
CREATE TABLE IF NOT EXISTS `bb_words`
(
  `word_id`     MEDIUMINT(8) UNSIGNED NOT NULL AUTO_INCREMENT,
  `word`        CHAR(100)             NOT NULL DEFAULT '',
  `replacement` CHAR(100)             NOT NULL DEFAULT '',
  PRIMARY KEY (`word_id`)
)
  ENGINE = MyISAM
  DEFAULT CHARSET = utf8mb4
  COLLATE = utf8mb4_unicode_ci;

-- ----------------------------
-- Records of bb_words
-- ----------------------------

-- ----------------------------
-- Table structure for `buf_last_seeder`
-- ----------------------------
DROP TABLE IF EXISTS `buf_last_seeder`;
CREATE TABLE IF NOT EXISTS `buf_last_seeder`
(
  `topic_id`         MEDIUMINT(8) UNSIGNED NOT NULL DEFAULT '0',
  `seeder_last_seen` INT(11)               NOT NULL DEFAULT '0',
  `user_id`          MEDIUMINT(8)          NOT NULL DEFAULT '0',
  PRIMARY KEY (`topic_id`)
)
  ENGINE = MyISAM
  DEFAULT CHARSET = utf8mb4
  COLLATE = utf8mb4_unicode_ci;

-- ----------------------------
-- Records of buf_last_seeder
-- ----------------------------

-- ----------------------------
-- Table structure for `bb_thx`
-- ----------------------------
DROP TABLE IF EXISTS `bb_thx`;
CREATE TABLE IF NOT EXISTS `bb_thx`
(
  `topic_id` MEDIUMINT(8) UNSIGNED NOT NULL DEFAULT '0',
  `user_id`  MEDIUMINT(8)          NOT NULL DEFAULT '0',
  `time`     INT(11)               NOT NULL DEFAULT '0',
  PRIMARY KEY (`topic_id`, `user_id`)
)
  ENGINE = MyISAM
  DEFAULT CHARSET = utf8mb4
  COLLATE = utf8mb4_unicode_ci;

-- ----------------------------
-- Records of bb_thx
-- ----------------------------

-- ----------------------------
-- Table structure for `buf_topic_view`
-- ----------------------------
DROP TABLE IF EXISTS `buf_topic_view`;
CREATE TABLE IF NOT EXISTS `buf_topic_view`
(
  `topic_id`    MEDIUMINT(8) UNSIGNED NOT NULL DEFAULT '0',
  `topic_views` MEDIUMINT(8) UNSIGNED NOT NULL DEFAULT '0',
  PRIMARY KEY (`topic_id`)
)
  ENGINE = MyISAM
  DEFAULT CHARSET = utf8mb4
  COLLATE = utf8mb4_unicode_ci;

-- ----------------------------
-- Records of buf_topic_view
-- ----------------------------
