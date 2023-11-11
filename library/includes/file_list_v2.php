<?php
/**
 * TorrentPier – Bull-powered BitTorrent tracker engine
 *
 * @copyright Copyright (c) 2005-2023 TorrentPier (https://torrentpier.com)
 * @link      https://github.com/torrentpier/torrentpier for the canonical source repository
 * @license   https://github.com/torrentpier/torrentpier/blob/master/LICENSE MIT License
 */

if (!defined('BB_ROOT')) {
    die(basename(__FILE__));
}

$user->session_start();

if ($bb_cfg['bt_disable_dht'] && IS_GUEST) {
    http_response_code(403);
    die($lang['BT_PRIVATE_TRACKER']);
}

$topic_id = !empty($_GET['t']) ? (int)$_GET['t'] : (http_response_code(404) && die($lang['INVALID_TOPIC_ID']));

$sql = 'SELECT t.attach_id, t.info_hash_v2, ad.physical_filename
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
    die($lang['BT_V2_FILE_LIST_ONLY']);
}

$file_path = get_attachments_dir() . '/' . $row['physical_filename'];

if (!is_file($file_path)) {
    http_response_code(410);
    die($lang['TOR_NOT_FOUND']);
}

$file_contents = file_get_contents($file_path);

if (!$tor = \Arokettu\Bencode\Bencode::decode($file_contents, dictType: \Arokettu\Bencode\Bencode\Collection::ARRAY)) {
    http_response_code(410);
    die($lang['TORFILE_INVALID']);
}

if (isset($tor['info']['private']) && IS_GUEST) {
    http_response_code(403);
    die($lang['BT_PRIVATE_TORRENT']);
}

$list_handler = new TorrentPier\Legacy\TorrentFileList($tor);

$files = $list_handler->fileTreeTable($tor['info']['file tree']);

$data = [
    'name' => htmlCHR($tor['info']['name'] ?? ''),
    'client' => htmlCHR(substr($tor['created by'] ?? 'unknown client', 0, 20)),
    'size' => humn_size($files['size']),
    'hash' => bin2hex($row['info_hash_v2']),
    'date' => (isset($tor['creation date']) && is_numeric($tor['creation date'])) ? delta_time($tor['creation date']) : 'unknown'
];

echo <<<EOF
<!DOCTYPE html>
<html>
<head>
<meta http-equiv="Content-Type" content="text/html;charset=utf-8"/>
<meta name="viewport" content="width=device-width, initial-scale=1" />
<meta name="robots" content="index, follow, noarchive">
<meta name="description" content="File listing for topic - $topic_id | {$data['name']} ({$data['size']})">

<title>{$data['name']} ({$data['size']}) | {$data['hash']} | {$bb_cfg['sitename']}</title>
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
<center>
    <h2 style="color: #b3b3b3; font-family: Monospace;">Name: {$data['name']} | Age: ({$data['date']}) | Size: {$data['size']}</h2>
<p>
    <i>Created by: {$data['client']}</i>
</p>
<hr>
<table>
    <tr>
        <th>Path</th>
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
<p>
    <i style = "color: gray">Generated by</i> <a href = "https://github.com/torrentpier/torrentpier" target="_blank">TorrentPier</a>
</p>
</center>
</body>
</html>';

die();
