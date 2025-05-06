<?php
/**
 * TorrentPier – Bull-powered BitTorrent tracker engine
 *
 * @copyright Copyright (c) 2005-2025 TorrentPier (https://torrentpier.com)
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
     * Stream context
     *
     * @var array
     */
    private const STREAM_CONTEXT = [
        'http' => [
            'header' => 'User-Agent: ' . APP_NAME . '-' . TIMENOW,
            'timeout' => 10,
            'ignore_errors' => true
        ]
    ];

    /**
     * Updater constructor
     *
     * @throws Exception
     */
    public function __construct()
    {
        $context = stream_context_create(self::STREAM_CONTEXT);
        $response = file_get_contents(UPDATER_URL, context: $context);

        if ($response !== false) {
            $this->jsonResponse = json_decode(mb_convert_encoding($response, DEFAULT_CHARSET, mb_detect_encoding($response)), true);
        }

        // Empty JSON result
        if (empty($this->jsonResponse)) {
            throw new Exception('Empty JSON response');
        }

        // Response message from GitHub
        if (isset($this->jsonResponse['message'])) {
            throw new Exception($this->jsonResponse['message']);
        }
    }

    /**
     * Download build from GitHub
     *
     * @param string $path
     * @param string $targetVersion
     * @param bool $force
     * @return bool
     * @throws Exception
     */
    public function download(string $path, string $targetVersion = 'latest', bool $force = false): bool
    {
        $this->targetVersion = $targetVersion;

        if ($this->targetVersion === 'latest') {
            $versionInfo = $this->getLastVersion();
        } else {
            $targetIndex = array_search($this->targetVersion, array_column($this->jsonResponse, 'tag_name'));
            $versionInfo = is_numeric($targetIndex) ? $this->jsonResponse[$targetIndex] : false;
        }

        if (empty($versionInfo)) {
            throw new Exception('No version info');
        }

        $downloadLink = $versionInfo['assets'][0]['browser_download_url'];
        $this->savePath = $path . $versionInfo['assets'][0]['name'];

        if (!is_file($this->savePath) || $force) {
            $context = stream_context_create(self::STREAM_CONTEXT);
            $getFile = file_get_contents($downloadLink, context: $context);
            if ($getFile === false) {
                return false;
            }

            // Save build file
            file_put_contents($this->savePath, $getFile);
            if (!is_file($this->savePath)) {
                throw new Exception("Can't save TorrentPier build file");
            }
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
     * Returns information of latest TorrentPier version available
     *
     * @param bool $allowPreReleases
     * @return array
     */
    public function getLastVersion(bool $allowPreReleases = true): array
    {
        if (!$allowPreReleases) {
            foreach ($this->jsonResponse as $index) {
                if (isset($index['prerelease']) && $index['prerelease']) {
                    continue;
                }

                return $index;
            }
        }

        return $this->jsonResponse[0];
    }
}
