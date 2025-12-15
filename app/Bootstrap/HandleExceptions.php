<?php

/**
 * TorrentPier – Bull-powered BitTorrent tracker engine
 *
 * @copyright Copyright (c) 2005-2025 TorrentPier (https://torrentpier.com)
 * @link      https://github.com/torrentpier/torrentpier for the canonical source repository
 * @license   https://github.com/torrentpier/torrentpier/blob/master/LICENSE MIT License
 */

declare(strict_types=1);

namespace App\Bootstrap;

use Illuminate\Contracts\Container\BindingResolutionException;
use TorrentPier\Application;
use TorrentPier\Tracy\TracyBarManager;
use TorrentPier\Whoops\WhoopsManager;

/**
 * Initialize error and exception handlers
 *
 * This bootstrapper handles:
 * - DBG_USER flag (enabled in local env or when APP_DEBUG=true)
 * - Whoops error handler initialization
 * - Tracy debug bar initialization
 */
class HandleExceptions
{
    /**
     * Bootstrap exception handlers
     * @throws BindingResolutionException
     */
    public function bootstrap(Application $app): void
    {
        // Define debug mode flag
        if (!\defined('DBG_USER')) {
            \define('DBG_USER', $app->isLocal() || env('APP_DEBUG', false));
        }

        // Initialize Whoops error handler
        $whoops = $app->make(WhoopsManager::class);
        $whoops->init();

        // Initialize Tracy debug bar
        $tracy = $app->make(TracyBarManager::class);
        $tracy->init();
    }
}
