<?php
/**
 * TorrentPier – Bull-powered BitTorrent tracker engine
 *
 * @copyright Copyright (c) 2005-2025 TorrentPier (https://torrentpier.com)
 * @link      https://github.com/torrentpier/torrentpier for the canonical source repository
 * @license   https://github.com/torrentpier/torrentpier/blob/master/LICENSE MIT License
 */

// Check CLI mode
if (PHP_SAPI != 'cli') {
    die('This script can only be run from command line');
}

define('BB_ROOT', dirname(__DIR__) . '/');
define('BB_PATH', BB_ROOT);

define('BB_TOPICS', 'bb_topics');
define('BB_POSTS', 'bb_posts');
define('BB_POSTS_TEXT', 'bb_posts_text');

if (!file_exists(BB_ROOT . 'library/defines.php')) {
    die('Error: library/defines.php not found. Please check BB_ROOT path: ' . BB_ROOT . "\n");
}

if (!file_exists(BB_ROOT . 'vendor/autoload.php')) {
    die('Error: vendor/autoload.php not found. Run "composer install" first.' . "\n");
}

require_once BB_ROOT . 'library/defines.php';
require_once BB_ROOT . 'vendor/autoload.php';

use Dotenv\Dotenv;

// Load environment variables
if (file_exists(BB_PATH . '.env')) {
    $dotenv = Dotenv::createMutable(BB_PATH);
    $dotenv->load();
}

// Helper function for environment variables
function env(string $key, mixed $default = null): mixed
{
    $value = $_ENV[$key] ?? getenv($key);
    if ($value === false) {
        return $default;
    }
    return $value;
}

/**
 * Get the Database instance
 *
 * @param string $db_alias
 * @return \TorrentPier\Database\Database
 */
function DB(string $db_alias = 'db'): \TorrentPier\Database\Database
{
    return TorrentPier\Database\DatabaseFactory::getInstance($db_alias);
}

// Initialize manticore
$bb_cfg['manticore_host'] = '127.0.0.1';
$bb_cfg['manticore_port'] = 9306;
$manticore = new \TorrentPier\ManticoreSearch($bb_cfg);
$manticore->initialLoad();
