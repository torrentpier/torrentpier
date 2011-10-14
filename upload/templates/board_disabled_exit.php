<?php

if (!defined('BB_ROOT')) die(basename(__FILE__));

send_no_cache_headers();

$message = '';

if (!empty($_POST['subject']))
{
	$message .= $_POST['subject'] ."\r\n\r\n";
}
if (!empty($_POST['message']))
{
	$message .= $_POST['message'];
}

?><!DOCTYPE HTML PUBLIC "-//W3C//DTD HTML 4.01//EN" "http://www.w3.org/TR/html4/strict.dtd">
<html>
<head>
	<meta http-equiv="Content-Type" content="text/html; charset=UTF-8">
	<meta http-equiv="Content-Style-Type" content="text/css">
	<title><?php echo $bb_cfg['server_name']; ?></title>
	<style type="text/css">
	body { min-width: 760px; color: #000000; background: #E3E3E3; font: bold 16px Verdana; }
	.msg { font: 12px Verdana; }
	</style>
</head>
<body>

<div>

	<br />
	<br />
	<p style="margin: 1em 0; text-align: center;"><?php echo $bb_cfg['board_disabled_msg']; ?></p>

	<?php if ($message) { ?>
	<br />
	<br />
	<p class="msg">ваше сообщение не было отправлено:</p>
	<textarea rows="18" cols="92"><?php echo $message ?></textarea>
	<?php } ?>

</div>

</body>
</html>

<?php exit; ?>