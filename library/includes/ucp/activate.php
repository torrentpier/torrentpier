<?php

/**
 * TorrentPier – Bull-powered BitTorrent tracker engine
 *
 * @copyright Copyright (c) 2005-2026 TorrentPier (https://torrentpier.com)
 * @link      https://github.com/torrentpier/torrentpier for the canonical source repository
 * @license   https://github.com/torrentpier/torrentpier/blob/master/LICENSE MIT License
 */

if (!request()->query->has(POST_USERS_URL) || !request()->query->has('act_key')) {
    bb_die('Bad request', 400);
}

$sql = 'SELECT user_active, user_id, username, user_email, user_newpasswd, user_lang, user_actkey, user_actkey_time
	FROM ' . BB_USERS . '
	WHERE user_id = ' . request()->query->getInt(POST_USERS_URL);
if (!($result = DB()->sql_query($sql))) {
    bb_die('Could not obtain user information');
}

$row = DB()->sql_fetchrow($result);
if (!$row) {
    bb_die(__('WRONG_ACTIVATION'), 400);
}

$stored_actkey = trim($row['user_actkey']);
$supplied_actkey = trim((string)request()->query->get('act_key'));
$actkey_ttl = (int)config()->get('auth.actkey_ttl');
$actkey_expired = $actkey_ttl > 0
    && (int)$row['user_actkey_time'] > 0
    && (TIMENOW - (int)$row['user_actkey_time']) > $actkey_ttl;

if ($stored_actkey !== '' && !$actkey_expired && hash_equals($stored_actkey, $supplied_actkey)) {
    $sql_update_pass = ($row['user_newpasswd'] != '') ? ", user_password = '" . user()->password_hash($row['user_newpasswd']) . "', user_newpasswd = ''" : '';

    $sql = 'UPDATE ' . BB_USERS . "
		SET user_active = 1, user_actkey = '', user_actkey_time = 0" . $sql_update_pass . '
		WHERE user_id = ' . $row['user_id'];
    if (!($result = DB()->sql_query($sql))) {
        bb_die('Could not update users table');
    }

    $message = ($sql_update_pass == '') ? __('ACCOUNT_ACTIVE') : __('PASSWORD_ACTIVATED');
    bb_die($message, 200);
}

bb_die(__('WRONG_ACTIVATION'), 400);
