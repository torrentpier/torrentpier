<?php

/**
 * TorrentPier – Bull-powered BitTorrent tracker engine
 *
 * @copyright Copyright (c) 2005-2025 TorrentPier (https://torrentpier.com)
 * @link      https://github.com/torrentpier/torrentpier for the canonical source repository
 * @license   https://github.com/torrentpier/torrentpier/blob/master/LICENSE MIT License
 */

declare(strict_types=1);

namespace App\Http\Middleware\Tracker;

use Illuminate\Contracts\Container\BindingResolutionException;
use Psr\Http\Message\ResponseInterface;
use Psr\Http\Message\ServerRequestInterface;
use Psr\Http\Server\MiddlewareInterface;
use Psr\Http\Server\RequestHandlerInterface;
use TorrentPier\Http\Response\BencodeResponse;

/**
 * Boots tracker environment for /bt/* requests.
 * Sets up tracker constants and checks tracker availability.
 */
class BootTracker implements MiddlewareInterface
{
    /**
     * Process tracker request
     * @throws BindingResolutionException
     */
    public function process(ServerRequestInterface $request, RequestHandlerInterface $handler): ResponseInterface
    {
        // Initialize tracker constants
        $this->initTrackerConstants();

        // Check if the tracker is globally disabled
        if (config()->get('tracker.bt_off')) {
            return new BencodeResponse([
                'interval' => 1800,
                'failure reason' => config()->get('tracker.bt_off_reason', 'Tracker is temporarily disabled'),
            ]);
        }

        return $handler->handle($request);
    }

    /**
     * Initialize tracker-specific constants
     * @throws BindingResolutionException
     */
    private function initTrackerConstants(): void
    {
        $announceInterval = config()->get('announce_interval', 1800);
        $scrapeInterval = config()->get('scrape_interval', 300);
        $expireFactor = config()->get('tracker.expire_factor', 2.5);

        if (!\defined('PEER_HASH_EXPIRE')) {
            \define('PEER_HASH_EXPIRE', (int)round($announceInterval * (0.85 * $expireFactor)));
        }
        if (!\defined('PEERS_LIST_EXPIRE')) {
            \define('PEERS_LIST_EXPIRE', (int)round($announceInterval * 0.7));
        }
        if (!\defined('SCRAPE_LIST_EXPIRE')) {
            \define('SCRAPE_LIST_EXPIRE', (int)round($scrapeInterval * 0.7));
        }
        if (!\defined('PEER_HASH_PREFIX')) {
            \define('PEER_HASH_PREFIX', 'peer_');
        }
        if (!\defined('PEERS_LIST_PREFIX')) {
            \define('PEERS_LIST_PREFIX', 'peers_list_');
        }
        if (!\defined('SCRAPE_LIST_PREFIX')) {
            \define('SCRAPE_LIST_PREFIX', 'scrape_list_');
        }
    }
}
