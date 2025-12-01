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
use League\Route\Router as LeagueRouter;
use League\Route\RouteGroup;
use League\Route\Strategy\ApplicationStrategy;
use Psr\Http\Message\ResponseInterface;
use Psr\Http\Message\ServerRequestInterface;

/**
 * Router wrapper for TorrentPier
 *
 * Provides singleton access to league/route with TorrentPier-specific defaults.
 */
class Router
{
    private static ?self $instance = null;
    private LeagueRouter $router;
    private bool $routesLoaded = false;

    private function __construct()
    {
        $this->router = new LeagueRouter();

        // Use ApplicationStrategy for standard request handling
        $strategy = new ApplicationStrategy();
        $this->router->setStrategy($strategy);
    }

    /**
     * Get the singleton instance
     */
    public static function getInstance(): self
    {
        if (self::$instance === null) {
            self::$instance = new self();
        }

        return self::$instance;
    }

    /**
     * Map a route to a handler
     *
     * @param string|array $method HTTP method (GET, POST, etc.) or '*' for any
     * @param string $path Route path (e.g., '/terms', '/viewtopic/{id}')
     * @param callable|string $handler Route handler
     * @return Route
     */
    public function map(string|array $method, string $path, callable|string $handler): \League\Route\Route
    {
        return $this->router->map($method, $path, $handler);
    }

    /**
     * Map a GET route
     */
    public function get(string $path, callable|string $handler): \League\Route\Route
    {
        return $this->router->get($path, $handler);
    }

    /**
     * Map a POST route
     */
    public function post(string $path, callable|string $handler): \League\Route\Route
    {
        return $this->router->post($path, $handler);
    }

    /**
     * Map a route that responds to any HTTP method
     */
    public function any(string $path, callable|string $handler): \League\Route\Route
    {
        return $this->router->map(['GET', 'POST', 'PUT', 'PATCH', 'DELETE'], $path, $handler);
    }

    /**
     * Create a route group with shared attributes
     *
     * @param string $prefix URL prefix for the group
     * @param callable $callback Callback to define routes in the group
     */
    public function group(string $prefix, callable $callback): RouteGroup
    {
        return $this->router->group($prefix, $callback);
    }

    /**
     * Dispatch a request and return a response
     *
     * @param ServerRequestInterface $request
     * @return ResponseInterface
     */
    public function dispatch(ServerRequestInterface $request): ResponseInterface
    {
        return $this->router->dispatch($request);
    }

    /**
     * Get the underlying league/route Router instance
     */
    public function getLeagueRouter(): LeagueRouter
    {
        return $this->router;
    }

    /**
     * Reset the singleton (useful for testing)
     */
    public static function reset(): void
    {
        self::$instance = null;
    }

    /**
     * Check if a route exists for the given path
     */
    public function hasRoute(string $path, string $method = 'GET'): bool
    {
        // Create a minimal PSR-7 request to test matching
        $request = new \Laminas\Diactoros\ServerRequest(
            serverParams: ['REQUEST_METHOD' => $method],
            uri: $path,
            method: $method
        );

        try {
            $this->router->dispatch($request);
            return true;
        } catch (\League\Route\Http\Exception\NotFoundException) {
            return false;
        } catch (\Throwable) {
            // Route exists, but the handler failed - that's OK, route still exists
            return true;
        }
    }

    /**
     * Mark routes as loaded
     */
    public function setRoutesLoaded(): void
    {
        $this->routesLoaded = true;
    }

    /**
     * Check if routes have been loaded
     */
    public function areRoutesLoaded(): bool
    {
        return $this->routesLoaded;
    }
}
