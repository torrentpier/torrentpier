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

$updaterDownloader = new \TorrentPier\Updater();

$get_version = $updaterDownloader['tag_name'];
$version_code_actual = (int)trim(str_replace(['.', 'v'], '', $get_version));

// Has update!
if (VERSION_CODE < $version_code_actual) {
    $latest_release_file = $updaterDownloader['assets'][0]['browser_download_url'];

    // Save current version & latest available
    file_write(VERSION_CODE . "\n" . $version_code_actual, UPDATER_FILE, replace_content: true);

    // Get MD5 checksum
    $md5_file_checksum = '';
    if (isset($latest_release_file)) {
        $md5_file_checksum = strtoupper(md5_file($latest_release_file));
    }

    // Build data array
    $data = [
        'available_update' => true,
        'latest_version' => $get_version,
        'latest_version_size' => isset($updaterDownloader['assets'][0]['size']) ? humn_size($updaterDownloader['assets'][0]['size']) : false,
        'latest_version_dl_link' => $latest_release_file ?? $updaterDownloader['html_url'],
        'latest_version_checksum' => $md5_file_checksum,
        'latest_version_link' => $updaterDownloader['html_url']
    ];
}

$data[] = ['latest_check_timestamp' => TIMENOW];
$this->store('check_updates', $data);
