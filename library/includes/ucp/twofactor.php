<?php

/**
 * TorrentPier – Bull-powered BitTorrent tracker engine
 *
 * @copyright Copyright (c) 2005-2025 TorrentPier (https://torrentpier.com)
 * @link      https://github.com/torrentpier/torrentpier for the canonical source repository
 * @license   https://github.com/torrentpier/torrentpier/blob/master/LICENSE MIT License
 */

if (!defined('BB_ROOT')) {
    die(basename(__FILE__));
}

if (IS_GUEST) {
    login_redirect();
}

$user_id = (int)userdata('user_id');

// Allow disable even when feature is off, so users aren't trapped with stale 2FA
if (!two_factor()->isFeatureEnabled() && !(request()->getString('action', '') === 'disable' && two_factor()->isEnabledForUser($user_id))) {
    bb_die(__('TWO_FACTOR_FEATURE_DISABLED'));
}

$action = request()->getString('action', '');

switch ($action) {
    /**
     * Enable 2FA — Step 1: Show QR code and setup form
     */
    case 'enable':
        if (request()->isPost() && request()->post->has('verify_2fa')) {
            // Guard against dual-tab race condition
            if (two_factor()->isEnabledForUser($user_id)) {
                bb_die(__('TWO_FACTOR_ALREADY_ENABLED'));
            }

            // POST: Verify the code and enable 2FA
            $setup_token = (string)request()->post->get('setup_token', '');
            $code = (string)request()->post->get('totp_code', '');

            if (empty($setup_token) || empty($code)) {
                bb_die(__('TWO_FACTOR_INVALID_CODE'));
            }

            // Retrieve cached secret by setup token
            $cache_key = '2fa_setup_' . $setup_token;
            $cached = CACHE('bb_cache')->get($cache_key);

            if (!$cached || !isset($cached['secret'], $cached['user_id']) || $cached['user_id'] !== $user_id) {
                bb_die(__('TWO_FACTOR_SETUP_EXPIRED'));
            }

            $secret = $cached['secret'];

            // Verify the TOTP code with attempt limiting
            if (!two_factor()->verifyCode($secret, $code, $user_id)) {
                $cached['attempts'] = ($cached['attempts'] ?? 0) + 1;
                if ($cached['attempts'] >= 5) {
                    CACHE('bb_cache')->rm($cache_key);
                    bb_die(__('TWO_FACTOR_SETUP_EXPIRED'));
                }
                CACHE('bb_cache')->set($cache_key, $cached, 600);
                bb_die(__('TWO_FACTOR_INVALID_CODE'));
            }

            // Enable 2FA for this user
            $recovery_codes = two_factor()->enableForUser($user_id, $secret);

            // Clean up setup cache
            CACHE('bb_cache')->rm($cache_key);

            // Store recovery codes temporarily for display (one-time view)
            $download_token = bin2hex(random_bytes(16));
            CACHE('bb_cache')->set('2fa_recovery_' . $download_token, [
                'user_id' => $user_id,
                'codes' => $recovery_codes,
            ], 600); // 10 minutes

            redirect(FORUM_PATH . 'profile/two-step?action=recovery&token=' . $download_token);
        }

        // GET: Generate secret and show setup page
        if (two_factor()->isEnabledForUser($user_id)) {
            bb_die(__('TWO_FACTOR_ALREADY_ENABLED'));
        }

        $secret = two_factor()->generateSecret();
        $setup_token = bin2hex(random_bytes(16));

        // Cache the secret temporarily
        CACHE('bb_cache')->set('2fa_setup_' . $setup_token, [
            'user_id' => $user_id,
            'secret' => $secret,
        ], 600); // 10 minutes

        $manual_key = two_factor()->formatSecretForDisplay($secret);

        print_page('usercp_twofactor_setup.twig', variables: [
            'PAGE_TITLE' => __('TWO_FACTOR_SETUP'),
            'SETUP_TOKEN' => $setup_token,
            'SECRET_KEY' => $manual_key,
            'U_USER_PROFILE' => SETTINGS_URL,
        ]);
        break;

    /**
     * Show recovery codes (one-time display after enabling)
     */
    case 'recovery':
        $token = request()->getString('token', '');

        if (empty($token)) {
            bb_die(__('TWO_FACTOR_INVALID_TOKEN'));
        }

        $cache_key = '2fa_recovery_' . $token;
        $cached = CACHE('bb_cache')->get($cache_key);

        if (!$cached || !isset($cached['codes'], $cached['user_id']) || $cached['user_id'] !== $user_id) {
            bb_die(__('TWO_FACTOR_RECOVERY_EXPIRED'));
        }

        // Prepare download token (same token, separate cache entry for download)
        $download_token = bin2hex(random_bytes(16));
        CACHE('bb_cache')->set('2fa_download_' . $download_token, [
            'user_id' => $user_id,
            'codes' => $cached['codes'],
        ], 600); // 10 minutes

        print_page('usercp_twofactor_recovery.twig', variables: [
            'PAGE_TITLE' => __('TWO_FACTOR_RECOVERY_CODES'),
            'RECOVERY_CODES' => $cached['codes'],
            'DOWNLOAD_TOKEN' => $download_token,
            'U_USER_PROFILE' => SETTINGS_URL,
        ]);

        // Remove recovery display cache after showing (one-time view)
        CACHE('bb_cache')->rm($cache_key);
        break;

    /**
     * Disable 2FA — confirmation page and processing
     */
    case 'disable':
        if (!two_factor()->isEnabledForUser($user_id)) {
            bb_die(__('TWO_FACTOR_NOT_ENABLED'));
        }

        if (request()->isPost() && request()->post->has('disable_2fa')) {
            $code = (string)request()->post->get('totp_code', '');

            if (empty($code)) {
                bb_die(__('TWO_FACTOR_INVALID_CODE'));
            }

            if (!two_factor()->verifyUserCode($user_id, $code)) {
                bb_die(__('TWO_FACTOR_INVALID_CODE'));
            }

            two_factor()->disableForUser($user_id);

            meta_refresh(SETTINGS_URL, 5);
            bb_die(__('TWO_FACTOR_DISABLED_SUCCESS'));
        }

        // GET: Show disable confirmation form
        print_page('usercp_twofactor_disable.twig', variables: [
            'PAGE_TITLE' => __('TWO_FACTOR_DISABLE'),
            'U_USER_PROFILE' => SETTINGS_URL,
        ]);
        break;

    /**
     * Regenerate recovery codes
     */
    case 'regenerate':
        if (!two_factor()->isEnabledForUser($user_id)) {
            bb_die(__('TWO_FACTOR_NOT_ENABLED'));
        }

        if (request()->isPost() && request()->post->has('regenerate_codes')) {
            $code = (string)request()->post->get('totp_code', '');

            if (empty($code)) {
                bb_die(__('TWO_FACTOR_INVALID_CODE'));
            }

            if (!two_factor()->verifyUserCode($user_id, $code)) {
                bb_die(__('TWO_FACTOR_INVALID_CODE'));
            }

            $recovery_codes = two_factor()->regenerateRecoveryCodes($user_id);

            $download_token = bin2hex(random_bytes(16));
            CACHE('bb_cache')->set('2fa_recovery_' . $download_token, [
                'user_id' => $user_id,
                'codes' => $recovery_codes,
            ], 600);
            CACHE('bb_cache')->set('2fa_download_' . $download_token, [
                'user_id' => $user_id,
                'codes' => $recovery_codes,
            ], 600);

            redirect(FORUM_PATH . 'profile/two-step?action=recovery&token=' . $download_token);
        }

        // GET: Show regenerate confirmation form (reuses disable template layout)
        print_page('usercp_twofactor_regenerate.twig', variables: [
            'PAGE_TITLE' => __('TWO_FACTOR_REGENERATE'),
            'U_USER_PROFILE' => SETTINGS_URL,
        ]);
        break;

    /**
     * Download recovery codes as a text file
     */
    case 'download_codes':
        $token = request()->getString('token', '');

        if (empty($token)) {
            bb_die(__('TWO_FACTOR_INVALID_TOKEN'));
        }

        $cache_key = '2fa_download_' . $token;
        $cached = CACHE('bb_cache')->get($cache_key);

        if (!$cached || !isset($cached['codes'], $cached['user_id']) || $cached['user_id'] !== $user_id) {
            bb_die(__('TWO_FACTOR_DOWNLOAD_EXPIRED'));
        }

        // Clean up after download
        CACHE('bb_cache')->rm($cache_key);

        $content = "TorrentPier - Two-Factor Authentication Recovery Codes\n";
        $content .= "======================================================\n";
        $content .= "Generated: " . bb_date(TIMENOW) . "\n";
        $content .= "User: " . userdata('username') . "\n\n";
        $content .= "Each code can only be used once.\n";
        $content .= "Keep these codes in a safe place.\n\n";

        foreach ($cached['codes'] as $code) {
            $content .= $code . "\n";
        }

        header('Content-Type: text/plain; charset=UTF-8');
        header('Content-Disposition: attachment; filename="torrentpier-recovery-codes.txt"');
        header('Content-Length: ' . strlen($content));
        header('Cache-Control: no-store, no-cache, must-revalidate');
        echo $content;
        exit;

    /**
     * Serve QR code PNG image
     */
    case 'qrcode':
        $setup_token = request()->getString('setup_token', '');

        if (empty($setup_token)) {
            bb_die(__('TWO_FACTOR_INVALID_TOKEN'));
        }

        $cache_key = '2fa_setup_' . $setup_token;
        $cached = CACHE('bb_cache')->get($cache_key);

        if (!$cached || !isset($cached['secret'], $cached['user_id']) || $cached['user_id'] !== $user_id) {
            bb_die(__('TWO_FACTOR_SETUP_EXPIRED'));
        }

        $qrImage = two_factor()->generateQrCode(
            userdata('username'),
            $cached['secret'],
        );

        header('Content-Type: image/png');
        header('Cache-Control: no-store, no-cache, must-revalidate');
        echo $qrImage;
        exit;

    default:
        redirect(SETTINGS_URL);
}
