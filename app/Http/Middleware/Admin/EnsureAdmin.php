<?php

/**
 * TorrentPier – Bull-powered BitTorrent tracker engine
 *
 * @copyright Copyright (c) 2005-2025 TorrentPier (https://torrentpier.com)
 * @link      https://github.com/torrentpier/torrentpier for the canonical source repository
 * @license   https://github.com/torrentpier/torrentpier/blob/master/LICENSE MIT License
 */

declare(strict_types=1);

namespace App\Http\Middleware\Admin;

use Illuminate\Contracts\Container\BindingResolutionException;
use JsonException;
use Laminas\Diactoros\Response\RedirectResponse;
use Psr\Http\Message\ResponseInterface;
use Psr\Http\Message\ServerRequestInterface;
use Psr\Http\Server\MiddlewareInterface;
use Psr\Http\Server\RequestHandlerInterface;

/**
 * Middleware to ensure the user has admin access
 *
 * Handles:
 * - Setting IN_ADMIN constant
 * - Checking user is logged in (not guest)
 * - Checking user has admin privileges
 * - Checking admin session is active
 */
readonly class EnsureAdmin implements MiddlewareInterface
{
    /**
     * @throws BindingResolutionException
     * @throws JsonException
     */
    public function process(ServerRequestInterface $request, RequestHandlerInterface $handler): ResponseInterface
    {
        if (!\defined('IN_ADMIN')) {
            \define('IN_ADMIN', true);
        }

        if (IS_GUEST) {
            return new RedirectResponse(LOGIN_URL . '?redirect=admin/index.php');
        }

        if (!IS_ADMIN) {
            bb_die(__('NOT_ADMIN'));
        }

        if (!userdata('session_admin')) {
            $redirect = url_arg((string)$request->getUri(), 'admin', 1);

            return new RedirectResponse(LOGIN_URL . "?redirect=$redirect");
        }

        return $handler->handle($request);
    }
}
