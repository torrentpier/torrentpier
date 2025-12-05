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

// Synchronization
\TorrentPier\Legacy\Admin\Common::sync('topic', 'all');
\TorrentPier\Legacy\Admin\Common::sync('user_posts', 'all');
\TorrentPier\Legacy\Admin\Common::sync_all_forums();

// Cleaning bb_poll_users
if ($poll_max_days = (int)config()->get('poll_max_days')) {
    $per_cycle = 20000;
    $row = DB()->fetch_row("SELECT MIN(topic_id) AS start_id, MAX(topic_id) AS finish_id FROM " . BB_POLL_USERS);
    $start_id = (int)$row['start_id'];
    $finish_id = (int)$row['finish_id'];

    while (true) {
        set_time_limit(600);
        $end_id = $start_id + $per_cycle - 1;

        DB()->query("
			DELETE FROM " . BB_POLL_USERS . "
			WHERE topic_id BETWEEN $start_id AND $end_id
				AND vote_dt < " . (TIMENOW - 86400 * $poll_max_days) . "
		");

        if ($end_id > $finish_id) {
            break;
        }

        $start_id += $per_cycle;
    }
}

// Cleaning user_newpasswd
DB()->query("UPDATE " . BB_USERS . " SET user_newpasswd = '' WHERE user_lastvisit < " . (TIMENOW - 7 * 86400));

// Cleaning post cache
if ($posts_days = (int)config()->get('posts_cache_days_keep')) {
    DB()->query("DELETE FROM " . BB_POSTS_HTML . " WHERE post_html_time < DATE_SUB(NOW(), INTERVAL $posts_days DAY)");
}

// Autofill announcer url
if (empty(config()->get('bt_announce_url')) || (config()->get('bt_announce_url') === 'https://localhost/bt/announce.php')) {
    bb_update_config(['bt_announce_url' => FULL_URL . 'bt/announce.php']);
}

// [Demo mode] Allow registering torrents by default for "Your first forum"
if (IN_DEMO_MODE) {
    DB()->query("UPDATE " . BB_FORUMS . " SET allow_reg_tracker = 1 WHERE allow_reg_tracker = 0 AND forum_id = 1 LIMIT 1");
}

// Check for updates
datastore()->update('check_updates');
