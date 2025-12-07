<?php

/**
 * TorrentPier – Bull-powered BitTorrent tracker engine
 *
 * @copyright Copyright (c) 2005-2025 TorrentPier (https://torrentpier.com)
 * @link      https://github.com/torrentpier/torrentpier for the canonical source repository
 * @license   https://github.com/torrentpier/torrentpier/blob/master/LICENSE MIT License
 */

namespace TorrentPier;

use GuzzleHttp\Client;
use GuzzleHttp\Exception\GuzzleException;
use stdClass;
use TorrentPier\Http\HttpClient;

/**
 * Class TorrServerAPI
 * @package TorrentPier
 */
class TorrServerAPI
{
    /**
     * URL to TorrServer instance
     *
     * @var string
     */
    private string $url;

    /**
     * HTTP client (simple, without retries)
     *
     * @var Client
     */
    private Client $client;

    /**
     * Endpoints list
     *
     * @var array|string[]
     */
    private array $endpoints = [
        'playlist' => 'playlist',
        'upload' => 'torrent/upload',
        'stream' => 'stream',
        'ffprobe' => 'ffp',
    ];


    /**
     * TorrServer constructor
     */
    public function __construct()
    {
        $this->url = rtrim(trim(config()->get('torr_server.url')), '/') . '/';
        $this->client = HttpClient::createSimpleClient([
            'timeout' => config()->get('torr_server.timeout'),
            'connect_timeout' => 2,
        ]);
    }

    /**
     * Check if TorrServer is available (cached for 30 sec)
     *
     * @return bool
     */
    public function isAvailable(): bool
    {
        static $checked = null;

        if ($checked !== null) {
            return $checked;
        }

        $cacheKey = 'torrserver_available';
        if (($cached = CACHE('bb_cache')->get($cacheKey)) !== false) {
            return $checked = (bool) $cached;
        }

        try {
            $response = $this->client->get($this->url . 'echo', [
                'timeout' => 2,
                'connect_timeout' => 1,
            ]);
            $checked = $response->getStatusCode() === 200;
        } catch (GuzzleException) {
            $checked = false;
        }

        CACHE('bb_cache')->set($cacheKey, (int) $checked, 30);
        return $checked;
    }

    /**
     * Upload torrent-file to TorrServer instance
     *
     * @param string $path
     * @param string $mimetype
     * @return bool
     */
    public function uploadTorrent(string $path, string $mimetype): bool
    {
        // Check mimetype
        if ($mimetype !== TORRENT_MIMETYPE) {
            return false;
        }

        // Skip if TorrServer is unavailable
        if (!$this->isAvailable()) {
            return false;
        }

        try {
            $response = $this->client->post($this->url . $this->endpoints['upload'], [
                'timeout' => config()->get('torr_server.timeout'),
                'headers' => [
                    'Accept' => 'application/json',
                ],
                'multipart' => [
                    [
                        'name' => 'file',
                        'contents' => fopen($path, 'r'),
                        'filename' => basename($path),
                        'headers' => ['Content-Type' => $mimetype],
                    ],
                ],
            ]);

            $isSuccess = $response->getStatusCode() === 200;
            if (!$isSuccess) {
                bb_log("TorrServer (ERROR) [$this->url]: Response code: {$response->getStatusCode()} | Content: {$response->getBody()->getContents()}" . LOG_LF);
            }

            return $isSuccess;
        } catch (GuzzleException $e) {
            bb_log("TorrServer (EXCEPTION) [$this->url]: {$e->getMessage()}" . LOG_LF);
            return false;
        }
    }

    /**
     * Saves M3U file (local)
     *
     * @param int $topic_id
     * @param string $hash
     * @return bool
     */
    public function saveM3U(int $topic_id, string $hash): bool
    {
        $m3uFile = Attachment::getPath($topic_id, M3U_EXT_ID);

        // Make stream call to store torrent in memory (2 retry max)
        for ($i = 0, $max_try = 2; $i <= $max_try; $i++) {
            if ($this->getStream($hash)) {
                break;
            } elseif ($i == $max_try) {
                return false;
            }
        }

        try {
            $response = $this->client->get($this->url . $this->endpoints['playlist'], [
                'timeout' => config()->get('torr_server.timeout'),
                'headers' => [
                    'Accept' => 'audio/x-mpegurl',
                ],
                'query' => ['hash' => $hash],
            ]);

            $responseBody = $response->getBody()->getContents();
            if ($response->getStatusCode() === 200 && !empty($responseBody)) {
                // Validate response
                $validResponse = false;
                $responseLines = explode("\n", $responseBody);
                foreach ($responseLines as $line) {
                    $line = trim($line);
                    if ($line === '') {
                        continue;
                    }

                    if (str_starts_with($line, '#EXTINF')) {
                        $validResponse = true;
                        break;
                    }
                }

                // Store M3U file
                if ($validResponse && !is_file($m3uFile)) {
                    file_put_contents($m3uFile, $responseBody);
                }
            } else {
                bb_log("TorrServer (ERROR) [$this->url]: Response code: {$response->getStatusCode()} | Content: {$responseBody}" . LOG_LF);
            }
        } catch (GuzzleException $e) {
            bb_log("TorrServer (EXCEPTION) [$this->url]: {$e->getMessage()}" . LOG_LF);
        }

        return is_file($m3uFile) && (int) filesize($m3uFile) > 0;
    }

    /**
     * Returns full path to M3U file
     *
     * @param int $topic_id
     * @return string|false
     */
    public function getM3UPath(int $topic_id): string|false
    {
        if (Attachment::m3uExists($topic_id)) {
            return Attachment::getPath($topic_id, M3U_EXT_ID);
        }

        return false;
    }

    /**
     * Removes M3U file (local)
     *
     * @param int $topic_id
     * @return bool
     */
    public function removeM3U(int $topic_id): bool
    {
        // Remove ffprobe data from cache
        CACHE('tr_cache')->rm("ffprobe_m3u_$topic_id");

        // Unlink .m3u file
        if (Attachment::m3uExists($topic_id)) {
            $m3uFile = Attachment::getPath($topic_id, M3U_EXT_ID);
            if (unlink($m3uFile)) {
                return true;
            } else {
                bb_log("TorrServer (ERROR) [removeM3U()]: Can't unlink file '$m3uFile'" . LOG_LF);
            }
        }

        return false;
    }

    /**
     * Returns info from TorrServer in-build ffprobe
     *
     * @param string $hash
     * @param int $index
     * @param int $topic_id
     * @return mixed
     */
    public function getFfpInfo(string $hash, int $index, int $topic_id): mixed
    {
        if (!$response = CACHE('tr_cache')->get("ffprobe_m3u_$topic_id")) {
            $response = new stdClass();
        }

        if (!isset($response->{$index})) {
            // Make stream call to store torrent in memory (2 retry max)
            for ($i = 0, $max_try = 2; $i <= $max_try; $i++) {
                if ($this->getStream($hash)) {
                    break;
                } elseif ($i == $max_try) {
                    return false;
                }
            }

            try {
                $httpResponse = $this->client->get($this->url . $this->endpoints['ffprobe'] . '/' . $hash . '/' . $index, [
                    'timeout' => config()->get('torr_server.timeout'),
                    'headers' => [
                        'Accept' => 'application/json',
                    ],
                ]);

                $response->{$index} = $httpResponse->getBody()->getContents();
                if ($httpResponse->getStatusCode() === 200 && !empty($response->{$index})) {
                    CACHE('tr_cache')->set("ffprobe_m3u_$topic_id", $response, 3600);
                } else {
                    bb_log("TorrServer (ERROR) [$this->url]: Response code: {$httpResponse->getStatusCode()}" . LOG_LF);
                }
            } catch (GuzzleException $e) {
                bb_log("TorrServer (EXCEPTION) [$this->url]: {$e->getMessage()}" . LOG_LF);
            }
        }

        return $response;
    }

    /**
     * Upstream
     *
     * @param string $hash
     * @return bool
     */
    private function getStream(string $hash): bool
    {
        try {
            $response = $this->client->get($this->url . $this->endpoints['stream'], [
                'timeout' => config()->get('torr_server.timeout'),
                'headers' => [
                    'Accept' => 'application/octet-stream',
                ],
                'query' => ['link' => $hash],
            ]);

            $isSuccess = $response->getStatusCode() === 200;
            if (!$isSuccess) {
                bb_log("TorrServer (ERROR) [$this->url]: Response code: {$response->getStatusCode()} | Content: {$response->getBody()->getContents()}" . LOG_LF);
            }

            return $isSuccess;
        } catch (GuzzleException $e) {
            bb_log("TorrServer (EXCEPTION) [$this->url]: {$e->getMessage()}" . LOG_LF);
            return false;
        }
    }
}
