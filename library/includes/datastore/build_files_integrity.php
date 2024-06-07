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
$filesList = ['123', '123', '123', '123','123', '123','123', '123','123', '123'];

$data = [
    'success' => false,
    'files' => implode("\n</li>\n<li>\n", $filesList),
    'timestamp' => TIMENOW
];

$this->store('files_integrity', $data);
