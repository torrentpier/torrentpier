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
    bb_simple_die($lang['BT_PRIVATE_TRACKER'], 403);
}

$topic_id = isset($_GET[POST_TOPIC_URL]) ? (int)$_GET[POST_TOPIC_URL] : 0;

if (!$topic_id) {
    bb_simple_die($lang['INVALID_TOPIC_ID'], 404);
}

$sql = 'SELECT t.attach_id, t.info_hash, t.info_hash_v2, t.size, ad.physical_filename
        FROM ' . BB_BT_TORRENTS . ' t
        LEFT JOIN ' . BB_ATTACHMENTS_DESC . ' ad
        ON t.attach_id = ad.attach_id
        WHERE t.topic_id = ' . $topic_id . '
        LIMIT 1';
$row = DB()->fetch_row($sql);

if (empty($row['physical_filename'])) {
    bb_simple_die($lang['INVALID_TOPIC_ID_DB'], 404);
}

// Protocol meta
$meta_v1 = !empty($row['info_hash']);
$meta_v2 = !empty($row['info_hash_v2']);

// Method fields
$t_version_field = $meta_v2 ? 'v2' : 'v1';
$t_files_field = $meta_v2 ? 'getFileTree' : 'getFiles';
$t_hash_field = $meta_v2 ? 'piecesRoot' : 'sha1';

$file_path = get_attachments_dir() . '/' . $row['physical_filename'];

if (!is_file($file_path)) {
    bb_simple_die($lang['TOR_NOT_FOUND'], 410);
}

$file_contents = file_get_contents($file_path);

if ($bb_cfg['flist_max_files']) {
    $filetree_pos = $meta_v2 ? strpos($file_contents, '9:file tree') : false;
    $files_pos = $meta_v1 ? strpos($file_contents, '5:files', $filetree_pos) : false;

    if ($filetree_pos) {
        $file_count = substr_count($file_contents, '6:length', $filetree_pos, ($files_pos ? ($files_pos - $filetree_pos) : null));
    } else {
        $file_count = substr_count($file_contents, '6:length', $files_pos);
    }

    if ($file_count > $bb_cfg['flist_max_files']) {
        bb_simple_die(sprintf($lang['BT_FLIST_LIMIT'], $bb_cfg['flist_max_files'], $file_count), 410);
    }
}

try {
    $torrent = \Arokettu\Torrent\TorrentFile::loadFromString($file_contents);
} catch (\Exception $e) {
    bb_simple_die(htmlCHR("{$lang['TORFILE_INVALID']}: {$e->getMessage()}"), 410);
}

if (IS_GUEST && $torrent->isPrivate()) {
    bb_simple_die($lang['BT_PRIVATE_TORRENT'], 403);
}

$files = $torrent->$t_version_field()->$t_files_field();

if ($meta_v1 && $meta_v2) {
    $files = new \RecursiveIteratorIterator($files); // Flatten the list
}

$allFiles = '';
foreach ($files as $file) {
    $allFiles .= '<tr><td>' . clean_tor_dirname(implode('/', $file->path)) . '</td><td>' . humn_size($file->length, 2) . '</td><td>' . $file->$t_hash_field . '</td></tr>';
}

$data = [
    'name' => !empty($t_name = $torrent->getName()) ? htmlCHR(substr($t_name, 0, 255)) : 'undefined',
    'client' => !empty($creator = $torrent->getCreatedBy()) ? htmlCHR(substr($creator, 0, 20)) : 'unknown client',
    'date' => (!empty($dt = $torrent->getCreationDate()) && is_numeric($creation_date = $dt->getTimestamp())) ? date('d-M-Y H:i (e)', $creation_date) : $lang['UNKNOWN'],
    'size' => humn_size($row['size'], 2),
    'file_count' => iterator_count($files),
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
<meta property="og:description" content="File listing for topic - $topic_id | {$data['name']} ({$data['size']})" />
<meta property="og:site_name" content="{$bb_cfg['sitename']}" />
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

hr {
    border: 0;
    height: 0;
    border-bottom: 1px solid #acacac;
}

table {
    table-layout: auto;
    border: none;
    width: auto;
    margin: 20px auto;
    font-family: "Segoe UI", "Noto Sans", Helvetica, sans-serif;
    background-color: #2c2c2c;
}

th, td {
    padding: 10px;
    text-align: left;
    color: #acacac;
    width: auto;
}

td {
    border: 3px solid #353535;
}

th {
    background-color: #1f1f1f;
    width: auto;
}

p {
    color: #b3b3b3;
}

a {
    text-decoration: none;
    color: #1d9100;
}

sup {
    color: #aa8000;
}

.tooltip {
    cursor: pointer;
    position: relative;
}

.tooltip .tooltiptext {
    visibility: hidden;
    position: absolute;
    z-index: 1;
    top: 0;
    opacity: 0;
    transition: opacity 0.7s;
    width: 200px;
    background-color: #111;
    color: #acacac;
    text-align: left;
    border-radius: 5px;
    padding: 5px;
}

.tooltip:hover .tooltiptext {
    visibility: visible;
    opacity: 0.97;
}
</style>
<a href="{$data['site_url']}{$data['topic_url']}" style="font-family: monospace; color: #569904;">&larr; Back to the topic</a>
<center>
    <h2 style="color: #b3b3b3; font-family: monospace;">Name: {$data['name']} | Date: {$data['date']} | Size: {$data['size']}</h2>
<p>
    <p style="font-family: Calibri, sans-serif;">Created by: <i title="Torrent client's name">{$data['client']}</i></p>
</p>
<hr>
<table>
    <tr>
        <th>Path ({$data['file_count']} files)</th>
        <th>Size</th>
        <th style="width: auto;">
            BTMR hash
            <sup class="tooltip">?
                <span class="tooltiptext">
                    BitTorrent Merkle Root is a hash of a file embedded in torrents with BitTorrent v2 support, tracker users can extract, calculate them, also download deduplicated torrents using desktop tools such as
                    <a href="https://github.com/kovalensky/tmrr" target="_blank" referrerpolicy="origin">Torrent Merkle Root Reader.</a>
                </span>
            </sup>
        </th>
    </tr>
    {$allFiles}
</table>
<p style="color: #b3b3b3; font-family: Calibri, sans-serif;">Generated by <a href="https://github.com/torrentpier/torrentpier" target="_blank" referrerpolicy="origin" title="Bull-powered BitTorrent tracker engine">TorrentPier</a></p>
</center>
</body>
</html>
EOF;
