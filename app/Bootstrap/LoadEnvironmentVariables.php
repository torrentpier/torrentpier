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

use Dotenv\Dotenv;
use Dotenv\Exception\InvalidPathException;
use TorrentPier\Application;

/**
 * Load environment variables and define base constants
 *
 * This bootstrapper handles:
 * - GLOBALS injection protection
 * - Base time constants (TIMESTART, TIMENOW)
 * - Path constants (BB_PATH, BB_ROOT)
 * - Security headers (X-Frame-Options)
 * - Timezone configuration (UTC)
 * - Environment variables from .env file
 */
class LoadEnvironmentVariables
{
    /**
     * Bootstrap the environment
     */
    public function bootstrap(Application $app): void
    {
        // Protection against GLOBALS injection attack
        if (isset($_REQUEST['GLOBALS'])) {
            die();
        }

        // Base time constants
        if (!\defined('TIMESTART')) {
            \define('TIMESTART', microtime(true));
            \define('TIMENOW', time());
            \define('BB_PATH', $app->basePath());
        }

        // BB_ROOT for legacy compatibility (used as a security guard in legacy files)
        if (!\defined('BB_ROOT')) {
            \define('BB_ROOT', './');
        }

        // Security headers (only for HTTP requests)
        if (PHP_SAPI !== 'cli') {
            header('X-Frame-Options: SAMEORIGIN');
        }

        // Timezone
        date_default_timezone_set('UTC');

        // Load .env file
        try {
            $dotenv = Dotenv::createMutable($app->basePath());
            $dotenv->load();
        } catch (InvalidPathException) {
            if (PHP_SAPI !== 'cli') {
                die('Setup required: Run <code>php bull app:install</code> to configure TorrentPier');
            }
            // In CLI mode, allow running without .env for install command
        }
    }
}
