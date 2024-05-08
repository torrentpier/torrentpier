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

set_time_limit(600);

global $cron_runtime_log;

$save_path = INT_DATA_DIR . '/GeoLite2-City.mmdb';
$repo_link = 'https://api.github.com/repos/P3TERX/GeoLite.mmdb/releases/latest';

if (rename($save_path, INT_DATA_DIR . '/GeoLite2-City.mmdb.old')) {
    $cron_runtime_log = date('Y-m-d H:i:s') . " -- Successfully renamed GeoLite file\n";
} else {
    $cron_runtime_log = date('Y-m-d H:i:s') . " -- Cannot rename GeoLite file\n";
}

$context = stream_context_create(['http' => ['header' => 'User-Agent: ' . APP_NAME]]);
$repo_content = file_get_contents($repo_link, context: $context);

$json_response = false;
if ($repo_content !== false) {
    $json_response = json_decode(utf8_encode($repo_content), true);
    $cron_runtime_log = date('Y-m-d H:i:s') . " -- Successfully connected to: " . $repo_link . "\n";
} else {
    $cron_runtime_log = date('Y-m-d H:i:s') . " -- Cannot access to: " . $repo_link . "\n";
}

if (is_array($json_response) && !empty($json_response)) {
    $download_link = $json_response['assets'][1]['browser_download_url'];
    if (!empty($download_link)) {
        $cron_runtime_log = date('Y-m-d H:i:s') . " -- Link to download is valid: " . $download_link . "\n";
        $get_file = file_get_contents($download_link);
        if ($get_file !== false) {
            $file_md5_hash = hash_file('md5', $get_file);
            $cron_runtime_log = date('Y-m-d H:i:s') . " -- GeoLite file obtained\n";
            $new_file = file_put_contents($save_path, $get_file);
            if ($file_md5_hash === hash_file('md5', $new_file)) {
                unlink(INT_DATA_DIR . '/GeoLite2-City.mmdb.old');
                $cron_runtime_log = date('Y-m-d H:i:s') . " -- GeoLite file successfully saved. MD5 hash is identical. MD5: $file_md5_hash\n";
            } else {
                if (unlink($save_path)) {
                    rename(INT_DATA_DIR . '/GeoLite2-City.mmdb.old', $save_path);
                }
                $cron_runtime_log = date('Y-m-d H:i:s') . " -- Reverting all changes. MD5 hash not identical\n";
            }
        } else {
            $cron_runtime_log = date('Y-m-d H:i:s') . " -- GeoLite file not obtained\n";
        }
    } else {
        $cron_runtime_log = date('Y-m-d H:i:s') . " -- Cannot find link to download\n";
    }
} else {
    $cron_runtime_log = date('Y-m-d H:i:s') . " -- Invalid response from server: " . $json_response . "\n";
}