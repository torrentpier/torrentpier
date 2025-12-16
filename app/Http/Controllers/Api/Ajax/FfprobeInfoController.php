<?php

/**
 * TorrentPier – Bull-powered BitTorrent tracker engine
 *
 * @copyright Copyright (c) 2005-2025 TorrentPier (https://torrentpier.com)
 * @link      https://github.com/torrentpier/torrentpier for the canonical source repository
 * @license   https://github.com/torrentpier/torrentpier/blob/master/LICENSE MIT License
 */

declare(strict_types=1);

namespace App\Http\Controllers\Api\Ajax;

use App\Http\Controllers\Api\Ajax\Concerns\AjaxResponse;
use Illuminate\Contracts\Container\BindingResolutionException;
use Psr\Http\Message\ResponseInterface;
use Psr\Http\Message\ServerRequestInterface;
use TorrentPier\TorrServerAPI;

/**
 * FFprobe Info Controller
 *
 * Gets media file information from TorrServer.
 */
class FfprobeInfoController
{
    use AjaxResponse;

    protected string $action = 'ffprobe_info';

    /**
     * @throws BindingResolutionException
     */
    public function __invoke(ServerRequestInterface $request): ResponseInterface
    {
        if (!config()->get('torr_server.enabled')) {
            return $this->error(__('MODULE_OFF'));
        }

        if (IS_GUEST) {
            return $this->error(__('NEED_TO_LOGIN_FIRST'));
        }

        $body = $request->getParsedBody() ?? [];

        $topicId = $body['topic_id'] ?? '';
        if (empty($topicId) || !is_numeric($topicId)) {
            return $this->error(__('INVALID_TOPIC_ID'));
        }

        $fileIndex = $body['file_index'] ?? null;
        if (!is_numeric($fileIndex) || $fileIndex < 0) {
            return $this->error(__('TORRSERVER_INVALID_REQUEST') . ": file_index=$fileIndex");
        }

        $infoHash = (string)($body['info_hash'] ?? '');
        if (!$infoHash || !ctype_xdigit($infoHash)) {
            return $this->error(__('TORRSERVER_INVALID_REQUEST') . ": info_hash=$infoHash");
        }

        $isAudio = isset($body['is_audio']) && $body['is_audio'];

        // Get ffprobe info from TorrServer
        $ffpInfo = new TorrServerAPI()->getFfpInfo($infoHash, $fileIndex, $topicId);
        if (!$ffpInfo || !isset($ffpInfo->{$fileIndex})) {
            return $this->error(__('TORRSERVER_UNAVAILABLE'));
        }

        $ffpInfo = $ffpInfo->{$fileIndex};
        $responseData = ['file_index' => $fileIndex];

        if (isset($ffpInfo->streams)) {
            // Video codec information
            $videoCodecInfo = null;
            $videoCodecIndex = array_search('video', array_column($ffpInfo->streams, 'codec_type'));
            if (\is_int($videoCodecIndex)) {
                $videoCodecInfo = $ffpInfo->streams[$videoCodecIndex];
            }

            // Audio codec information
            $audioTracks = array_filter($ffpInfo->streams, fn ($e) => $e->codec_type === 'audio');

            // Audio tracks information
            $audioDub = array_map(/**
             * @throws BindingResolutionException
             */ function ($stream) {
                $result = '<span class="warnColor2">' . \sprintf(__('AUDIO_TRACK'), (!isset($stream->index) || $stream->index === 0) ? 1 : $stream->index) . '</span><br/>';

                if (isset($stream->tags->language)) {
                    if (isset($stream->tags->title)) {
                        $result .= '<b>' . mb_strtoupper($stream->tags->language, DEFAULT_CHARSET) . ' (' . $stream->tags->title . ')</b>';
                    } else {
                        $result .= '<b>' . mb_strtoupper($stream->tags->language, DEFAULT_CHARSET) . '</b>';
                    }
                    $result .= '<br/>';
                }

                if (!empty($stream->codec_name)) {
                    $result .= \sprintf(__('AUDIO_CODEC'), $stream->codec_long_name, mb_strtoupper($stream->codec_name, DEFAULT_CHARSET)) . '<br/>';
                }
                if (!empty($stream->bit_rate)) {
                    $result .= \sprintf(__('BITRATE'), $this->formatBitrate((int)$stream->bit_rate)) . '<br/>';
                }
                if (!empty($stream->sample_rate)) {
                    $result .= \sprintf(__('SAMPLE_RATE'), $this->formatSampleRate((int)$stream->sample_rate)) . '<br/>';
                }
                if (!empty($stream->channels)) {
                    $result .= \sprintf(__('CHANNELS'), $stream->channels) . '<br/>';
                }
                if (!empty($stream->channel_layout)) {
                    $result .= \sprintf(__('CHANNELS_LAYOUT'), $stream->channel_layout);
                }

                return $result;
            }, $audioTracks);

            // Generate output data
            $data = [
                'filesize' => \sprintf(__('FILESIZE') . ': <b>%s</b>', humn_size($ffpInfo->format->size)),
                'resolution' => (!$isAudio && isset($videoCodecInfo)) ? \sprintf(__('RESOLUTION'), $videoCodecInfo->width . 'x' . $videoCodecInfo->height) : '',
                'video_codec' => (!$isAudio && isset($videoCodecInfo->codec_name)) ? \sprintf(__('VIDEO_CODEC'), $videoCodecInfo->codec_long_name, mb_strtoupper($videoCodecInfo->codec_name, DEFAULT_CHARSET)) : '',
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

            $responseData['ffprobe_data'] = $result;
        }

        return $this->response($responseData);
    }

    /**
     * Format bitrate to human-readable format
     */
    private function formatBitrate(int $bitrate, string $space = '&nbsp;'): string
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

        return \sprintf('%d', commify($bitrate)) . $space . $unit;
    }

    /**
     * Format sample rate to human-readable format
     */
    private function formatSampleRate(int $sampleRate, string $space = '&nbsp;'): string
    {
        if ($sampleRate >= 1000000) {
            $unit = 'Mhz';
        } elseif ($sampleRate >= 1000) {
            $unit = 'kHz';
        } else {
            $unit = 'Hz';
        }

        return \sprintf('%.1f', commify($sampleRate)) . $space . $unit;
    }
}
