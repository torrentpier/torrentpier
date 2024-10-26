INSERT INTO `bb_extensions` VALUES ('993', '1', 'avif', '');
INSERT INTO `bb_extensions` VALUES ('992', '3', 'm3u', '');
ALTER TABLE `bb_topics` ADD COLUMN `topic_allow_robots` TINYINT(1) UNSIGNED NOT NULL DEFAULT '0';
