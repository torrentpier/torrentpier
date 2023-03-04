SET SQL_MODE = "";

-- ----------------------------
-- Table structure for `bb_bt_tracker`
-- ----------------------------
DROP TABLE IF EXISTS `bb_bt_tracker`;
CREATE TABLE IF NOT EXISTS `bb_bt_tracker` (
  `peer_hash` varchar(32) CHARACTER SET utf8 COLLATE utf8_bin NOT NULL DEFAULT '',
  `topic_id` mediumint(8) unsigned NOT NULL DEFAULT '0',
  `peer_id` varchar(20) NOT NULL,
  `user_id` mediumint(9) NOT NULL DEFAULT '0',
  `ip` varchar(42) NOT NULL DEFAULT '0',
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
  `complete` int(11) NOT NULL DEFAULT '0',
  PRIMARY KEY (`topic_id`)
) ENGINE=MyISAM DEFAULT CHARSET=utf8;

-- ----------------------------
-- Records of bb_bt_tracker_snap
-- ----------------------------
