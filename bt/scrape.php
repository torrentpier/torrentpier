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

// Verify info_hash
if (!isset($info_hash)) {
    msg_die('info_hash was not provided');
}
// Store info hash in hex format
$info_hash_hex = mb_check_encoding($info_hash, 'UTF8') ? $info_hash : bin2hex($info_hash);

// Check info_hash version
if (strlen($info_hash) == 32) {
    $is_bt_v2 = true;
} elseif (strlen($info_hash) == 20) {
    $is_bt_v2 = false;
} else {
    msg_die('Invalid info_hash: ' . $info_hash_hex);
}

// Handle multiple hashes

preg_match_all('/info_hash=([^&]*)/i', $_SERVER["QUERY_STRING"], $info_hash_array);

    $torrents = [];
    $info_hashes = [];

    foreach ($info_hash_array[1] as $hash) {

        $decoded_hash = urldecode($hash);

        if ($scrape_cache = CACHE('tr_cache')->get(SCRAPE_LIST_PREFIX . bin2hex($decoded_hash))) {
            $torrents['files'][$info_key = array_key_first($scrape_cache)] = $scrape_cache[$info_key];
        }
        else{
            $info_hashes[] = '\''. DB()->escape(($decoded_hash)) . '\'';
        }
    }

    $info_hash_count = count($info_hashes);

    if (!empty($info_hash_count)) {

        if ($info_hash_count > $bb_cfg['max_scrapes']) {
          $info_hashes = array_slice($info_hashes, 0, $bb_cfg['max_scrapes']);
        }

        $info_hashes_sql = 'tor.info_hash' . ' IN ( ' . implode(', ', $info_hashes). ' )';
        $sql = "
            SELECT tor.info_hash, tor.complete_count, snap.seeders, snap.leechers
            FROM " . BB_BT_TORRENTS . " tor
            LEFT JOIN " . BB_BT_TRACKER_SNAP . " snap ON (snap.topic_id = tor.topic_id)
            WHERE $info_hashes_sql
            LIMIT $info_hash_count
        ";

        $rowset = DB()->fetch_rowset($sql);

        if (count($rowset) > 0) {
            foreach ($rowset as $scrapes) {
                $torrents['files'][$scrapes['info_hash']] = [
                    'complete' => (int)$scrapes['seeders'],
                    'downloaded' => (int)$scrapes['complete_count'],
                    'incomplete' => (int)$scrapes['leechers']
                ];
                CACHE('tr_cache')->set(SCRAPE_LIST_PREFIX . bin2hex($scrapes['info_hash']), array_slice($torrents['files'], -1, null, true), SCRAPE_LIST_EXPIRE);
            }
        }
    }

    if (empty($torrents)) {
        msg_die('Torrent not registered, info_hash = ' . $info_hash_hex);
    }

    die(\Arokettu\Bencode\Bencode::encode($torrents));
