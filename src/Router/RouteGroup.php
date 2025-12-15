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

use League\Route\Route;
use League\Route\RouteGroup as LeagueRouteGroup;

/**
 * RouteGroup wrapper for TorrentPier
 *
 * Adds convenience methods like any() to League's RouteGroup.
 */
readonly class RouteGroup
{
    public function __construct(
        private LeagueRouteGroup $group,
    ) {}

    public function map(string|array $method, string $path, callable|string $handler): Route
    {
        return $this->group->map($method, $path, $handler);
    }

    public function get(string $path, callable|string $handler): Route
    {
        return $this->group->get($path, $handler);
    }

    public function post(string $path, callable|string $handler): Route
    {
        return $this->group->post($path, $handler);
    }

    public function put(string $path, callable|string $handler): Route
    {
        return $this->group->put($path, $handler);
    }

    public function patch(string $path, callable|string $handler): Route
    {
        return $this->group->patch($path, $handler);
    }

    public function delete(string $path, callable|string $handler): Route
    {
        return $this->group->delete($path, $handler);
    }

    public function any(string $path, callable|string $handler): Route
    {
        return $this->group->map(['GET', 'POST', 'PUT', 'PATCH', 'DELETE'], $path, $handler);
    }
}
