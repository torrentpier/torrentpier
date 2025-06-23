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

$users_per_cycle = 1000;

while (true) {
    set_time_limit(600);
    $prune_users = $not_activated_users = $not_active_users = [];

    if ($not_activated_days = (int)tp_config()->get('user_not_activated_days_keep')) {
        $sql = DB()->fetch_rowset("SELECT user_id FROM " . BB_USERS . "
			WHERE user_level      = 0
			AND user_lastvisit    = 0
			AND user_session_time = 0
			AND user_regdate      <= " . (TIMENOW - 86400 * $not_activated_days) . "
			AND user_id           NOT IN(" . EXCLUDED_USERS . ")
			LIMIT $users_per_cycle");

        foreach ($sql as $row) {
            $not_activated_users[] = $row['user_id'];
        }
    }

    if ($not_active_days = (int)tp_config()->get('user_not_active_days_keep')) {
        $sql = DB()->fetch_rowset("SELECT user_id FROM " . BB_USERS . "
			WHERE user_level   = 0
			AND user_posts     = 0
			AND user_lastvisit <= " . (TIMENOW - 86400 * $not_active_days) . "
			AND user_id        NOT IN(" . EXCLUDED_USERS . ")
			LIMIT $users_per_cycle");

        foreach ($sql as $row) {
            $not_active_users[] = $row['user_id'];
        }
    }

    if ($prune_users = $not_activated_users + $not_active_users) {
        \TorrentPier\Legacy\Admin\Common::user_delete($prune_users);
    }

    if (count($prune_users) < $users_per_cycle) {
        break;
    }
}
