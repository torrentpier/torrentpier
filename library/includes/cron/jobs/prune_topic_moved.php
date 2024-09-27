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

if ($bb_cfg['topic_moved_days_keep']) {
    $prune_time = TIME_DAY * $bb_cfg['topic_moved_days_keep'];

    DB()->query("
		DELETE FROM " . BB_TOPICS . "
		WHERE topic_status = " . TOPIC_MOVED . "
			AND topic_time < $prune_time
	");
}
