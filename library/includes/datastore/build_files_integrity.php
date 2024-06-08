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

global $bb_cfg;

$data = [];
$filesList = [];

$checksumFile = new SplFileObject(CHECKSUMS_FILE, 'r');
$checksumFile->setFlags(SplFileObject::SKIP_EMPTY | SplFileObject::DROP_NEW_LINE);

$lines = [];
foreach ($checksumFile as $line) {
    $parts = explode('  ', $line);
    if (!isset($parts[1])) {
        // Skip end line
        break;
    }
    if (basename($parts[1]) === basename(CHECKSUMS_FILE)) {
        // Skip checksums.md5
        continue;
    }
    $filesList[] = [
        'path' => trim($parts[1]),
        'hash' => trim($parts[0])
    ];
}

$dynamicFiles = [
    BB_ENABLED
];

$wrongFilesList = [];
foreach ($filesList as $file) {
    if (!empty($dynamicFiles) && in_array(hide_bb_path($file['path']), $dynamicFiles)) {
        // Exclude dynamic files
        continue;
    }
    if (!file_exists(BB_ROOT . '/' . $file['path']) || strtolower(md5_file(BB_ROOT . '/' . $file['path'])) !== strtolower($file['hash'])) {
        $wrongFilesList[] = $file['path'];
    }
}

$data = [
    'success' => empty($wrongFilesList),
    'wrong_files' => $wrongFilesList,
    'wrong_files_num' => count($wrongFilesList),
    'total_num' => count($filesList),
    'timestamp' => TIMENOW,
];

// Restore corrupt files
if (is_file(RESTORE_CORRUPT_CONFIRM_FILE)) {
    // ----- //

    // Delete restore confirm file
    unlink(RESTORE_CORRUPT_CONFIRM_FILE);
}

$this->store('files_integrity', $data);
