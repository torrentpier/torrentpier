<?php
/**
 * TorrentPier – Bull-powered BitTorrent tracker engine
 *
 * @copyright Copyright (c) 2005-2025 TorrentPier (https://torrentpier.com)
 * @link      https://github.com/torrentpier/torrentpier for the canonical source repository
 * @license   https://github.com/torrentpier/torrentpier/blob/master/LICENSE MIT License
 */

if (!defined('BB_ROOT')) {
    die(basename(__FILE__));
}

set_time_limit(600);

global $cron_runtime_log;

$dump_path = BB_ROOT . 'install/sql/mysql.sql';

if (!IN_DEMO_MODE || !is_file($dump_path) || !is_readable($dump_path)) {
    return;
}

// Clean cache & datastore
$datastore->clean();
foreach (config()->get('cache.engines') as $cache_name => $cache_val) {
    CACHE($cache_name)->rm();
}

// Drop tables & Insert sql dump
$temp_line = '';
foreach (file($dump_path) as $line) {
    if (str_starts_with($line, '--') || $line == '') {
        continue;
    }

    $temp_line .= $line;
    if (str_ends_with(trim($line), ';')) {
        if (!DB()->query($temp_line)) {
            $cron_runtime_log[] = date('Y-m-d H:i:s') . " -- Error performing query: " . $temp_line . " | " . DB()->sql_error()['message'];
        }
        $temp_line = '';
    }
}
