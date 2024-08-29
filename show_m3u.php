<?php
/**
 * TorrentPier – Bull-powered BitTorrent tracker engine
 *
 * @copyright Copyright (c) 2005-2024 TorrentPier (https://torrentpier.com)
 * @link      https://github.com/torrentpier/torrentpier for the canonical source repository
 * @license   https://github.com/torrentpier/torrentpier/blob/master/LICENSE MIT License
 */

define('BB_SCRIPT', 'show_m3u');

require __DIR__ . '/common.php';

if (!$bb_cfg['torr_server']['enabled']) {
    redirect('index.php');
}

// Valid file formats
$validFormats = [
    'audio' => ['mp3', 'flac', 'wav'],
    'video' => ['mp4', 'mkv', 'avi']
];

// Start session management
$user->session_start(['req_login' => $bb_cfg['torr_server']['disable_for_guest']]);

// Disable robots indexing
$page_cfg['allow_robots'] = false;

// Check attach_id
if (!$attach_id = request_var('attach_id', 0)) {
    bb_die($lang['INVALID_ATTACH_ID']);
}

// Check m3u file exist
if (!$m3uFile = (new \TorrentPier\TorrServerAPI())->getM3UPath($attach_id)) {
    bb_die($lang['ERROR_NO_ATTACHMENT']);
}

// Parse M3U file
$m3uParser = new M3uParser\M3uParser();
$m3uParser->addDefaultTags();
$m3uData = $m3uParser->parseFile($m3uFile);

foreach ($m3uData as $entry) {
    $filesCount = $m3uData->count();
    $rowClass = ($filesCount % 2) ? 'row1' : 'row2';

    // Validate URL
    $streamLink = $entry->getPath();
    if (!filter_var($streamLink, FILTER_VALIDATE_URL)) {
        continue;
    }

    // Parse tags
    foreach ($entry->getExtTags() as $extTag) {
        // #EXTINF tag
        if ($extTag == $extTag instanceof \M3uParser\Tag\ExtInf) {
            $title = $extTag->getTitle();
        }
    }

    // Validate title
    if (!isset($title)) {
        continue;
    }

    // Validate file extension
    $getExtension = pathinfo($title, PATHINFO_EXTENSION);
    $isValidFormat = in_array($getExtension, array_merge($validFormats['audio'], $validFormats['video']));

    $template->assign_block_vars('m3ulist', [
        'ROW_NUMBER' => $filesCount,
        'ROW_CLASS' => $rowClass,
        'IS_VALID' => $isValidFormat,
        'IS_AUDIO' => in_array($getExtension, $validFormats['audio']),
        'STREAM_LINK' => $isValidFormat ? $streamLink : $m3uFile,
        'TITLE' => $title,
    ]);
}

$template->assign_vars([
    'PAGE_TITLE' => $lang['PLAYBACK_M3U'],
    'FILES_COUNT' => sprintf($lang['BT_FLIST_FILE_PATH'], declension($filesCount, 'files')),
]);

print_page('show_m3u.tpl');
