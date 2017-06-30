<?php
/**
 * TorrentPier – Bull-powered BitTorrent tracker engine
 *
 * @copyright Copyright (c) 2005-2017 TorrentPier (https://torrentpier.com)
 * @link      https://github.com/torrentpier/torrentpier for the canonical source repository
 * @license   https://github.com/torrentpier/torrentpier/blob/master/LICENSE MIT License
 */

if (!defined('BB_ROOT')) {
    die(basename(__FILE__));
}

if (empty($_GET['u']) || empty($_GET['act_key'])) {
    bb_die('Bad request');
}

$sql = "SELECT user_active, user_id, username, user_email, user_newpasswd, user_lang, user_actkey
	FROM " . BB_USERS . "
	WHERE user_id = " . (int)$_GET[POST_USERS_URL];
if (!($result = OLD_DB()->sql_query($sql))) {
    bb_die('Could not obtain user information');
}

if ($row = OLD_DB()->sql_fetchrow($result)) {
    if ($row['user_active'] && trim($row['user_actkey']) == '') {
        bb_die(trans('messages.ALREADY_ACTIVATED'));
    } elseif ((trim($row['user_actkey']) == trim($_GET['act_key'])) && (trim($row['user_actkey']) != '')) {
        $sql_update_pass = ($row['user_newpasswd'] != '') ? ", user_password = '" . md5(md5($row['user_newpasswd'])) . "', user_newpasswd = ''" : '';

        $sql = "UPDATE " . BB_USERS . "
			SET user_active = 1, user_actkey = ''" . $sql_update_pass . "
			WHERE user_id = " . $row['user_id'];
        if (!($result = OLD_DB()->sql_query($sql))) {
            bb_die('Could not update users table');
        }

        $message = ($sql_update_pass == '') ? trans('messages.ACCOUNT_ACTIVE') : trans('messages.PASSWORD_ACTIVATED');
        bb_die($message);
    } else {
        bb_die(trans('messages.WRONG_ACTIVATION'));
    }
} else {
    bb_die(trans('messages.NO_SUCH_USER'));
}
