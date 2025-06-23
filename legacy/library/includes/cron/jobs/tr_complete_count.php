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

// Update TORRENT "completed" counters
DB()->query("
	UPDATE
		" . BB_BT_TORRENTS . " tor,
		" . BB_BT_TRACKER_SNAP . " snap
	SET
		tor.complete_count = snap.completed
	WHERE
		tor.topic_id = snap.topic_id
");
