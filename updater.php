<?php
/**
 * TorrentPier – Bull-powered BitTorrent tracker engine
 *
 * @copyright Copyright (c) 2005-2024 TorrentPier (https://torrentpier.com)
 * @link      https://github.com/torrentpier/torrentpier for the canonical source repository
 * @license   https://github.com/torrentpier/torrentpier/blob/master/LICENSE MIT License
 */

define('BB_ROOT', __DIR__ . '/');
define('BB_PATH', __DIR__);

// Check CLI mode
if (php_sapi_name() !== 'cli') {
    die('Please run <code style="background:#222;color:#00e01f;padding:2px 6px;border-radius:3px;">php ' . basename(__FILE__) . '</code> in CLI mode');
}

// Get all constants
require_once BB_ROOT . '/library/defines.php';

// Include functions
require_once INC_DIR . '/functions.php';
require_once INC_DIR . '/functions_cli.php';

// Welcoming message
cli_out("--- TorrentPier Updater ---\n", 'info');

// Backup note
cli_out('- Please make a backup before upgrading your TorrentPier!!!', 'warning');

// Backup confirmation
if (!cli_confirm("\nHave you already backed up your project files? [y/N]: ")) {
    exit;
}
if (!cli_confirm('Are you sure you have created a backed up of your project files?! [y/N]: ')) {
    exit;
}

// Get information from updater file
cli_out(sprintf("\n- Trying to get information from %s file...", basename(UPDATER_FILE)), 'info');
$updaterFile = readUpdaterFile();
if (empty($updaterFile)) {
    cli_out('- Hmm, it seems you have the latest available version of TorrentPier', 'info');
    exit;
}
cli_out(sprintf('- Success! %s file found!', basename(UPDATER_FILE)), 'success');

// Check versions
if (VERSION_CODE == $updaterFile['previous_version']['short_code']) {
    cli_out('- It seems you have not unpacked the archive with the latest version of TorrentPier', 'warning');
    exit;
} elseif ($updaterFile['previous_version']['short_code'] > VERSION_CODE) {
    exit;
}

// Set 'in updater' status
define('IN_UPDATER', true);

// Get changes
foreach ($updaterFile as $version) {
}
