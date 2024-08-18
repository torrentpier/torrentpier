<?php
/**
 * TorrentPier – Bull-powered BitTorrent tracker engine
 *
 * @copyright Copyright (c) 2005-2024 TorrentPier (https://torrentpier.com)
 * @link      https://github.com/torrentpier/torrentpier for the canonical source repository
 * @license   https://github.com/torrentpier/torrentpier/blob/master/LICENSE MIT License
 */

define('BB_SCRIPT', 'filelist');

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

if (!$row = DB()->fetch_row($sql)) {
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

$files_count = 0;
foreach ($files as $file) {
    $files_count++;
    $row_class = ($files_count % 2) ? 'row1' : 'row2';
    $template->assign_block_vars('filelist', [
        'ROW_NUMBER' => $files_count,
        'ROW_CLASS' => $row_class,
        'FILE_PATH' => clean_tor_dirname(implode('/', $file->path)),
        'FILE_LENGTH' => humn_size($file->length, 2),
        'FILE_HASH' => $file->$t_hash_field ?? '-'
    ]);
}

$torrent_name = !empty($t_name = $torrent->getName()) ? htmlCHR(str_short($t_name, 200)) : $lang['UNKNOWN'];
$torrent_size = humn_size($row['size'], 2);

$template->assign_vars([
    'PAGE_TITLE' => "$torrent_name (" . $torrent_size . ")",
    'FILES_COUNT' => sprintf($lang['BT_FLIST_FILE_PATH'], declension(iterator_count($files), 'files')),
    'TORRENT_CREATION_DATE' => (!empty($dt = $torrent->getCreationDate()) && is_numeric($creation_date = $dt->getTimestamp())) ? date('d-M-Y H:i (e)', $creation_date) : $lang['UNKNOWN'],
    'TORRENT_CLIENT' => !empty($creator = $torrent->getCreatedBy()) ? htmlCHR(str_short($creator, 20)) : $lang['UNKNOWN'],

    'BTMR_NOTICE' => sprintf($lang['BT_FLIST_BTMR_NOTICE'], 'https://github.com/kovalensky/tmrr'),
    'U_TOPIC' => TOPIC_URL . $topic_id,
]);

print_page('filelist.tpl');
