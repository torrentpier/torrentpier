<?php

/**
 * TorrentPier – Bull-powered BitTorrent tracker engine
 *
 * @copyright Copyright (c) 2005-2025 TorrentPier (https://torrentpier.com)
 * @link      https://github.com/torrentpier/torrentpier for the canonical source repository
 * @license   https://github.com/torrentpier/torrentpier/blob/master/LICENSE MIT License
 */

declare(strict_types=1);

namespace App\Http\Controllers\Tracker;

use App\Http\Controllers\Tracker\Concerns\TrackerResponses;
use Illuminate\Contracts\Container\BindingResolutionException;
use Psr\Http\Message\ResponseInterface;
use Psr\Http\Message\ServerRequestInterface;
use TorrentPier\Http\Response\BencodeResponse;

/**
 * BitTorrent Scrape Controller
 *
 * Handles scrape requests from BitTorrent clients to get torrent statistics.
 * Returns seeder/leecher counts and download statistics for requested torrents.
 */
class ScrapeController
{
    use TrackerResponses;

    /**
     * Handle scrape request
     * @throws BindingResolutionException
     */
    public function __invoke(ServerRequestInterface $request): ResponseInterface
    {
        // Check if scrape is enabled
        if (!config()->get('tracker.scrape')) {
            return $this->msgDie('Please disable SCRAPE!');
        }

        $queryParams = $request->getQueryParams();
        $queryString = $request->getServerParams()['QUERY_STRING'] ?? '';

        // Recover info_hash (handle quirky ?info_hash parameter)
        $infoHash = $this->extractInfoHash($queryParams);

        // Verify info_hash was provided
        if ($infoHash === null) {
            return $this->msgDie('info_hash was not provided');
        }

        // Store info hash in hex format for error messages
        $infoHashHex = bin2hex($infoHash);

        // Check info_hash length (must be 20 bytes)
        if (\strlen($infoHash) !== 20) {
            $displayHash = mb_check_encoding($infoHash, DEFAULT_CHARSET) ? $infoHash : $infoHashHex;

            return $this->msgDie('Invalid info_hash: ' . $displayHash);
        }

        // Handle multiple hashes from query string
        preg_match_all('/info_hash=([^&]*)/i', $queryString, $infoHashArray);

        $torrents = [];
        $infoHashes = [];

        // Process each hash - check cache first, collect uncached for DB query
        foreach ($infoHashArray[1] as $hash) {
            $decodedHash = urldecode($hash);

            if (\strlen($decodedHash) !== 20) {
                continue;
            }

            $cacheKey = SCRAPE_LIST_PREFIX . bin2hex($decodedHash);
            $scrapeCache = CACHE('tr_cache')->get($cacheKey);

            if ($scrapeCache) {
                $infoKey = array_key_first($scrapeCache);
                $torrents['files'][$infoKey] = $scrapeCache[$infoKey];
            } else {
                $infoHashes[] = bin2hex($decodedHash);
            }
        }

        // Query database for uncached hashes
        $infoHashCount = \count($infoHashes);

        if ($infoHashCount > 0) {
            // Limit number of hashes to prevent abuse
            $maxScrapes = config()->get('tracker.max_scrapes');
            if ($infoHashCount > $maxScrapes) {
                $infoHashes = \array_slice($infoHashes, 0, $maxScrapes);
            }

            $infoHashesUnhex = implode("'), UNHEX('", $infoHashes);

            /**
             * Currently torrent clients send truncated v2 hashes (the design raises questions).
             * @see https://github.com/bittorrent/bittorrent.org/issues/145#issuecomment-1720040343
             */
            $infoHashWhere = "tor.info_hash IN (UNHEX('{$infoHashesUnhex}')) OR SUBSTRING(tor.info_hash_v2, 1, 20) IN (UNHEX('{$infoHashesUnhex}'))";

            $sql = '
                SELECT tor.info_hash, tor.info_hash_v2, tor.complete_count, snap.seeders, snap.leechers
                FROM ' . BB_BT_TORRENTS . ' tor
                LEFT JOIN ' . BB_BT_TRACKER_SNAP . " snap ON (snap.topic_id = tor.topic_id)
                WHERE {$infoHashWhere}
            ";

            $scrapes = DB()->fetch_rowset($sql);

            if (!empty($scrapes)) {
                foreach ($scrapes as $scrape) {
                    $hashV1 = !empty($scrape['info_hash']) ? $scrape['info_hash'] : '';
                    $hashV2 = !empty($scrape['info_hash_v2']) ? substr($scrape['info_hash_v2'], 0, 20) : '';

                    // Determine which hash version was requested
                    // Replace logic to prioritize $hashV2, in case of future prioritization of v2
                    $infoHashScrape = \in_array(urlencode($hashV1), $infoHashArray[1], true) ? $hashV1 : $hashV2;

                    $torrents['files'][$infoHashScrape] = [
                        'complete' => (int)$scrape['seeders'],
                        'downloaded' => (int)$scrape['complete_count'],
                        'incomplete' => (int)$scrape['leechers'],
                    ];

                    // Cache the result
                    $cacheKey = SCRAPE_LIST_PREFIX . bin2hex($infoHashScrape);
                    $cacheData = \array_slice($torrents['files'], -1, null, true);
                    CACHE('tr_cache')->set($cacheKey, $cacheData, SCRAPE_LIST_EXPIRE);
                }
            }
        }

        // Verify if torrent registered on tracker
        if (empty($torrents)) {
            $displayHash = mb_check_encoding($infoHash, DEFAULT_CHARSET) ? $infoHash : $infoHashHex;

            return $this->msgDie('Torrent not registered, info_hash = ' . $displayHash);
        }

        return new BencodeResponse($torrents);
    }

    /**
     * Extract info_hash from query parameters
     *
     * Handles the quirky ?info_hash parameter that some clients send
     */
    private function extractInfoHash(array $queryParams): ?string
    {
        // Handle ?info_hash quirk (some clients send it with leading ?)
        if (isset($queryParams['?info_hash']) && !isset($queryParams['info_hash'])) {
            return (string)$queryParams['?info_hash'];
        }

        if (isset($queryParams['info_hash'])) {
            return (string)$queryParams['info_hash'];
        }

        return null;
    }
}
