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

require_once("include/bittorrent.php");

dbconn();

loggedinorreturn();

$new_tr_url = "http://torrentpier.me/"; // with ending slash
$subject = "Переезд на новый движок";
$msg = '[b]Внимание![/b] Наш трекер переехал на новый движок! Адрес трекера - [url=' . $new_tr_url . ']' . $new_tr_url . '[/url]
	Вся база перенесена на новый движок, регистрироваться заново не надо.
	Войти на трекер можно [url=' . $new_tr_url . 'login.php]здесь[/url]. Ваши данные на новом трекере:
	[b]Логин:[/b] %s
	[b]Пароль:[/b] %s
	Сменить пароль можно после входа на трекер в [url=' . $new_tr_url . 'profile.php?mode=editprofile]настройках[/url].';

if (empty($_POST['confirm'])) {
    stdhead();
    echo '
		<br />
		<center>
		<form action="' . $_SERVER['PHP_SELF'] . '" method="post">
		<input type="submit" name="confirm" value="Start mass PM" />
		</form>
		</center>
	';
} else {
    if (!file_exists('passwords.php')) {
        stderr($tracker_lang['error'], 'passwords.php not exists');
    }

    include('passwords.php');
    stdhead();
    foreach ($passwords as $user) {
        $msg_sql = sprintf($msg, $user['username'], $user['new_passwd']);
        sql_query("INSERT INTO messages (receiver, added, subject, msg)	VALUES({$user['tb_user_id']}, NOW(), " . sqlesc($subject) . ", " . sqlesc($msg_sql) . ")");
    }
    stdmsg('OK', 'Mass PM succesful');
}

stdfoot();
