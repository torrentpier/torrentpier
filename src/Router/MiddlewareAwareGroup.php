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

use Illuminate\Contracts\Container\BindingResolutionException;
use League\Route\RouteGroup as LeagueRouteGroup;
use Psr\Http\Server\MiddlewareInterface;

/**
 * Wrapper for LeagueRouteGroup that supports string middleware aliases
 *
 * Allows Laravel-style middleware registration:
 *   $router->group('/api', fn($g) => ...)->middleware('session');
 *
 * Instead of:
 *   $router->group('/api', fn($g) => ...)->middleware(app()->make(StartSession::class));
 */
readonly class MiddlewareAwareGroup
{
    public function __construct(
        private LeagueRouteGroup $group,
        private Router           $router,
    ) {}

    /**
     * Add middleware to the group
     *
     * @param string|MiddlewareInterface $middleware Alias string or middleware instance
     * @throws BindingResolutionException
     */
    public function middleware(string|MiddlewareInterface $middleware): self
    {
        $this->group->middleware($this->router->resolveMiddleware($middleware));

        return $this;
    }

    /**
     * Get the underlying League RouteGroup
     */
    public function getLeagueGroup(): LeagueRouteGroup
    {
        return $this->group;
    }
}
