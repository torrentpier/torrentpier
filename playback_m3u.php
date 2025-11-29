<?php
/**
 * TorrentPier – Bull-powered BitTorrent tracker engine
 *
 * @copyright Copyright (c) 2005-2025 TorrentPier (https://torrentpier.com)
 * @link      https://github.com/torrentpier/torrentpier for the canonical source repository
 * @license   https://github.com/torrentpier/torrentpier/blob/master/LICENSE MIT License
 */

define('BB_SCRIPT', 'playback_m3u');

require __DIR__ . '/common.php';

if (!config()->get('torr_server.enabled')) {
    redirect('index.php');
}

// Valid file formats
$validFormats = [
    'audio' => ['mp3', 'flac', 'wav', 'm4a'],
    'video' => ['mp4', 'mkv', 'avi', 'm4v']
];

// Start session management
$user->session_start(['req_login' => config()->get('torr_server.disable_for_guest')]);

// Disable robots indexing
$page_cfg['allow_robots'] = false;

// Check topic_id
$topic_id = isset($_GET[POST_TOPIC_URL]) ? (int)$_GET[POST_TOPIC_URL] : 0;
if (!$topic_id) {
    bb_die($lang['INVALID_TOPIC_ID'], 404);
}

// Getting torrent info from database
$sql = 'SELECT topic_id, forum_id, info_hash, info_hash_v2, tor_status, poster_id
            FROM ' . BB_BT_TORRENTS . '
            WHERE topic_id = ' . $topic_id . '
        LIMIT 1';

if (!$row = DB()->fetch_row($sql)) {
    bb_die($lang['INVALID_TOPIC_ID_DB'], 404);
}

// Check m3u file exist
$torrServer = new \TorrentPier\TorrServerAPI();
if (!$m3uFile = $torrServer->getM3UPath($topic_id)) {
    bb_die($lang['ERROR_NO_ATTACHMENT']);
}

$forum_id = $row['forum_id'];
set_die_append_msg($forum_id, $topic_id);

// Check rights
$is_auth = auth(AUTH_ALL, $forum_id, $userdata);
if (!$is_auth['auth_download']) {
    bb_die($lang['SORRY_AUTH_VIEW_ATTACH']);
}

// Check for frozen torrent
$tor_status = $row['tor_status'];
if (!IS_AM && isset(config()->get('tor_frozen')[$tor_status]) && !(isset(config()->get('tor_frozen_author_download')[$tor_status]) && \TorrentPier\Topic\Guard::isAuthor($row['poster_id']))) {
    bb_die($lang['TOR_STATUS_FORBIDDEN'] . $lang['TOR_STATUS_NAME'][$tor_status]);
}

// Parse M3U file
$m3uParser = new M3uParser\M3uParser();
$m3uParser->addDefaultTags();
$m3uData = $m3uParser->parseFile($m3uFile);

$filesCount = 0;
foreach ($m3uData as $entry) {
    // Validate URL
    $streamLink = $entry->getPath();
    if (!filter_var($streamLink, FILTER_VALIDATE_URL)) {
        continue;
    }
    parse_str(parse_url($streamLink, PHP_URL_QUERY), $urlParams);

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
    if ($getExtension === str_replace('.', '', $torrServer::M3U['extension'])) {
        // Skip m3u files
        continue;
    }

    $filesCount++;
    $rowClass = ($filesCount % 2) ? 'row1' : 'row2';
    $template->assign_block_vars('m3ulist', [
        'ROW_NUMBER' => $filesCount,
        'FILE_INDEX' => $urlParams['index'],
        'ROW_CLASS' => $rowClass,
        'IS_VALID' => in_array($getExtension, array_merge($validFormats['audio'], $validFormats['video'])),
        'IS_AUDIO' => (int)in_array($getExtension, $validFormats['audio']),
        'STREAM_LINK' => $streamLink,
        'M3U_DL_LINK' => DL_URL . $topic_id . '&m3u=1',
        'TITLE' => $title,
    ]);
}

// Generate output
$template->assign_vars([
    'HAS_ITEMS' => $filesCount > 0,
    'PAGE_TITLE' => $lang['PLAYBACK_M3U'],
    'TOPIC_ID' => $topic_id,
    'INFO_HASH' => bin2hex($row['info_hash'] ?? $row['info_hash_v2']),
    'FILES_COUNT_TITLE' => sprintf($lang['BT_FLIST_FILE_PATH'], declension($filesCount, 'files')),
    'U_TOPIC' => TOPIC_URL . $topic_id,
]);

print_page('playback_m3u.tpl');
