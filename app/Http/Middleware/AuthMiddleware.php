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

use Laminas\Diactoros\Response\RedirectResponse;
use Psr\Http\Message\ResponseInterface;
use Psr\Http\Message\ServerRequestInterface;
use Psr\Http\Server\MiddlewareInterface;
use Psr\Http\Server\RequestHandlerInterface;
use TorrentPier\Legacy\Common\User;

/**
 * Auth Middleware
 *
 * Requires an authenticated user to access the route.
 * Redirects guests to the login page with return URL.
 */
readonly class AuthMiddleware implements MiddlewareInterface
{
    public function __construct(
        private User $user,
    ) {}

    public function process(
        ServerRequestInterface  $request,
        RequestHandlerInterface $handler,
    ): ResponseInterface {
        if (!$this->user->data['session_logged_in']) {
            // Redirect to the login page with return URL
            $loginUrl = '/login?redirect=' . urlencode($request->getUri()->getPath());

            return new RedirectResponse($loginUrl);
        }

        return $handler->handle($request);
    }
}
