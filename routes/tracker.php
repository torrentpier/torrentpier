<?php

/**
 * TorrentPier – Bull-powered BitTorrent tracker engine
 *
 * @copyright Copyright (c) 2005-2025 TorrentPier (https://torrentpier.com)
 * @link      https://github.com/torrentpier/torrentpier for the canonical source repository
 * @license   https://github.com/torrentpier/torrentpier/blob/master/LICENSE MIT License
 */

declare(strict_types=1);

use App\Http\Controllers\Tracker\AnnounceController;
use App\Http\Controllers\Tracker\ScrapeController;
use App\Http\Middleware\TrackerMiddleware;
use TorrentPier\Router\Router;

/**
 * Route definitions for TorrentPier tracker application
 *
 * All routes are prefixed with /bt and use TrackerMiddleware
 * for tracker initialization.
 */
return static function (Router $router): void {
    $router->group('/bt', function ($group) {
        // Announce endpoint
        $group->map('GET', '/announce.php', AnnounceController::class);
        $group->map('GET', '/announce', AnnounceController::class);

        // Scrape endpoint
        $group->map('GET', '/scrape.php', ScrapeController::class);
        $group->map('GET', '/scrape', ScrapeController::class);

        // Legacy index.php and root paths -> announce
        $group->map('GET', '/index.php', AnnounceController::class);
        $group->map('GET', '/', AnnounceController::class);
        $group->map('GET', '', AnnounceController::class);
    })->middleware(new TrackerMiddleware);
};
