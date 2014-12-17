<?php

define('BB_SCRIPT', 'login');
define('IN_LOGIN', true);
define('BB_ROOT', './');
require(BB_ROOT .'common.php');

array_deep($_POST, 'trim');

$user->session_start();

// Logout
if (!empty($_GET['logout']))
{
	if (!IS_GUEST)
	{
		$user->session_end();
	}
	redirect("index.php");
}

$redirect_url = "index.php";
$login_errors = array();

// Requested redirect
if (preg_match('/^redirect=([a-z0-9\.#\/\?&=\+\-_]+)/si', $_SERVER['QUERY_STRING'], $matches))
{
	$redirect_url = $matches[1];

	if (!strstr($redirect_url, '?') && $first_amp = strpos($redirect_url, '&'))
	{
		$redirect_url[$first_amp] = '?';
	}
}
elseif (!empty($_POST['redirect']))
{
	$redirect_url = str_replace('&amp;', '&', htmlspecialchars($_POST['redirect']));
}
elseif (!empty($_SERVER['HTTP_REFERER']) && ($parts = @parse_url($_SERVER['HTTP_REFERER'])))
{
	$redirect_url = (isset($parts['path']) ? $parts['path'] : "index.php") . (isset($parts['query']) ? '?'. $parts['query'] : '');
}

$redirect_url = str_replace('&admin=1', '', $redirect_url);
$redirect_url = str_replace('?admin=1', '', $redirect_url);

if (!$redirect_url || strstr(urldecode($redirect_url), "\n") || strstr(urldecode($redirect_url), "\r") || strstr(urldecode($redirect_url), ';url'))
{
	$redirect_url = "index.php";
}

$redirect_url = str_replace("&sid={$user->data['session_id']}", '', $redirect_url);

if (isset($_REQUEST['admin']) && !IS_AM) bb_die($lang['NOT_ADMIN']);

$mod_admin_login = (IS_AM && !$user->data['session_admin']);

// login username & password
$login_username = ($mod_admin_login) ? $userdata['username'] : (isset($_POST['login_username']) ? $_POST['login_username'] : '');
$login_password = isset($_POST['login_password']) ? $_POST['login_password'] : '';

// Проверка на неверную комбинацию логин/пароль
$need_captcha = false;
if (!$mod_admin_login)
{
	$need_captcha = CACHE('bb_login_err')->get('l_err_'. USER_IP);
	if ($need_captcha < $bb_cfg['invalid_logins']) $need_captcha = false;
}

// login
if (isset($_POST['login']))
{
	if (!$mod_admin_login)
	{
		if (!IS_GUEST)
		{
			redirect('index.php');
		}
		if ($login_username == '' || $login_password == '')
		{
			$login_errors[] = $lang['ENTER_PASSWORD'];
		}
	}

	// Captcha
	if ($need_captcha && !bb_captcha('check') && !$bb_cfg['captcha']['disabled'])
	{
		$login_errors[] = $lang['CAPTCHA_WRONG'];
	}

	if (!$login_errors)
	{
		if ($user->login($_POST, $mod_admin_login))
		{
			$redirect_url = (defined('FIRST_LOGON')) ? $bb_cfg['first_logon_redirect_url'] : $redirect_url;
			// Обнуление при введении правильно комбинации логин/пароль
			CACHE('bb_login_err')->set('l_err_'. USER_IP, 0, 3600);

			if ($redirect_url == '/' . LOGIN_URL || $redirect_url == LOGIN_URL) $redirect_url = 'index.php';
			redirect($redirect_url);
		}

		$login_errors[] = $lang['ERROR_LOGIN'];

		if (!$mod_admin_login)
		{
			$login_err = CACHE('bb_login_err')->get('l_err_'. USER_IP);
			if ($login_err > $bb_cfg['invalid_logins']) $need_captcha = true;
			if ($login_err > 50)
			{
				// TODO temp ban ip
			}
			CACHE('bb_login_err')->set('l_err_'. USER_IP, ($login_err + 1), 3600);
		}
		else $need_captcha = false;
	}
}

// Login page
if (IS_GUEST || $mod_admin_login)
{
	$template->assign_vars(array(
		'LOGIN_USERNAME'  => htmlCHR($login_username),
		'LOGIN_PASSWORD'  => htmlCHR($login_password),
		'ERROR_MESSAGE'   => join('<br />', $login_errors),
		'ADMIN_LOGIN'     => $mod_admin_login,
		'REDIRECT_URL'    => htmlCHR($redirect_url),
		'CAPTCHA_HTML'    => ($need_captcha && !$bb_cfg['captcha']['disabled']) ? bb_captcha('get') : '',
		'PAGE_TITLE'      => $lang['LOGIN'],
		'S_LOGIN_ACTION'  => LOGIN_URL,
	));

	print_page('login.tpl');
}

redirect($redirect_url);