<?php
/**
 * TorrentPier – Bull-powered BitTorrent tracker engine
 *
 * @copyright Copyright (c) 2005-2025 TorrentPier (https://torrentpier.com)
 * @link      https://github.com/torrentpier/torrentpier for the canonical source repository
 * @license   https://github.com/torrentpier/torrentpier/blob/master/LICENSE MIT License
 */

namespace TorrentPier;

use Curl\Curl;
use CURLFile;

use stdClass;

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
        'ffprobe' => 'ffp'
    ];

    /**
     * M3U file params
     */
    const M3U = [
        'prefix' => 'm3u_',
        'extension' => '.m3u'
    ];

    /**
     * TorrServer constructor
     */
    public function __construct()
    {
        global $bb_cfg;

        $this->url = rtrim(trim($bb_cfg['torr_server']['url']), '/') . '/';
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
        if ($mimetype !== TORRENT_MIMETYPE) {
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
            bb_log("TorrServer (ERROR) [$this->url]: Response code: {$curl->httpStatusCode} | Content: {$curl->response}" . LOG_LF);
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
        for ($i = 0, $max_try = 3; $i <= $max_try; $i++) {
            if ($this->getStream($hash)) {
                break;
            } elseif ($i == $max_try) {
                return false;
            }
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
                file_put_contents($m3uFile, $curl->response);
            }
        } else {
            bb_log("TorrServer (ERROR) [$this->url]: Response code: {$curl->httpStatusCode} | Content: {$curl->response}" . LOG_LF);
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
        // Remove ffprobe data from cache
        CACHE('tr_cache')->rm("ffprobe_m3u_$attach_id");

        // Unlink .m3u file
        $m3uFile = get_attachments_dir() . '/' . self::M3U['prefix'] . $attach_id . self::M3U['extension'];
        if (is_file($m3uFile)) {
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
     * @param int|string $attach_id
     * @return mixed
     */
    public function getFfpInfo(string $hash, int $index, int|string $attach_id): mixed
    {
        global $bb_cfg;

        if (!$response = CACHE('tr_cache')->get("ffprobe_m3u_$attach_id")) {
            $response = new stdClass();
        }

        if (!isset($response->{$index})) {
            // Make stream call to store torrent in memory
            for ($i = 0, $max_try = 3; $i <= $max_try; $i++) {
                if ($this->getStream($hash)) {
                    break;
                } elseif ($i == $max_try) {
                    return false;
                }
            }

            $curl = new Curl();
            $curl->setTimeout($bb_cfg['torr_server']['timeout']);

            $curl->setHeader('Accept', 'application/json');
            $curl->get($this->url . $this->endpoints['ffprobe'] . '/' . $hash . '/' . $index);
            $response->{$index} = $curl->response;
            if ($curl->httpStatusCode === 200 && !empty($response->{$index})) {
                CACHE('tr_cache')->set("ffprobe_m3u_$attach_id", $response, 3600);
            } else {
                bb_log("TorrServer (ERROR) [$this->url]: Response code: {$curl->httpStatusCode}" . LOG_LF);
            }
            $curl->close();
        }

        return $response;
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
            bb_log("TorrServer (ERROR) [$this->url]: Response code: {$curl->httpStatusCode} | Content: {$curl->response}" . LOG_LF);
        }
        $curl->close();

        return $isSuccess;
    }
}
