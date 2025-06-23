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

if (tp_config()->get('prune_enable')) {
    $sql = "SELECT forum_id, prune_days FROM " . BB_FORUMS . " WHERE prune_days != 0";

    foreach (DB()->fetch_rowset($sql) as $row) {
        \TorrentPier\Legacy\Admin\Common::topic_delete('prune', $row['forum_id'], (TIMENOW - 86400 * $row['prune_days']));
    }
}
