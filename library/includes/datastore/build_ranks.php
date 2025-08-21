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

$sql = "SELECT * FROM " . BB_RANKS;
$ranks = [];

foreach (DB()->fetch_rowset($sql) as $row) {
    $ranks[$row['rank_id']] = $row;
}

$this->store('ranks', $ranks);
