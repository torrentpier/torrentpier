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

$save_path = INT_DATA_DIR . '/';
// $cron_runtime_log = date('Y-m-d H:i:s') . " -- Error performing query: " . $temp_line . " | " . DB()->sql_error()['message'] . "\n";
