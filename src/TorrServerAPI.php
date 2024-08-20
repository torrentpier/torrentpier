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
use CURLFile;

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
        'stream' => 'stream'
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

        $this->url = $bb_cfg['torr_server']['url'] . '/';
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
        global $bb_cfg;

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
        $curl->post($this->url . $this->endpoints['upload'], [
            'file' => new CURLFile($path, $mimetype)
        ]);
        $isSuccess = $curl->httpStatusCode === 200;
        if (!$isSuccess) {
            bb_log("TorrServer (ERROR) [$this->url]: Response code: {$curl->httpStatusCode} | Content: {$curl->response}\n", $this->logFile);
        }
        $curl->close();

        return $isSuccess;
    }

    /**
     * Saves M3U file (local)
     *
     * @param string|int $attach_id
     * @param string $hash
     * @return string
     */
    public function saveM3U(string|int $attach_id, string $hash): string
    {
        global $bb_cfg;

        $m3uFile = get_attachments_dir() . '/' . self::M3U['prefix'] . $attach_id . self::M3U['extension'];

        // Make stream call to store torrent in memory
        if (!$this->getStream($hash)) {
            return false;
        }

        $curl = new Curl();
        $curl->setTimeout($bb_cfg['torr_server']['timeout']);

        $curl->setHeader('Accept', 'audio/x-mpegurl');
        $curl->get($this->url . $this->endpoints['playlist'], ['hash' => $hash]);
        if ($curl->httpStatusCode === 200 && !empty($curl->response)) {
            // Validate response
            $validResponse = false;
            $responseLines = explode("\n", $curl->response);
            foreach ($responseLines as $line) {
                if (str_contains($line, '#EXTINF')) {
                    $validResponse = true;
                    break;
                }
            }

            // Store M3U file
            if ($validResponse && !is_file($m3uFile)) {
                file_put_contents($m3uFile, $curl->response);
            }
        } else {
            bb_log("TorrServer (ERROR) [$this->url]: Response code: {$curl->httpStatusCode} | Content: {$curl->response}\n", $this->logFile);
        }
        $curl->close();

        return is_file($m3uFile) && (int)filesize($m3uFile) > 0;
    }

    /**
     * Returns full path to M3U file
     *
     * @param int|string $attach_id
     * @return string
     */
    public function getM3UPath(int|string $attach_id): string
    {
        $m3uFile = get_attachments_dir() . '/' . self::M3U['prefix'] . $attach_id . self::M3U['extension'];
        if (is_file($m3uFile)) {
            return $m3uFile;
        }

        return false;
    }

    /**
     * Removed M3U file (local)
     *
     * @param string|int $attach_id
     * @return bool
     */
    public function removeM3U(string|int $attach_id): bool
    {
        $m3uFile = get_attachments_dir() . '/' . self::M3U['prefix'] . $attach_id . self::M3U['extension'];
        if (is_file($m3uFile)) {
            return unlink($m3uFile);
        }

        return false;
    }

    /**
     * Up stream
     *
     * @param string $hash
     * @return bool
     */
    private function getStream(string $hash): bool
    {
        global $bb_cfg;

        $curl = new Curl();
        $curl->setTimeout($bb_cfg['torr_server']['timeout']);

        $curl->setHeader('Accept', 'application/octet-stream');
        $curl->get($this->url . $this->endpoints['stream'], ['link' => $hash]);
        $isSuccess = $curl->httpStatusCode === 200;
        if (!$isSuccess) {
            bb_log("TorrServer (ERROR) [$this->url]: Response code: {$curl->httpStatusCode} | Content: {$curl->response}\n", $this->logFile);
        }
        $curl->close();

        return $isSuccess;
    }
}
