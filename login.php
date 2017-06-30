<?php
/**
 * TorrentPier – Bull-powered BitTorrent tracker engine
 *
 * @copyright Copyright (c) 2005-2017 TorrentPier (https://torrentpier.com)
 * @link      https://github.com/torrentpier/torrentpier for the canonical source repository
 * @license   https://github.com/torrentpier/torrentpier/blob/master/LICENSE MIT License
 */

define('BB_SCRIPT', 'login');
define('IN_LOGIN', true);
define('BB_ROOT', './');
require __DIR__ . '/common.php';

array_deep($_POST, 'trim');

$user->session_start();

// Logout
if (!empty($_GET['logout'])) {
    if (!IS_GUEST) {
        $user->session_end();
    }
    redirectToUrl("index.php");
}

$redirect_url = "index.php";
$login_errors = array();

// Requested redirect
if (preg_match('/^redirect=([a-z0-9\.#\/\?&=\+\-_]+)/si', $_SERVER['QUERY_STRING'], $matches)) {
    $redirect_url = $matches[1];

    if (false === strpos($redirect_url, '?') && $first_amp = strpos($redirect_url, '&')) {
        $redirect_url[$first_amp] = '?';
    }
} elseif (!empty($_POST['redirect'])) {
    $redirect_url = str_replace('&amp;', '&', htmlspecialchars($_POST['redirect']));
} elseif (!empty($_SERVER['HTTP_REFERER']) && ($parts = @parse_url($_SERVER['HTTP_REFERER']))) {
    $redirect_url = ($parts['path'] ?? "index.php") . (isset($parts['query']) ? '?' . $parts['query'] : '');
}

$redirect_url = str_replace('&admin=1', '', $redirect_url);
$redirect_url = str_replace('?admin=1', '', $redirect_url);

if (!$redirect_url || false !== strpos(urldecode($redirect_url), "\n") || false !== strpos(urldecode($redirect_url), "\r") || false !== strpos(urldecode($redirect_url), ';url')) {
    $redirect_url = "index.php";
}

$redirect_url = str_replace("&sid={$user->data['session_id']}", '', $redirect_url);

if (isset($_REQUEST['admin']) && !IS_AM) {
    bb_die(trans('messages.NOT_ADMIN'));
}

$mod_admin_login = (IS_AM && !$user->data['session_admin']);

// login username & password
$login_username = $mod_admin_login ? $userdata['username'] : ($_POST['login_username'] ?? '');
$login_password = $_POST['login_password'] ?? '';

// Проверка на неверную комбинацию логин/пароль
$need_captcha = false;
if (!$mod_admin_login) {
    $need_captcha = OLD_CACHE('bb_login_err')->get('l_err_' . USER_IP);
    if ($need_captcha < config('tp.invalid_logins')) {
        $need_captcha = false;
    }
}

// login
if (isset($_POST['login'])) {
    if (!$mod_admin_login) {
        if (!IS_GUEST) {
            redirectToUrl('index.php');
        }
        if ($login_username == '' || $login_password == '') {
            $login_errors[] = trans('messages.ENTER_PASSWORD');
        }
    }

    // Captcha
    if ($need_captcha && !bb_captcha('check') && !config('tp.captcha.disabled')) {
        $login_errors[] = trans('messages.CAPTCHA_WRONG');
    }

    if (!$login_errors) {
        if ($user->login($_POST, $mod_admin_login)) {
            $redirect_url = defined('FIRST_LOGON') ? config('tp.first_logon_redirect_url') : $redirect_url;
            // Обнуление при введении правильно комбинации логин/пароль
            OLD_CACHE('bb_login_err')->set('l_err_' . USER_IP, 0, 3600);

            if ($redirect_url == '/' . LOGIN_URL || $redirect_url == LOGIN_URL) {
                $redirect_url = 'index.php';
            }
            redirectToUrl($redirect_url);
        }

        $login_errors[] = trans('messages.ERROR_LOGIN');

        if (!$mod_admin_login) {
            $login_err = OLD_CACHE('bb_login_err')->get('l_err_' . USER_IP);
            if ($login_err > config('tp.invalid_logins')) {
                $need_captcha = true;
            }
            OLD_CACHE('bb_login_err')->set('l_err_' . USER_IP, $login_err + 1, 3600);
        } else {
            $need_captcha = false;
        }
    }
}

// Login page
if (IS_GUEST || $mod_admin_login) {
    $template->assign_vars(array(
        'LOGIN_USERNAME' => htmlCHR($login_username),
        'LOGIN_PASSWORD' => htmlCHR($login_password),
        'ERROR_MESSAGE' => implode('<br />', $login_errors),
        'ADMIN_LOGIN' => $mod_admin_login,
        'REDIRECT_URL' => htmlCHR($redirect_url),
        'CAPTCHA_HTML' => ($need_captcha && !config('tp.captcha.disabled')) ? bb_captcha('get') : '',
        'PAGE_TITLE' => trans('messages.LOGIN'),
        'S_LOGIN_ACTION' => LOGIN_URL,
    ));

    print_page('login.tpl');
}

redirectToUrl($redirect_url);
