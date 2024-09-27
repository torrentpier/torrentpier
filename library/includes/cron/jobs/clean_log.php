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

$log_days_keep = (int)$bb_cfg['log_days_keep'];

if ($log_days_keep !== 0) {
    DB()->query("DELETE FROM " . BB_LOG . " WHERE log_time < " . (TIME_DAY * $log_days_keep));
}
