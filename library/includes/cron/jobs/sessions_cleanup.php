<?php
/**
 * MIT License
 *
 * Copyright (c) 2005-2017 TorrentPier
 *
 * Permission is hereby granted, free of charge, to any person obtaining a copy
 * of this software and associated documentation files (the "Software"), to deal
 * in the Software without restriction, including without limitation the rights
 * to use, copy, modify, merge, publish, distribute, sublicense, and/or sell
 * copies of the Software, and to permit persons to whom the Software is
 * furnished to do so, subject to the following conditions:
 *
 * The above copyright notice and this permission notice shall be included in all
 * copies or substantial portions of the Software.
 *
 * THE SOFTWARE IS PROVIDED "AS IS", WITHOUT WARRANTY OF ANY KIND, EXPRESS OR
 * IMPLIED, INCLUDING BUT NOT LIMITED TO THE WARRANTIES OF MERCHANTABILITY,
 * FITNESS FOR A PARTICULAR PURPOSE AND NONINFRINGEMENT. IN NO EVENT SHALL THE
 * AUTHORS OR COPYRIGHT HOLDERS BE LIABLE FOR ANY CLAIM, DAMAGES OR OTHER
 * LIABILITY, WHETHER IN AN ACTION OF CONTRACT, TORT OR OTHERWISE, ARISING FROM,
 * OUT OF OR IN CONNECTION WITH THE SOFTWARE OR THE USE OR OTHER DEALINGS IN THE
 * SOFTWARE.
 */

if (!defined('BB_ROOT')) {
    die(basename(__FILE__));
}

$user_session_expire_time = TIMENOW - (int)$bb_cfg['user_session_duration'];
$admin_session_expire_time = TIMENOW - (int)$bb_cfg['admin_session_duration'];

$user_session_gc_time = $user_session_expire_time - (int)$bb_cfg['user_session_gc_ttl'];
$admin_session_gc_time = $admin_session_expire_time;

// ############################ Tables LOCKED ################################
DB()->lock(array(
    BB_USERS . ' u',
    BB_SESSIONS . ' s',
));

// Update user's session time
DB()->query("
	UPDATE
		" . BB_USERS . " u,
		" . BB_SESSIONS . " s
	SET
		u.user_session_time = IF(u.user_session_time < s.session_time, s.session_time, u.user_session_time)
	WHERE
				u.user_id = s.session_user_id
		AND s.session_user_id != " . GUEST_UID . "
		AND (
			(s.session_time < $user_session_expire_time AND s.session_admin = 0)
			OR
			(s.session_time < $admin_session_expire_time AND s.session_admin != 0)
		)
");

DB()->unlock();
// ############################ Tables UNLOCKED ##############################

// Delete staled sessions
DB()->query("
	DELETE s
	FROM " . BB_SESSIONS . " s
	WHERE
		(s.session_time < $user_session_gc_time AND s.session_admin = 0)
		OR
		(s.session_time < $admin_session_gc_time AND s.session_admin != 0)
");
