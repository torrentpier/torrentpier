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
 * Final application bootstrap
 *
 * This bootstrapper handles:
 * - Board initialization (init_bb.php) for web requests
 * - Skipped for CLI (init_bb.php uses output buffering)
 */
class BootApplication
{
    /**
     * Bootstrap the application
     */
    public function bootstrap(Application $app): void
    {
        // Skip for CLI - init_bb.php starts output buffering
        if (PHP_SAPI === 'cli' || PHP_SAPI === 'phpdbg') {
            return;
        }

        require_once INC_DIR . '/init_bb.php';
    }
}
