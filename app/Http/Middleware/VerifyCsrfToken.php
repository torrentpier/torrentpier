<?php

/**
 * TorrentPier – Bull-powered BitTorrent tracker engine
 *
 * @copyright Copyright (c) 2005-2025 TorrentPier (https://torrentpier.com)
 * @link      https://github.com/torrentpier/torrentpier for the canonical source repository
 * @license   https://github.com/torrentpier/torrentpier/blob/master/LICENSE MIT License
 */

declare(strict_types=1);

namespace App\Http\Middleware;

use Laminas\Diactoros\Response\TextResponse;
use Psr\Http\Message\ResponseInterface;
use Psr\Http\Message\ServerRequestInterface;
use Psr\Http\Server\MiddlewareInterface;
use Psr\Http\Server\RequestHandlerInterface;
use TorrentPier\Http\Csrf;

/**
 * Reject state-changing requests that lack a valid CSRF token.
 *
 * Token is read from `_token` POST field, falling back to the `X-CSRF-Token`
 * header for AJAX. GET/HEAD/OPTIONS pass through but still trigger token
 * generation so subsequent POSTs have something to send.
 */
final class VerifyCsrfToken implements MiddlewareInterface
{
    public function process(
        ServerRequestInterface  $request,
        RequestHandlerInterface $handler,
    ): ResponseInterface {
        $method = strtoupper($request->getMethod());

        // Always touch the token so guest GETs warm the cache for their next POST.
        Csrf::token();

        if (!\in_array($method, Csrf::protectedMethods(), true)) {
            return $handler->handle($request);
        }

        $supplied = null;
        $parsed = $request->getParsedBody();
        if (\is_array($parsed) && isset($parsed[Csrf::FIELD]) && \is_string($parsed[Csrf::FIELD])) {
            $supplied = $parsed[Csrf::FIELD];
        }
        if ($supplied === null && $request->hasHeader(Csrf::HEADER)) {
            $supplied = $request->getHeaderLine(Csrf::HEADER);
        }

        if (!Csrf::verify($supplied)) {
            return new TextResponse('CSRF token mismatch.', 419);
        }

        return $handler->handle($request);
    }
}
