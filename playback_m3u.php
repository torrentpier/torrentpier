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
    $ffpInfo = $ffpInfo->{$filesCount};
    if (isset($ffpInfo->streams)) {
        // Video codec information
        $videoCodecIndex = array_search('video', array_column($ffpInfo->streams, 'codec_type'));
        if (is_int($videoCodecIndex)) {
            $videoCodecInfo = $ffpInfo->streams[$videoCodecIndex];
        }
        // Audio codec information
        $audioTracks = array_filter($ffpInfo->streams, function ($e) {
            return $e->codec_type === 'audio';
        });
        $audioDub = array_map(function ($stream) {
            global $lang;

            $result = '<span class="warnColor2">' . sprintf($lang['AUDIO_TRACK'], (!isset($stream->index) || $stream->index === 0) ? 1 : $stream->index) . '</span><br>';
            if (isset($stream->tags->language)) {
                if (isset($stream->tags->title)) {
                    $result .= '<b>' . mb_strtoupper($stream->tags->language, 'UTF-8') . ' (' . $stream->tags->title . ')' . '</b>';
                } else {
                    $result .= '<b>' . mb_strtoupper($stream->tags->language, 'UTF-8') . '</b>';
                }
                $result .= '<br>';
            }

            if (!empty($stream->codec_name)) {
                $result .= sprintf($lang['AUDIO_CODEC'], mb_strtoupper($stream->codec_name, 'UTF-8')) . '<br>';
            }
            if (!empty($stream->bit_rate)) {
                $result .= sprintf($lang['BITRATE'], humn_bitrate($stream->bit_rate)) . '<br>';
            }
            if (!empty($stream->sample_rate)) {
                $result .= sprintf($lang['SAMPLE_RATE'], $stream->sample_rate) . '<br>';
            }
            if (!empty($stream->channels)) {
                $result .= sprintf($lang['CHANNELS'], $stream->channels) . '<br>';
            }
            if (!empty($stream->channel_layout)) {
                $result .= sprintf($lang['CHANNELS_LAYOUT'], $stream->channel_layout);
            }

            return $result;
        }, $audioTracks);

        $template->assign_block_vars('m3ulist.ffprobe', [
            'FILESIZE' => sprintf($lang['FILESIZE'] . ': <b>%s</b>', humn_size($ffpInfo->format->size)),
            'RESOLUTION' => (!$isAudio && isset($videoCodecInfo)) ? sprintf($lang['RESOLUTION'], $videoCodecInfo->width . 'x' . $videoCodecInfo->height) : '',
            'VIDEO_CODEC' => (!$isAudio && isset($videoCodecInfo->codec_name)) ? sprintf($lang['VIDEO_CODEC'], mb_strtoupper($videoCodecInfo->codec_name, 'UTF-8')) : '',
            'AUDIO_DUB' => implode('<hr>', $audioDub)
        ]);
    }
}

// Generate output
$template->assign_vars([
    'HAS_ITEMS' => (bool)$filesCount,
    'PAGE_TITLE' => $lang['PLAYBACK_M3U'],
    'FILES_COUNT' => sprintf($lang['BT_FLIST_FILE_PATH'], declension($filesCount, 'files')),
    'U_TOPIC' => TOPIC_URL . $topic_id,
]);

print_page('playback_m3u.tpl');
