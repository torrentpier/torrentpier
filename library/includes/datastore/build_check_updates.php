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

if (!$bb_cfg['tp_updater_settings']['enabled']) {
    return;
}

$data = [];

$updaterDownloader = new \TorrentPier\Updater();
$updaterDownloader = $updaterDownloader->getLastVersion($bb_cfg['tp_updater_settings']['allow_pre_releases']);

$getVersion = $updaterDownloader['tag_name'];
$versionActual = new PHLAK\SemVer\Version($getVersion);
$currentVersion = new PHLAK\SemVer\Version($bb_cfg['tp_version']);

// Has update!
if ($versionActual->gt($currentVersion)) {
    $latestBuildFileLink = $updaterDownloader['assets'][0]['browser_download_url'];

    // Check updater file
    $updaterFile = readUpdaterFile();
    $versionFromUpdaterFile = new PHLAK\SemVer\Version($updaterFile['latest_version']);
    $updaterNeedReplaced = !empty($updaterFile) && ($versionActual->gt($versionFromUpdaterFile));

    // Save current version & latest available
    if (!is_file(UPDATER_FILE) || $updaterNeedReplaced) {
        file_write(json_encode([
            'previous_version' => $bb_cfg['tp_version'],
            'latest_version' => $getVersion
        ]), UPDATER_FILE, replace_content: true);
    }

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
