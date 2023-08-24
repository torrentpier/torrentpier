<?php
/**
 * TorrentPier – Bull-powered BitTorrent tracker engine
 *
 * @copyright Copyright (c) 2005-2023 TorrentPier (https://torrentpier.com)
 * @link      https://github.com/torrentpier/torrentpier for the canonical source repository
 * @license   https://github.com/torrentpier/torrentpier/blob/master/LICENSE MIT License
 */

if (!defined('BB_PATH')) {
    die(basename(__FILE__));
}

$pm_days_keep = (int)$bb_cfg['pm_days_keep'];

DB()->query("
	DELETE FROM " . BB_PRIVMSGS . "
	WHERE privmsgs_date < " . (TIMENOW - 86400 * $pm_days_keep) . "
");
