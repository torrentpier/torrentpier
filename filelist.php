<?php
/**
 * TorrentPier – Bull-powered BitTorrent tracker engine
 *
 * @copyright Copyright (c) 2005-2024 TorrentPier (https://torrentpier.com)
 * @link      https://github.com/torrentpier/torrentpier for the canonical source repository
 * @license   https://github.com/torrentpier/torrentpier/blob/master/LICENSE MIT License
 */

require __DIR__ . '/common.php';

// Start session management
$user->session_start();

if ($bb_cfg['bt_disable_dht'] && IS_GUEST) {
    http_response_code(403);
    die($lang['BT_PRIVATE_TRACKER']);
}

$topic_id = !empty($_GET['topic']) ? (int)$_GET['topic'] : (http_response_code(404) && die($lang['INVALID_TOPIC_ID']));

$sql = 'SELECT t.attach_id, t.info_hash, t.info_hash_v2, t.size, ad.physical_filename
        FROM ' . BB_BT_TORRENTS . ' t
        LEFT JOIN ' . BB_ATTACHMENTS_DESC . ' ad
        ON t.attach_id = ad.attach_id
        WHERE t.topic_id = ' . $topic_id . '
        LIMIT 1';

$row = DB()->fetch_row($sql);

if (empty($row) || empty($row['physical_filename'])) {
    http_response_code(404);
    die($lang['TOPIC_POST_NOT_EXIST']);
}

if (empty($row['info_hash_v2'])) {
    http_response_code(410);
    die($lang['BT_V2_FLIST_ONLY']);
}

$file_path = get_attachments_dir() . '/' . $row['physical_filename'];

if (!is_file($file_path)) {
    http_response_code(410);
    die($lang['TOR_NOT_FOUND']);
}

$file_contents = file_get_contents($file_path);

if ($bb_cfg['flist_max_files']) {
    $filetree_pos = strpos($file_contents, '9:file tree');
    $files_pos = !empty($row['info_hash']) ? strpos($file_contents, '5:files', $filetree_pos) : false;
    $file_count = substr_count($file_contents, '6:length', $filetree_pos, ($files_pos ? ($files_pos - $filetree_pos) : null));

    if ($file_count > $bb_cfg['flist_max_files']) {
        http_response_code(410);
        die(sprintf($lang['BT_V2_FLIST_LIMIT'], $bb_cfg['flist_max_files'], $file_count));
    }
}

try {
    $torrent = \Arokettu\Bencode\Bencode::decode($file_contents, dictType: \Arokettu\Bencode\Bencode\Collection::ARRAY);
} catch (\Exception $e) {
    http_response_code(410);
    die(htmlCHR("{$lang['TORFILE_INVALID']}: {$e->getMessage()}"));
}

if (isset($torrent['info']['private']) && IS_GUEST) {
    http_response_code(403);
    die($lang['BT_PRIVATE_TORRENT']);
}

$files = (new TorrentPier\Legacy\TorrentFileList($torrent))->fileTreeTable($torrent['info']['file tree']);

date_default_timezone_set('UTC');

$data = [
    'name' => isset($torrent['info']['name']) ? htmlCHR(substr($torrent['info']['name'], 0, 255)) : 'undefined',
    'client' => isset($torrent['created by']) ? htmlCHR(substr($torrent['created by'], 0, 20)) : 'unknown client',
    'date' => (isset($torrent['creation date']) && is_numeric($torrent['creation date'])) ? date('d-M-Y H:i (e)', $torrent['creation date']) : 'unknown',
    'size' => humn_size($row['size']),
    'site_url' => FULL_URL,
    'topic_url' => TOPIC_URL . $topic_id,
];

header('Cache-Control: public, max-age=3600');

echo <<<EOF
<!DOCTYPE html>
<html lang="en">
<head>
<meta http-equiv="Content-Type" content="text/html;charset=utf-8" />
<meta name="viewport" content="width=device-width, initial-scale=0.1, maximum-scale=1.0" />
<meta name="robots" content="index" />
<meta name="description" content="File listing for topic - $topic_id | {$data['name']} ({$data['size']})" />
<meta name="generator" content="TorrentPier" />
<meta name="version" content="{$bb_cfg['tp_version']}" />
<link rel="shortcut icon" href="favicon.png" type="image/x-icon" />
<link rel="search" type="application/opensearchdescription+xml" href="{$data['site_url']}opensearch_desc.xml" title="{$bb_cfg['sitename']} (Forum)" />
<link rel="search" type="application/opensearchdescription+xml" href="{$data['site_url']}opensearch_desc_bt.xml" title="{$bb_cfg['sitename']} (Tracker)" />

<title>{$data['name']} ({$data['size']}) | {$bb_cfg['sitename']}</title>
</head>
<body>
<style>
body {
    background-color: #1f1f1f; color: #ffffff;
}

table {
    table-layout: auto;
    border: collapse;
    width: auto;
    margin: 20px auto;
    font-family: -apple-system,BlinkMacSystemFont,"Segoe UI","Noto Sans",Helvetica,Arial,sans-serif,"Apple Color Emoji","Segoe UI Emoji";
    background-color: #2c2c2c;
}

th, td {
    padding: 8px;
    text-align: left;
    color: #acacac;
    width: auto;
}

td {
    border: 2px solid #353535;
}

th {
    background-color: #1f1f1f;
    width: auto;
}

p {
    color: #b3b3b3;
}

a {
    color: #1d9100;
}

sup {
    color: #aa8000;
}

.tooltip {
    position: relative;
}

.tooltip .tooltiptext {
    visibility: hidden;
    position: absolute;
    z-index: 1;
    top: 0;
    opacity: 0;
    transition: opacity 0.7s;
    width: 400px;
    background-color: #111;
    color: #c6c4c4;
    text-align: left;
    border-radius: 5px;
    padding: 5px;
}

.tooltip:hover .tooltiptext {
    visibility: visible;
    opacity: 0.97;
}
</style>
<a href = "{$data['site_url']}{$data['topic_url']}" style = "font-family: Monospace; color: #569904;">🠔 Back to the topic</a>
<center>
    <h2 style="color: #b3b3b3; font-family: Monospace;">Name: {$data['name']} | Date: {$data['date']} | Size: {$data['size']}</h2>
<p>
    <i>Created by: {$data['client']}</i>
</p>
<hr>
<table>
    <tr>
        <th>Path ({$files['count']} files)</th>
        <th>Size</th>
        <th class="tooltip" style="width: auto;">
            BTMR hash
            <sup>?
                <span class="tooltiptext">
                    BitTorrent Merkle Root is a hash of a file embedded in torrents with BitTorrent v2 support, tracker users can extract, calculate them, and download deduplicated torrents using desktop tools such as
                    <a href="https://github.com/kovalensky/tmrr" target="_blank">Torrent Merkle Root Reader.</a>
                </span>
            </sup>
        </th>
    </tr>
EOF;

echo $files['list'];

echo '
</table>
<p style = "color: gray; font-family: Calibri Light">Generated by <a href = "https://github.com/torrentpier/torrentpier" target="_blank" title = "Bull-powered BitTorrent tracker engine">TorrentPier</a></p>
</center>
</body>
</html>';

die();
