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

use Laminas\Diactoros\Response;
use Psr\Http\Message\ResponseInterface;

/**
 * Common response factory methods for route handlers
 *
 * Provides consistent response creation across RouteAdapter and LegacyRedirect.
 */
trait ResponseTrait
{
    /**
     * Create a 404 Not Found response
     *
     * @param string $message Error message to display
     * @return ResponseInterface
     */
    protected function notFoundResponse(string $message): ResponseInterface
    {
        $response = new Response();
        $response->getBody()->write($message);

        return $response
            ->withStatus(404)
            ->withHeader('Content-Type', 'text/plain');
    }

    /**
     * Create a redirect response
     *
     * @param string $url Target URL
     * @param int $status HTTP status code (301 for permanent, 302 for temporary)
     * @return ResponseInterface
     */
    protected function redirectResponse(string $url, int $status = 301): ResponseInterface
    {
        $response = new Response();

        return $response
            ->withStatus($status)
            ->withHeader('Location', $url);
    }

    /**
     * Create a permanent (301) redirect response
     *
     * @param string $url Target URL
     * @return ResponseInterface
     */
    protected function permanentRedirect(string $url): ResponseInterface
    {
        return $this->redirectResponse($url);
    }

    /**
     * Create a temporary (302) redirect response
     *
     * @param string $url Target URL
     * @return ResponseInterface
     */
    protected function temporaryRedirect(string $url): ResponseInterface
    {
        return $this->redirectResponse($url, 302);
    }
}
