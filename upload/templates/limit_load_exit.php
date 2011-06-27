<?php

if (!defined('IN_PHPBB'))	die(basename(__FILE__));

global $DBS;

if (!empty($DBS))
{
	DB()->close();
}
send_no_cache_headers();

$redirect_url = !empty($_POST['redirect']) ? $_POST['redirect'] : $_SERVER['REQUEST_URI'];


	// LOG
	global $userdata;

	if ($userdata['username'])
	{
		$name = html_entity_decode($userdata['username']);
	}
	else if (!empty($_POST['login_username']))
	{
		$name = $_POST['login_username'];
	}
	else
	{
		$name = '';
	}

	$file = 'load/load-'. date('m-d');
	$str = array();
	$str[] = date('H:i:s');
	$str[] = sprintf('%-5s', floatval(LOADAVG));
	$str[] = sprintf('%-15s', $_SERVER['REMOTE_ADDR']);
	$str[] = sprintf('%-20s', $name);
	$str[] = $redirect_url;
	$str = join(LOG_SEPR, $str) . LOG_LF;
	bb_log($str, $file);


?><!DOCTYPE HTML PUBLIC "-//W3C//DTD HTML 4.01//EN" "http://www.w3.org/TR/html4/strict.dtd">
<html>
<head>
	<meta http-equiv="Content-Type" content="text/html; charset=UTF-8">
	<meta http-equiv="Content-Style-Type" content="text/css">
	<title><?php echo $bb_cfg['sitename']?></title>
	<style type="text/css">
	body { min-width: 760px; color: #000000; background: #E3E3E3; font: 16px Verdana; }
	.msg { margin: 20%; text-align: center; background: #EFEFEF; border: 1px solid #B7C0C5; }
	</style>
</head>
<body>

<form action="login.php" method="post">
<input type="hidden" name="redirect" value="<?php echo $redirect_url ?>" />

<div class="msg">
	<p style="margin: 1em 0;">Извините, в данный момент сервер перегружен.</p>
	<p style="margin: 1em 0;">Попробуйте зайти через несколько минут.</p>
	<p style="margin: 1.5em 0;"><input type="submit" name="enter" value="Вход" /></p>
</div>

</form>

</body>
</html>

<?php exit; ?>