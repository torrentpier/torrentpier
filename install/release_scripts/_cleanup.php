<?php
/**
 * TorrentPier – Bull-powered BitTorrent tracker engine
 *
 * Cleanup script - removes development files from production installation
 *
 * @copyright Copyright (c) 2005-2025 TorrentPier (https://torrentpier.com)
 * @link      https://github.com/torrentpier/torrentpier for the canonical source repository
 * @license   https://github.com/torrentpier/torrentpier/blob/master/LICENSE MIT License
 */

if (!defined('BB_ROOT')) {
    define('BB_ROOT', dirname(__DIR__, 2) . DIRECTORY_SEPARATOR);
    define('BB_PATH', BB_ROOT);
}

// Check CLI mode
if (PHP_SAPI !== 'cli') {
    exit;
}

// ==============================================================================
// Standalone file operations (no external dependencies)
// ==============================================================================

if (!function_exists('removeFile')) {
    function removeFile(string $file, bool $silent = false): void
    {
        if (@unlink($file)) {
            if (!$silent) {
                echo "- File removed: $file" . PHP_EOL;
            }
        } else {
            if (!$silent) {
                echo "- File cannot be removed: $file" . PHP_EOL;
            }
        }
    }
}

if (!function_exists('removeDir')) {
    function removeDir(string $dir, bool $silent = false): void
    {
        if (!is_dir($dir)) {
            return;
        }

        $iterator = new RecursiveIteratorIterator(
            new RecursiveDirectoryIterator($dir, FilesystemIterator::SKIP_DOTS),
            RecursiveIteratorIterator::CHILD_FIRST
        );

        foreach ($iterator as $item) {
            if ($item->isDir()) {
                @rmdir($item->getPathname());
            } else {
                @unlink($item->getPathname());
            }
        }

        if (@rmdir($dir)) {
            if (!$silent) {
                echo "- Folder removed: $dir" . PHP_EOL;
            }
        } else {
            if (!$silent) {
                echo "- Folder cannot be removed: $dir" . PHP_EOL;
            }
        }
    }
}

// ==============================================================================
// Files to remove in production
// ==============================================================================

$items = [
    '.github',
    '.editorconfig',
    '.gitattributes',
    '.gitignore',
    '.php-cs-fixer.php',
    'CHANGELOG.md',
    'CLAUDE.md',
    'phpunit.xml',
    'README.md',
    'tests',
    'UPGRADE_GUIDE.md',
];

foreach ($items as $item) {
    $path = BB_ROOT . $item;

    if (is_file($path)) {
        removeFile($path);
    } elseif (is_dir($path)) {
        removeDir($path);
    }
}
