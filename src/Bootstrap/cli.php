<?php
/**
 * TorrentPier – Bull-powered BitTorrent tracker engine
 *
 * Lightweight CLI Bootstrap
 * This file provides minimal bootstrapping for CLI commands,
 * loading only what's necessary without the full web application.
 *
 * @copyright Copyright (c) 2005-2025 TorrentPier (https://torrentpier.com)
 * @link      https://github.com/torrentpier/torrentpier for the canonical source repository
 * @license   https://github.com/torrentpier/torrentpier/blob/master/LICENSE MIT License
 */

if (PHP_SAPI !== 'cli') {
    die('This script must be run from the command line.');
}

define('TIMESTART', microtime(true));
define('TIMENOW', time());
define('BB_PATH', dirname(__DIR__, 2));

if (!defined('BB_ROOT')) {
    define('BB_ROOT', BB_PATH . '/');
}

// Mock server variables for CLI
$_SERVER['REMOTE_ADDR'] = '127.0.0.1';
$_SERVER['HTTP_USER_AGENT'] = 'Bull CLI';
$_SERVER['HTTP_REFERER'] = '';
$_SERVER['SERVER_NAME'] = 'localhost';
$_SERVER['SERVER_ADDR'] = '127.0.0.1';

date_default_timezone_set('UTC');

// Get all constants
require_once BB_PATH . '/library/defines.php';

// Composer autoloader
if (!is_file(BB_PATH . '/vendor/autoload.php')) {
    die("Error: Composer dependencies not installed.\nRun: composer install\n");
}
require_once BB_PATH . '/vendor/autoload.php';

/**
 * Gets the value of an environment variable.
 */
function env(string $key, mixed $default = null): mixed
{
    return \TorrentPier\Env::get($key, $default);
}

// Load ENV (optional for CLI - migrations need DB credentials)
if (file_exists(BB_PATH . '/.env')) {
    $dotenv = Dotenv\Dotenv::createMutable(BB_PATH);
    $dotenv->load();
}

// Load main config
if (file_exists(BB_PATH . '/library/config.php')) {
    require_once BB_PATH . '/library/config.php';
}

// Load local config overrides
if (file_exists(BB_PATH . '/library/config.local.php')) {
    require_once BB_PATH . '/library/config.local.php';
}

// Make $bb_cfg globally available
global $bb_cfg;

/**
 * Microseconds timestamp
 */
function utime(): float
{
    return microtime(true);
}

