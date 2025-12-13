<?php

/**
 * TorrentPier – Bull-powered BitTorrent tracker engine
 *
 * @copyright Copyright (c) 2005-2025 TorrentPier (https://torrentpier.com)
 * @link      https://github.com/torrentpier/torrentpier for the canonical source repository
 * @license   https://github.com/torrentpier/torrentpier/blob/master/LICENSE MIT License
 */

declare(strict_types=1);

namespace TorrentPier\Router;

/**
 * Log URL redirects for debugging and migration tracking
 *
 * Helps identify places where old URLs are still being used
 * so they can be updated to generate semantic URLs directly.
 *
 * Enable by adding to config:
 * 'log_redirects' => true,
 */
class RedirectLogger
{
    private static bool $enabled = true;

    /**
     * Log a redirect
     *
     * @param string $from Original URL
     * @param string $to Target URL
     * @param string $type Type of redirect (legacy, canonical, trailing_slash, etc.)
     * @param string|null $source Source file/class that triggered the redirect
     */
    public static function log(string $from, string $to, string $type = 'unknown', ?string $source = null): void
    {
        if (!self::$enabled) {
            return;
        }

        // Skip if not configured to log
        if (!config()->get('log_redirects', false)) {
            return;
        }

        // Get referrer info
        $referer = $_SERVER['HTTP_REFERER'] ?? '-';

        // Format log entry with timestamp and newline
        $entry = \sprintf(
            '[%s] %s | %s -> %s | referer: %s | from: %s',
            date('Y-m-d H:i:s'),
            $type,
            $from,
            $to,
            $referer,
            $source ?? 'unknown',
        );

        // Use bb_log to write to storage/logs/redirects.log
        bb_log($entry . LOG_LF, 'redirects', false);
    }

    /**
     * Log a legacy URL redirect (e.g., viewtopic.php?t=123 -> /threads/title.123/)
     */
    public static function legacy(string $from, string $to, ?string $source = null): void
    {
        self::log($from, $to, 'legacy', $source);
    }

    /**
     * Log a canonical URL redirect (e.g., /threads/123/ -> /threads/title.123/)
     */
    public static function canonical(string $from, string $to, ?string $source = null): void
    {
        self::log($from, $to, 'canonical', $source);
    }

    /**
     * Log a slug mismatch redirect (e.g., /threads/wrong-slug.123/ -> /threads/correct-slug.123/)
     */
    public static function slugMismatch(string $from, string $to, ?string $source = null): void
    {
        self::log($from, $to, 'slug_mismatch', $source);
    }

    /**
     * Log a trailing slash redirect
     */
    public static function trailingSlash(string $from, string $to, ?string $source = null): void
    {
        self::log($from, $to, 'trailing_slash', $source);
    }

    /**
     * Enable redirect logging at runtime
     */
    public static function enable(): void
    {
        self::$enabled = true;
    }

    /**
     * Disable redirect logging at runtime
     */
    public static function disable(): void
    {
        self::$enabled = false;
    }

    /**
     * Check if logging is enabled
     */
    public static function isEnabled(): bool
    {
        return self::$enabled && config()->get('log_redirects', false);
    }
}
