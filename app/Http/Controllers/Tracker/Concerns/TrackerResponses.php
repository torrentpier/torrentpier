<?php

/**
 * TorrentPier – Bull-powered BitTorrent tracker engine
 *
 * @copyright Copyright (c) 2005-2025 TorrentPier (https://torrentpier.com)
 * @link      https://github.com/torrentpier/torrentpier for the canonical source repository
 * @license   https://github.com/torrentpier/torrentpier/blob/master/LICENSE MIT License
 */

declare(strict_types=1);

namespace App\Http\Controllers\Tracker\Concerns;

use Illuminate\Contracts\Container\BindingResolutionException;
use Illuminate\Support\Str;
use Psr\Http\Message\ResponseInterface;
use TorrentPier\Http\Response\BencodeResponse;

/**
 * Tracker Response Methods
 *
 * Provides common response methods for BitTorrent tracker controllers.
 * All methods return PSR-7 ResponseInterface instead of using die().
 */
trait TrackerResponses
{
    /**
     * Exit with a warning message (non-fatal)
     *
     * Used when the tracker wants to inform the client about something
     * without causing an error.
     */
    protected function silentExit(string $msg = ''): ResponseInterface
    {
        return new BencodeResponse([
            'warning message' => Str::squish($msg),
        ]);
    }

    /**
     * Exit with a failure reason (fatal error)
     *
     * Used when the request cannot be processed due to an error.
     */
    protected function errorExit(string $msg = ''): ResponseInterface
    {
        return new BencodeResponse([
            'failure reason' => Str::squish($msg),
        ]);
    }

    /**
     * Exit with a failure message and interval
     *
     * Standard tracker error response that tells the client to retry later.
     */
    protected function msgDie(string $msg): ResponseInterface
    {
        return new BencodeResponse([
            'interval' => 1800,
            'failure reason' => $msg,
        ]);
    }

    /**
     * Exit with a dummy response
     *
     * Used to send a minimal valid response when actual data is not needed.
     * Often used to prevent re-announce spam or when tracker is overloaded.
     *
     * @param int $interval Announce interval in seconds
     * @param array $cacheDict Optional cached peer statistics
     * @throws BindingResolutionException
     */
    protected function dummyExit(int $interval = 1800, array $cacheDict = []): ResponseInterface
    {
        $remoteAddr = request()->getClientIp() ?? '127.0.0.1';

        $output = [
            'interval' => $interval,
            'peers' => pack('Nn', 0, 0),
            'external ip' => inet_pton($remoteAddr),
        ];

        if (!empty($cacheDict)) {
            if (isset($cacheDict['complete'])) {
                $output['complete'] = $cacheDict['complete'];
            }
            if (isset($cacheDict['incomplete'])) {
                $output['incomplete'] = $cacheDict['incomplete'];
            }
            if (isset($cacheDict['downloaded'])) {
                $output['downloaded'] = $cacheDict['downloaded'];
            }
        }

        if (isset($cacheDict['peers'])) {
            $output['peers'] = $cacheDict['peers'];
        }

        if (isset($cacheDict['peers6'])) {
            $output['peers6'] = $cacheDict['peers6'];
        }

        return new BencodeResponse($output);
    }

    /**
     * Drop fast announces (re-announce spam prevention)
     *
     * Checks if the client is announcing too frequently and returns a dummy
     * response with an adjusted interval if so.
     *
     * @param array $lpInfo Last peer info with 'update_time' key
     * @param array $lpCachedPeers Cached peer list
     * @throws BindingResolutionException
     * @return ResponseInterface|null Returns response if should exit, null to continue
     */
    protected function dropFastAnnounce(array $lpInfo, array $lpCachedPeers = []): ?ResponseInterface
    {
        $announceInterval = config()->get('tracker.announce_interval');

        // If an announce interval is correct, allow the announce
        if ($lpInfo['update_time'] < (TIMENOW - $announceInterval + 60)) {
            return null;
        }

        // Calculate a new interval and return dummy response
        $newAnnInterval = $lpInfo['update_time'] + $announceInterval - TIMENOW;

        return $this->dummyExit($newAnnInterval, $lpCachedPeers);
    }
}
