<?php

if (!defined('BB_ROOT')) die(basename(__FILE__));

$user_session_expire_time  = TIMENOW - intval($bb_cfg['user_session_duration']);
$admin_session_expire_time = TIMENOW - intval($bb_cfg['admin_session_duration']);

$user_session_gc_time  = $user_session_expire_time - intval($bb_cfg['user_session_gc_ttl']);
$admin_session_gc_time = $admin_session_expire_time;

// ############################ Tables LOCKED ################################
DB()->lock(array(
	BB_USERS    .' u',
	BB_SESSIONS .' s',
));

// Update user's session time
DB()->query("
	UPDATE
		". BB_USERS    ." u,
		". BB_SESSIONS ." s
	SET
		u.user_session_time = IF(u.user_session_time < s.session_time, s.session_time, u.user_session_time)
	WHERE
				u.user_id = s.session_user_id
		AND s.session_user_id != ". GUEST_UID ."
		AND (
			(s.session_time < $user_session_expire_time AND s.session_admin = 0)
			OR
			(s.session_time < $admin_session_expire_time AND s.session_admin != 0)
		)
");

DB()->unlock();
// ############################ Tables UNLOCKED ##############################

sleep(5);

// Delete staled sessions
DB()->query("
	DELETE s
	FROM ". BB_SESSIONS ." s
	WHERE
		(s.session_time < $user_session_gc_time AND s.session_admin = 0)
		OR
		(s.session_time < $admin_session_gc_time AND s.session_admin != 0)
");