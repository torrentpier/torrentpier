<?php

/**
 * TorrentPier – Bull-powered BitTorrent tracker engine
 *
 * @copyright Copyright (c) 2005-2025 TorrentPier (https://torrentpier.com)
 * @link      https://github.com/torrentpier/torrentpier for the canonical source repository
 * @license   https://github.com/torrentpier/torrentpier/blob/master/LICENSE MIT License
 */

$sql = 'SELECT * FROM ' . BB_BANLIST;
$bans = [];

foreach (DB()->fetch_rowset($sql) as $row) {
    $bans[$row['ban_userid']] = $row;
}

$this->store('ban_list', $bans);
