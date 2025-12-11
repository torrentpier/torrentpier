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

use TorrentPier\Helpers\Slug;
use TorrentPier\Router\Exception\RedirectException;
use TorrentPier\Router\RedirectLogger;
use TorrentPier\Router\SemanticUrl\EntityConfig;

/**
 * Canonical URL handling: assertions, parsing, redirects
 */
trait CanonicalUrls
{
    /**
     * Assert that the current URL matches the canonical URL
     *
     * If the URL slug doesn't match the expected slug from the title,
     * throws a RedirectException to the canonical URL.
     *
     * @param string $type Entity type (threads, forums, members, groups, groups_edit, categories)
     * @param int $id Entity ID
     * @param string $title Current title/name of the entity
     * @param string|null $requestedSlug The slug from the current URL (null to parse from REQUEST_URI)
     * @throws RedirectException If the slug doesn't match and redirect is needed
     */
    public static function assertCanonical(string $type, int $id, string $title, ?string $requestedSlug = null): void
    {
        // Parse the requested slug from the URL if not provided
        if ($requestedSlug === null) {
            $requestedSlug = self::parseSlugFromRequest($type);
        }

        // Generate the expected slug from the current title
        $expectedSlug = Slug::generate($title);

        // If slugs don't match, throw a redirect exception to canonical URL
        if ($requestedSlug !== $expectedSlug) {
            $canonicalUrl = self::buildCanonicalUrl($type, $id, $title);
            $targetUrl = make_url($canonicalUrl);
            $requestUri = $_SERVER['REQUEST_URI'] ?? '';
            RedirectLogger::slugMismatch($requestUri, $targetUrl, "CanonicalUrls::{$type}");
            throw RedirectException::permanent($targetUrl);
        }
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

        // Handle special URL patterns
        $pattern = match ($type) {
            // /groups/slug.id/edit/
            'groups_edit' => '#^/groups/([^/]*?)\.(\d+)/edit/?$#',
            // Standard pattern: /type/slug.id/ or /type/slug.id
            default => '#^/' . preg_quote($type, '#') . '/([^/]*?)\.(\d+)/?$#',
        };

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

        // Build the URL using EntityConfig
        return EntityConfig::buildUrl($type, $id, $title, $params);
    }
}
