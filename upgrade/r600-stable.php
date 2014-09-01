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

bb_die('
	<h1 style="color: red">Для обновления до стабильной ревизии R600, вам необходимо воспользоваться <a
	href="http://torrentpier.me/threads/26147/">инструкцией, опубликованной в данной теме</a> на нашем форуме.
	Вы также можете заглянуть в исходный код этого скрипта, в нем опубликована схема изменений от ревизии 400,
	до ревизии 600. Не забывайте про бекап базы данных перед обновлением!</h1>
');

/*

Схема изенений r400->r600 для написания конвертера.

Изменения в базе:

INSERT INTO `bb_config` VALUES ('tor_comment', '1');                                  // добавлено 407
ALTER TABLE `bb_posts` ADD `post_mod_comment` TEXT NOT NULL DEFAULT '';               // добавлено 458
ALTER TABLE `bb_posts` ADD `post_mod_comment_type` TINYINT( 1 ) NOT NULL DEFAULT '0'; // добавлено 458
ALTER TABLE `bb_posts` ADD `post_mc_mod_id` mediumint(8) NOT NULL;                    // добавлено 458
ALTER TABLE `bb_posts` ADD `post_mc_mod_name` varchar(25) NOT NULL DEFAULT '';        // добавлено 458

// 496 - отдельный конвертер

ALTER TABLE `bb_users` ADD `tpl_name` varchar(255) NOT NULL DEFAULT 'default';        // добавлено 507
UPDATE `bb_config` SET `config_value` = '1' WHERE `config_name` = 'bt_unset_dltype_on_tor_unreg';
                                                                                      // изменено 508 ↑
ALTER TABLE `bb_users` DROP `ignore_srv_load`;                                        // удалено 537
UPDATE `bb_users` SET `username` = 'Guest' WHERE `user_id` = -1;                      // изменено 540
DROP TABLE IF EXISTS `bb_bt_torrents_del`;                                            // удалено 551
DROP TABLE IF EXISTS `xbt_announce_log`;                                              // удалено 551
DROP TABLE IF EXISTS `xbt_config`;                                                    // удалено 551
DROP TABLE IF EXISTS `xbt_deny_from_hosts`;                                           // удалено 551
DROP TABLE IF EXISTS `xbt_files_users`;                                               // удалено 551
DROP TABLE IF EXISTS `xbt_scrape_log`;                                                // удалено 551
ALTER TABLE `bb_bt_tracker` DROP `xbt_error`;                                         // удалено 551
DELETE FROM `bb_config` WHERE `config_name` = 'torrent_pass_private_key';             // удалено 551
DELETE FROM `bb_config` WHERE `config_name` = 'board_email';                          // удалено 552
DELETE FROM `bb_config` WHERE `config_name` = 'board_email_form';                     // удалено 552
DELETE FROM `bb_config` WHERE `config_name` = 'board_email_sig';                      // удалено 552
DELETE FROM `bb_config` WHERE `config_name` = 'smtp_delivery';                        // удалено 552
DELETE FROM `bb_config` WHERE `config_name` = 'smtp_host';                            // удалено 552
DELETE FROM `bb_config` WHERE `config_name` = 'smtp_password';                        // удалено 552
DELETE FROM `bb_config` WHERE `config_name` = 'smtp_username';                        // удалено 552
DELETE FROM `bb_config` WHERE `config_name` = 'gallery_enabled';                      // удалено 554
DELETE FROM `bb_config` WHERE `config_name` = 'pic_dir';                              // удалено 554
DELETE FROM `bb_config` WHERE `config_name` = 'pic_max_size';                         // удалено 554
DELETE FROM `bb_config` WHERE `config_name` = 'auto_delete_posted_pics';              // удалено 554
DELETE FROM `bb_config` WHERE `config_name` = 'allow_avatar_remote';                  // удалено 555
ALTER TABLE `bb_topics` DROP COLUMN `is_draft`;                                       // удалено 558
DELETE FROM `bb_config` WHERE `config_name` = 'bt_add_comment';                       // удалено 565
DELETE FROM `bb_config` WHERE `config_name` = 'bt_add_publisher';                     // удалено 565
DELETE FROM `bb_config` WHERE `config_name` = 'bt_gen_passkey_on_reg';                // удалено 565
DROP TABLE IF EXISTS `sph_counter`;                                                   // удалено 571

// 571 - отдельный конвертер

DELETE FROM `bb_config` WHERE `config_name` = 'max_inbox_privmsgs';                   // удалено 573
DELETE FROM `bb_config` WHERE `config_name` = 'max_savebox_privmsgs';                 // удалено 573
DELETE FROM `bb_config` WHERE `config_name` = 'max_sentbox_privmsgs';                 // удалено 573
DELETE FROM `bb_config` WHERE `config_name` = 'privmsg_disable';                      // удалено 573

// 575 - отдельный конвертер

DELETE FROM `bb_config` WHERE `config_name` = 'config_id';                            // удалено 579
DELETE FROM `bb_config` WHERE `config_name` = 'sendmail_fix';                         // удалено 579
DELETE FROM `bb_config` WHERE `config_name` = 'version';                              // удалено 579
DELETE FROM `bb_config` WHERE `config_name` = 'xs_add_comments';                      // удалено 579
DELETE FROM `bb_config` WHERE `config_name` = 'xs_auto_compile';                      // удалено 579
DELETE FROM `bb_config` WHERE `config_name` = 'xs_auto_recompile';                    // удалено 579
DELETE FROM `bb_config` WHERE `config_name` = 'xs_php';                               // удалено 579
DELETE FROM `bb_config` WHERE `config_name` = 'xs_shownav';                           // удалено 579
DELETE FROM `bb_config` WHERE `config_name` = 'xs_template_time';                     // удалено 579
DELETE FROM `bb_config` WHERE `config_name` = 'xs_version';                           // удалено 579
INSERT INTO `bb_cron` VALUES (22, 1, 'Attach maintenance', 'attach_maintenance.php', 'daily', NULL, '05:00:00', 40, '', '', NULL, 1, '', 0, 1, 0);
                                                                                      // добавлено 582 ↑
DELETE FROM `bb_config` WHERE `config_name` = 'allow_avatar_local';                   // удалено 583
DELETE FROM `bb_config` WHERE `config_name` = 'allow_avatar_upload';                  // удалено 583
DELETE FROM `bb_config` WHERE `config_name` = 'avatar_filesize';                      // удалено 583
DELETE FROM `bb_config` WHERE `config_name` = 'avatar_gallery_path';                  // удалено 583
DELETE FROM `bb_config` WHERE `config_name` = 'avatar_max_height';                    // удалено 583
DELETE FROM `bb_config` WHERE `config_name` = 'avatar_max_width';                     // удалено 583
DELETE FROM `bb_config` WHERE `config_name` = 'avatar_path';                          // удалено 583
DELETE FROM `bb_config` WHERE `config_name` = 'require_activation';                   // удалено 583
DELETE FROM `bb_config` WHERE `config_name` = 'no_avatar';                            // удалено 583
UPDATE `bb_config` SET `config_value` = '0' WHERE `config_name` = 'show_mod_index';   // изменено 583
DELETE FROM `bb_cron` WHERE `cron_script` = 'avatars_cleanup.php';                    // удалено 583
UPDATE `bb_cron` SET `cron_id` = '3' WHERE `cron_script` = 'attach_maintenance.php';  // изменено 583

// 583 - отдельный конвертер

DROP TABLE IF EXISTS `bb_topic_templates`;                                            // удалено 584
ALTER TABLE `bb_forums` DROP `topic_tpl_id`;                                          // удалено 584
UPDATE `bb_config` SET `config_value` = 'http://ip-whois.net/ip_geos.php?ip=' WHERE `config_name` = 'whois_info';
                                                                                      // обновлено 586 ↑
UPDATE `bb_config` SET `config_value` = 'ru' WHERE `config_name` = 'default_lang';    // обновлено 588

// 588 - отдельный конвертер

ALTER TABLE `bb_users` DROP `user_next_birthday_greeting`;                            // удалено 589
ALTER TABLE `bb_posts` CHANGE `post_mod_comment` `mc_comment` TEXT CHARACTER SET utf8 COLLATE utf8_general_ci NOT NULL DEFAULT '';
                                                                                      // изменено 590 ↑
ALTER TABLE `bb_posts` CHANGE `post_mod_comment_type` `mc_type` TINYINT(1) NOT NULL DEFAULT '0';
                                                                                      // изменено 590 ↑
ALTER TABLE `bb_posts` CHANGE `post_mc_mod_id` `mc_user_id` MEDIUMINT(8) NOT NULL DEFAULT '0';
                                                                                      // изменено 590 ↑
ALTER TABLE `bb_posts` DROP `post_mc_mod_name`;                                       // удалено 590
DELETE FROM `bb_attachments_config` WHERE `config_name` = 'allow_ftp_upload';         // удалено 592
DELETE FROM `bb_attachments_config` WHERE `config_name` = 'ftp_server';               // удалено 592
DELETE FROM `bb_attachments_config` WHERE `config_name` = 'ftp_path';                 // удалено 592
DELETE FROM `bb_attachments_config` WHERE `config_name` = 'download_path';            // удалено 592
DELETE FROM `bb_attachments_config` WHERE `config_name` = 'ftp_user';                 // удалено 592
DELETE FROM `bb_attachments_config` WHERE `config_name` = 'ftp_pass';                 // удалено 592
DELETE FROM `bb_attachments_config` WHERE `config_name` = 'ftp_pasv_mode';            // удалено 592
DELETE FROM `bb_extensions` WHERE `extension` = 'wma';                                // удалено 592
DELETE FROM `bb_extensions` WHERE `extension` = 'swf';                                // удалено 592
UPDATE `bb_extensions` SET `group_id` = '6' WHERE `extension` = 'torrent';            // изменено 592
DELETE FROM `bb_extension_groups` WHERE `group_name` = 'Streams';                     // удалено 592
DELETE FROM `bb_extension_groups` WHERE `group_name` = 'Flash Files';                 // удалено 592
UPDATE `bb_extension_groups` SET `group_id` = '6' WHERE `group_id` = 8;               // изменено 592
INSERT INTO `bb_config` VALUES ('sitemap_time', '');                                  // добавлено 593
INSERT INTO `bb_config` VALUES ('static_sitemap', '');                                // добавлено 593
INSERT INTO `bb_cron` VALUES (22, 1, 'Sitemap update', 'sitemap.php', 'daily', NULL, '06:00:00', 30, '', '', NULL, 0, '', 0, 0, 0);
                                                                                      // добавлено 593 ↑
INSERT INTO `bb_cron` VALUES (23, 1, 'Update forums atom', 'update_forums_atom.php', 'interval', NULL, NULL, 255, '', '', '00:15:00', 0, '', 0, 0, 0);
                                                                                      // добавлено 595 ↑
UPDATE `bb_attachments_config` SET `config_value` = 'old_files' WHERE `config_name` = 'upload_dir';
                                                                                      // изменено 595 ↑
DELETE FROM `bb_smilies` WHERE `code` = ':cd:';                                       // удалено 596
ALTER TABLE `bb_groups` CHANGE `group_description` `group_description` text NOT NULL DEFAULT '';
                                                                                      // изменено 598 ↑
ALTER TABLE `bb_groups` ADD `avatar_ext_id` int(15) NOT NULL DEFAULT '0' AFTER `group_id`;
                                                                                      // добавлено 598 ↑
ALTER TABLE `bb_groups` ADD `mod_time` INT(11) NOT NULL DEFAULT '0' AFTER `group_time`;
                                                                                      // добавлено 598 ↑
ALTER TABLE `bb_groups` ADD `release_group` tinyint(4) NOT NULL DEFAULT '0' AFTER `group_type`;
                                                                                      // добавлено 598 ↑
ALTER TABLE `bb_groups` ADD `group_signature` text NOT NULL DEFAULT '' AFTER `group_description`;
                                                                                      // добавлено 598 ↑
ALTER TABLE `bb_posts` ADD `poster_rg_id` mediumint(8) NOT NULL DEFAULT '0' AFTER `poster_ip`;
                                                                                      // добавлено 598 ↑
ALTER TABLE `bb_posts` ADD `attach_rg_sig` tinyint(4) NOT NULL DEFAULT '0' AFTER `poster_rg_id`;
                                                                                      // добавлено 598 ↑
INSERT INTO `bb_config` VALUES ('terms', '');                                         // добавлено 599b

Удаленные файлы/папки:

admin/.htaccess
admin/admin_topic_templates.php
admin/admin_xs.php
admin/xs_cache.php
admin/xs_config.php
admin/xs_frameset.php
admin/xs_include.php
admin/xs_index.php
develop
images/avatars/bot.gif
images/logo/logo_big.png
images/smiles/cd.gif
images/smiles/smileys.pak
images/icon_disk.gif
images/icon_disk_gray.gif
includes/cron/jobs/avatars_cleanup.php
includes/topic_templates
includes/ucp/torrent_userprofile.php
includes/ucp/usercp_activate.php
includes/ucp/usercp_attachcp.php
includes/ucp/usercp_avatar.php
includes/ucp/usercp_bonus.php
includes/ucp/usercp_email.php
includes/ucp/usercp_register.php
includes/ucp/usercp_sendpasswd.php
includes/ucp/usercp_viewprofile.php
includes/sphinxapi.php
includes/topic_templates.php
language/lang_english
language/lang_russian
misc/html
misc/.htaccess
pictures
templates/admin/admin_topic_templates.tpl
templates/default/images/lang_english
templates/default/images/lang_russian
templates/default/images/index.html
templates/default/topic_templates
templates/default/agreement.tpl
templates/default/donate.tpl
templates/default/faq.tpl
templates/default/gallery.tpl
templates/default/posting_poll.tpl
templates/default/posting_tpl.tpl
templates/default/usercp_avatar_gallery.tpl
templates/default/viewonline.tpl
templates/xs_mod
templates/board_disabled_exit.php
templates/limit_load_exit.php
templates/topic_tpl_overall_header.html
templates/topic_tpl_rules_video.html
donate.php
download.php
faq.php
gallery.php
viewonline.php

Прочие изменения:

Все файлы перекодированы для использования окончаний строк LF.

 */