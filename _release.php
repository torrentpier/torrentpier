<?php
/**
 * TorrentPier – Bull-powered BitTorrent tracker engine
 *
 * @copyright Copyright (c) 2005-2025 TorrentPier (https://torrentpier.com)
 * @link      https://github.com/torrentpier/torrentpier for the canonical source repository
 * @license   https://github.com/torrentpier/torrentpier/blob/master/LICENSE MIT License
 */

define('BB_ROOT', __DIR__ . DIRECTORY_SEPARATOR);
define('BB_PATH', BB_ROOT);

// Check CLI mode
if (php_sapi_name() !== 'cli') {
    die('Please run <code style="background:#222;color:#00e01f;padding:2px 6px;border-radius:3px;">php ' . basename(__FILE__) . '</code> in CLI mode');
}

// Get all constants
require_once BB_ROOT . 'library/defines.php';

// Include CLI functions
require INC_DIR . '/functions_cli.php';

$configFile = BB_PATH . '/library/config.php';

if (!is_file($configFile)) {
    out("- Config file " . basename($configFile) . " not found", 'error');
}
if (!is_readable($configFile)) {
    out("- Config file " . basename($configFile) . " is not readable", 'error');
}
if (!is_writable($configFile)) {
    out("- Config file " . basename($configFile) . " is not writable", 'error');
}

// Welcoming message
out("--- Release creation tool ---\n", 'info');

// Ask for version
fwrite(STDOUT, "Enter version number (e.g, v2.4.0): ");
$version = trim(fgets(STDIN));

// Ask for release date or use today's date
fwrite(STDOUT, "Enter release date (e.g. 25-05-2025), leave empty to use today's date: ");
$date = trim(fgets(STDIN));

if (empty($date)) {
    $date = date('d-m-Y');
    out("- Using current date: $date", 'info');
} else {
    // Validate date format (dd-mm-yyyy)
    $dateObj = DateTime::createFromFormat('d-m-Y', $date);
    if (!$dateObj || $dateObj->format('d-m-Y') !== $date) {
        out("Invalid date format. Expected format: DD-MM-YYYY", 'error');
    }

    out("- Using date: $date", 'info');
}

$content = file_get_contents($configFile);
$content = preg_replace(
    "/\\\$bb_cfg\['tp_version'\]\s*=\s*'[^']*';/",
    "\$bb_cfg['tp_version'] = '$version';",
    $content
);
$content = preg_replace(
    "/\\\$bb_cfg\['tp_release_date'\]\s*=\s*'[^']*';/",
    "\$bb_cfg['tp_release_date'] = '$date';",
    $content
);
file_put_contents($configFile, $content);
