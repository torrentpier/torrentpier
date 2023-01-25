<?php
/**
 * TorrentPier – Bull-powered BitTorrent tracker engine
 *
 * @copyright Copyright (c) 2005-2023 TorrentPier (https://torrentpier.com)
 * @link      https://github.com/torrentpier/torrentpier for the canonical source repository
 * @license   https://github.com/torrentpier/torrentpier/blob/master/LICENSE MIT License
 */

define('IN_TRACKER', true);
define('BB_ROOT', './../');
require dirname(__DIR__) . '/common.php';

global $bb_cfg;

if (!$bb_cfg['tracker']['scrape']) {
    msg_die('Please disable SCRAPE!');
}

// Recover info_hash
if (isset($_GET['?info_hash']) && !isset($_GET['info_hash'])) {
    $_GET['info_hash'] = $_GET['?info_hash'];
}

if (!isset($_GET['info_hash']) || strlen($_GET['info_hash']) != 20) {
    msg_die('Invalid info_hash');
}

$info_hash = $_GET['info_hash'];

function msg_die($msg)
{
    $output = \Rych\Bencode\Bencode::encode([
        'min interval' => (int)1800,
        'failure reason' => (string)$msg,
        'warning message' => (string)$msg,
    ]);

    die($output);
}

require __DIR__ . '/includes/init_tr.php';

$info_hash_sql = rtrim(DB()->escape($info_hash), ' ');

$row = DB()->fetch_row("
		SELECT tor.complete_count, snap.seeders, snap.leechers
		FROM " . BB_BT_TORRENTS . " tor
		LEFT JOIN " . BB_BT_TRACKER_SNAP . " snap ON (snap.topic_id = tor.topic_id)
		WHERE tor.info_hash = '$info_hash_sql'
		LIMIT 1
");

$output['files'][$info_hash] = array(
    'complete' => (int)$row['seeders'],
    'downloaded' => (int)$row['complete_count'],
    'incomplete' => (int)$row['leechers'],
);

echo \Rych\Bencode\Bencode::encode($output);

exit;
