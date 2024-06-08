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

if (!$bb_cfg['integrity_check']) {
    return;
}

$checksumFile = new SplFileObject(CHECKSUMS_FILE, 'r');
$checksumFile->setFlags(SplFileObject::SKIP_EMPTY | SplFileObject::DROP_NEW_LINE);

$filesList = [];
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

// Restore corrupt files
if (is_file(RESTORE_CORRUPT_CONFIRM_FILE)) {
    $buildDownloader = new \TorrentPier\Updater();
    if ($buildDownloader->download(INT_DATA_DIR . '/', $bb_cfg['tp_version'])) {
        // Unzip downloaded build file
        $zipArchive = new ZipArchive;
        $extractDownloadedFile = $zipArchive->open($buildDownloader->savePath);
        if ($extractDownloadedFile === true) {
            if ($zipArchive->extractTo(BB_ROOT, $wrongFilesList)) {
                $wrongFilesList = [];
            }
            $zipArchive->close();
        }
    }

    // Delete restore confirm file & build file
    unlink($buildDownloader->savePath);
    unlink(RESTORE_CORRUPT_CONFIRM_FILE);
}

$data = [
    'success' => empty($wrongFilesList),
    'wrong_files' => $wrongFilesList,
    'wrong_files_num' => count($wrongFilesList),
    'total_num' => count($filesList),
    'timestamp' => TIMENOW,
];

$this->store('files_integrity', $data);
