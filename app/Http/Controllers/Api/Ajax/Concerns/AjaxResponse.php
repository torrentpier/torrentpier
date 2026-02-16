<?php

/**
 * TorrentPier – Bull-powered BitTorrent tracker engine
 *
 * @copyright Copyright (c) 2005-2025 TorrentPier (https://torrentpier.com)
 * @link      https://github.com/torrentpier/torrentpier for the canonical source repository
 * @license   https://github.com/torrentpier/torrentpier/blob/master/LICENSE MIT License
 */

declare(strict_types=1);

namespace App\Http\Controllers\Api\Ajax\Concerns;

use Illuminate\Contracts\Container\BindingResolutionException;
use Laminas\Diactoros\Response\JsonResponse;
use Psr\Http\Message\ResponseInterface;

/**
 * Ajax Response Trait
 *
 * Provides response helpers matching the legacy Ajax format.
 */
trait AjaxResponse
{
    /**
     * Send successful response with data
     */
    protected function response(array $data = []): ResponseInterface
    {
        return new JsonResponse([
            'action' => $this->action,
            ...$data,
        ]);
    }

    /**
     * Send error response
     */
    protected function error(string $message, int $code = E_AJAX_GENERAL_ERROR): ResponseInterface
    {
        return new JsonResponse([
            'action' => $this->action,
            'error_code' => $code,
            'error_msg' => strip_tags(br2nl($message)),
        ]);
    }

    /**
     * Prompt for password (admin session validation)
     */
    protected function promptPassword(): ResponseInterface
    {
        return new JsonResponse([
            'action' => $this->action,
            'prompt_password' => 1,
        ]);
    }

    /**
     * Prompt for confirmation
     * @throws BindingResolutionException
     */
    protected function promptConfirm(string $message = ''): ResponseInterface
    {
        return new JsonResponse([
            'action' => $this->action,
            'prompt_confirm' => 1,
            'confirm_msg' => strip_tags(br2nl($message ?: __('QUESTION'))),
        ]);
    }

    /**
     * Get required mode from request body
     */
    protected function requireMode(array $body): string|ResponseInterface
    {
        $mode = (string)($body['mode'] ?? '');
        if (!$mode) {
            return $this->error('invalid mode (empty)');
        }

        return $mode;
    }

    /**
     * Get required user_id from request body
     * @throws BindingResolutionException
     */
    protected function requireUserId(array $body): int|ResponseInterface
    {
        $userId = (int)($body['user_id'] ?? 0);
        if (!$userId) {
            return $this->error(__('NO_USER_ID_SPECIFIED'));
        }

        return $userId;
    }

    /**
     * Check and validate admin session
     *
     * @throws BindingResolutionException
     * @return ResponseInterface|null Returns prompt response or null if session is valid
     */
    protected function checkAdminSession(array $body): ?ResponseInterface
    {
        if (!userdata('session_admin')) {
            if (empty($body['user_password'])) {
                return $this->promptPassword();
            }

            $loginArgs = [
                'login_username' => userdata('username'),
                'login_password' => $body['user_password'],
            ];

            $loginResult = user()->login($loginArgs, true);

            if (!empty($loginResult['2fa_required'])) {
                return $this->error(__('TWO_FACTOR_AUTH'));
            }

            if (!$loginResult) {
                return $this->error(__('ERROR_LOGIN'));
            }
        }

        return null;
    }

    /**
     * Verify moderator rights for a forum
     *
     * @throws BindingResolutionException
     * @return ResponseInterface|null Returns error response or null if authorized
     */
    protected function verifyModRights(int $forumId): ?ResponseInterface
    {
        if (IS_ADMIN) {
            return null;
        }

        $isAuth = auth(AUTH_MOD, $forumId, userdata());
        if (!$isAuth['auth_mod']) {
            return $this->error(__('NOT_MODERATOR'));
        }

        return null;
    }
}
