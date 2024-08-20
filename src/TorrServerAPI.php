<?php
/**
 * TorrentPier – Bull-powered BitTorrent tracker engine
 *
 * @copyright Copyright (c) 2005-2024 TorrentPier (https://torrentpier.com)
 * @link      https://github.com/torrentpier/torrentpier for the canonical source repository
 * @license   https://github.com/torrentpier/torrentpier/blob/master/LICENSE MIT License
 */

namespace TorrentPier;

use Curl\Curl;

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
     * Curl object
     *
     * @var Curl
     */
    private Curl $curl;

    /**
     * Endpoints list
     *
     * @var array|string[]
     */
    private array $endpoints = [
        'playlist' => 'playlist',
        'upload' => 'torrent/upload',
        'stream' => 'stream',
        'isUp' => 'echo'
    ];

    /**
     * M3U file prefix
     */
    const M3U_FILE_PREFIX = 'm3u_';

    /**
     * M3U file extension
     */
    const M3U_EXTENSION = '.m3u';

    /**
     * Log filename
     *
     * @var string
     */
    private string $logFile = 'torr_server';

    /**
     * TorrServer constructor
     */
    public function __construct()
    {
        global $bb_cfg;

        if (!$bb_cfg['torr_server']['enabled']) {
            return;
        }

        $this->curl = new Curl();
        $this->curl->setTimeout($bb_cfg['torr_server']['timeout']);
        $this->url = $bb_cfg['torr_server']['url'] . '/';
    }

    /**
     * Test server connection
     *
     * @return bool
     */
    public function serverIsUp(): bool
    {
        $this->curl->setHeader('Accept', 'text/plain');
        $this->curl->get($this->url . $this->endpoints['isUp']);
        $isSuccess = $this->curl->httpStatusCode === 200;
        $this->curl->close();

        return $isSuccess;
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
        // Check connection
        if (!$this->serverIsUp()) {
            bb_log("TorrServer [$this->url]: Server is down!", $this->logFile);
            return false;
        }

        // Check mimetype
        if ($mimetype !== 'application/x-bittorrent') {
            return false;
        }

        // Set headers
        $this->curl->setHeader('Accept', 'application/json');
        $this->curl->setHeader('Content-Type', 'multipart/form-data');

        // Make request
        $this->curl->post($this->url . $this->endpoints['upload'], [
            'file' => curl_file_create($path, $mimetype)
        ]);

        // Check response & close connect
        $isSuccess = $this->curl->httpStatusCode === 200;
        $this->curl->close();

        return $isSuccess;
    }

    /**
     * @param null|string $infoHashV1
     * @param null|string $infoHashV2
     *
     * @return string
     */
    public function saveM3U(null|string $infoHashV1, null|string $infoHashV2): string
    {
        // Check connection
        if (!$this->serverIsUp()) {
            bb_log("TorrServer [$this->url]: Server is down!", $this->logFile);
            return false;
        }

        $hash = $infoHashV1 ?? $infoHashV2;

        // Check if file is already exist
        $m3uFile = get_attachments_dir() . '/' . self::M3U_FILE_PREFIX . $hash . self::M3U_EXTENSION;
        if (is_file($m3uFile)) {
            return true;
        }

        // Make stream call to store torrent in memory
        $this->curl->setHeader('Accept', 'application/octet-stream');
        $this->curl->get($this->url . $this->endpoints['stream'], ['link' => strtoupper($hash)]);
        if ($this->curl->httpStatusCode !== 200) {
            return false;
        }
        $this->curl->close();

        // Save m3u file
        $this->curl->setHeader('Accept', 'audio/x-mpegurl');
        $this->curl->get($this->url . $this->endpoints['playlist'], ['hash' => strtoupper($hash)]);
        if ($this->curl->httpStatusCode === 200 && !empty($this->curl->response)) {
            file_put_contents($m3uFile, $this->curl->response);
        }
        $this->curl->close();

        return is_file($m3uFile) && (int)filesize($m3uFile) > 0;
    }

    /**
     * Returns full path to M3U file
     *
     * @param string|null $infoHashV1
     * @param string|null $infoHashV2
     * @return string
     */
    public function getM3UPath(null|string $infoHashV1, null|string $infoHashV2): string
    {
        $hash = $infoHashV1 ?? $infoHashV2;
        $m3uFile = get_attachments_dir() . '/' . self::M3U_FILE_PREFIX . $hash . self::M3U_EXTENSION;

        if (is_file($m3uFile)) {
            return $m3uFile;
        }

        return false;
    }
}
