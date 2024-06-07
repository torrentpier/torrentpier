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

$checksumFile = new SplFileObject(INT_DATA_DIR . '/checksums.md5', 'r');
$checksumFile->setFlags(SplFileObject::SKIP_EMPTY | SplFileObject::DROP_NEW_LINE);

$lines = [];
foreach ($checksumFile as $line) {
    $parts = explode('  ', $line);
    if (!isset($parts[1])) {
        break;
    }
    $filesList[] = [
        'path' => trim($parts[1]),
        'hash' => trim($parts[0])
    ];
}

$wrongFilesList = [];
foreach ($filesList as $file) {
    if (strtolower(md5_file(BB_ROOT . '/' . $file['path'])) !== strtolower($file['hash'])) {
        $wrongFilesList[] = basename($file['path']);
    }
}

$data = [
    'success' => false,
    'files' => implode("\n</li>\n<li>\n", $wrongFilesList),
    'timestamp' => TIMENOW
];

$this->store('files_integrity', $data);
