<?php

/**
 * TorrentPier – Bull-powered BitTorrent tracker engine
 *
 * @copyright Copyright (c) 2005-2025 TorrentPier (https://torrentpier.com)
 * @link      https://github.com/torrentpier/torrentpier for the canonical source repository
 * @license   https://github.com/torrentpier/torrentpier/blob/master/LICENSE MIT License
 */

declare(strict_types=1);

namespace TorrentPier\Auth\TwoFactor;

use Random\RandomException;
use RuntimeException;
use TorrentPier\Config;

/**
 * Two-Factor Authentication facade
 *
 * Orchestrates TOTP verification, recovery codes, encryption,
 * user persistence, and rate limiting. Delegates crypto and
 * TOTP operations to specialized services.
 */
class TwoFactorAuth
{
    public function __construct(
        private readonly Config $config,
        private readonly TotpService $totp,
        private readonly RecoveryCodes $recoveryCodes,
        private readonly Encryption $encryption,
    ) {}

    // ---------------------------------------------------------------
    // Feature toggle
    // ---------------------------------------------------------------

    public function isFeatureEnabled(): bool
    {
        return (bool)$this->config->get('auth.two_factor.enabled');
    }

    // ---------------------------------------------------------------
    // Secret & QR
    // ---------------------------------------------------------------

    /**
     * @throws RandomException
     */
    public function generateSecret(): string
    {
        return $this->totp->generateSecret();
    }

    public function formatSecretForDisplay(string $secret): string
    {
        return $this->totp->formatSecretForDisplay($secret);
    }

    public function generateQrCode(string $username, string $secret): string
    {
        return $this->totp->generateQrCode($username, $secret);
    }

    // ---------------------------------------------------------------
    // TOTP verification
    // ---------------------------------------------------------------

    /**
     * Verify a TOTP code against a raw (unencrypted) secret
     */
    public function verifyCode(string $secret, string $code, int $userId = 0): bool
    {
        return $this->totp->verifyCode($secret, $code, $userId);
    }

    /**
     * Verify a TOTP code for an already-enabled user (loads + decrypts secret from DB)
     */
    public function verifyUserCode(int $userId, string $code): bool
    {
        $row = eloquent()->table('users')->where('user_id', $userId)->first(['totp_secret']);

        if (!$row || empty($row->totp_secret)) {
            return false;
        }

        try {
            $secret = $this->encryption->decrypt($row->totp_secret);
        } catch (RuntimeException) {
            return false;
        }

        return $this->totp->verifyCode($secret, $code, $userId);
    }

    // ---------------------------------------------------------------
    // Recovery codes (delegates to RecoveryCodes)
    // ---------------------------------------------------------------

    public function verifyRecoveryCode(string $code, array $hashedCodes): int|false
    {
        return $this->recoveryCodes->verify($code, $hashedCodes);
    }

    /**
     * @throws RandomException
     * @return string[]|null New plain recovery codes if auto-regenerated, null otherwise
     */
    public function consumeRecoveryCode(int $userId, int $codeIndex, array $currentCodes): ?array
    {
        return $this->recoveryCodes->consume($userId, $codeIndex, $currentCodes);
    }

    /**
     * @throws RandomException
     */
    public function regenerateRecoveryCodes(int $userId): array
    {
        return $this->recoveryCodes->regenerateForUser($userId);
    }

    // ---------------------------------------------------------------
    // Encryption (delegates to Encryption)
    // ---------------------------------------------------------------

    /**
     * @throws RandomException
     * @throws RuntimeException
     */
    public function encryptSecret(string $secret): string
    {
        return $this->encryption->encrypt($secret);
    }

    /**
     * @throws RuntimeException
     */
    public function decryptSecret(string $encrypted): string
    {
        return $this->encryption->decrypt($encrypted);
    }

    // ---------------------------------------------------------------
    // User state
    // ---------------------------------------------------------------

    /**
     * Check if 2FA is enabled from a userdata array (no DB query)
     */
    public function isEnabled(array $userdata): bool
    {
        return !empty($userdata['totp_enabled']);
    }

    /**
     * Check if 2FA is enabled for a user by ID (DB query)
     */
    public function isEnabledForUser(int $userId): bool
    {
        $row = eloquent()->table('users')->where('user_id', $userId)->first(['totp_enabled']);

        return !empty($row->totp_enabled);
    }

    /**
     * Enable 2FA — encrypts secret, generates recovery codes, updates DB
     *
     *
     * @throws RuntimeException
     * @throws RandomException
     * @return string[] Plain recovery codes for one-time display
     */
    public function enableForUser(int $userId, string $secret): array
    {
        $encryptedSecret = $this->encryption->encrypt($secret);
        $codes = $this->recoveryCodes->generate();

        eloquent()->table('users')
            ->where('user_id', $userId)
            ->update([
                'totp_secret' => $encryptedSecret,
                'totp_enabled' => 1,
                'totp_recovery_codes' => json_encode($codes['hashed']),
                'totp_enabled_at' => TIMENOW,
                'autologin_id' => '', // Invalidate autologin so stolen cookies can't bypass 2FA
            ]);

        return $codes['plain'];
    }

    /**
     * Disable 2FA — clears all TOTP data
     */
    public function disableForUser(int $userId): void
    {
        eloquent()->table('users')
            ->where('user_id', $userId)
            ->update([
                'totp_secret' => null,
                'totp_enabled' => 0,
                'totp_recovery_codes' => null,
                'totp_enabled_at' => null,
            ]);
    }

    // ---------------------------------------------------------------
    // Rate limiting
    // ---------------------------------------------------------------

    /**
     * @return bool True if within limit, false if locked out
     */
    public function checkRateLimit(int $userId): bool
    {
        $attempts = CACHE('bb_cache')->get("2fa_attempts_$userId");

        return !$attempts || $attempts < (int)$this->config->get('auth.two_factor.max_attempts');
    }

    public function incrementAttempts(int $userId): void
    {
        $key = "2fa_attempts_$userId";
        $attempts = (int)CACHE('bb_cache')->get($key);

        CACHE('bb_cache')->set(
            $key,
            $attempts + 1,
            (int)$this->config->get('auth.two_factor.lockout_duration'),
        );
    }

    public function clearAttempts(int $userId): void
    {
        CACHE('bb_cache')->rm("2fa_attempts_$userId");
    }
}
