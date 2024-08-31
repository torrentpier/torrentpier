<?php
/**
 * TorrentPier – Bull-powered BitTorrent tracker engine
 *
 * @copyright Copyright (c) 2005-2024 TorrentPier (https://torrentpier.com)
 * @link      https://github.com/torrentpier/torrentpier for the canonical source repository
 * @license   https://github.com/torrentpier/torrentpier/blob/master/LICENSE MIT License
 */

if (!defined('IN_AJAX')) {
    die(basename(__FILE__));
}

global $bb_cfg, $lang;

if (!$bb_cfg['torr_server']['enabled']) {
    $this->ajax_die($lang['MODULE_OFF']);
}

if (!$attach_id = (int)$this->request['attach_id']) {
    $this->ajax_die($lang['INVALID_ATTACH_ID']);
}

if (!$file_index = (int)$this->request['file_index']) {
    $this->ajax_die('Invalid file index');
}

if (!$info_hash = (string)$this->request['info_hash']) {
    $this->ajax_die('Invalid info_hash');
}

$isAudio = (bool)$this->request['is_audio'];

// Get ffprobe info from TorrServer
$ffpInfo = (new \TorrentPier\TorrServerAPI())->getFfpInfo($info_hash, $file_index, $attach_id);
$ffpInfo = $ffpInfo->{$file_index};
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

    $data = [
        'filesize' => sprintf($lang['FILESIZE'] . ': <b>%s</b>', humn_size($ffpInfo->format->size)),
        'resolution' => (!$isAudio && isset($videoCodecInfo)) ? sprintf($lang['RESOLUTION'], $videoCodecInfo->width . 'x' . $videoCodecInfo->height) : '',
        'video_codec' => (!$isAudio && isset($videoCodecInfo->codec_name)) ? sprintf($lang['VIDEO_CODEC'], mb_strtoupper($videoCodecInfo->codec_name, 'UTF-8')) : '',
        'audio_dub' => implode('<hr>', $audioDub)
    ];

    $result = '<hr>';
    if (!empty($data['resolution'])) {
        $result .= $data['resolution'] . '<br>';
    }
    if (!empty($data['filesize'])) {
        $result .= $data['filesize'] . '<br>';
    }
    if (!empty($data['video_codec'])) {
        $result .= $data['video_codec'];
    }
    if (!empty($data['audio_dub'])) {
        $result .= '<hr>' . $data['audio_dub'];
    }

    $this->response['ffprobe_data'] = $result;
}
