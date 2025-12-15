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

use TorrentPier\Application;

/**
 * Register global helper functions
 *
 * This bootstrapper loads:
 * - library/helpers.php - Container access (app, config, DB, request, user, etc.)
 * - library/functions.php - Utility functions (bb_log, bitfields, page_cfg, etc.)
 */
class RegisterHelpers
{
    /**
     * Bootstrap helper registration
     */
    public function bootstrap(Application $app): void
    {
        $basePath = $app->basePath();

        require_once $basePath . '/library/helpers.php';
        require_once $basePath . '/library/functions.php';
    }
}
