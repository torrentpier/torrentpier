<?php

/**
 * TorrentPier – Bull-powered BitTorrent tracker engine
 *
 * @copyright Copyright (c) 2005-2025 TorrentPier (https://torrentpier.com)
 * @link      https://github.com/torrentpier/torrentpier for the canonical source repository
 * @license   https://github.com/torrentpier/torrentpier/blob/master/LICENSE MIT License
 */

declare(strict_types=1);

namespace TorrentPier\Router\Exception;

use Laminas\Diactoros\Response;
use Psr\Http\Message\ResponseInterface;
use RuntimeException;

/**
 * Exception for triggering HTTP redirects from anywhere in the routing flow
 *
 * Allows canonical URL assertions and other redirect logic to signal
 * a redirect without using exit() or breaking PSR-7 flow.
 *
 * Usage:
 * ```php
 * throw new RedirectException('/new-url/', 301);
 * ```
 *
 * Caught by:
 * - FrontController: converts to PSR-7 response
 * - LegacyAdapter: handles during controller execution
 */
class RedirectException extends RuntimeException
{
    /**
     * @param string $url Target URL
     * @param int $status HTTP status code (301, 302, 307, 308)
     */
    public function __construct(
        private readonly string $url,
        private readonly int $status = 301,
    ) {
        parent::__construct("Redirect to: {$url}", $status);
    }

    /**
     * Create a permanent (301) redirect exception
     */
    public static function permanent(string $url): self
    {
        return new self($url, 301);
    }

    /**
     * Create a temporary (302) redirect exception
     */
    public static function temporary(string $url): self
    {
        return new self($url, 302);
    }

    /**
     * Get the target URL
     */
    public function getUrl(): string
    {
        return $this->url;
    }

    /**
     * Get the HTTP status code
     */
    public function getStatus(): int
    {
        return $this->status;
    }

    /**
     * Create a PSR-7 redirect response
     */
    public function toResponse(): ResponseInterface
    {
        $response = new Response();

        return $response
            ->withStatus($this->status)
            ->withHeader('Location', $this->url);
    }
}
