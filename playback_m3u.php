<?php
/**
 * TorrentPier – Bull-powered BitTorrent tracker engine
 *
 * @copyright Copyright (c) 2005-2024 TorrentPier (https://torrentpier.com)
 * @link      https://github.com/torrentpier/torrentpier for the canonical source repository
 * @license   https://github.com/torrentpier/torrentpier/blob/master/LICENSE MIT License
 */

define('BB_SCRIPT', 'playback_m3u');

require __DIR__ . '/common.php';

if (!$bb_cfg['torr_server']['enabled']) {
    redirect('index.php');
}

// Valid file formats
$validFormats = [
    'audio' => ['mp3', 'flac', 'wav'],
    'video' => ['mp4', 'mkv', 'avi', 'm4v']
];

// Start session management
$user->session_start(['req_login' => $bb_cfg['torr_server']['disable_for_guest']]);

// Disable robots indexing
$page_cfg['allow_robots'] = false;

// Check topic_id
$topic_id = isset($_GET[POST_TOPIC_URL]) ? (int)$_GET[POST_TOPIC_URL] : 0;
if (!$topic_id) {
    bb_die($lang['INVALID_TOPIC_ID'], 404);
}

// Getting torrent info from database
$sql = 'SELECT attach_id, info_hash, info_hash_v2
            FROM ' . BB_BT_TORRENTS . '
            WHERE topic_id = ' . $topic_id . '
        LIMIT 1';

if (!$row = DB()->fetch_row($sql)) {
    bb_die($lang['INVALID_TOPIC_ID_DB'], 404);
}

// Check m3u file exist
if (!$m3uFile = (new \TorrentPier\TorrServerAPI())->getM3UPath($row['attach_id'])) {
    bb_die($lang['ERROR_NO_ATTACHMENT']);
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
    if ($getExtension === 'm3u') {
        // Skip m3u files
        continue;
    }

    $filesCount++;
    $rowClass = ($filesCount % 2) ? 'row1' : 'row2';

    $isAudio = in_array($getExtension, $validFormats['audio']);
    $template->assign_block_vars('m3ulist', [
        'ROW_NUMBER' => $filesCount,
        'ROW_CLASS' => $rowClass,
        'IS_VALID' => in_array($getExtension, array_merge($validFormats['audio'], $validFormats['video'])),
        'IS_AUDIO' => $isAudio,
        'STREAM_LINK' => $streamLink,
        'M3U_DL_LINK' => $m3uFile,
        'TITLE' => $title,
    ]);

    // Get ffprobe info from TorrServer
    $ffpInfo = (new \TorrentPier\TorrServerAPI())->getFfpInfo($row['info_hash'] ?? $row['info_hash_v2'], $filesCount, $row['attach_id']);
    if (isset($ffpInfo->streams)) {
        dump($ffpInfo);
        // Video codec information
        $videoCodecIndex = array_search('video', array_column($ffpInfo->streams, 'codec_type'));
        $videoCodecInfo = $ffpInfo->streams[$videoCodecIndex];
        // Audio codec information
        $audioDub = array_map(function ($stream) {
            global $lang;
            if (!isset($stream->tags)) {
                $result = $lang['UNKNOWN'];
            } else {
                if (isset($stream->tags->title)) {
                    $result = $stream->tags->language . ' (' . $stream->tags->title . ') [Каналов: ' . $stream->channels . ' | Битрейт: ' . $stream->bit_rate . ' ]';
                } else {
                    $result = $stream->tags->language;
                }
            }

            return $result;
        }, array_filter($ffpInfo->streams, function ($e) {
            return $e->codec_type === 'audio';
        }));

        if (isset($videoCodecInfo)) {
            $template->assign_block_vars('m3ulist.ffprobe', [
                'FILESIZE' => sprintf($lang['FILESIZE'] . ': %s', humn_size($ffpInfo->format->size)),
                'RESOLUTION' => !$isAudio ? sprintf($lang['RESOLUTION'], $videoCodecInfo->width . 'x' . $videoCodecInfo->height) : '',
                'VIDEO_CODEC' => sprintf($lang['VIDEO_CODEC'], mb_strtoupper($videoCodecInfo->codec_name, 'UTF-8')),
                'AUDIO_DUB' => implode('<br>', $audioDub)
            ]);
        }
    }
}

// Generate output
$template->assign_vars([
    'HAS_ITEMS' => (bool)$filesCount,
    'PAGE_TITLE' => $lang['PLAYBACK_M3U'],
    'FILES_COUNT' => sprintf($lang['BT_FLIST_FILE_PATH'], declension($filesCount, 'files')),
]);

print_page('playback_m3u.tpl');
