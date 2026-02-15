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

use chillerlan\QRCode\Common\EccLevel;
use chillerlan\QRCode\Output\QROutputInterface;
use chillerlan\QRCode\QRCode;
use chillerlan\QRCode\QROptions;
use OTPHP\TOTP;
use ParagonIE\ConstantTime\Base32;
use Psr\Clock\ClockInterface;
use Random\RandomException;
use TorrentPier\Config;

/**
 * TOTP generation and verification (RFC 6238)
 */
class TotpService
{
    public function __construct(
        private readonly Config $config,
        private readonly ClockInterface $clock,
    ) {
    }

    /**
     * Generate a new TOTP secret (20 bytes = 32 chars Base32, standard for SHA1)
     *
     * @throws RandomException
     */
    public function generateSecret(): string
    {
        return Base32::encodeUpperUnpadded(random_bytes(20));
    }

    /**
     * Format a secret for manual display (groups of 4 characters)
     */
    public function formatSecretForDisplay(string $secret): string
    {
        return implode(' ', str_split($secret, 4));
    }

    /**
     * Get the otpauth:// provisioning URI for authenticator apps
     */
    public function getProvisioningUri(string $secret, string $username): string
    {
        $totp = $this->createTotp($secret);
        $totp->setLabel($username);
        $totp->setIssuer($this->config->get('auth.two_factor.issuer'));

        return $totp->getProvisioningUri();
    }

    /**
     * Generate a QR code as raw PNG binary
     */
    public function generateQrCode(string $username, string $secret): string
    {
        $provisioningUri = $this->getProvisioningUri($secret, $username);

        $options = new QROptions([
            'outputType' => QROutputInterface::GDIMAGE_PNG,
            'eccLevel' => EccLevel::M,
            'scale' => 5,
            'imageBase64' => false,
        ]);

        return new QRCode($options)->render($provisioningUri);
    }

    /**
     * Verify a TOTP code against a secret with replay protection
     */
    public function verifyCode(string $secret, string $code, int $userId = 0): bool
    {
        $totp = $this->createTotp($secret);
        $period = $totp->getPeriod();
        $window = (int)$this->config->get('auth.two_factor.window');

        // OTPHP requires leeway < period; use (window * period - 1) for ±N step tolerance
        $leeway = $window > 0 ? ($window * $period - 1) : 0;

        $isValid = $totp->verify($code, null, $leeway > 0 ? $leeway : null);

        if ($isValid && $userId > 0) {
            $codeHash = hash('sha256', $code);
            $cacheKey = "2fa_used_{$userId}_{$codeHash}";

            if (CACHE('bb_cache')->get($cacheKey)) {
                return false;
            }

            // Cache for the full window span so codes valid across steps can't be replayed
            CACHE('bb_cache')->set($cacheKey, 1, $period * ($window + 1));
        }

        return $isValid;
    }

    /**
     * Create a configured TOTP instance
     */
    private function createTotp(string $secret): TOTP
    {
        $totp = TOTP::createFromSecret($secret, $this->clock);
        $totp->setDigits((int)$this->config->get('auth.two_factor.digits'));
        $totp->setPeriod((int)$this->config->get('auth.two_factor.period'));
        $totp->setDigest($this->config->get('auth.two_factor.algorithm'));

        return $totp;
    }
}
