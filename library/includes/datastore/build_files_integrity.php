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
$filesList = [
    0 => [
        'path' => BB_ROOT . '/index.php',
        'hash' => '4DC6C23A599961D6082FC853C723B8F1'
    ]
];
$wrongFilesList = [];

foreach ($filesList as $file) {
    if (strtolower(md5_file($file['path'])) !== strtolower($file['hash'])) {
        $wrongFilesList[] = basename($file['path']);
    }
}

$data = [
    'success' => false,
    'files' => implode("\n</li>\n<li>\n", $wrongFilesList),
    'timestamp' => TIMENOW
];

$this->store('files_integrity', $data);
