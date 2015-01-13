<?php

define('BB_SCRIPT', 'dl');
define('NO_GZIP', true);
define('BB_ROOT',  './');
require(BB_ROOT .'common.php');

if (!$topic_id = (int) request_var('t', 0))
{
	bb_simple_die('Ошибочный запрос: не указан topic_id'); // TODO
}

$user->session_start();

global $bb_cfg, $lang, $userdata;

// $t_data
$sql = "
		SELECT t.*, f.*
		FROM ". BB_TOPICS ." t, ". BB_FORUMS ." f
		WHERE t.topic_id = $topic_id
			AND f.forum_id = t.forum_id
		LIMIT 1
";
if (!$t_data = DB()->fetch_row($sql))
{
	bb_simple_die('Файл не найден [DB]'); // TODO
}
if (!$t_data['attach_ext_id'])
{
	bb_simple_die('Файл не найден [EXT_ID]'); // TODO
}

// Auth check
$is_auth = auth(AUTH_ALL, $t_data['forum_id'], $userdata, $t_data);
$guest_allow = false;
if (!IS_GUEST) $guest_allow = true;
if (IS_GUEST && $bb_cfg['guest_tracker']) $guest_allow = true;
if ($t_data['attach_ext_id'] != 8 && !$is_auth['auth_download']) login_redirect($bb_cfg['dl_url'] . $topic_id);
if ($t_data['attach_ext_id'] == 8 && (!$is_auth['auth_download'] || !$guest_allow)) login_redirect($bb_cfg['dl_url'] . $topic_id);


// Проверка рефёрера (не качать с других сайтов)
$referer = (!empty($_SERVER['HTTP_REFERER'])) ? $_SERVER['HTTP_REFERER'] : '';
if (!preg_match("/". $bb_cfg['server_name']."/", $referer)) exit;

DB()->sql_query('UPDATE ' . BB_TOPICS . ' SET download_count = download_count + 1 WHERE topic_id = ' . (int) $t_data['topic_id']);

// Captcha for guest
if (IS_GUEST && !bb_captcha('check'))
{
	global $template;

	$redirect_url = isset($_POST['redirect_url']) ? $_POST['redirect_url'] : (isset($_SERVER['HTTP_REFERER']) ? $_SERVER['HTTP_REFERER'] : '/');
	$message = '<form action="'. DOWNLOAD_URL . $attachment['attach_id'] .'" method="post">';
	$message .= $lang['CAPTCHA'].':';
	$message .= '<div  class="mrg_10" align="center">'. bb_captcha('get') .'</div>';
	$message .= '<input type="hidden" name="redirect_url" value="'. $redirect_url .'" />';
	$message .= '<input type="submit" class="bold" value="'. $lang['SUBMIT'] .'" /> &nbsp;';
	$message .= '<input type="button" class="bold" value="'. $lang['GO_BACK'] .'" onclick="document.location.href = \''. $redirect_url .'\';" />';
	$message .= '</form>';

	$template->assign_vars(array(
		'ERROR_MESSAGE' => $message,
	));

	require(PAGE_HEADER);
	require(PAGE_FOOTER);
}

$t_data['user_id'] = $userdata['user_id'];
$t_data['is_am']   = IS_AM;

//die(var_dump($t_data));

// Torrent
if ($t_data['attach_ext_id'] == 8)
{
	if (!(isset($_GET['original']) && !IS_USER))
	{
		require(INC_DIR .'functions_torrent.php');
		send_torrent_with_passkey($t_data);
	}
}

// All other
$file_path = get_attach_path($topic_id);

if (($file_contents = @file_get_contents($file_path)) === false)
{
	bb_simple_die("Файл не найден [HDD]"); // TODO
}

$send_filename = "t-$topic_id.". $bb_cfg['file_id_ext'][$t_data['attach_ext_id']];

header("Content-Type: application/x-download; name=\"$send_filename\"");
header("Content-Disposition: attachment; filename=\"$send_filename\"");

bb_exit($file_contents);