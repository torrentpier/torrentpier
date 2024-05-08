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

global $cron_runtime_log;

foreach ($bb_cfg['cache']['engines'] as $cache_name => $cache_val) {
    if (method_exists(CACHE($cache_name), 'gc')) {
        $changes = CACHE($cache_name)->gc();
        $cron_runtime_log .= date('Y-m-d H:i:s') . " -- " . str_pad("$cache_name ", 25, '-', STR_PAD_RIGHT) . " del: $changes\n";
    }
}
