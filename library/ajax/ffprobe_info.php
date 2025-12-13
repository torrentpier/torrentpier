<?php

/**
 * TorrentPier – Bull-powered BitTorrent tracker engine
 *
 * @copyright Copyright (c) 2005-2025 TorrentPier (https://torrentpier.com)
 * @link      https://github.com/torrentpier/torrentpier for the canonical source repository
 * @license   https://github.com/torrentpier/torrentpier/blob/master/LICENSE MIT License
 */

if (!defined('IN_AJAX')) {
    die(basename(__FILE__));
}

if (!config()->get('torr_server.enabled')) {
    $this->ajax_die(__('MODULE_OFF'));
}

if (config()->get('torr_server.disable_for_guest') && IS_GUEST) {
    $this->ajax_die(__('NEED_TO_LOGIN_FIRST'));
}

$topic_id = $this->request['topic_id'] ?? '';
if (empty($topic_id) || !is_numeric($topic_id)) {
    $this->ajax_die(__('INVALID_TOPIC_ID'));
}

$file_index = $this->request['file_index'] ?? null;
if ($file_index === null || !is_numeric($file_index) || $file_index < 0) {
    $this->ajax_die(__('TORRSERVER_INVALID_REQUEST') . ": file_index={$file_index}");
}

if (!$info_hash = (string)$this->request['info_hash'] or !ctype_xdigit($info_hash)) {
    $this->ajax_die(__('TORRSERVER_INVALID_REQUEST') . ": info_hash={$info_hash}");
}

$isAudio = isset($this->request['is_audio']) && $this->request['is_audio'];

// Get ffprobe info from TorrServer
$ffpInfo = new TorrentPier\TorrServerAPI()->getFfpInfo($info_hash, $file_index, $topic_id);
if (!$ffpInfo || !isset($ffpInfo->{$file_index})) {
    $this->ajax_die(__('TORRSERVER_UNAVAILABLE'));
}
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
    // Audio tracks information
    $audioDub = array_map(function ($stream) {
        $result = '<span class="warnColor2">' . sprintf(__('AUDIO_TRACK'), (!isset($stream->index) || $stream->index === 0) ? 1 : $stream->index) . '</span><br/>';
        if (isset($stream->tags->language)) {
            if (isset($stream->tags->title)) {
                $result .= '<b>' . mb_strtoupper($stream->tags->language, DEFAULT_CHARSET) . ' (' . $stream->tags->title . ')</b>';
            } else {
                $result .= '<b>' . mb_strtoupper($stream->tags->language, DEFAULT_CHARSET) . '</b>';
            }
            $result .= '<br/>';
        }

        if (!empty($stream->codec_name)) {
            $result .= sprintf(__('AUDIO_CODEC'), $stream->codec_long_name, mb_strtoupper($stream->codec_name, DEFAULT_CHARSET)) . '<br/>';
        }
        if (!empty($stream->bit_rate)) {
            $result .= sprintf(__('BITRATE'), humn_bitrate((int)$stream->bit_rate)) . '<br/>';
        }
        if (!empty($stream->sample_rate)) {
            $result .= sprintf(__('SAMPLE_RATE'), humn_sample_rate((int)$stream->sample_rate)) . '<br/>';
        }
        if (!empty($stream->channels)) {
            $result .= sprintf(__('CHANNELS'), $stream->channels) . '<br/>';
        }
        if (!empty($stream->channel_layout)) {
            $result .= sprintf(__('CHANNELS_LAYOUT'), $stream->channel_layout);
        }

        return $result;
    }, $audioTracks);

    // Generate output data
    $data = [
        'filesize' => sprintf(__('FILESIZE') . ': <b>%s</b>', humn_size($ffpInfo->format->size)),
        'resolution' => (!$isAudio && isset($videoCodecInfo)) ? sprintf(__('RESOLUTION'), $videoCodecInfo->width . 'x' . $videoCodecInfo->height) : '',
        'video_codec' => (!$isAudio && isset($videoCodecInfo->codec_name)) ? sprintf(__('VIDEO_CODEC'), $videoCodecInfo->codec_long_name, mb_strtoupper($videoCodecInfo->codec_name, DEFAULT_CHARSET)) : '',
        'audio_dub' => implode('<hr/>', $audioDub),
    ];

    // Validate output data
    $result = '<hr/>';
    if (!empty($data['resolution'])) {
        $result .= $data['resolution'] . '<br/>';
    }
    if (!empty($data['filesize'])) {
        $result .= $data['filesize'] . '<br/>';
    }
    if (!empty($data['video_codec'])) {
        $result .= $data['video_codec'];
    }
    if (!empty($data['audio_dub'])) {
        $result .= '<hr/>' . $data['audio_dub'];
    }

    $this->response['ffprobe_data'] = $result;
}

/**
 * Bitrate to human-readable format
 *
 * @param int $bitrate
 * @param string $space
 * @return string
 */
function humn_bitrate(int $bitrate, string $space = '&nbsp;'): string
{
    if ($bitrate >= 1000000) {
        $unit = 'Mbps';
        $bitrate /= 1000000;
    } elseif ($bitrate >= 1000) {
        $unit = 'kbps';
        $bitrate /= 1000;
    } else {
        $unit = 'bps';
    }

    return sprintf('%d', commify($bitrate)) . $space . $unit;
}

/**
 * Sample rate to human-readable format
 *
 * @param int $sample_rate
 * @param string $space
 * @return string
 */
function humn_sample_rate(int $sample_rate, string $space = '&nbsp;'): string
{
    if ($sample_rate >= 1000000) {
        $unit = 'Mhz';
    } elseif ($sample_rate >= 1000) {
        $unit = 'kHz';
    } else {
        $unit = 'Hz';
    }

    return sprintf('%.1f', commify($sample_rate)) . $space . $unit;
}

$this->response['file_index'] = $file_index;
