<?php
/**
 * TorrentPier – Bull-powered BitTorrent tracker engine
 *
 * @copyright Copyright (c) 2005-2024 TorrentPier (https://torrentpier.com)
 * @link      https://github.com/torrentpier/torrentpier for the canonical source repository
 * @license   https://github.com/torrentpier/torrentpier/blob/master/LICENSE MIT License
 */

if (!defined('BB_ROOT')) {
    die(basename(__FILE__));
}

// Get all constants
require_once BB_PATH . '/library/defines.php';

// Composer
if (!is_file(BB_PATH . '/vendor/autoload.php')) {
    die('🔩 Manual install: <a href="https://getcomposer.org/download/" target="_blank" rel="noreferrer" style="color:#0a25bb;">Install composer</a> and run <code style="background:#222;color:#00e01f;padding:2px 6px;border-radius:3px;">composer install</code>.<br>☕️ Quick install: Run <code style="background:#222;color:#00e01f;padding:2px 6px;border-radius:3px;">php install.php</code> in CLI mode.');
}
require_once BB_PATH . '/vendor/autoload.php';

/**
 * Gets the value of an environment variable.
 *
 * @param string $key
 * @param mixed|null $default
 * @return mixed
 */
function env(string $key, mixed $default = null): mixed
{
    return \TorrentPier\Env::get($key, $default);
}

// Load ENV
try {
    $dotenv = Dotenv\Dotenv::createMutable(BB_PATH);
    $dotenv->load();
} catch (\Dotenv\Exception\InvalidPathException $pathException) {
    if (!isCommandLineInterface()) {
        die('🔩 Manual install: Rename from <code style="background:#222;color:#00e01f;padding:2px 6px;border-radius:3px;">.env.example</code> to <code style="background:#222;color:#00e01f;padding:2px 6px;border-radius:3px;">.env</code>, and configure it.<br>☕️ Quick install: Run <code style="background:#222;color:#00e01f;padding:2px 6px;border-radius:3px;">php install.php</code> in CLI mode.');
    }
}

// Load config
require_once BB_PATH . '/library/config.php';

// Local config
if (is_file(BB_PATH . '/library/config.local.php')) {
    require_once BB_PATH . '/library/config.local.php';
}

function isCommandLineInterface(): bool
{
    return (php_sapi_name() === 'cli');
}
