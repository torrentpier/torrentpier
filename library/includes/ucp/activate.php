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

if (empty($_GET['u']) || empty($_GET['act_key'])) {
    bb_die('Bad request');
}

$sql = "SELECT user_active, user_id, username, user_email, user_newpasswd, user_lang, user_actkey
	FROM " . BB_USERS . "
	WHERE user_id = " . (int)$_GET[POST_USERS_URL];
if (!($result = DB()->sql_query($sql))) {
    bb_die('Could not obtain user information');
}

if ($row = DB()->sql_fetchrow($result)) {
    if ($row['user_active'] && trim($row['user_actkey']) == '') {
        bb_die($lang['ALREADY_ACTIVATED']);
    } elseif ((trim($row['user_actkey']) == trim($_GET['act_key'])) && (trim($row['user_actkey']) != '')) {
        $sql_update_pass = ($row['user_newpasswd'] != '') ? ", user_password = '" . md5(md5($row['user_newpasswd'])) . "', user_newpasswd = ''" : '';

        $sql = "UPDATE " . BB_USERS . "
			SET user_active = 1, user_actkey = ''" . $sql_update_pass . "
			WHERE user_id = " . $row['user_id'];
        if (!($result = DB()->sql_query($sql))) {
            bb_die('Could not update users table');
        }

        $message = ($sql_update_pass == '') ? $lang['ACCOUNT_ACTIVE'] : $lang['PASSWORD_ACTIVATED'];
        bb_die($message);
    } else {
        bb_die($lang['WRONG_ACTIVATION']);
    }
} else {
    bb_die($lang['NO_SUCH_USER']);
}
