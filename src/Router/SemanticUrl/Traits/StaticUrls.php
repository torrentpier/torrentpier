<?php

/**
 * TorrentPier – Bull-powered BitTorrent tracker engine
 *
 * @copyright Copyright (c) 2005-2025 TorrentPier (https://torrentpier.com)
 * @link      https://github.com/torrentpier/torrentpier for the canonical source repository
 * @license   https://github.com/torrentpier/torrentpier/blob/master/LICENSE MIT License
 */

declare(strict_types=1);

namespace TorrentPier\Router\SemanticUrl\Traits;

/**
 * URL generators for static pages (no slug.id pattern)
 */
trait StaticUrls
{
    /**
     * Generate a members list URL (/members/)
     */
    public static function members(): string
    {
        return '/members/';
    }

    /**
     * Generate a groups list URL (/groups/)
     */
    public static function groups(): string
    {
        return '/groups/';
    }

    /**
     * Generate a registration URL (/register/)
     */
    public static function register(): string
    {
        return '/register/';
    }

    /**
     * Generate a settings/edit profile URL (/settings/)
     */
    public static function settings(): string
    {
        return '/settings/';
    }

    /**
     * Generate a password recovery URL (/password-recovery/)
     */
    public static function passwordRecovery(): string
    {
        return '/password-recovery/';
    }

    /**
     * Generate a profile bonus URL (/profile/bonus/)
     */
    public static function profileBonus(): string
    {
        return '/profile/bonus/';
    }

    /**
     * Generate a profile watchlist URL (/profile/watchlist/)
     */
    public static function profileWatchlist(): string
    {
        return '/profile/watchlist/';
    }

    /**
     * Generate an activation URL (/activate/{key}/)
     */
    public static function activate(string $key): string
    {
        return '/activate/' . urlencode($key) . '/';
    }

    /**
     * Generate a legacy-style URL (for backward compatibility)
     *
     * @param string $type Entity type
     * @param int $id Entity ID
     * @return string Legacy URL format
     */
    public static function legacy(string $type, int $id): string
    {
        return match ($type) {
            'threads' => 'viewtopic?t=' . $id,
            'forums' => 'viewforum?f=' . $id,
            'members' => 'profile?mode=viewprofile&u=' . $id,
            default => '/',
        };
    }
}
