<?php

/**
 * TorrentPier – Bull-powered BitTorrent tracker engine
 *
 * @copyright Copyright (c) 2005-2026 TorrentPier (https://torrentpier.com)
 * @link      https://github.com/torrentpier/torrentpier for the canonical source repository
 * @license   https://github.com/torrentpier/torrentpier/blob/master/LICENSE MIT License
 */

declare(strict_types=1);

namespace TorrentPier\Http;

use Illuminate\Support\Str;

/**
 * CSRF token store persisted in bb_cache. Keyed on the stable cookie sid
 * for logged-in users, with an sha1(ip|user-agent) fallback for guests.
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
        // Logged-in: stable cookie sid survives session_create() rotations.
        $sid = (string)(user()->sessiondata['sid'] ?? '');
        if ($sid !== '') {
            return self::CACHE_PREFIX . $sid;
        }

        // Guests have no persistent sid; the DB session_id rotates after the
        // 180s userdata cache (auth.sessions.update_interval), so derive a
        // per-browser key from IP+UA instead. Tradeoff: guests behind the
        // same NAT with identical UA share one token; acceptable because
        // guest POST endpoints are read-only or low-privilege.
        $ip = \defined('USER_IP') && USER_IP !== '' ? (string)USER_IP : (string)(request()->getClientIp() ?? '');
        if ($ip === '') {
            return null;
        }
        $ua = (string)(request()->getUserAgent() ?? '');

        return self::CACHE_PREFIX . 'guest_' . sha1($ip . '|' . $ua);
    }

    private static function ttl(): int
    {
        $ttl = (int)config()->get('auth.sessions.user_duration');

        return $ttl > 0 ? $ttl : 86400;
    }
}
