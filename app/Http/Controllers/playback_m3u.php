<?php

/**
 * TorrentPier – Bull-powered BitTorrent tracker engine
 *
 * @copyright Copyright (c) 2005-2025 TorrentPier (https://torrentpier.com)
 * @link      https://github.com/torrentpier/torrentpier for the canonical source repository
 * @license   https://github.com/torrentpier/torrentpier/blob/master/LICENSE MIT License
 */

if (!config()->get('services.torrserver.enabled')) {
    redirect('index.php');
}

// Valid file formats
$validFormats = [
    'audio' => ['mp3', 'flac', 'wav', 'm4a'],
    'video' => ['mp4', 'mkv', 'avi', 'm4v'],
];

// Disable robots indexing
page_cfg('allow_robots', false);

// Check topic_id
$topic_id = request()->getInt(POST_TOPIC_URL);
if (!$topic_id) {
    bb_die(__('INVALID_TOPIC_ID'), 404);
}

// Getting torrent info from database
$sql = 'SELECT topic_id, forum_id, info_hash, info_hash_v2, tor_status, poster_id
            FROM ' . BB_BT_TORRENTS . '
            WHERE topic_id = ' . $topic_id . '
        LIMIT 1';

if (!$row = DB()->fetch_row($sql)) {
    bb_die(__('INVALID_TOPIC_ID_DB'), 404);
}

// Check m3u file exist
$torrServer = new TorrentPier\TorrServerAPI;
if (!$m3uFile = $torrServer->getM3UPath($topic_id)) {
    bb_die(__('ERROR_NO_ATTACHMENT'));
}

$forum_id = $row['forum_id'];
set_die_append_msg($forum_id, $topic_id);

// Check rights
$is_auth = auth(AUTH_ALL, $forum_id, userdata());
if (!$is_auth['auth_download']) {
    bb_die(__('SORRY_AUTH_VIEW_ATTACH'));
}

// Check for frozen torrent
$tor_status = $row['tor_status'];
if (!IS_AM && isset(config()->get('tracker.tor_frozen')[$tor_status]) && !(isset(config()->get('tracker.tor_frozen_author_download')[$tor_status]) && TorrentPier\Topic\Guard::isAuthor($row['poster_id']))) {
    bb_die(__('TOR_STATUS_FORBIDDEN') . __('TOR_STATUS_NAME')[$tor_status]);
}

// Parse M3U file
$m3uParser = new M3uParser\M3uParser;
$m3uParser->addDefaultTags();
$m3uData = $m3uParser->parseFile($m3uFile);

$m3ulist = [];
$filesCount = 0;

foreach ($m3uData as $entry) {
    // Validate URL
    $streamLink = $entry->getPath();
    if (!filter_var($streamLink, FILTER_VALIDATE_URL)) {
        continue;
    }
    parse_str(parse_url($streamLink, PHP_URL_QUERY) ?? '', $urlParams);

    // Skip if no index parameter
    if (!isset($urlParams['index'])) {
        continue;
    }

    // Parse tags
    foreach ($entry->getExtTags() as $extTag) {
        // #EXTINF tag
        if ($extTag instanceof M3uParser\Tag\ExtInf) {
            $title = $extTag->getTitle();
        }
    }

    // Validate title
    if (!isset($title)) {
        continue;
    }

    // Validate file extension
    $getExtension = pathinfo($title, PATHINFO_EXTENSION);
    if ($getExtension === M3U_EXT) {
        // Skip m3u files
        continue;
    }

    $filesCount++;
    $m3ulist[] = [
        'ROW_NUMBER' => $filesCount,
        'FILE_INDEX' => $urlParams['index'],
        'ROW_CLASS' => ($filesCount % 2) ? 'row1' : 'row2',
        'IS_VALID' => in_array($getExtension, array_merge($validFormats['audio'], $validFormats['video'])),
        'IS_AUDIO' => (int)in_array($getExtension, $validFormats['audio']),
        'STREAM_LINK' => $streamLink,
        'M3U_DL_LINK' => DL_URL . $topic_id . '/?m3u=1',
        'TITLE' => $title,
    ];
}

print_page('playback_m3u.twig', variables: [
    'PAGE_TITLE' => __('PLAYBACK_M3U'),
    'TOPIC_ID' => $topic_id,
    'INFO_HASH' => bin2hex($row['info_hash'] ?? $row['info_hash_v2']),
    'FILES_COUNT_TITLE' => sprintf(__('BT_FLIST_FILE_PATH'), declension($filesCount, 'files')),
    'U_TOPIC' => TOPIC_URL . $topic_id,
    'HAS_ITEMS' => $filesCount > 0,
    'M3ULIST' => $m3ulist,
]);
