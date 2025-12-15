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

use Illuminate\Contracts\Container\BindingResolutionException;
use Psr\Http\Message\ResponseInterface;
use Psr\Http\Message\ServerRequestInterface;
use Psr\Http\Server\MiddlewareInterface;
use Psr\Http\Server\RequestHandlerInterface;
use TorrentPier\Language;
use TorrentPier\Legacy\Common\User;

/**
 * Starts a user session and initializes locale.
 */
readonly class StartSession implements MiddlewareInterface
{
    public function __construct(
        private User     $user,
        private Language $lang,
    ) {}

    /**
     * @throws BindingResolutionException
     */
    public function process(
        ServerRequestInterface  $request,
        RequestHandlerInterface $handler,
    ): ResponseInterface {
        // Initialize a user session (unless already started)
        if (!\defined('SESSION_STARTED')) {
            $this->user->session_start();
            \define('SESSION_STARTED', true);
        }

        // Initialize language from user preferences
        $userLang = $this->user->data['user_lang'] ?? '';
        $this->lang->initializeLanguage($userLang);

        return $handler->handle($request);
    }
}
