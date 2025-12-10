<?php

/**
 * TorrentPier – Bull-powered BitTorrent tracker engine
 *
 * @copyright Copyright (c) 2005-2025 TorrentPier (https://torrentpier.com)
 * @link      https://github.com/torrentpier/torrentpier for the canonical source repository
 * @license   https://github.com/torrentpier/torrentpier/blob/master/LICENSE MIT License
 */

declare(strict_types=1);

namespace TorrentPier\Router\SemanticUrl;

use TorrentPier\Helpers\Slug;
use TorrentPier\Http\Response;

/**
 * Central URL builder for SEO-friendly URLs
 *
 * Generates URLs in the format: /type/slug.id/
 * Examples:
 *   - /topic/bugonia.5/
 *   - /forum/hd-video.1/
 *   - /profile/admin.2/
 */
class UrlBuilder
{
    private static ?self $instance = null;

    /**
     * Get singleton instance for use in templates
     */
    public static function instance(): self
    {
        return self::$instance ??= new self();
    }

    /**
     * Magic method to proxy instance calls to static methods
     *
     * @param string $name Method name
     * @param array $args Method arguments
     * @return string URL
     */
    public function __call(string $name, array $args): string
    {
        return self::$name(...$args);
    }

    /**
     * Generate a topic URL
     *
     * @param int|null $id Topic ID
     * @param string $title Topic title (will be slugified)
     * @param array $params Additional query parameters (e.g., ['start' => 20])
     * @return string Full URL path
     */
    public static function topic(?int $id, string $title = '', array $params = []): string
    {
        if ($id === null || $id <= 0) {
            return '#';
        }
        return self::buildUrl('topic', $id, $title, $params);
    }

    /**
     * Generate a forum URL
     *
     * @param int|null $id Forum ID
     * @param string $name Forum name (will be slugified)
     * @param array $params Additional query parameters
     * @return string Full URL path
     */
    public static function forum(?int $id, string $name = '', array $params = []): string
    {
        if ($id === null || $id <= 0) {
            return '#';
        }
        return self::buildUrl('forum', $id, $name, $params);
    }

    /**
     * Generate a user profile URL
     *
     * @param int|null $id User ID
     * @param string $username Username (will be slugified)
     * @param array $params Additional query parameters
     * @return string Full URL path
     */
    public static function profile(?int $id, string $username = '', array $params = []): string
    {
        if ($id === null || $id <= 0) {
            return '#';
        }
        return self::buildUrl('profile', $id, $username, $params);
    }

    /**
     * Generate a profile email URL (/profile/slug.id/email/)
     */
    public static function profileEmail(int $id, string $username = ''): string
    {
        return rtrim(self::profile($id, $username), '/') . '/email/';
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
     * Build the URL with the format: /type/slug.id/
     *
     * @param string $type Entity type (topic, forum, profile)
     * @param int $id Entity ID
     * @param string $title Title/name to slugify
     * @param array $params Additional query parameters (use '_fragment' for #anchor)
     * @return string URL path
     */
    private static function buildUrl(string $type, int $id, string $title, array $params): string
    {
        $slug = Slug::generate($title);

        // Extract fragment (anchor) if present
        $fragment = '';
        if (isset($params['_fragment'])) {
            $fragment = '#' . $params['_fragment'];
            unset($params['_fragment']);
        }

        // Build base path: /type/slug.id/
        $path = '/' . $type . '/' . $slug . '.' . $id . '/';

        // Append a query string if there are additional parameters
        if (!empty($params)) {
            $queryString = http_build_query($params, '', '&');
            if ($queryString !== '') {
                $path .= '?' . $queryString;
            }
        }

        // Append a fragment at the end
        $path .= $fragment;

        return $path;
    }

    /**
     * Assert that the current URL matches the canonical URL
     *
     * If the URL slug doesn't match the expected slug from the title,
     * performs a 301 redirect to the canonical URL.
     *
     * @param string $type Entity type (topic, forum, profile)
     * @param int $id Entity ID
     * @param string $title Current title/name of the entity
     * @param string|null $requestedSlug The slug from the current URL (null to parse from REQUEST_URI)
     */
    public static function assertCanonical(string $type, int $id, string $title, ?string $requestedSlug = null): void
    {
        // Parse the requested slug from the URL if not provided
        if ($requestedSlug === null) {
            $requestedSlug = self::parseSlugFromRequest($type);
        }

        // Generate the expected slug from the current title
        $expectedSlug = Slug::generate($title);

        // If slugs don't match, redirect to canonical URL
        if ($requestedSlug !== $expectedSlug) {
            $canonicalUrl = self::buildCanonicalUrl($type, $id, $title);
            self::redirectToCanonical($canonicalUrl);
        }
    }

    /**
     * Parse the slug from the current request URI
     *
     * Expected format: /type/slug.id/ or /type/slug.id
     *
     * @param string $type Entity type to match
     * @return string Extracted slug (empty string if not found)
     */
    private static function parseSlugFromRequest(string $type): string
    {
        $requestUri = $_SERVER['REQUEST_URI'] ?? '';

        // Remove query string
        $path = parse_url($requestUri, PHP_URL_PATH) ?? '';

        // Match pattern: /type/slug.id/ or /type/slug.id
        $pattern = '#^/' . preg_quote($type, '#') . '/([^/]*?)\.(\d+)/?$#';

        if (preg_match($pattern, $path, $matches)) {
            return $matches[1];
        }

        return '';
    }

    /**
     * Build the full canonical URL with current query parameters preserved
     *
     * @param string $type Entity type
     * @param int $id Entity ID
     * @param string $title Title to slugify
     * @return string Full canonical URL
     */
    private static function buildCanonicalUrl(string $type, int $id, string $title): string
    {
        // Get current query parameters (excluding slug-related ones)
        $params = [];
        $queryString = $_SERVER['QUERY_STRING'] ?? '';
        if ($queryString !== '') {
            parse_str($queryString, $params);
        }

        // Build the URL using the appropriate method
        return match ($type) {
            'topic' => self::topic($id, $title, $params),
            'forum' => self::forum($id, $title, $params),
            'profile' => self::profile($id, $title, $params),
            default => '/',
        };
    }

    /**
     * Perform a 301 redirect to the canonical URL
     *
     * @param string $url Target URL
     */
    private static function redirectToCanonical(string $url): void
    {
        // Build full URL with base
        $fullUrl = make_url($url);

        // Send 301 redirect and exit
        Response::permanentRedirect($fullUrl)->send();
        exit;
    }

    /**
     * Parse a semantic URL and extract slug and ID
     *
     * @param string $params The URL segment containing "slug.id"
     * @return array{slug: string, id: int}|null Parsed data or null if invalid
     */
    public static function parseParams(string $params): ?array
    {
        // Match pattern: anything.number (slug can be empty)
        if (preg_match('/^(.*?)\.(\d+)$/', $params, $matches)) {
            return [
                'slug' => $matches[1],
                'id' => (int) $matches[2],
            ];
        }

        return null;
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
            'topic' => 'viewtopic?t=' . $id,
            'forum' => 'viewforum?f=' . $id,
            'profile' => 'profile?mode=viewprofile&u=' . $id,
            default => '/',
        };
    }
}
