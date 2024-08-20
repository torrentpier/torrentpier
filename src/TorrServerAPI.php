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

use ErrorException;

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
     * M3U file params
     */
    const M3U = [
        'prefix' => 'm3u_',
        'extension' => '.m3u'
    ];

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

        $this->url = $bb_cfg['torr_server']['url'] . '/';
    }

    /**
     * Test server connection
     *
     * @return bool
     */
    public function serverIsUp(): bool
    {
        global $bb_cfg;

        $curl = new Curl();
        $curl->setTimeout($bb_cfg['torr_server']['timeout']);

        $curl->setHeader('Accept', 'text/plain');
        $curl->get($this->url . $this->endpoints['isUp']);
        $isSuccess = $curl->httpStatusCode === 200;
        $curl->close();

        return $isSuccess;
    }

    /**
     * Upload torrent-file to TorrServer instance
     *
     * @param string $path
     * @param string $mimetype
     * @return bool
     * @throws ErrorException
     */
    public function uploadTorrent(string $path, string $mimetype): bool
    {
        global $bb_cfg;

        // Check connection
        if (!$this->serverIsUp()) {
            bb_log("TorrServer [$this->url]: Server is down!", $this->logFile);
            return false;
        }

        // Check mimetype
        if ($mimetype !== 'application/x-bittorrent') {
            return false;
        }

        $curl = new Curl();
        $curl->setTimeout($bb_cfg['torr_server']['timeout']);

        $curl->setHeaders([
            'Accept' => 'application/json',
            'Content-Type' => 'multipart/form-data'
        ]);
        $curl->post($this->url . $this->endpoints['upload']);
        // $curl->buildPostData(['file' => "@$path;type=$mimetype"]);
        $isSuccess = $curl->httpStatusCode === 200;
        dump($curl->response);
        $curl->close();

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
        global $bb_cfg;

        // Check connection
        if (!$this->serverIsUp()) {
            bb_log("TorrServer [$this->url]: Server is down!", $this->logFile);
            return false;
        }

        $hash = $infoHashV1 ?? $infoHashV2;

        // Check if file is already exist
        $m3uFile = get_attachments_dir() . '/' . self::M3U['prefix'] . $hash . self::M3U['extension'];
        if (is_file($m3uFile)) {
            return true;
        }

        // Make stream call to store torrent in memory
        $this->curl->setHeader('Accept', 'application/octet-stream');
        $this->curl->get($this->url . $this->endpoints['stream'], ['link' => strtoupper($hash)]);
        if ($this->curl->httpStatusCode !== 200) {
            return false;
        }

        $curl = new Curl();
        $curl->setTimeout($bb_cfg['torr_server']['timeout']);

        $curl->setHeader('Accept', 'audio/x-mpegurl');
        $curl->get($this->url . $this->endpoints['playlist'], ['hash' => strtoupper($hash)]);
        if ($curl->httpStatusCode === 200 && !empty($curl->response)) {
            file_put_contents($m3uFile, $curl->response);
        }
        $curl->close();

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

        $m3uFile = get_attachments_dir() . '/' . self::M3U['prefix'] . $hash . self::M3U['extension'];
        if (is_file($m3uFile)) {
            return $m3uFile;
        }

        return false;
    }

    /**
     * Removed M3U file (local)
     *
     * @param string|null $infoHashV1
     * @param string|null $infoHashV2
     * @return bool
     */
    public function removeM3U(null|string $infoHashV1, null|string $infoHashV2): bool
    {
        $hash = $infoHashV1 ?? $infoHashV2;

        $m3uFile = get_attachments_dir() . '/' . self::M3U['prefix'] . $hash . self::M3U['extension'];
        if (is_file($m3uFile)) {
            return unlink($m3uFile);
        }

        return false;
    }
}
