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
        'upload' => 'torrent/upload'
    ];

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
        $this->url = $bb_cfg['torr_server']['host'] . ':' . $bb_cfg['torr_server']['port'] . '/';
    }

    public function uploadTorrent(string $path, string $mimetype): bool
    {
        // Check mimetype
        if ($mimetype !== 'application/x-bittorrent') {
            return false;
        }

        // Set headers
        $this->curl->setHeader('Accept', 'application/json');
        $this->curl->setHeader('Content-Type', 'multipart/form-data');

        // Make request
        $cFile = curl_file_create($path, $mimetype);
        $this->curl->post($this->url . $this->endpoints['upload'], ['file' => $cFile]);

        // Check response & close connect
        $isSuccess = $this->curl->httpStatusCode === 200;
        $this->curl->close();

        return $isSuccess;
    }

    /**
     * Returns link to M3U file
     *
     * @param string $infoHash
     * @return string
     */
    public function getM3U(string $infoHash): string
    {
        return $this->url . $this->endpoints['playlist'] . '?hash=' . $infoHash;
    }
}
