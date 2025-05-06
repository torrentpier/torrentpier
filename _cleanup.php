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

$items = [
    '.github',
    '.cliffignore',
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

foreach ($items as $item) {
    $path = BB_ROOT . $item;

    if (is_file($path)) {
        removeFile($path);
    } elseif (is_dir($path)) {
        removeDir($path);
    }
}

/**
 * Remove target file
 *
 * @param string $file Path to file
 */
function removeFile(string $file): void
{
    if (unlink($file)) {
        echo "- File removed: $file" . PHP_EOL;
    } else {
        echo "- File cannot be removed: $file" . PHP_EOL;
        exit;
    }
}

/**
 * Remove folder (recursively)
 *
 * @param string $dir Path to folder
 */
function removeDir(string $dir): void
{
    $it = new RecursiveDirectoryIterator($dir, FilesystemIterator::SKIP_DOTS);
    $files = new RecursiveIteratorIterator($it, RecursiveIteratorIterator::CHILD_FIRST);

    foreach ($files as $file) {
        if ($file->isDir()) {
            removeDir($file->getPathname());
        } else {
            removeFile($file->getPathname());
        }
    }

    if (rmdir($dir)) {
        echo "- Folder removed: $dir" . PHP_EOL;
    } else {
        echo "- Folder cannot be removed: $dir" . PHP_EOL;
        exit;
    }
}
