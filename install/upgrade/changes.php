<?php
/**
 * TorrentPier – Bull-powered BitTorrent tracker engine
 *
 * @copyright Copyright (c) 2005-2024 TorrentPier (https://torrentpier.com)
 * @link      https://github.com/torrentpier/torrentpier for the canonical source repository
 * @license   https://github.com/torrentpier/torrentpier/blob/master/LICENSE MIT License
 */

if (!defined('IN_UPDATED')) {
    die(basename(__FILE__));
}

// Changes schema
return [
    'v2.4.5' => [
        'sql_queries' => [
            "INSERT INTO `bb_extensions` VALUES ('993', '1', 'avif', '');",
            "INSERT INTO `bb_extensions` VALUES ('992', '3', 'm3u', '');",
            "ALTER TABLE `bb_topics` ADD COLUMN `topic_allow_robots` TINYINT(1) UNSIGNED NOT NULL DEFAULT '0';"
        ],
        'removed_files' => [
            'install/upgrade/changes.txt',
        ]
    ]
];
