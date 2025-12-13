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

DB()->expect_slow_query(600);

if (config()->get('seed_bonus_enabled') && config()->get('seed_bonus_points') && config()->get('seed_bonus_release')) {
    DB()->query("
		CREATE TEMPORARY TABLE tmp_bonus (
			user_id       INT UNSIGNED NOT NULL DEFAULT '0',
			release_count INT UNSIGNED NOT NULL DEFAULT '0'
		) ENGINE = MEMORY
	");

    $tor_size = (config()->get('seed_bonus_tor_size') * 1073741824);

    DB()->query('INSERT INTO tmp_bonus
		SELECT bt.user_id, count(bt.seeder) AS release_count
			FROM ' . BB_BT_TRACKER . ' bt, ' . BB_BT_TORRENTS . " tor
			WHERE tor.topic_id = bt.topic_id
				AND tor.size   > {$tor_size}
				AND bt.seeder  > 0
			GROUP BY bt.user_id
	");

    $seed_bonus = unserialize(config()->get('seed_bonus_points'));
    $seed_release = unserialize(config()->get('seed_bonus_release'));

    foreach ($seed_bonus as $i => $points) {
        if (!$points || !$seed_release[$i]) {
            continue;
        }

        $user_points = ((float)$points / 4);
        $release = (int)$seed_release[$i];
        $user_regdate = (TIMENOW - config()->get('seed_bonus_user_regdate') * 86400);

        DB()->query('
			UPDATE ' . BB_USERS . ' u, ' . BB_BT_USERS . " bu, tmp_bonus b
			SET
				u.user_points       = u.user_points + '{$user_points}',
				bu.points_today     = bu.points_today + '{$user_points}',
				b.user_id           = 0
			WHERE
				b.user_id           =  u.user_id
				AND bu.user_id      =  u.user_id
				AND b.release_count <= {$release}
				AND u.user_regdate  <  {$user_regdate}
				AND u.user_active   =  1
				AND u.user_id       not IN(" . EXCLUDED_USERS . ')
		');
    }

    DB()->query('DROP TEMPORARY TABLE IF EXISTS tmp_bonus');
}
