<?php

define('IN_FORUM', true);
define('BB_ROOT', './');
require(BB_ROOT . 'common.php');

while (@ob_end_flush()) ;
ob_implicit_flush();
error_reporting(E_ALL);
ini_set('display_errors', 1);

$user->session_start();

set_die_append_msg();
if (!IS_SUPER_ADMIN) bb_die($lang['ONLY_FOR_SUPER_ADMIN']);

$confirm = request_var('confirm', '');

if ($confirm) {
	DB()->query("UPDATE " . BB_CONFIG . " SET `config_value` = 'ru' WHERE `config_name` = 'default_lang'");
	DB()->query("ALTER TABLE " . BB_USERS . " ADD `user_twitter` varchar (15) NOT NULL DEFAULT '' AFTER `user_skype`");

	$rows_per_cycle = 10000;

	$row = DB()->fetch_row("SELECT MAX(user_id) AS end_id FROM " . BB_USERS);
	$end_id = (int)$row['end_id'];
	$start = 0;

	while (true) {
		set_time_limit(600);
		$end = $start + $rows_per_cycle - 1;

		DB()->query("UPDATE " . BB_USERS . " SET user_lang = 'ru' WHERE user_lang = 'russian'");
		DB()->query("UPDATE " . BB_USERS . " SET user_lang = 'en' WHERE user_lang = 'english'");

		if ($end > $end_id) {
			break;
		}
		$start += $rows_per_cycle;
		sleep(1);
	}

	bb_die("База данных успешно обновлена. Можно приступать к обновлению файлов. Не забудьте удалить этот файл.");
} else {
	$msg = '<form method="POST">';
	$msg .= '<h1 style="color: red">Перед тем как нажать на кнопку, сделайте бекап базы данных! В ходе обновления базы данных, произойдет автоматическая конвертация текущих языков интерфейса пользователей
	на новое именование, а также будет добавлено поле в базу данных пользователей, для их Twitter-аккаунтов. После этого, вам можно будет приступать к обновлению файлов.</h1><br />';
	$msg .= '<input type="submit" name="confirm" value="Начать обновление Базы Данных (R588)" style="height: 30px; font:bold 14px Arial, Helvetica, sans-serif;" />';
	$msg .= '</form>';

	bb_die($msg);
}