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

use BadMethodCallException;
use TorrentPier\Helpers\Slug;
use TorrentPier\Router\SemanticUrl\Traits\CanonicalUrls;
use TorrentPier\Router\SemanticUrl\Traits\EntityUrls;
use TorrentPier\Router\SemanticUrl\Traits\StaticUrls;

/**
 * Central URL builder for SEO-friendly URLs
 *
 * Generates URLs in the format: /type/slug.id/
 * Examples:
 *   - /topic/bugonia.5/
 *   - /forum/hd-video.1/
 *   - /members/admin.2/
 *
 * @method static string topic(?int $id, string $title = '', array $params = [])
 * @method static string topicPost(int $topicId, string $title, int $postId)
 * @method static string topicNewest(int $topicId, string $title)
 * @method static string forum(?int $id, string $name = '', array $params = [])
 * @method static string member(?int $id, string $username = '', array $params = [])
 * @method static string memberEmail(int $id, string $username = '')
 * @method static string group(?int $id, string $name = '', array $params = [])
 * @method static string groupEdit(int $id, string $name = '', array $params = [])
 * @method static string category(?int $id, string $name = '', array $params = [])
 * @method static string members()
 * @method static string groups()
 * @method static string register()
 * @method static string settings()
 * @method static string passwordRecovery()
 * @method static string profileBonus()
 * @method static string profileWatchlist()
 * @method static string activate(string $key)
 * @method static string legacy(string $type, int $id)
 * @method static void assertCanonical(string $type, int $id, string $title, ?string $requestedSlug = null)
 * @method static array|null parseParams(string $params)
 */
class UrlBuilder
{
    use EntityUrls;
    use StaticUrls;
    use CanonicalUrls;

    private static ?self $instance = null;

    /**
     * Get a singleton instance for use in templates
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
     * @return mixed
     * @throws BadMethodCallException If the method doesn't exist
     */
    public function __call(string $name, array $args): mixed
    {
        if (!method_exists(self::class, $name)) {
            throw new BadMethodCallException("Method UrlBuilder::$name() does not exist");
        }
        return self::$name(...$args);
    }

    /**
     * Build the URL with the format: /type/slug.id/
     *
     * @param string $type Entity type (topic, forum, members, groups)
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
     * Append query parameters to a URL
     *
     * @param string $url Base URL
     * @param array $params Query parameters (use '_fragment' for #anchor)
     * @return string URL with parameters
     */
    private static function appendParams(string $url, array $params): string
    {
        if (empty($params)) {
            return $url;
        }

        // Extract fragment (anchor) if present
        $fragment = '';
        if (isset($params['_fragment'])) {
            $fragment = '#' . $params['_fragment'];
            unset($params['_fragment']);
        }

        // Append a query string if there are parameters
        if (!empty($params)) {
            $queryString = http_build_query($params, '', '&');
            if ($queryString !== '') {
                $url .= '?' . $queryString;
            }
        }

        // Append a fragment at the end
        $url .= $fragment;

        return $url;
    }
}
