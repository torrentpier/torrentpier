<?php
/**
 * TorrentPier – Bull-powered BitTorrent tracker engine
 *
 * @copyright Copyright (c) 2005-2025 TorrentPier (https://torrentpier.com)
 * @link      https://github.com/torrentpier/torrentpier for the canonical source repository
 * @license   https://github.com/torrentpier/torrentpier/blob/master/LICENSE MIT License
 */

if (!defined('BB_ROOT')) {
    define('BB_ROOT', __DIR__ . DIRECTORY_SEPARATOR);
}

// Check CLI mode
if (php_sapi_name() !== 'cli') {
    exit;
}

// Get all constants
require_once BB_PATH . '/library/defines.php';

// Include CLI functions
require INC_DIR . '/functions_cli.php';

$items = [
    '.github',
    '.cliffignore',
    '.editorconfig',
    '.gitignore',
    '.styleci.yml',
    'CHANGELOG.md',
    'cliff.toml',
    'CODE_OF_CONDUCT.md',
    'CONTRIBUTING.md',
    'crowdin.yml',
    'HISTORY.md',
    'README.md',
    'SECURITY.md'
];

foreach ($items as $item) {
    $path = BB_ROOT . $item;

    if (is_file($path)) {
        removeFile($path);
    } elseif (is_dir($path)) {
        removeDir($path);
    }
}
