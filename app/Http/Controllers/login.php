<?php

/**
 * TorrentPier – Bull-powered BitTorrent tracker engine
 *
 * @copyright Copyright (c) 2005-2025 TorrentPier (https://torrentpier.com)
 * @link      https://github.com/torrentpier/torrentpier for the canonical source repository
 * @license   https://github.com/torrentpier/torrentpier/blob/master/LICENSE MIT License
 */

/*
 * ===========================================================================
 * Refactor to Modern Controller
 * ===========================================================================
 * Target: Convert to PSR-7 controller with constructor dependency injection
 *
 * Dependencies to inject:
 * - TorrentPier\Config (configuration access)
 * - TorrentPier\Database\Database (database operations)
 * - TorrentPier\Legacy\User (user session and authentication)
 * - TorrentPier\Http\Request (HTTP request handling)
 * - TorrentPier\Legacy\Templates (template rendering)
 *
 * Target namespace: TorrentPier\Http\Controllers\Auth
 * Target class: LoginController
 *
 * Key refactoring tasks:
 * 1. Extract procedural code into controller methods (login, logout, register)
 * 2. Replace global function calls with injected dependencies
 * 3. Implement PSR-7 request/response handling
 * 4. Extract authentication logic into AuthService
 * 5. Add proper CSRF protection
 * 6. Implement rate limiting for login attempts
 * ===========================================================================
 */

define('IN_LOGIN', true);

page_cfg('allow_robots', false);

// Logout
if (request()->getBool('logout')) {
    if (!IS_GUEST) {
        user()->session_end();
    }
    redirect('/');
}

$redirect_url = '/';
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
    $redirect_url = ($parts['path'] ?? '/') . (isset($parts['query']) ? '?' . $parts['query'] : '');
}

$redirect_url = str_replace(['&admin=1', '?admin=1'], '', $redirect_url);

if (!$redirect_url || str_contains(urldecode($redirect_url), "\n") || str_contains(urldecode($redirect_url), "\r") || str_contains(urldecode($redirect_url), ';url')) {
    $redirect_url = '/';
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
    if ($need_captcha < config()->get('auth.invalid_logins')) {
        $need_captcha = false;
    }
}

// 2FA recovery/totp mode switch (via POST)
if (request()->post->has('switch_2fa_mode')) {
    $twoFaToken = request()->post->get('2fa_token', '');
    $switchMode = request()->post->get('switch_2fa_mode', '');

    print_page('login_2fa.twig', variables: [
        'TWO_FA_TOKEN' => htmlCHR($twoFaToken),
        'REDIRECT_URL' => htmlCHR($redirect_url),
        'PAGE_TITLE' => __('TWO_FACTOR_AUTH'),
        'USE_RECOVERY' => $switchMode === 'recovery',
        'ERROR_MESSAGE' => '',
        'S_LOGIN_ACTION' => LOGIN_URL,
    ]);
}

// 2FA verification
if (request()->post->has('verify_2fa')) {
    $twoFaToken = request()->post->get('2fa_token', '');
    $totpCode = request()->post->get('totp_code', '');
    $recoveryCode = request()->post->get('recovery_code', '');

    if ($recoveryCode && !preg_match('/^[A-Fa-f0-9]{4}-?[A-Fa-f0-9]{4}$/', trim($recoveryCode))) {
        $recoveryCode = '';
    }
    if ($totpCode && !preg_match('/^\d{6}$/', $totpCode)) {
        $totpCode = '';
    }
    $useRecovery = !empty($recoveryCode);

    // Helper to re-render 2FA page with error
    $render2fa = static fn (string $error = '') => print_page('login_2fa.twig', variables: [
        'TWO_FA_TOKEN' => htmlCHR($twoFaToken),
        'REDIRECT_URL' => htmlCHR($redirect_url),
        'PAGE_TITLE' => __('TWO_FACTOR_AUTH'),
        'USE_RECOVERY' => $useRecovery,
        'ERROR_MESSAGE' => $error,
        'S_LOGIN_ACTION' => LOGIN_URL,
    ]);

    // Validate pending session
    $pending = $twoFaToken ? CACHE('bb_cache')->get('2fa_pending_' . $twoFaToken) : null;
    $userId = is_array($pending) ? (int)($pending['user_id'] ?? 0) : 0;

    if (!$userId || ($pending['ip'] ?? '') !== USER_IP) {
        $render2fa(__('TWO_FACTOR_SESSION_EXPIRED'));
    }

    if (!two_factor()->checkRateLimit($userId)) {
        CACHE('bb_cache')->rm('2fa_pending_' . $twoFaToken);
        $render2fa(__('TWO_FACTOR_TOO_MANY_ATTEMPTS'));
    }

    // Load and validate user
    $userdata = get_userdata($userId, false, true);
    if (!$userdata || !$userdata['user_active']) {
        $render2fa(__('TWO_FACTOR_SESSION_EXPIRED'));
    }

    // Decrypt TOTP secret
    try {
        $decryptedSecret = two_factor()->decryptSecret($userdata['totp_secret']);
    } catch (RuntimeException) {
        $render2fa(__('TWO_FACTOR_SESSION_EXPIRED'));
    }

    // Verify code
    if ($useRecovery) {
        $hashedCodes = json_decode($userdata['totp_recovery_codes'], true) ?: [];
        $codeIndex = two_factor()->verifyRecoveryCode($recoveryCode, $hashedCodes);

        if ($codeIndex === false) {
            two_factor()->incrementAttempts($userId);
            $render2fa(__('TWO_FACTOR_INVALID_RECOVERY'));
        }
        two_factor()->consumeRecoveryCode($userId, $codeIndex, $hashedCodes);
    } else {
        if (!two_factor()->verifyCode($decryptedSecret, $totpCode, $userId)) {
            two_factor()->incrementAttempts($userId);
            $render2fa(__('TWO_FACTOR_INVALID_CODE'));
        }
    }

    // Success — clean up and create session
    CACHE('bb_cache')->rm('2fa_pending_' . $twoFaToken);
    two_factor()->clearAttempts($userId);
    CACHE('bb_login_err')->rm('l_err_' . USER_IP);

    $pendingModAdmin = !empty($pending['mod_admin_login']);
    if ($pendingModAdmin) {
        eloquent()->table(BB_SESSIONS)
            ->where('session_user_id', $userId)
            ->where('session_id', user()->data['session_id'])
            ->update(['session_admin' => $userdata['user_level']]);

        user()->data['session_admin'] = $userdata['user_level'];
        TorrentPier\Sessions::cache_update_userdata(user()->data);
    } else {
        user()->session_create($userdata, false);

        eloquent()->table(BB_SESSIONS)
            ->where('session_ip', USER_IP)
            ->where('session_user_id', GUEST_UID)
            ->delete();
    }

    if ($redirect_url == '/' . LOGIN_URL || $redirect_url == LOGIN_URL) {
        $redirect_url = '/';
    }
    redirect($redirect_url);
}

// login
if (request()->post->has('login')) {
    if (!$mod_admin_login) {
        if (!IS_GUEST) {
            redirect('/');
        }
        if ($login_username == '' || $login_password == '') {
            $login_errors[] = __('ENTER_PASSWORD');
        }
    }

    // Captcha
    if ($need_captcha && !config()->get('forum.captcha.disabled') && !bb_captcha('check')) {
        $login_errors[] = __('CAPTCHA_WRONG');
    }

    if (!$login_errors) {
        $loginResult = user()->login(request()->post->all(), $mod_admin_login);

        if (!empty($loginResult['2fa_required'])) {
            // User has 2FA enabled — show verification page
            print_page('login_2fa.twig', variables: [
                'TWO_FA_TOKEN' => htmlCHR($loginResult['2fa_token']),
                'REDIRECT_URL' => htmlCHR($redirect_url),
                'PAGE_TITLE' => __('TWO_FACTOR_AUTH'),
                'USE_RECOVERY' => false,
                'ERROR_MESSAGE' => '',
                'S_LOGIN_ACTION' => LOGIN_URL,
            ]);
        } elseif ($loginResult) {
            $redirect_url = (defined('FIRST_LOGON')) ? config()->get('auth.first_logon_redirect_url') : $redirect_url;
            // Reset when entering the correct login/password combination
            CACHE('bb_login_err')->rm('l_err_' . USER_IP);

            if ($redirect_url == '/' . LOGIN_URL || $redirect_url == LOGIN_URL) {
                $redirect_url = '/';
            }
            redirect($redirect_url);
        }

        if (empty($loginResult['2fa_required'])) {
            $login_errors[] = __('ERROR_LOGIN');
        }
    }

    if (empty($loginResult['2fa_required'])) {
        if (!$mod_admin_login) {
            $login_err = CACHE('bb_login_err')->get('l_err_' . USER_IP);
            if ($login_err > config()->get('auth.invalid_logins')) {
                $need_captcha = true;
            }
            CACHE('bb_login_err')->set('l_err_' . USER_IP, ($login_err + 1), 3600);
        } else {
            $need_captcha = false;
        }
    }
}

// Login page
if (IS_GUEST || $mod_admin_login) {
    print_page('login.twig', variables: [
        'LOGIN_USERNAME' => htmlCHR($login_username),
        'LOGIN_PASSWORD' => htmlCHR($login_password),
        'ERROR_MESSAGE' => implode('<br />', $login_errors),
        'ADMIN_LOGIN' => $mod_admin_login,
        'REDIRECT_URL' => htmlCHR($redirect_url),
        'CAPTCHA_HTML' => ($need_captcha && !config()->get('forum.captcha.disabled')) ? bb_captcha('get') : '',
        'PAGE_TITLE' => __('LOGIN'),
        'S_LOGIN_ACTION' => LOGIN_URL,
    ]);
}

redirect($redirect_url);
