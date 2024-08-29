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

// Valid file formats
$validFormats = [
    'audio' => ['mp3', 'flac', 'wav'],
    'video' => ['mp4', 'mkv']
];

// Start session management
$user->session_start();

// Check attach_id
if (!$attach_id = request_var('attach_id', 0)) {
    bb_die($lang['INVALID_ATTACH_ID']);
}

// Check m3u file exist
if (!$m3u_file = (new \TorrentPier\TorrServerAPI())->getM3UPath($attach_id)) {
    bb_die($lang['ERROR_NO_ATTACHMENT']);
}

$m3uParser = new M3uParser\M3uParser();
$m3uParser->addDefaultTags();
$m3uData = $m3uParser->parseFile($m3u_file);

foreach ($m3uData as $entry) {
    $streamLink = $entry->getPath();
    $title = $lang['UNKNOWN'];

    // Validate URL
    if (!filter_var($streamLink, FILTER_VALIDATE_URL)) {
        continue;
    }

    // Validate file extension
    $getExtension = pathinfo(parse_url($streamLink, PHP_URL_PATH), PATHINFO_EXTENSION);
    $isValidFormat = in_array($getExtension, array_merge($validFormats['audio'], $validFormats['video']));

    // Parse tags
    foreach ($entry->getExtTags() as $extTag) {
        if ($extTag == $extTag instanceof \M3uParser\Tag\ExtInf) {
            $title = $extTag->getTitle();
        }
    }

    $template->assign_block_vars('m3ulist', [
        'IS_AUDIO' => in_array($getExtension, $validFormats['audio']),
        'STREAM_LINK' => $isValidFormat ? $streamLink : '', // TODO: Download M3U file
        'TITLE' => $title,
    ]);
}

print_page('showm3u.tpl');
