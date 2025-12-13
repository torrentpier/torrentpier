<?php

/**
 * TorrentPier – Bull-powered BitTorrent tracker engine
 *
 * @copyright Copyright (c) 2005-2025 TorrentPier (https://torrentpier.com)
 * @link      https://github.com/torrentpier/torrentpier for the canonical source repository
 * @license   https://github.com/torrentpier/torrentpier/blob/master/LICENSE MIT License
 */

declare(strict_types=1);

namespace TorrentPier\Http\Middleware;

use Laminas\Diactoros\Response;
use Psr\Http\Message\ResponseInterface;
use Psr\Http\Message\ServerRequestInterface;

/**
 * Redirect handler for URLs missing trailing slash
 *
 * Redirects /topic/slug.5 to /topic/slug.5/
 */
class TrailingSlashRedirect
{
    public function __invoke(ServerRequestInterface $request, array $args = []): ResponseInterface
    {
        // Get the current URI
        $uri = $request->getUri();
        $path = $uri->getPath();

        // Add trailing slash
        $newPath = rtrim($path, '/') . '/';

        // Preserve query string
        $query = $uri->getQuery();
        $newUrl = $newPath . ($query ? '?' . $query : '');

        // 301 Permanent Redirect
        $response = new Response();

        return $response
            ->withStatus(301)
            ->withHeader('Location', make_url($newUrl));
    }
}
