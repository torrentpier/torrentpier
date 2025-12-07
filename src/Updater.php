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
use Psr\Http\Message\ResponseInterface;
use TorrentPier\Http\Exception\HttpClientException;
use TorrentPier\Http\HttpClient;

/**
 * Class Updater
 * @package TorrentPier
 */
class Updater
{
    /**
     * Target version of TorrentPier
     */
    public private(set) string $targetVersion;

    /**
     * Json response
     */
    private array $jsonResponse = [];

    /**
     * Save path
     */
    public private(set) string $savePath;

    /**
     * HTTP client instance
     *
     * @var HttpClient
     */
    private HttpClient $httpClient;

    /**
     * LTS version pattern (v2.8.*)
     *
     * @var string
     */
    private const string LTS_VERSION_PATTERN = '/^v2\.8\.\d+$/';

    /**
     * Updater constructor
     *
     * @throws Exception
     */
    public function __construct()
    {
        // Initialize HTTP client with 10-second timeout
        $this->httpClient = HttpClient::getInstance([
            'timeout' => 10,
        ]);

        try {
            $response = $this->httpClient->get(UPDATER_URL, [
                'headers' => [
                    'Accept' => 'application/json',
                ],
            ]);

            // Check response status
            $statusCode = $response->getStatusCode();
            if ($statusCode !== 200) {
                $this->handleGitHubError($response);
            }

            $responseBody = $response->getBody()->getContents();
            $encodedBody = mb_convert_encoding($responseBody, DEFAULT_CHARSET, mb_detect_encoding($responseBody));

            // Validate JSON before decoding
            if (!json_validate($encodedBody)) {
                throw new Exception('Invalid JSON response from GitHub API');
            }

            $this->jsonResponse = json_decode($encodedBody, true);

            // Empty JSON result
            if (empty($this->jsonResponse)) {
                throw new Exception('Empty JSON response from GitHub API');
            }

            // Response message from GitHub (error message)
            if (isset($this->jsonResponse['message'])) {
                throw new Exception('GitHub API error: ' . $this->jsonResponse['message']);
            }
        } catch (HttpClientException $e) {
            throw new Exception('Failed to fetch releases from GitHub: ' . $e->getMessage(), 0, $e);
        }
    }

    /**
     * Download the build from GitHub with streaming support
     *
     * @param string $path Target directory path
     * @param string $targetVersion Version to download ('latest' or specific version tag)
     * @param bool $force Force re-download even if a file exists
     * @param callable|null $progressCallback Progress callback function (float $percent, int $downloaded, int $total)
     * @return bool
     * @throws Exception
     */
    public function download(
        string    $path,
        string    $targetVersion = 'latest',
        bool      $force = false,
        ?callable $progressCallback = null
    ): bool {
        $this->targetVersion = $targetVersion;

        if ($this->targetVersion === 'latest') {
            $versionInfo = $this->getLastVersion();
        } else {
            $targetIndex = array_search($this->targetVersion, array_column($this->jsonResponse, 'tag_name'));
            $versionInfo = is_numeric($targetIndex) ? $this->jsonResponse[$targetIndex] : false;
        }

        if (empty($versionInfo)) {
            throw new Exception('No version info found for version: ' . $this->targetVersion);
        }

        if (empty($versionInfo['assets'][0]['browser_download_url'])) {
            throw new Exception('No download URL found in release assets');
        }

        $downloadLink = $versionInfo['assets'][0]['browser_download_url'];
        $this->savePath = $path . $versionInfo['assets'][0]['name'];

        // Download the file if it doesn't exist or a force flag is set
        if (!is_file($this->savePath) || $force) {
            try {
                $success = $this->httpClient->downloadWithProgress(
                    $downloadLink,
                    $this->savePath,
                    $progressCallback
                );

                if (!$success || !is_file($this->savePath)) {
                    throw new Exception("Failed to save TorrentPier build file to: $this->savePath");
                }
            } catch (HttpClientException $e) {
                // Clean up failed download
                if (is_file($this->savePath)) {
                    @unlink($this->savePath);
                }
                throw new Exception("Failed to download release: {$e->getMessage()}", 0, $e);
            }
        }

        // Verify a file exists and has content
        if (!is_file($this->savePath) || filesize($this->savePath) === 0) {
            throw new Exception("Downloaded file is empty or does not exist: $this->savePath");
        }

        return true;
    }

    /**
     * Returns information of latest TorrentPier LTS version (v2.8.*) available
     *
     * @param bool $allowPreReleases
     * @return array
     * @throws Exception
     */
    public function getLastVersion(bool $allowPreReleases = true): array
    {
        // Filter releases to get only LTS versions (v2.8.*)
        $ltsVersions = array_filter($this->jsonResponse, function ($release) {
            return preg_match(self::LTS_VERSION_PATTERN, $release['tag_name']);
        });

        if (empty($ltsVersions)) {
            throw new Exception('No LTS versions (v2.8.*) found');
        }

        // Sort LTS versions by version number (descending)
        usort($ltsVersions, function ($a, $b) {
            return version_compare($b['tag_name'], $a['tag_name']);
        });

        if (!$allowPreReleases) {
            // PHP 8.4: Use array_find to get the first non-prerelease version
            $stableRelease = array_find($ltsVersions, fn($release) => !($release['prerelease'] ?? false));

            if ($stableRelease === null) {
                throw new Exception('No stable LTS versions (v2.8.*) found');
            }

            return $stableRelease;
        }

        return $ltsVersions[0];
    }

    /**
     * Get all available LTS versions (v2.8.*)
     *
     * @param bool $allowPreReleases
     * @return array
     */
    public function getAllLTSVersions(bool $allowPreReleases = true): array
    {
        // Filter releases to get only LTS versions (v2.8.*)
        $ltsVersions = array_filter($this->jsonResponse, function ($release) use ($allowPreReleases) {
            $isLTSVersion = preg_match(self::LTS_VERSION_PATTERN, $release['tag_name']);

            if (!$allowPreReleases && isset($release['prerelease']) && $release['prerelease']) {
                return false;
            }

            return $isLTSVersion;
        });

        // Sort LTS versions by version number (descending)
        usort($ltsVersions, function ($a, $b) {
            return version_compare($b['tag_name'], $a['tag_name']);
        });

        return array_values($ltsVersions);
    }

    /**
     * Handle GitHub API errors based on the response status code
     *
     * @param ResponseInterface $response
     * @return void
     * @throws Exception
     */
    private function handleGitHubError(ResponseInterface $response): void
    {
        $statusCode = $response->getStatusCode();
        $body = $response->getBody()->getContents();
        $data = json_decode($body, true);
        $message = $data['message'] ?? 'Unknown GitHub API error';

        switch ($statusCode) {
            case 403:
                // Check if it's rate limiting
                if (isset($data['documentation_url']) && str_contains($data['documentation_url'], 'rate-limiting')) {
                    $resetTime = $response->getHeader('X-RateLimit-Reset')[0] ?? null;
                    $resetMsg = $resetTime ? ' (resets at ' . date('Y-m-d H:i:s', (int) $resetTime) . ')' : '';
                    throw new Exception("GitHub API rate limit exceeded$resetMsg: $message");
                }
                throw new Exception("GitHub API access forbidden: $message");

            case 404:
                throw new Exception("GitHub API resource not found: $message");

            case 500:
            case 502:
            case 503:
                throw new Exception("GitHub API server error ($statusCode): $message");

            default:
                throw new Exception("GitHub API error ($statusCode): $message");
        }
    }
}
