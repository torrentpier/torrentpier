<?php
/**
 * TorrentPier – Bull-powered BitTorrent tracker engine
 *
 * @copyright Copyright (c) 2005-2025 TorrentPier (https://torrentpier.com)
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

$filesList = [];
$wrongFilesList = [];

$checksumFile = new SplFileObject(CHECKSUMS_FILE, 'r+');
$checksumFile->setFlags(SplFileObject::SKIP_EMPTY | SplFileObject::DROP_NEW_LINE);

$ignoreFiles = [
    '.git/*',
    '.github/*',
    '*.md',
    '*.yml',
    '*.toml',
    '*.json',
    '*.lock',
    '.env*',
    'vendor',
    '.gitignore',
    '.editorconfig',
    '.htaccess',
    'robots.txt',
    // TorrentPier specific
    'install.php',
    'favicon.png',
    hide_bb_path(CHECKSUMS_FILE),
    hide_bb_path(BB_ENABLED),
    'library/config.php',
    'library/defines.php',
    'styles/images/logo/logo.png'
];

foreach ($checksumFile as $line) {
    $parts = explode('  ', $line);
    if (!isset($parts[0]) || !isset($parts[1])) {
        // Skip end line
        break;
    }

    // Skip files from "Ignoring list"
    if (!empty($ignoreFiles)) {
        $skip = false;
        foreach ($ignoreFiles as $pattern) {
            $pattern = trim($pattern);
            if (fnmatch($pattern, $parts[1])) {
                $skip = true;
                break;
            }
        }
        if ($skip) {
            continue;
        }
    }

    $filesList[] = [
        'path' => trim($parts[1]),
        'hash' => trim($parts[0])
    ];
}

foreach ($filesList as $file) {
    if (!file_exists(BB_ROOT . $file['path']) || (strtolower(md5_file(BB_ROOT . $file['path'])) !== strtolower($file['hash']))) {
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
