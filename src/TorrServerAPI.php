<?php

namespace TorrentPier;

use Curl\Curl;

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
        'playlist' => 'playlist'
    ];

    public function __construct()
    {
        global $bb_cfg;

        if (!$bb_cfg['torr_server']['enabled']) {
            return;
        }

        $this->curl = new Curl();
        $this->url = $bb_cfg['torr_server']['host'] . ':' . $bb_cfg['torr_server']['port'] . '/';
    }

    public function uploadTorrent(array $_FILES)
    {
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
