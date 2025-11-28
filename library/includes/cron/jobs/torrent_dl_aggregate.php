<?php
/**
 * TorrentPier – Bull-powered BitTorrent tracker engine
 *
 * @copyright Copyright (c) 2005-2025 TorrentPier (https://torrentpier.com)
 * @link      https://github.com/torrentpier/torrentpier for the canonical source repository
 * @license   https://github.com/torrentpier/torrentpier/blob/master/LICENSE MIT License
 */

if (!defined('BB_ROOT')) {
    die(basename(__FILE__));
}

// Aggregate download counts from bb_torrent_dl to bb_topics
// Then clear daily tracking tables

// Get aggregated counts per topic
$downloads = DB()->table(BB_TORRENT_DL)
    ->select('topic_id, COUNT(*) AS cnt')
    ->group('topic_id')
    ->fetchAll();

foreach ($downloads as $row) {
    DB()->table(BB_TOPICS)
        ->where('topic_id', $row->topic_id)
        ->update(['download_count+=' => $row->cnt]);
}

// Clear tracking tables for the new day
DB()->query("TRUNCATE TABLE " . BB_TORRENT_DL);
DB()->query("TRUNCATE TABLE " . BB_USER_DL_DAY);
