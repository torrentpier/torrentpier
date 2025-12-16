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
use TorrentPier\Router\Router;

/**
 * Tracker route definitions
 *
 * All routes are prefixed with /bt and use BootTracker middleware
 * for tracker initialization.
 */
return static function (Router $router): void {
    $router->group('/bt', function ($group) {
        // Announce endpoint
        $group->get('/announce.php', AnnounceController::class);
        $group->get('/announce', AnnounceController::class);

        // Scrape endpoint
        $group->get('/scrape.php', ScrapeController::class);
        $group->get('/scrape', ScrapeController::class);

        // Legacy index.php and root paths -> announce
        $group->get('/index.php', AnnounceController::class);
        $group->get('/', AnnounceController::class);
        $group->get('', AnnounceController::class);
    })->middleware('tracker');
};
