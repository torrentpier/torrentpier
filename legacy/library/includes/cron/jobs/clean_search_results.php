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

$search_results_expire = TIMENOW - 3 * 3600;

DB()->query('
	DELETE FROM '.BB_SEARCH."
	WHERE search_time < $search_results_expire
");
