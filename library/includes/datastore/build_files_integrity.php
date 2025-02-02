<?php
/**
 * TorrentPier – Bull-powered BitTorrent tracker engine
 *
 * @copyright Copyright (c) 2005-2025 TorrentPier (https://torrentpier.com)
 * @link      https://github.com/torrentpier/torrentpier for the canonical source repository
 * @license   https://github.com/torrentpier/torrentpier/blob/master/LICENSE MIT License
 */

use TorrentPier\IntegrityChecker;

if (!defined('BB_ROOT')) {
    die(basename(__FILE__));
}

global $bb_cfg;

if (!$bb_cfg['integrity_check']) {
    return;
}

$wrongFilesList = [];

$integrityChecker = new IntegrityChecker();
$filesList = $integrityChecker->readChecksumFile();
foreach ($filesList as $file) {
    if (
        !file_exists(BB_ROOT . $file['path'])
        || (hash_file($integrityChecker::HASH_ALGO, BB_ROOT . $file['path']) !== $file['hash'])
    ) {
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
    if (isset($buildDownloader->savePath)) {
        unlink($buildDownloader->savePath);
    }
    if (is_file(RESTORE_CORRUPT_CONFIRM_FILE)) {
        unlink(RESTORE_CORRUPT_CONFIRM_FILE);
    }
}

$data = [
    'success' => empty($wrongFilesList),
    'wrong_files' => $wrongFilesList,
    'wrong_files_num' => count($wrongFilesList),
    'total_num' => count($filesList),
    'timestamp' => TIMENOW,
];

$this->store('files_integrity', $data);
