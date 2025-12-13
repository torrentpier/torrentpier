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

$user_session_expire_time = TIMENOW - (int)config()->get('user_session_duration');
$admin_session_expire_time = TIMENOW - (int)config()->get('admin_session_duration');

$user_session_gc_time = $user_session_expire_time - (int)config()->get('user_session_gc_ttl');
$admin_session_gc_time = $admin_session_expire_time;

// ############################ Tables LOCKED ################################
DB()->lock([
    BB_USERS . ' u',
    BB_SESSIONS . ' s',
]);

// Update user's session time
DB()->query('
	UPDATE
		' . BB_USERS . ' u,
		' . BB_SESSIONS . ' s
	SET
		u.user_session_time = IF(u.user_session_time < s.session_time, s.session_time, u.user_session_time)
	WHERE
				u.user_id = s.session_user_id
		AND s.session_user_id != ' . GUEST_UID . "
		AND (
			(s.session_time < {$user_session_expire_time} AND s.session_admin = 0)
			OR
			(s.session_time < {$admin_session_expire_time} AND s.session_admin != 0)
		)
");

DB()->unlock();
// ############################ Tables UNLOCKED ##############################

// Delete staled sessions
DB()->query('
	DELETE s
	FROM ' . BB_SESSIONS . " s
	WHERE
		(s.session_time < {$user_session_gc_time} AND s.session_admin = 0)
		OR
		(s.session_time < {$admin_session_gc_time} AND s.session_admin != 0)
");
