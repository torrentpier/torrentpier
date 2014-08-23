<?php

define('IN_FORUM', true);
define('BB_ROOT', './');
require(BB_ROOT . 'common.php');

while (@ob_end_flush()) ;
ob_implicit_flush();
error_reporting(E_ALL);
ini_set('display_errors', 1);

$user->session_start();

set_die_append_msg();
if (!IS_SUPER_ADMIN) bb_die($lang['ONLY_FOR_SUPER_ADMIN']);

// DRAFT, TODO IN NEXT REVISIONS

/*

INSERT INTO `bb_config` VALUES ('tor_comment', '1'); // добавлено 407
ALTER TABLE `bb_posts` ADD `post_mod_comment` TEXT NOT NULL DEFAULT ''; // добавлено 458
ALTER TABLE `bb_posts` ADD `post_mod_comment_type` TINYINT( 1 ) NOT NULL DEFAULT '0'; // добавлено 458
ALTER TABLE `bb_posts` ADD `post_mc_mod_id` mediumint(8) NOT NULL; // добавлено 458
ALTER TABLE `bb_posts` ADD `post_mc_mod_name` varchar(25) NOT NULL DEFAULT ''; // добавлено 458
ALTER TABLE `bb_topics` ADD COLUMN `is_draft` TINYINT(1) UNSIGNED NOT NULL DEFAULT '0' AFTER `topic_show_first_post`; // удалено 486
ALTER TABLE `bb_users` CHANGE `user_birthday` `user_birthday` date NOT NULL DEFAULT '0000-00-00'; // изменено 496
// 496 - отдельный конвертер
ALTER TABLE `bb_users` ADD `tpl_name` varchar(255) NOT NULL DEFAULT 'default'; // добавлено 507
INSERT INTO `bb_config` VALUES ('bt_unset_dltype_on_tor_unreg', '1'); // изменено значение 508
ALTER TABLE `bb_users` DROP `ignore_srv_load`; // удалено 537
INSERT INTO `bb_users` VALUES (-1, 0, 'Guest', // переименовали гостя 540
DROP TABLE IF EXISTS `bb_bt_torrents_del`; // удалено 551
DROP TABLE IF EXISTS `xbt_announce_log`; // удалено 551
DROP TABLE IF EXISTS `xbt_config`; // удалено 551
DROP TABLE IF EXISTS `xbt_deny_from_hosts`; // удалено 551
DROP TABLE IF EXISTS `xbt_files_users`; // удалено 551
DROP TABLE IF EXISTS `xbt_scrape_log`; // удалено 551
ALTER TABLE `bb_bt_tracker` DROP `xbt_error`; // удалено 551
INSERT INTO `bb_config` VALUES ('torrent_pass_private_key', 'вставить_из_конфига_XBTT'); // удалено 551
INSERT INTO `bb_config` VALUES ('board_email', 'board_email@yourdomain.com'); // удалено 552
INSERT INTO `bb_config` VALUES ('board_email_form', '0'); // удалено 552
INSERT INTO `bb_config` VALUES ('board_email_sig', 'Thanks, The Management'); // удалено 552
INSERT INTO `bb_config` VALUES ('smtp_delivery', '0'); // удалено 552
INSERT INTO `bb_config` VALUES ('smtp_host', ''); // удалено 552
INSERT INTO `bb_config` VALUES ('smtp_password', ''); // удалено 552
INSERT INTO `bb_config` VALUES ('smtp_username', ''); // удалено 552
INSERT INTO `bb_config` VALUES ('gallery_enabled', '1'); // удалено 554
INSERT INTO `bb_config` VALUES ('pic_dir', 'pictures/'); // удалено 554
INSERT INTO `bb_config` VALUES ('pic_max_size', '3'); // удалено 554
INSERT INTO `bb_config` VALUES ('auto_delete_posted_pics', '1'); // удалено 554
INSERT INTO `bb_config` VALUES ('allow_avatar_remote', '0'); // удалено 555
ALTER TABLE `bb_topics` DROP COLUMN `is_draft`;
INSERT INTO `bb_users` VALUES (-1, 0, 'Guest', 'd41d8cd98f00b204e9800998ecf8427e', 0, 0, '0', // обновили число сообщенией гостю 561
INSERT INTO `bb_config` VALUES ('bt_add_comment', ''); // удалено 565
INSERT INTO `bb_config` VALUES ('bt_add_publisher', 'YourSiteName'); // удалено 565
INSERT INTO `bb_config` VALUES ('bt_gen_passkey_on_reg', '1'); // удалено 565
INSERT INTO `bb_config` VALUES ('bt_announce_url', 'http://demo.torrentpier.me/bt/announce.php'); // обновлено 565
INSERT INTO `bb_config` VALUES ('site_desc', 'A little text to describe your forum'); // обновлено 565
INSERT INTO `bb_posts` VALUES (1, 1, 1, 2, UNIX_TIMESTAMP(), '', '', 0, 0, 0, 0, 1, '', 0, 0, ''); // обновлено 570
INSERT INTO `bb_topics` VALUES (1, 1, 'Добро пожаловать в TorrentPier II', 2, UNIX_TIMESTAMP(),  // обновлено 570
DROP TABLE IF EXISTS `bb_bt_dlstatus_main`; // переименовано 571
DROP TABLE IF EXISTS `bb_bt_dlstatus_mrg`; // удалено 571
DROP TABLE IF EXISTS `bb_bt_dlstatus_new`; // удалено 571
DROP TABLE IF EXISTS `sph_counter`; // удалено 571
// 571 - отдельный конвертер
INSERT INTO `bb_config` VALUES ('max_inbox_privmsgs', '200'); // удалено 573
INSERT INTO `bb_config` VALUES ('max_savebox_privmsgs', '50'); // удалено 573
INSERT INTO `bb_config` VALUES ('max_sentbox_privmsgs', '25'); // удалено 573
INSERT INTO `bb_config` VALUES ('privmsg_disable', '0'); // удалено 573
// добавлено 575
CREATE TABLE IF NOT EXISTS `bb_poll_users` (
  `topic_id` int(10) unsigned NOT NULL,
  `user_id` int(11) NOT NULL,
  `vote_ip` varchar(32) NOT NULL,
  `vote_dt` int(11) NOT NULL DEFAULT '0',
  PRIMARY KEY (`topic_id`,`user_id`)
) ENGINE=MyISAM DEFAULT CHARSET=utf8;
// добавлено 575
CREATE TABLE IF NOT EXISTS `bb_poll_votes` (
  `topic_id` int(10) unsigned NOT NULL,
  `vote_id` tinyint(4) unsigned NOT NULL,
  `vote_text` varchar(255) NOT NULL,
  `vote_result` mediumint(8) unsigned NOT NULL,
  PRIMARY KEY (`topic_id`,`vote_id`)
) ENGINE=MyISAM DEFAULT CHARSET=utf8;
INSERT INTO `bb_config` VALUES ('config_id', '1'); // удалено 579
INSERT INTO `bb_config` VALUES ('sendmail_fix', '0'); // удалено 579
INSERT INTO `bb_config` VALUES ('version', '.0.22'); // удалено 579
INSERT INTO `bb_config` VALUES ('xs_add_comments', '0'); // удалено 579
INSERT INTO `bb_config` VALUES ('xs_auto_compile', '1'); // удалено 579
INSERT INTO `bb_config` VALUES ('xs_auto_recompile', '1'); // удалено 579
INSERT INTO `bb_config` VALUES ('xs_php', 'php'); // удалено 579
INSERT INTO `bb_config` VALUES ('xs_shownav', '17'); // удалено 579
INSERT INTO `bb_config` VALUES ('xs_template_time', '0'); // удалено 579
INSERT INTO `bb_config` VALUES ('xs_version', '8'); // удалено 579
INSERT INTO `bb_cron` VALUES (22, 1, 'Attach maintenance', 'attach_maintenance.php', 'daily', NULL, '05:00:00', 40, '', '', NULL, 1, '', 0, 1, 0); // добавлено 582
INSERT INTO `bb_config` VALUES ('allow_avatar_local', '1'); // удалено 583
INSERT INTO `bb_config` VALUES ('allow_avatar_upload', '1'); // удалено 583
INSERT INTO `bb_config` VALUES ('avatar_filesize', '100000'); // удалено 583
INSERT INTO `bb_config` VALUES ('avatar_gallery_path', 'images/avatars/gallery'); // удалено 583
INSERT INTO `bb_config` VALUES ('avatar_max_height', '100'); // удалено 583
INSERT INTO `bb_config` VALUES ('avatar_max_width', '100'); // удалено 583
INSERT INTO `bb_config` VALUES ('avatar_path', 'images/avatars'); // удалено 583
INSERT INTO `bb_config` VALUES ('require_activation', '0'); // удалено 583
INSERT INTO `bb_config` VALUES ('no_avatar', 'images/avatars/gallery/noavatar.png'); // удалено 583
INSERT INTO `bb_config` VALUES ('show_mod_index', '0'); // изменено 583
INSERT INTO `bb_cron` VALUES (3, 1, 'Avatars cleanup', 'avatars_cleanup.php', // удалено заменено на аттачи 583
// 583 - отдельный конвертер
DROP TABLE IF EXISTS `bb_topic_templates`; // удалено 584
ALTER TABLE `bb_forums` DROP `topic_tpl_id`; // удалено 584
INSERT INTO `bb_config` VALUES ('whois_info', 'http://ip-whois.net/ip_geos.php?ip='); // обновлено 586
INSERT INTO `bb_config` VALUES ('default_lang', 'ru'); // обновлено 588
// 588 - отдельный конвертер
ALTER TABLE `bb_users` DROP `user_next_birthday_greeting`; // удалено 589
ALTER TABLE `bb_posts` CHANGE `post_mod_comment` `mc_comment` TEXT CHARACTER SET utf8 COLLATE utf8_general_ci NOT NULL DEFAULT ''; // изменено 590
ALTER TABLE `bb_posts` CHANGE `post_mod_comment_type` `mc_type` TINYINT(1) NOT NULL DEFAULT '0'; // изменено 590
ALTER TABLE `bb_posts` CHANGE `post_mc_mod_id` `mc_user_id` MEDIUMINT(8) NOT NULL DEFAULT '0'; // изменено 590
ALTER TABLE `bb_posts` DROP `post_mc_mod_name`; // изменено 590
INSERT INTO `bb_attachments_config` VALUES ('allow_ftp_upload', '0'); // удалено 592
INSERT INTO `bb_attachments_config` VALUES ('ftp_server', ''); // удалено 592
INSERT INTO `bb_attachments_config` VALUES ('ftp_path', ''); // удалено 592
INSERT INTO `bb_attachments_config` VALUES ('download_path', ''); // удалено 592
INSERT INTO `bb_attachments_config` VALUES ('ftp_user', ''); // удалено 592
INSERT INTO `bb_attachments_config` VALUES ('ftp_pass', ''); // удалено 592
INSERT INTO `bb_attachments_config` VALUES ('ftp_pasv_mode', '1'); // удалено 592
INSERT INTO `bb_extensions` VALUES (27, 6, 'wma', ''); // удалено 592
INSERT INTO `bb_extensions` VALUES (28, 7, 'swf', ''); // удалено 592
INSERT INTO `bb_extensions` VALUES (29, 6, 'torrent', ''); // изменено 592
INSERT INTO `bb_extension_groups` VALUES (6, 'Streams', 2, 0, 1, '', 262144, ''); // удалено 592
INSERT INTO `bb_extension_groups` VALUES (7, 'Flash Files', 3, 0, 1, '', 262144, ''); // удалено 592
INSERT INTO `bb_extension_groups` VALUES (6, 'Torrent', 0, 1, 1, '', 122880, ''); // изменено 592
INSERT INTO `bb_config` VALUES ('sitemap_time', ''); // добавлено 593
INSERT INTO `bb_config` VALUES ('static_sitemap', ''); // добавлено 593
INSERT INTO `bb_cron` VALUES (22, 1, 'Sitemap update', 'sitemap.php', 'daily', NULL, '06:00:00', 30, '', '', NULL, 0, '', 0, 0, 0); // добавлено 593
INSERT INTO `bb_cron` VALUES (23, 1, 'Update forums atom', 'update_forums_atom.php', 'interval', NULL, NULL, 255, '', '', '00:15:00', 0, '', 0, 0, 0); // добавлено 595
INSERT INTO `bb_attachments_config` VALUES ('upload_dir', 'old_files'); // изменено 595
INSERT INTO `bb_smilies` VALUES (56, ':cd:', 'cd.gif', 'cd'); // удалено 596

 */