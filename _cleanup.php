<?php
/**
 * TorrentPier – Bull-powered BitTorrent tracker engine
 *
 * @copyright Copyright (c) 2005-2025 TorrentPier (https://torrentpier.com)
 * @link      https://github.com/torrentpier/torrentpier for the canonical source repository
 * @license   https://github.com/torrentpier/torrentpier/blob/master/LICENSE MIT License
 */

define('BB_ROOT', __DIR__ . '/');

$list = [
    '.github',
    '.editorconfig',
    '.gitignore',
    '.styleci.yml',
    'CHANGELOG.md',
    'cliff.toml',
    'cliff-releases.toml',
    'CODE_OF_CONDUCT.md',
    'CONTRIBUTING.md',
    'crowdin.yml',
    'HISTORY.md',
    'README.md',
    'SECURITY.md'
];

foreach ($list as $file) {
    if (is_file(BB_ROOT . $file)) {
        if (unlink(BB_ROOT . $file)) {
            echo '[INFO] File removed:' . $file . PHP_EOL;
        } else {
            echo '[ERROR] File cannot be removed:' . $file . PHP_EOL;
            exit;
        }
    } elseif (is_dir(BB_ROOT . $file)) {
        if (rmdir(BB_ROOT . $file)) {
            echo '[INFO] Folder removed:' . $file . PHP_EOL;
        } else {
            echo '[ERROR] Folder cannot be removed:' . $file . PHP_EOL;
            exit;
        }
    }
}
