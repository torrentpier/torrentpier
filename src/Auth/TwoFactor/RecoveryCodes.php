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
use TorrentPier\Config;

/**
 * Recovery code generation, verification, and persistence
 *
 * Codes are 8 hex characters formatted as XXXX-XXXX.
 * Stored as bcrypt hashes in JSON array.
 */
class RecoveryCodes
{
    private const int BCRYPT_COST = 10;

    public function __construct(
        private readonly Config $config,
    ) {
    }

    /**
     * Generate a set of recovery codes
     *
     * @return array{plain: string[], hashed: string[]}
     *
     * @throws RandomException
     */
    public function generate(int $count = 0): array
    {
        if ($count <= 0) {
            $count = (int)$this->config->get('auth.two_factor.recovery_codes_count');
        }

        $plain = [];
        $hashed = [];

        for ($i = 0; $i < $count; $i++) {
            do {
                $code = strtoupper(bin2hex(random_bytes(4)));
                $code = substr($code, 0, 4) . '-' . substr($code, 4, 4);
            } while (in_array($code, $plain));
            $plain[] = $code;
            $hashed[] = password_hash(
                $this->normalize($code),
                PASSWORD_BCRYPT,
                ['cost' => self::BCRYPT_COST]
            );
        }

        return ['plain' => $plain, 'hashed' => $hashed];
    }

    /**
     * Verify a recovery code against stored hashes
     *
     * @return int|false Index of matched code or false
     */
    public function verify(string $code, array $hashedCodes): int|false
    {
        $normalized = $this->normalize($code);

        foreach ($hashedCodes as $index => $hash) {
            if (password_verify($normalized, $hash)) {
                return $index;
            }
        }

        return false;
    }

    /**
     * Consume a recovery code — removes it from the stored set
     *
     * @return string[]|null New plain recovery codes if auto-regenerated, null otherwise
     *
     * @throws RandomException
     */
    public function consume(int $userId, int $codeIndex, array $currentCodes): ?array
    {
        array_splice($currentCodes, $codeIndex, 1);

        if (empty($currentCodes)) {
            // Auto-regenerate when all codes consumed
            return $this->regenerateForUser($userId);
        }

        eloquent()->table('users')
            ->where('user_id', $userId)
            ->update(['totp_recovery_codes' => json_encode($currentCodes)]);

        return null;
    }

    /**
     * Regenerate recovery codes for a user — replaces all existing codes
     *
     * @return string[] Plain recovery codes for one-time display
     *
     * @throws RandomException
     */
    public function regenerateForUser(int $userId): array
    {
        $codes = $this->generate();

        eloquent()->table('users')
            ->where('user_id', $userId)
            ->update(['totp_recovery_codes' => json_encode($codes['hashed'])]);

        return $codes['plain'];
    }

    /**
     * Normalize input: uppercase, remove dashes and whitespace
     */
    private function normalize(string $code): string
    {
        return strtoupper(str_replace(['-', ' '], '', trim($code)));
    }
}
