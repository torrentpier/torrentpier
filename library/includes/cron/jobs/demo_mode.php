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

ini_set('memory_limit', '-1');
set_time_limit(600);

global $cron_runtime_log;

define('SELECTED_DB', DB()->selected_db);
$dump_path = BB_ROOT . 'install/sql/mysql.sql';

if (!IN_DEMO_MODE || !is_file($dump_path) || !is_readable($dump_path)) {
    return;
}

$sql_dump = file_get_contents($dump_path);

// Delete database
if (!DB()->query("DROP DATABASE " . SELECTED_DB)) {
    return;
}

// Create database
if (!DB()->query("CREATE DATABASE " . SELECTED_DB)) {
    return;
}

// Import sql dump from file
if (!DB()->multi_query($sql_dump)) {
    return;
}
