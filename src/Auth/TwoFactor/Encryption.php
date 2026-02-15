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

use function strlen;

/**
 * AES-256-GCM encryption for TOTP secrets at rest
 */
class Encryption
{
    private const string CIPHER = 'aes-256-gcm';
    private const int NONCE_LENGTH = 12;
    private const int TAG_LENGTH = 16;

    public function __construct(
        private readonly Config $config,
    ) {
    }

    /**
     * Encrypt a plaintext secret
     *
     * Format: base64(nonce + ciphertext + tag)
     *
     * @throws RandomException
     * @throws RuntimeException
     */
    public function encrypt(string $plaintext): string
    {
        $key = $this->deriveKey();
        $nonce = random_bytes(self::NONCE_LENGTH);

        $ciphertext = openssl_encrypt(
            $plaintext,
            self::CIPHER,
            $key,
            OPENSSL_RAW_DATA,
            $nonce,
            $tag,
            '',
            self::TAG_LENGTH
        );

        if ($ciphertext === false) {
            throw new RuntimeException('Failed to encrypt TOTP secret');
        }

        return base64_encode($nonce . $ciphertext . $tag);
    }

    /**
     * Decrypt an encrypted secret
     *
     * @throws RuntimeException
     */
    public function decrypt(string $encrypted): string
    {
        $key = $this->deriveKey();
        $decoded = base64_decode($encrypted, true);

        $minLength = self::NONCE_LENGTH + self::TAG_LENGTH;
        if ($decoded === false || strlen($decoded) < $minLength) {
            throw new RuntimeException('Invalid encrypted data');
        }

        $nonce = substr($decoded, 0, self::NONCE_LENGTH);
        $tag = substr($decoded, -self::TAG_LENGTH);
        $ciphertext = substr($decoded, self::NONCE_LENGTH, -self::TAG_LENGTH);

        $plaintext = openssl_decrypt(
            $ciphertext,
            self::CIPHER,
            $key,
            OPENSSL_RAW_DATA,
            $nonce,
            $tag
        );

        if ($plaintext === false) {
            throw new RuntimeException('Failed to decrypt TOTP secret');
        }

        return $plaintext;
    }

    /**
     * Derive a 32-byte key using HKDF with domain separation
     */
    private function deriveKey(): string
    {
        $configKey = $this->config->get('auth.two_factor.encryption_key');

        if (empty($configKey)) {
            throw new RuntimeException('TOTP encryption key is not configured. Set TOTP_ENCRYPTION_KEY in .env');
        }


        return hash_hkdf('sha256', $configKey, 32, 'totp-secret-encryption');
    }
}
