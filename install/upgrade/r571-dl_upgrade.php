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

define('IN_FORUM', true);
define('BB_ROOT', './');
require(BB_ROOT . 'common.php');

$user->session_start();

set_die_append_msg();
if (!IS_SUPER_ADMIN) {
    bb_die($lang['ONLY_FOR_SUPER_ADMIN']);
}

$confirm = request_var('confirm', '');

if ($confirm) {
    DB()->query("
		CREATE TEMPORARY TABLE tmp_buf_dlstatus (
			user_id      mediumint(9)          NOT NULL default '0',
			topic_id     mediumint(8) unsigned NOT NULL default '0',
			user_status  tinyint(1)            NOT NULL default '0',
			PRIMARY KEY (user_id, topic_id)
		) ENGINE = MyISAM
	");

    DB()->query("
		INSERT INTO tmp_buf_dlstatus
			(user_id, topic_id, user_status)
		SELECT
			user_id, topic_id, user_status
		FROM bb_bt_dlstatus_new
	");

    DB()->query("
		REPLACE INTO bb_bt_dlstatus_main
			(user_id, topic_id, user_status)
		SELECT
			user_id, topic_id, user_status
		FROM tmp_buf_dlstatus
	");

    DB()->query("DROP TEMPORARY TABLE IF EXISTS tmp_buf_dlstatus");
    DB()->query("RENAME TABLE bb_bt_dlstatus_main TO bb_bt_dlstatus");

    DB()->query("DROP TABLE IF EXISTS bb_bt_dlstatus_mrg");
    DB()->query("DROP TABLE IF EXISTS bb_bt_dlstatus_new");

    bb_die('<h1 style="color: green">База данных обновлена</h1>');
} else {
    $msg = '<form method="POST">';
    $msg .= '<h1 style="color: red">!!! Перед тем как нажать на кнопку, сделайте бекап базы данных !!!</h1><br />';
    $msg .= '<input type="submit" name="confirm" value="Начать обновление Базы Данных (R571)" style="height: 30px; font:bold 14px Arial, Helvetica, sans-serif;" />';
    $msg .= '</form>';

    bb_die($msg);
}
