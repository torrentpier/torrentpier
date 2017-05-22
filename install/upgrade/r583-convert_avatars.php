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

while (@ob_end_flush()) ;
ob_implicit_flush();
error_reporting(E_ALL);
ini_set('display_errors', 1);

$user->session_start();

set_die_append_msg();
if (!IS_SUPER_ADMIN) {
    bb_die($lang['ONLY_FOR_SUPER_ADMIN']);
}

$confirm = request_var('confirm', '');

if ($confirm) {
    DB()->query("ALTER TABLE " . BB_USERS . " ADD `avatar_ext_id` TINYINT( 4 ) NOT NULL AFTER `user_rank`");

    $rows_per_cycle = 10000;

    $row = DB()->fetch_row("SELECT MAX(user_id) AS end_id FROM " . BB_USERS);
    $end_id = (int)$row['end_id'];
    $start = $avatars_ok = $avatars_err = 0;

    echo "<pre>\n";

    while (true) {
        set_time_limit(600);
        echo "$start [ $avatars_ok / $avatars_err ]\n";
        $end = $start + $rows_per_cycle - 1;
        $sql = "
			SELECT user_id, avatar_ext_id, user_avatar
			FROM " . BB_USERS . "
			WHERE user_avatar != ''
				AND avatar_ext_id = 0
				AND user_id BETWEEN $start AND $end
			ORDER BY NULL
		";

        foreach (DB()->fetch_rowset($sql) as $row) {
            $FILE = array(
                'name' => '',
                'type' => '',
                'size' => 0,
                'tmp_name' => BB_ROOT . $bb_cfg['avatar_path'] . '/' . basename($row['user_avatar']),
                'error' => 0,
            );
            $upload = new TorrentPier\Legacy\Common\Upload();

            if ($upload->init($bb_cfg['avatars'], $FILE, false) and $upload->store('avatar', $row)) {
                DB()->query("UPDATE " . BB_USERS . " SET avatar_ext_id = {$upload->file_ext_id} WHERE user_id = {$row['user_id']} LIMIT 1");
                $avatars_ok++;
            } else {
                echo "{$row['user_id']}: ", implode("\n{$row['user_id']}: ", $upload->errors), "\n";
                $avatars_err++;
            }
        }

        if ($end > $end_id) {
            break;
        }
        $start += $rows_per_cycle;
        sleep(1);
    }

    echo "---------- База данных успешно обновлена. Аватары указанных выше пользователей перенесены не были. ----------\n";

    DB()->query("ALTER TABLE " . BB_USERS . " DROP `user_avatar`");
    DB()->query("ALTER TABLE " . BB_USERS . " DROP `user_avatar_type`");
} else {
    $msg = '<form method="POST">';
    $msg .= '<h1 style="color: red">Перед тем как нажать на кнопку, сделайте бекап базы данных! В ходе обновления базы данных, произойдет автоматическая конвертация имеющихся аватаров пользователей
	по новому алгоритму. Для конвертации аватарка пользователя должна соответствовать текущим значениям из конфига: ширина не более ' . $bb_cfg['avatars']['max_width'] . ' пикселов, высота не более ' . $bb_cfg['avatars']['max_height'] . ' пикселов
	и объем не более ' . $bb_cfg['avatars']['max_size'] . ' байт. Если эти условия не соблюдены - аватарка пользователя не будет конвертирована и пользователю придется залить ее заново! Если вы хотите поправить указанные
	значения - ПЕРЕД обновлением базы данных сделайте это в config.php!</h1><br />';
    $msg .= '<input type="submit" name="confirm" value="Начать обновление Базы Данных (R583)" style="height: 30px; font:bold 14px Arial, Helvetica, sans-serif;" />';
    $msg .= '</form>';

    bb_die($msg);
}
