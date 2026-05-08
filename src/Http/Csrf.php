<?php

/**
 * TorrentPier – Bull-powered BitTorrent tracker engine
 *
 * @copyright Copyright (c) 2005-2025 TorrentPier (https://torrentpier.com)
 * @link      https://github.com/torrentpier/torrentpier for the canonical source repository
 * @license   https://github.com/torrentpier/torrentpier/blob/master/LICENSE MIT License
 */

declare(strict_types=1);

namespace TorrentPier\Http;

use Illuminate\Support\Str;

/**
 * Per-session CSRF token store.
 *
 * Tokens are bound to the active session identifier (real session_id for
 * authenticated users, session_ip for guests) and persisted in the bb_cache
 * store so they survive across page loads. Same identifier → same token until
 * it expires or session_id changes (login regenerates).
 */
final class Csrf
{
    public const string FIELD = '_token';
    public const string HEADER = 'X-CSRF-Token';
    public const string CACHE_PREFIX = 'csrf_';
    public const int TOKEN_LENGTH = 40;

    /**
     * Resolve the active token, generating one on first access for the session.
     */
    public static function token(): string
    {
        $key = self::cacheKey();
        if ($key === null) {
            return '';
        }

        $token = CACHE('bb_cache')->get($key);
        if (\is_string($token) && \strlen($token) === self::TOKEN_LENGTH) {
            return $token;
        }

        $token = Str::random(self::TOKEN_LENGTH);
        CACHE('bb_cache')->set($key, $token, self::ttl());

        return $token;
    }

    /**
     * Compare a supplied token against the stored one in constant time.
     */
    public static function verify(?string $supplied): bool
    {
        if (!\is_string($supplied) || $supplied === '') {
            return false;
        }
        $stored = self::token();
        if ($stored === '') {
            return false;
        }
        return hash_equals($stored, $supplied);
    }

    /**
     * HTTP methods that mutate state and therefore require a token.
     *
     * @return list<string>
     */
    public static function protectedMethods(): array
    {
        return ['POST', 'PUT', 'PATCH', 'DELETE'];
    }

    /**
     * Drop the stored token (used after logout / on session destruction).
     */
    public static function rotate(): void
    {
        $key = self::cacheKey();
        if ($key !== null) {
            CACHE('bb_cache')->rm($key);
        }
    }

    private static function cacheKey(): ?string
    {
        $userdata = userdata() ?: null;
        if (!$userdata) {
            return null;
        }
        $identifier = ((int)($userdata['user_id'] ?? GUEST_UID) === GUEST_UID)
            ? (string)($userdata['session_ip'] ?? '')
            : (string)($userdata['session_id'] ?? '');
        if ($identifier === '') {
            return null;
        }
        return self::CACHE_PREFIX . $identifier;
    }

    private static function ttl(): int
    {
        $ttl = (int)config()->get('auth.sessions.user_duration');
        return $ttl > 0 ? $ttl : 86400;
    }
}
