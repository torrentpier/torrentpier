<?php

/**
 * TorrentPier – Bull-powered BitTorrent tracker engine
 *
 * @copyright Copyright (c) 2005-2025 TorrentPier (https://torrentpier.com)
 * @link      https://github.com/torrentpier/torrentpier for the canonical source repository
 * @license   https://github.com/torrentpier/torrentpier/blob/master/LICENSE MIT License
 */

use TorrentPier\Torrent\Registry;

if (!defined('BB_ROOT')) {
    die(basename(__FILE__));
}

if (empty(config()->get('seeder_last_seen_days_keep')) || empty(config()->get('seeder_never_seen_days_keep'))) {
    return;
}

$last_seen_time = TIMENOW - 86400 * config()->get('seeder_last_seen_days_keep');
$never_seen_time = TIMENOW - 86400 * config()->get('seeder_never_seen_days_keep');
$limit_sql = 3000;

$dead_topics = DB()->table(BB_BT_TORRENTS)
    ->where('reg_time < ?', $never_seen_time)
    ->where('seeder_last_seen < ?', $last_seen_time)
    ->limit($limit_sql)
    ->fetchPairs('topic_id', 'topic_id');

if ($dead_topics) {
    // Delete torstat for dead torrents
    DB()->table(BB_BT_TORSTAT)
        ->where('topic_id', array_keys($dead_topics))
        ->delete();

    // Unregister each torrent properly
    foreach ($dead_topics as $topic_id) {
        try {
            Registry::unregister($topic_id);
        } catch (Throwable) {
            // Continue with remaining topics
        }
    }
}
