UPDATE `bb_config`
SET `config_value` = 'http://whatismyipaddress.com/ip/'
WHERE `config_name` = 'whois_info';
-- ----------------------------
DELETE
FROM `bb_smilies`
WHERE `code` = ':ad:';
-- ----------------------------
INSERT INTO `bb_smilies` (`code`, `smile_url`, `emoticon`)
VALUES (':сd:', 'сd.gif', 'сd');
-- ----------------------------
DROP TABLE IF EXISTS `bb_ads`;
-- ----------------------------
DELETE
FROM `bb_config`
WHERE `config_name` = 'active_ads';
-- ----------------------------
ALTER TABLE `bb_log` DROP COLUMN `log_username`;
-- ----------------------------
DELETE
FROM `bb_config`
WHERE `config_name` = 'new_tpls';
-- ----------------------------
UPDATE `bb_posts`
SET `poster_ip` = '0';
-- ----------------------------
ALTER TABLE `bb_posts` CHANGE `poster_ip` `poster_ip` varchar (42) NOT NULL DEFAULT '0';
-- ----------------------------
UPDATE `bb_bt_tracker`
SET `ip` = '0';
-- ----------------------------
ALTER TABLE `bb_bt_tracker` CHANGE `ip` `ip` varchar (42) NOT NULL DEFAULT '0';
-- ----------------------------
UPDATE `bb_users`
SET `user_last_ip` = '0';
-- ----------------------------
ALTER TABLE `bb_users` CHANGE `user_last_ip` `user_last_ip` varchar (42) NOT NULL DEFAULT '0';
-- ----------------------------
UPDATE `bb_users`
SET `user_reg_ip` = '0';
-- ----------------------------
ALTER TABLE `bb_users` CHANGE `user_reg_ip` `user_reg_ip` varchar (42) NOT NULL DEFAULT '0';
-- ----------------------------
UPDATE `bb_log`
SET `log_user_ip` = '0';
-- ----------------------------
ALTER TABLE `bb_log` CHANGE `log_user_ip` `log_user_ip` varchar (42) NOT NULL DEFAULT '0';
-- ----------------------------
UPDATE `bb_poll_users`
SET `vote_ip` = '0';
-- ----------------------------
ALTER TABLE `bb_poll_users` CHANGE `vote_ip` `vote_ip` varchar (42) NOT NULL DEFAULT '0';
-- ----------------------------
UPDATE `bb_privmsgs`
SET `privmsgs_ip` = '0';
-- ----------------------------
ALTER TABLE `bb_privmsgs` CHANGE `privmsgs_ip` `privmsgs_ip` varchar (42) NOT NULL DEFAULT '0';
-- ----------------------------
UPDATE `bb_sessions`
SET `session_ip` = '0';
-- ----------------------------
ALTER TABLE `bb_sessions` CHANGE `session_ip` `session_ip` varchar (42) NOT NULL DEFAULT '0';
-- ----------------------------
UPDATE `bb_banlist`
SET `ban_ip` = '0';
-- ----------------------------
ALTER TABLE `bb_banlist` CHANGE `ban_ip` `ban_ip` varchar (42) NOT NULL DEFAULT '0';
