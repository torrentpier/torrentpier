<?php

/**
 * TorrentPier – Bull-powered BitTorrent tracker engine
 *
 * @copyright Copyright (c) 2005-2025 TorrentPier (https://torrentpier.com)
 * @link      https://github.com/torrentpier/torrentpier for the canonical source repository
 * @license   https://github.com/torrentpier/torrentpier/blob/master/LICENSE MIT License
 */

$log_days_keep = (int)config()->get('logging.log_days_keep');

if ($log_days_keep !== 0) {
    DB()->query('DELETE FROM ' . BB_LOG . ' WHERE log_time < ' . (TIMENOW - 86400 * $log_days_keep));
}
