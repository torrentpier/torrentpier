<?php

/**
 * TorrentPier – Bull-powered BitTorrent tracker engine
 *
 * @copyright Copyright (c) 2005-2025 TorrentPier (https://torrentpier.com)
 * @link      https://github.com/torrentpier/torrentpier for the canonical source repository
 * @license   https://github.com/torrentpier/torrentpier/blob/master/LICENSE MIT License
 */

declare(strict_types=1);

namespace App\Http\Middleware\Api;

use Illuminate\Contracts\Container\BindingResolutionException;
use Laminas\Diactoros\Response\JsonResponse;
use Psr\Http\Message\ResponseInterface;
use Psr\Http\Message\ServerRequestInterface;
use Psr\Http\Server\MiddlewareInterface;
use Psr\Http\Server\RequestHandlerInterface;

/**
 * Parameterized middleware for role-based API access control.
 * Returns JSON error responses for unauthorized requests.
 */
readonly class EnsureRole implements MiddlewareInterface
{
    public function __construct(
        private string $role,
    ) {}

    /**
     * @throws BindingResolutionException
     */
    public function process(
        ServerRequestInterface  $request,
        RequestHandlerInterface $handler,
    ): ResponseInterface {
        $authorized = match ($this->role) {
            'guest' => true,
            'user' => !IS_GUEST,
            'mod' => IS_AM,
            'admin' => IS_ADMIN,
            'super_admin' => IS_SUPER_ADMIN,
            default => false,
        };

        if (!$authorized) {
            $error = match ($this->role) {
                'user' => ['error_code' => E_AJAX_GENERAL_ERROR, 'error_msg' => __('NEED_TO_LOGIN_FIRST')],
                'mod' => ['error_code' => E_AJAX_GENERAL_ERROR, 'error_msg' => __('ONLY_FOR_MOD')],
                'admin' => ['error_code' => E_AJAX_GENERAL_ERROR, 'error_msg' => __('ONLY_FOR_ADMIN')],
                'super_admin' => ['error_code' => E_AJAX_GENERAL_ERROR, 'error_msg' => __('ONLY_FOR_SUPER_ADMIN')],
                default => ['error_code' => E_AJAX_GENERAL_ERROR, 'error_msg' => __('NOT_AUTHORISED')],
            };

            return new JsonResponse($error, $this->role === 'user' ? 401 : 403);
        }

        return $handler->handle($request);
    }
}
