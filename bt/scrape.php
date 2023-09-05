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

$info_hash = isset($_GET['info_hash']) ? (string)$_GET['info_hash'] : null;
$info_hash_hex = bin2hex($info_hash);

$lp_scrape_info = CACHE('tr_cache')->get(SCRAPE_LIST_PREFIX . $info_hash_hex);

if ($lp_scrape_info) {

die(\SandFox\Bencode\Bencode::encode($lp_scrape_info));

}

$is_bt_v2 = null;
// Verify info_hash
if (!isset($info_hash)) {
    msg_die('info_hash does not exist');
}

// Check info_hash version
if (strlen($info_hash) == 32) {
    $is_bt_v2 = true;
} elseif (strlen($info_hash) == 20) {
    $is_bt_v2 = false;
} else {
    msg_die('Invalid info_hash');
}

function msg_die($msg)
{
    $output = \SandFox\Bencode\Bencode::encode([
        'min interval' => (int)1800,
        'failure reason' => (string)$msg
    ]);

    die($output);
}

require __DIR__ . '/includes/init_tr.php';

$info_hash_sql = rtrim(DB()->escape($info_hash), ' ');
/**
 * Поскольку торрент-клиенты в настоящее время обрезают инфо-хэш до 20 символов (независимо от его типа, как известно v1 = 20 символов, а v2 = 32 символа),
 * то результатов $is_bt_v2 (исходя из длины строки определяем тип инфо-хэша) проверки нам будет мало, именно поэтому происходит поиск v2 хэша, если торрент является v1 (по длине) и если в tor.info_hash столбце нету v1 хэша.
 */
$info_hash_where = $is_bt_v2 ? "WHERE tor.info_hash_v2 = '$info_hash_sql'" : "WHERE tor.info_hash = '$info_hash_sql' OR tor.info_hash_v2 LIKE '$info_hash_sql%'";

$row = DB()->fetch_row("
		SELECT tor.complete_count, snap.seeders, snap.leechers
		FROM " . BB_BT_TORRENTS . " tor
		LEFT JOIN " . BB_BT_TRACKER_SNAP . " snap ON (snap.topic_id = tor.topic_id)
		$info_hash_where
		LIMIT 1
");

if (!$row) {
    msg_die('Torrent not registered, info_hash = ' . bin2hex($info_hash_sql));
}

$output['files'][$info_hash] = [
    'complete' => (int)$row['seeders'],
    'downloaded' => (int)$row['complete_count'],
    'incomplete' => (int)$row['leechers'],
];

$peers_list_cached = CACHE('tr_cache')->set(SCRAPE_LIST_PREFIX . bin2hex($info_hash_hex), $output, SCRAPE_LIST_EXPIRE);

echo \SandFox\Bencode\Bencode::encode($output);

exit;
