<?php
/**
 * TorrentPier – Bull-powered BitTorrent tracker engine
 *
 * @copyright Copyright (c) 2005-2024 TorrentPier (https://torrentpier.com)
 * @link      https://github.com/torrentpier/torrentpier for the canonical source repository
 * @license   https://github.com/torrentpier/torrentpier/blob/master/LICENSE MIT License
 */

namespace TorrentPier;

/**
 * Class Updater
 * @package TorrentPier
 */
class Updater
{
    /**
     * Target version of TorrentPier
     *
     * @var int
     */
    public int $targetVersion;

    /**
     * Json response
     *
     * @var array
     */
    private array $jsonResponse = [];

    /**
     * Updater constructor
     *
     * @param int $targetVersion
     * @throws \Exception
     */
    public function __construct(int $targetVersion)
    {
        $this->targetVersion = $targetVersion;

        $context = stream_context_create(['http' => ['header' => 'User-Agent: ' . APP_NAME, 'timeout' => 10, 'ignore_errors' => true]]);
        $response = file_get_contents(UPDATER_URL, context: $context);

        if ($response !== false) {
            $this->jsonResponse = json_decode(utf8_encode($response), true);
        }

        // Empty JSON result
        if (empty($this->jsonResponse)) {
            throw new \Exception('Empty JSON response');
        }

        // Response message from GitHub
        if (isset($json_response['message'])) {
            throw new \Exception($json_response['message']);
        }
    }

    /**
     * Download build from GitHub
     *
     * @param string $path
     * @return bool
     */
    public function download(string $path): bool
    {
        return false;
    }
}
