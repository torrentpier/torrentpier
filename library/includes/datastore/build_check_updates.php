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

$context = stream_context_create(['http' => ['header' => 'User-Agent: TorrentPier Updater. With love!']]);
$updater_content = file_get_contents(UPDATER_URL, context: $context);

$json_response = false;
if ($updater_content !== false) {
    $json_response = json_decode(utf8_encode($updater_content), true);
}

if (!is_array($json_response) && !empty($json_response)) {
    $get_version = $json_response['tag_name'];
    $version_code = (int)trim(str_replace(['.', 'v', ','], '', strstr($bb_cfg['tp_version'], '-', true)));
    $version_code_actual = (int)trim(str_replace(['.', 'v', ','], '', $get_version));

    $has_update = $version_code < $version_code_actual;

    if ($has_update) {
        file_write($version_code . "\n" . $version_code_actual, INT_DATA_DIR . '/updater.ver', replace_content: true);
    }

    // Build data array
    $data = [
        'available_update' => $has_update,
        'latest_version' => $get_version,
        'latest_version_size' => isset($json_response['assets'][0]['size']) ? humn_size($json_response['assets'][0]['size']) : false,
        'latest_version_link' => $json_response['assets'][0]['browser_download_url'] ?? $json_response['html_url']
    ];
}

$this->store('check_updates', $data);
