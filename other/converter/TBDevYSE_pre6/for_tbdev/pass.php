<?php
	
require_once("include/bittorrent.php");

dbconn();

loggedinorreturn();

$new_tr_url = "http://pushchino.tv/forum/"; // with ending slash
$subject = "Переезд на новый движок";
$msg = '[b]Внимание![/b] Наш трекер переехал на новый движок! Адрес трекера - [url='.$new_tr_url.']'.$new_tr_url.'[/url] 
	Вся база перенесена на новый движок, регистрироваться заново не надо. 
	Войти на трекер можно [url='.$new_tr_url.'login.php]здесь[/url]. Ваши данные на новом трекере: 
	[b]Логин:[/b] %s 
	[b]Пароль:[/b] %s 
	Сменить пароль можно после входа на трекер в [url='.$new_tr_url.'profile.php?mode=editprofile]настройках[/url].';
	


if (empty($_POST['confirm']))
{
	stdhead();
	echo '
		<br />
		<center>
		<form action="'. $_SERVER['PHP_SELF'] .'" method="post">
		<input type="submit" name="confirm" value="Start mass PM" />
		</form>
		</center>
	';
}
else
{
	if(!file_exists('passwords.php')) stderr($tracker_lang['error'], 'passwords.php not exists');
	
	include('passwords.php');
	stdhead();
	foreach ($passwords as $user)
	{
		$msg_sql = sprintf($msg, $user['username'], $user['new_passwd']);
		sql_query("INSERT INTO messages (receiver, added, subject, msg)	VALUES({$user['tb_user_id']}, NOW(), ".sqlesc($subject).", ".sqlesc($msg_sql).")");
	}	
	stdmsg('OK', 'Mass PM succesful');
}

stdfoot();