<?php
/**
 * TorrentPier – Bull-powered BitTorrent tracker engine
 *
 * @copyright Copyright (c) 2005-2025 TorrentPier (https://torrentpier.com)
 * @link      https://github.com/torrentpier/torrentpier for the canonical source repository
 * @license   https://github.com/torrentpier/torrentpier/blob/master/LICENSE MIT License
 */

define('IN_TRACKER', true);
define('BB_ROOT', './../');
require dirname(__DIR__) . '/common.php';

if (!tp_config()->get('tracker.scrape')) {
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
$info_hash_hex = bin2hex($info_hash);

// Check info_hash length
if (strlen($info_hash) !== 20) {
    msg_die('Invalid info_hash: ' . (mb_check_encoding($info_hash, 'UTF8') ? $info_hash : $info_hash_hex));
}

// Handle multiple hashes
preg_match_all('/info_hash=([^&]*)/i', $_SERVER['QUERY_STRING'], $info_hash_array);

$torrents = [];
$info_hashes = [];

foreach ($info_hash_array[1] as $hash) {
    $decoded_hash = urldecode($hash);

    if (strlen($decoded_hash) !== 20) {
        continue;
    }

    if ($scrape_cache = CACHE('tr_cache')->get(SCRAPE_LIST_PREFIX . bin2hex($decoded_hash))) {
        $torrents['files'][$info_key = array_key_first($scrape_cache)] = $scrape_cache[$info_key];
    } else {
        $info_hashes[] = DB()->escape(($decoded_hash));
    }
}

$info_hash_count = count($info_hashes);

if (!empty($info_hash_count)) {
    if ($info_hash_count > tp_config()->get('max_scrapes')) {
        $info_hashes = array_slice($info_hashes, 0, tp_config()->get('max_scrapes'));
    }

    $info_hashes_sql = implode('\', \'', $info_hashes);

    /**
     * Currently torrent clients send truncated v2 hashes (the design raises questions).
     * @see https://github.com/bittorrent/bittorrent.org/issues/145#issuecomment-1720040343
     */
    $info_hash_where = "tor.info_hash IN ('$info_hashes_sql') OR SUBSTRING(tor.info_hash_v2, 1, 20) IN ('$info_hashes_sql')";

    $sql = "
        SELECT tor.info_hash, tor.info_hash_v2, tor.complete_count, snap.seeders, snap.leechers
        FROM " . BB_BT_TORRENTS . " tor
        LEFT JOIN " . BB_BT_TRACKER_SNAP . " snap ON (snap.topic_id = tor.topic_id)
        WHERE $info_hash_where
    ";

    $scrapes = DB()->fetch_rowset($sql);

    if (!empty($scrapes)) {
        foreach ($scrapes as $scrape) {
            $hash_v1 = !empty($scrape['info_hash']) ? $scrape['info_hash'] : '';
            $hash_v2 = !empty($scrape['info_hash_v2']) ? substr($scrape['info_hash_v2'], 0, 20) : '';
            $info_hash_scrape = (in_array(urlencode($hash_v1), $info_hash_array[1])) ? $hash_v1 : $hash_v2; // Replace logic to prioritize $hash_v2, in case of future prioritization of v2

            $torrents['files'][$info_hash_scrape] = [
                'complete' => (int)$scrape['seeders'],
                'downloaded' => (int)$scrape['complete_count'],
                'incomplete' => (int)$scrape['leechers']
            ];
            CACHE('tr_cache')->set(SCRAPE_LIST_PREFIX . bin2hex($info_hash_scrape), array_slice($torrents['files'], -1, null, true), SCRAPE_LIST_EXPIRE);
        }
    }
}

// Verify if torrent registered on tracker
if (empty($torrents)) {
    msg_die('Torrent not registered, info_hash = ' . (mb_check_encoding($info_hash, 'UTF8') ? $info_hash : $info_hash_hex));
}

die(\Arokettu\Bencode\Bencode::encode($torrents));
