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
     * @var int|string
     */
    public int|string $targetVersion;

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
    private string $savePath;

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
            throw new Exception('Empty JSON response');
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
     * @param string|int $targetVersion
     * @return bool
     */
    public function download(string $path, string|int $targetVersion): bool
    {
        $this->targetVersion = $targetVersion;
        $this->savePath = $path;

        return false;
    }
}
