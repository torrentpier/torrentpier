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
    DB()->query("ALTER TABLE " . BB_USERS . " CHANGE COLUMN user_birthday user_birthday_old int(11) NOT NULL DEFAULT 0 AFTER user_gender");
    DB()->query("ALTER TABLE " . BB_USERS . " ADD user_birthday date NOT NULL DEFAULT '0000-00-00' AFTER user_gender");

    $sql = "SELECT user_id, user_birthday_old FROM " . BB_USERS . " WHERE user_birthday_old != 0 AND user_id NOT IN ('" . EXCLUDED_USERS_CSV . "')";

    foreach (DB()->fetch_rowset($sql) as $row) {
        $birthday = bb_date($row['user_birthday_old'] * 86400 + 1, 'Y-m-d', 0);
        DB()->query("UPDATE " . BB_USERS . " SET user_birthday = '" . $birthday . "' WHERE user_id = " . $row['user_id'] . "");
    }

    DB()->query("ALTER TABLE " . BB_USERS . " DROP user_birthday_old");

    bb_die('<h1 style="color: green">База данных обновлена</h1>');
} else {
    $msg = '<form method="POST">';
    $msg .= '<h1 style="color: red">!!! Перед тем как нажать на кнопку, сделайте бекап базы данных !!!</h1><br />';
    $msg .= '<input type="submit" name="confirm" value="Начать обновление Базы Данных (R496)" style="height: 30px; font:bold 14px Arial, Helvetica, sans-serif;" />';
    $msg .= '</form>';

    bb_die($msg);
}
