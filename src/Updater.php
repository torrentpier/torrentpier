<?php
/**
 * TorrentPier – Bull-powered BitTorrent tracker engine
 *
 * @copyright Copyright (c) 2005-2024 TorrentPier (https://torrentpier.com)
 * @link      https://github.com/torrentpier/torrentpier for the canonical source repository
 * @license   https://github.com/torrentpier/torrentpier/blob/master/LICENSE MIT License
 */

namespace TorrentPier;

use Exception;

/**
 * Class Updater
 * @package TorrentPier
 */
class Updater
{
    /**
     * Target version of TorrentPier
     *
     * @var string
     */
    public string $targetVersion;

    /**
     * Json response
     *
     * @var array
     */
    private array $jsonResponse = [];

    /**
     * Save path
     *
     * @var string
     */
    public string $savePath;

    /**
     * Updater constructor
     *
     * @throws Exception
     */
    public function __construct()
    {
        $context = stream_context_create(['http' => ['header' => 'User-Agent: ' . APP_NAME, 'timeout' => 10, 'ignore_errors' => true]]);
        $response = file_get_contents(UPDATER_URL, context: $context);

        if ($response !== false) {
            $this->jsonResponse = json_decode(utf8_encode($response), true);
        }

        // Empty JSON result
        if (empty($this->jsonResponse)) {
            return false;
        }

        // Response message from GitHub
        if (isset($json_response['message'])) {
            throw new Exception($json_response['message']);
        }

        return $this->jsonResponse;
    }

    /**
     * Download build from GitHub
     *
     * @param string $path
     * @param string $targetVersion
     * @return bool
     * @throws Exception
     */
    public function download(string $path, string $targetVersion = 'latest'): bool
    {
        $this->targetVersion = $targetVersion;

        if ($targetVersion === 'latest') {
            $versionInfo = $this->getLastVersion();
        } else {
            $targetIndex = array_search($targetVersion, array_column($this->jsonResponse, 'tag_name'));
            $versionInfo = is_numeric($targetIndex) ? $this->jsonResponse[$targetIndex] : false;
        }

        if (empty($versionInfo)) {
            return false;
        }

        $downloadLink = $versionInfo['assets'][0]['browser_download_url'];

        $getFile = file_get_contents($downloadLink);
        if ($getFile === false) {
            return false;
        }

        // Save build file
        $this->savePath = $path . $versionInfo['assets'][0]['name'];
        file_put_contents($this->savePath, $getFile);
        if (!is_file($this->savePath)) {
            throw new Exception("Can't save TorrentPier build file");
        }

        // Get MD5 checksums
        $getMD5OfRemoteFile = strtoupper(md5_file($downloadLink));
        $getMD5OfSavedFile = strtoupper(md5_file($this->savePath));

        // Compare MD5 hashes
        if ($getMD5OfRemoteFile !== $getMD5OfSavedFile) {
            throw new Exception("MD5 hashes don't match");
        }

        return true;
    }

    /**
     * Returns information of latest TorrentPier version
     *
     * @return array
     */
    public function getLastVersion(): array
    {
        return $this->jsonResponse[0];
    }
}
