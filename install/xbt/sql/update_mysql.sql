CREATE TABLE IF NOT EXISTS `bb_bt_tor_dl_stat` (
  `topic_id` mediumint(8) unsigned NOT NULL default '0',
  `user_id` mediumint(9) NOT NULL default '0',
  `attach_id` mediumint(8) unsigned NOT NULL default '0',
  `t_up_total` bigint(20) unsigned NOT NULL default '0',
  `t_down_total` bigint(20) unsigned NOT NULL default '0',
  `t_bonus_total` bigint(20) unsigned NOT NULL default '0',
  PRIMARY KEY  (`topic_id`,`user_id`)
);

CREATE TABLE IF NOT EXISTS `bb_bt_torrents_del` (
  `topic_id` mediumint(8) unsigned NOT NULL,
  `info_hash` tinyblob NOT NULL,
  `is_del` tinyint(4) NOT NULL default '1',
  `dl_percent` tinyint(4) NOT NULL default '100',
  PRIMARY KEY  (`topic_id`)
);

ALTER TABLE `bb_bt_tracker` ADD `peer_id` varchar(20) NOT NULL AFTER `topic_id`;
ALTER TABLE `bb_bt_tracker` ADD `ipv6` varchar(32) DEFAULT NULL;
ALTER TABLE `bb_bt_tracker` ADD `complete_percent` bigint(20) NOT NULL default '0';

ALTER TABLE bb_bt_torrents ADD speed_up mediumint(8) NOT NULL default 0;
ALTER TABLE bb_bt_torrents ADD speed_down mediumint(8) NOT NULL default 0;

-----------------
-- XBTT Tables --
-----------------
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
);

CREATE TABLE IF NOT EXISTS `xbt_config` (
  `name` varchar(255) NOT NULL DEFAULT '',
  `value` varchar(255) NOT NULL DEFAULT '',
  PRIMARY KEY (`name`)
);

CREATE TABLE IF NOT EXISTS `xbt_deny_from_hosts` (
  `begin` int(11) NOT NULL DEFAULT '0',
  `end` int(11) NOT NULL DEFAULT '0'
);

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
);

CREATE TABLE IF NOT EXISTS `xbt_scrape_log` (
  `id` int(11) NOT NULL AUTO_INCREMENT,
  `ipa` int(11) NOT NULL DEFAULT '0',
  `info_hash` blob,
  `uid` int(11) NOT NULL DEFAULT '0',
  `mtime` int(11) NOT NULL DEFAULT '0',
  PRIMARY KEY (`id`)
);

-- bb_config --
insert into bb_config
select * from xbt_config
where name='torrent_pass_private_key';