<?php

/**
 * TorrentPier – Bull-powered BitTorrent tracker engine.
 *
 * @copyright Copyright (c) 2005-2025 TorrentPier (https://torrentpier.com)
 *
 * @link      https://github.com/torrentpier/torrentpier for the canonical source repository
 *
 * @license   https://github.com/torrentpier/torrentpier/blob/master/LICENSE MIT License
 */
if (!defined('BB_ROOT')) {
    exit(basename(__FILE__));
}

/**
 * Remove target file.
 *
 * @param string $file          Path to file
 * @param bool   $withoutOutput Hide output
 */
function removeFile(string $file, bool $withoutOutput = false): void
{
    if (unlink($file)) {
        if ($withoutOutput === false) {
            echo "- File removed: $file".PHP_EOL;
        }
    } else {
        if ($withoutOutput === false) {
            echo "- File cannot be removed: $file".PHP_EOL;
        }
        exit;
    }
}

/**
 * Remove folder (recursively).
 *
 * @param string $dir           Path to folder
 * @param bool   $withoutOutput Hide output
 */
function removeDir(string $dir, bool $withoutOutput = false): void
{
    $it = new RecursiveDirectoryIterator($dir, FilesystemIterator::SKIP_DOTS);
    $files = new RecursiveIteratorIterator($it, RecursiveIteratorIterator::CHILD_FIRST);

    foreach ($files as $file) {
        if ($file->isDir()) {
            removeDir($file->getPathname(), $withoutOutput);
        } else {
            removeFile($file->getPathname(), $withoutOutput);
        }
    }

    if (rmdir($dir)) {
        if ($withoutOutput === false) {
            echo "- Folder removed: $dir".PHP_EOL;
        }
    } else {
        if ($withoutOutput === false) {
            echo "- Folder cannot be removed: $dir".PHP_EOL;
        }
        exit;
    }
}

/**
 * Colored console output.
 *
 * @param string $str
 * @param string $type
 *
 * @return void
 */
function out(string $str, string $type = ''): void
{
    echo match ($type) {
        'error'   => "\033[31m$str \033[0m\n",
        'success' => "\033[32m$str \033[0m\n",
        'warning' => "\033[33m$str \033[0m\n",
        'info'    => "\033[36m$str \033[0m\n",
        'debug'   => "\033[90m$str \033[0m\n",
        default   => "$str\n",
    };
}

/**
 * Run process with realtime output.
 *
 * @param string      $cmd
 * @param string|null $input
 *
 * @return int
 */
function runProcess(string $cmd, ?string $input = null): int
{
    $descriptorSpec = [
        0 => ['pipe', 'r'],
        1 => ['pipe', 'w'],
        2 => ['pipe', 'w'],
    ];

    $process = proc_open($cmd, $descriptorSpec, $pipes);

    if (!is_resource($process)) {
        out('- Could not start subprocess', 'error');

        return -1;
    }

    // Write input if provided
    if ($input !== null) {
        fwrite($pipes[0], $input);
        fclose($pipes[0]);
    }

    // Read and print output in real-time
    while (!feof($pipes[1])) {
        echo stream_get_contents($pipes[1], 1);
        flush(); // Flush output buffer for immediate display
    }

    // Read and print error output
    while (!feof($pipes[2])) {
        echo stream_get_contents($pipes[2], 1);
        flush();
    }

    fclose($pipes[1]);
    fclose($pipes[2]);

    return proc_close($process);
}

/**
 * Setting permissions recursively.
 *
 * @param string $dir
 * @param int    $dirPermissions
 * @param int    $filePermissions
 *
 * @return void
 */
function chmod_r(string $dir, int $dirPermissions, int $filePermissions): void
{
    $dp = opendir($dir);
    while ($file = readdir($dp)) {
        if (($file == '.') || ($file == '..')) {
            continue;
        }

        $fullPath = realpath($dir.'/'.$file);
        if (is_dir($fullPath)) {
            out("- Directory: $fullPath");
            chmod($fullPath, $dirPermissions);
            chmod_r($fullPath, $dirPermissions, $filePermissions);
        } elseif (is_file($fullPath)) {
            // out("- File: $fullPath");
            chmod($fullPath, $filePermissions);
        } else {
            out("- Cannot find target path: $fullPath", 'error');

            return;
        }
    }

    closedir($dp);
}
