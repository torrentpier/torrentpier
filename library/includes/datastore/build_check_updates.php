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
$updaterDownloader = $updaterDownloader->getLastVersion();

$getVersion = $updaterDownloader['tag_name'];
$versionCodeActual = (int)trim(str_replace(['.', 'v'], '', $getVersion));

// Has update!
if (VERSION_CODE < $versionCodeActual) {
    $latestBuildFileLink = $updaterDownloader['assets'][0]['browser_download_url'];

    // Save current version & latest available
    file_write(json_encode([
        'previous_version' => VERSION_CODE,
        'latest_version' => $versionCodeActual
    ]), UPDATER_FILE, replace_content: true);

    // Get MD5 checksum
    $buildFileChecksum = '';
    if (isset($latestBuildFileLink)) {
        $buildFileChecksum = strtoupper(md5_file($latestBuildFileLink));
    }

    // Build data array
    $data = [
        'available_update' => true,
        'latest_version' => $getVersion,
        'latest_version_size' => isset($updaterDownloader['assets'][0]['size']) ? humn_size($updaterDownloader['assets'][0]['size']) : false,
        'latest_version_dl_link' => $latestBuildFileLink ?? $updaterDownloader['html_url'],
        'latest_version_checksum' => $buildFileChecksum,
        'latest_version_link' => $updaterDownloader['html_url']
    ];
}

$data[] = ['latest_check_timestamp' => TIMENOW];
$this->store('check_updates', $data);
