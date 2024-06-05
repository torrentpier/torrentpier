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

$context = stream_context_create(['http' => ['header' => 'User-Agent: ' . APP_NAME]]);
$updater_content = file_get_contents(UPDATER_URL, context: $context);

$json_response = false;
if ($updater_content !== false) {
    $json_response = json_decode(utf8_encode($updater_content), true);
}

if (is_array($json_response) && !empty($json_response)) {
    $get_version = $json_response['tag_name'];
    $version_code_actual = (int)trim(str_replace(['.', 'v'], '', $get_version));
    $has_update = VERSION_CODE < $version_code_actual;
    $latest_release_file = $json_response['assets'][0]['browser_download_url'];

    // Save current version & latest available
    if ($has_update) {
        file_write(VERSION_CODE . "\n" . $version_code_actual, UPDATER_FILE, replace_content: true);
    }

    // Get MD5 checksum
    $md5_file_checksum = '';
    if (isset($latest_release_file)) {
        $md5_file_checksum = md5_file($latest_release_file);
    }

    // Build data array
    $data = [
        'available_update' => $has_update,
        'latest_version' => $get_version,
        'latest_version_size' => isset($json_response['assets'][0]['size']) ? humn_size($json_response['assets'][0]['size']) : false,
        'latest_version_dl_link' => $latest_release_file ?? $json_response['html_url'],
        'latest_version_checksum' => $md5_file_checksum,
        'latest_version_link' => $json_response['html_url']
    ];
}

$this->store('check_updates', $data);
