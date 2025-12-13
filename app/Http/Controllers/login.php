<?php

/**
 * TorrentPier – Bull-powered BitTorrent tracker engine
 *
 * @copyright Copyright (c) 2005-2025 TorrentPier (https://torrentpier.com)
 * @link      https://github.com/torrentpier/torrentpier for the canonical source repository
 * @license   https://github.com/torrentpier/torrentpier/blob/master/LICENSE MIT License
 */
define('IN_LOGIN', true);

page_cfg('allow_robots', false);

// Logout
if (request()->getBool('logout')) {
    if (!IS_GUEST) {
        user()->session_end();
    }
    redirect('index.php');
}

$redirect_url = 'index.php';
$login_errors = [];

// Requested redirect
$queryString = request()->getQueryString() ?? '';
if (preg_match('/^redirect=([a-z0-9\.#\/\?&=\+\-_]+)/si', $queryString, $matches)) {
    $redirect_url = $matches[1];

    if (!str_contains($redirect_url, '?') && $first_amp = strpos($redirect_url, '&')) {
        $redirect_url[$first_amp] = '?';
    }
} elseif ($postRedirect = request()->post->get('redirect')) {
    $redirect_url = str_replace('&amp;', '&', htmlspecialchars($postRedirect));
} elseif (($referer = request()->getReferer()) && ($parts = @parse_url($referer))) {
    $redirect_url = ($parts['path'] ?? 'index.php') . (isset($parts['query']) ? '?' . $parts['query'] : '');
}

$redirect_url = str_replace(['&admin=1', '?admin=1'], '', $redirect_url);

if (!$redirect_url || str_contains(urldecode($redirect_url), "\n") || str_contains(urldecode($redirect_url), "\r") || str_contains(urldecode($redirect_url), ';url')) {
    $redirect_url = 'index.php';
}

$redirect_url = str_replace('&sid=' . user()->data['session_id'], '', $redirect_url);

if (request()->has('admin') && !IS_AM) {
    bb_die(__('NOT_ADMIN'));
}

$mod_admin_login = (IS_AM && !userdata('session_admin'));

// login username & password
$login_username = ($mod_admin_login) ? userdata('username') : request()->post->get('login_username', '');
$login_password = request()->post->get('login_password', '');

// Checking for incorrect login/password combination
$need_captcha = false;
if (!$mod_admin_login) {
    $need_captcha = CACHE('bb_login_err')->get('l_err_' . USER_IP);
    if ($need_captcha < config()->get('invalid_logins')) {
        $need_captcha = false;
    }
}

// login
if (request()->post->has('login')) {
    if (!$mod_admin_login) {
        if (!IS_GUEST) {
            redirect('index.php');
        }
        if ($login_username == '' || $login_password == '') {
            $login_errors[] = __('ENTER_PASSWORD');
        }
    }

    // Captcha
    if ($need_captcha && !config()->get('captcha.disabled') && !bb_captcha('check')) {
        $login_errors[] = __('CAPTCHA_WRONG');
    }

    if (!$login_errors) {
        if (user()->login(request()->post->all(), $mod_admin_login)) {
            $redirect_url = (defined('FIRST_LOGON')) ? config()->get('first_logon_redirect_url') : $redirect_url;
            // Reset when entering the correct login/password combination
            CACHE('bb_login_err')->rm('l_err_' . USER_IP);

            if ($redirect_url == '/' . LOGIN_URL || $redirect_url == LOGIN_URL) {
                $redirect_url = 'index.php';
            }
            redirect($redirect_url);
        }

        $login_errors[] = __('ERROR_LOGIN');
    }

    if (!$mod_admin_login) {
        $login_err = CACHE('bb_login_err')->get('l_err_' . USER_IP);
        if ($login_err > config()->get('invalid_logins')) {
            $need_captcha = true;
        }
        CACHE('bb_login_err')->set('l_err_' . USER_IP, ($login_err + 1), 3600);
    } else {
        $need_captcha = false;
    }
}

// Login page
if (IS_GUEST || $mod_admin_login) {
    template()->assign_vars([
        'LOGIN_USERNAME' => htmlCHR($login_username),
        'LOGIN_PASSWORD' => htmlCHR($login_password),
        'ERROR_MESSAGE' => implode('<br />', $login_errors),
        'ADMIN_LOGIN' => $mod_admin_login,
        'REDIRECT_URL' => htmlCHR($redirect_url),
        'CAPTCHA_HTML' => ($need_captcha && !config()->get('captcha.disabled')) ? bb_captcha('get') : '',
        'PAGE_TITLE' => __('LOGIN'),
        'S_LOGIN_ACTION' => LOGIN_URL,
    ]);

    print_page('login.tpl');
}

redirect($redirect_url);
