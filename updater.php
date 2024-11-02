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
cli_out('- Please make a backup before upgrading your TorrentPier!!!', 'error');
cli_out('- Then extract (with replace) the files of the archive (new build) you downloaded to the root directory with TorrentPier installed', 'warning');

// Backup confirmation
if (!cli_confirm("\nHave you already backed up your project files? [y/N]: ")) {
    exit;
}
if (!cli_confirm('You have extracted the files of the new version of TorrentPier you downloaded? [y/N]: ')) {
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
    cli_out("\n- It seems you have not unpacked the archive with the latest version of TorrentPier", 'warning');
    exit;
} elseif ($updaterFile['previous_version']['short_code'] > VERSION_CODE) {
    exit;
}

// Set 'in updater' status
define('IN_UPDATER', true);

// Define version codes
$fromVersion = $updaterFile['previous_version']['short_code'] + 1;
$targetVersion = $updaterFile['latest_version']['short_code'];
$versionsRange = range($fromVersion, $targetVersion);

// Define some updater constants
define('UPDATES_DIR', BB_ROOT . 'install/upgrade/');
define('UPDATE_SCRIPT_NAME', 'update.php');
define('UPDATE_SQL_NAME', 'changes.sql');

// Get changes
foreach ($versionsRange as $version) {
    $targetUpdate = UPDATES_DIR . $version;

    // Check update directory exists
    if (!is_dir($targetUpdate)) {
        continue;
    }

    // Check updater script exists
    if (is_file($targetUpdate . '/' . UPDATE_SCRIPT_NAME)) {
        cli_out("\n- Updater script for ... found!", 'success');
        $updaterScript = require_once $targetUpdate . '/' . UPDATE_SCRIPT_NAME;

        // Deleting old files
        if (!empty($updaterScript['removed_files'])) {
            cli_out("- Removing old files from previous version...", 'info');
            foreach ($updaterScript['removed_files'] as $file) {
                $fileToRemove = BB_ROOT . $file;
                if (is_file($fileToRemove)) {
                    unlink($fileToRemove);
                    cli_out("\n- $fileToRemove successfully removed!\n", 'success');
                }
            }
        }
    }

    // Checking SQL dump
    $dumpPath = $targetUpdate . '/' . UPDATE_SQL_NAME;
    if (is_file($dumpPath)) {
        if (!is_readable($dumpPath)) {
            cli_out('- SQL file not readable', 'error');
            exit;
        }
        cli_out('- SQL file found and readable!', 'success');

        // Inserting SQL dump
        cli_out('- Start applying SQL changes...', 'info');
        $tempLine = '';
        foreach (file($dumpPath) as $line) {
            if (str_starts_with($line, '--') || $line == '') {
                continue;
            }

            $tempLine .= $line;
            if (str_ends_with(trim($line), ';')) {
                if (!$conn->query($tempLine)) {
                    cli_out("- Error performing query: $tempLine", 'error');
                    exit;
                }
                $tempLine = '';
            }
        }
    }
}
