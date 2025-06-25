<?php

/**
 * TorrentPier – Bull-powered BitTorrent tracker engine.
 *
 * @copyright Copyright (c) 2005-2025 TorrentPier (https://torrentpier.com)
 *
 * @link      https://github.com/torrentpier/torrentpier for the canonical source repository
 *
 * @license   https://github.com/torrentpier/torrentpier/blob/master/LICENSE MIT License
 */
if (!defined('BB_ROOT')) {
    exit(basename(__FILE__));
}

if (!config()->get('tp_updater_settings.enabled')) {
    return;
}

$data = [];

$updaterDownloader = new \TorrentPier\Updater();
$updaterDownloader = $updaterDownloader->getLastVersion(config()->get('tp_updater_settings.allow_pre_releases'));

$getVersion = \TorrentPier\Helpers\VersionHelper::removerPrefix($updaterDownloader['tag_name']);
$currentVersion = \TorrentPier\Helpers\VersionHelper::removerPrefix(config()->get('tp_version'));

// Has update!
if (\z4kn4fein\SemVer\Version::greaterThan($getVersion, $currentVersion)) {
    $latestBuildFileLink = $updaterDownloader['assets'][0]['browser_download_url'];

    // Check updater file
    $updaterFile = readUpdaterFile();
    $updaterFileNeedReplaced = !empty($updaterFile) && \z4kn4fein\SemVer\Version::greaterThan($getVersion, $updaterFile['latest_version']);

    // Save current version & latest available
    if (!is_file(UPDATER_FILE) || $updaterFileNeedReplaced) {
        file_write(json_encode([
            'previous_version' => $currentVersion,
            'latest_version'   => $getVersion,
        ]), UPDATER_FILE, replace_content: true);
    }

    // Get MD5 checksum
    $buildFileChecksum = '';
    if (isset($latestBuildFileLink)) {
        $buildFileChecksum = strtoupper(md5_file($latestBuildFileLink));
    }

    // Build data array
    $data = [
        'available_update'        => true,
        'latest_version'          => $getVersion,
        'latest_version_size'     => isset($updaterDownloader['assets'][0]['size']) ? humn_size($updaterDownloader['assets'][0]['size']) : false,
        'latest_version_dl_link'  => $latestBuildFileLink ?? $updaterDownloader['html_url'],
        'latest_version_checksum' => $buildFileChecksum,
        'latest_version_link'     => $updaterDownloader['html_url'],
    ];
}

$data[] = ['latest_check_timestamp' => TIMENOW];
$this->store('check_updates', $data);
