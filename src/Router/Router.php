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
use InvalidArgumentException;
use League\Route\Route;
use League\Route\RouteGroup as LeagueRouteGroup;
use League\Route\Router as LeagueRouter;
use League\Route\Strategy\ApplicationStrategy;
use Psr\Container\ContainerInterface;
use Psr\Http\Message\ResponseInterface;
use Psr\Http\Message\ServerRequestInterface;
use Psr\Http\Server\MiddlewareInterface;
use ReflectionClass;
use Throwable;

/**
 * Router wrapper for TorrentPier
 *
 * Provides access to league/route with TorrentPier-specific defaults.
 */
class Router
{
    private LeagueRouter $router;
    private bool $routesLoaded = false;

    /** @var array<string, class-string<MiddlewareInterface>> */
    private array $middlewareAliases = [];

    public function __construct(?ContainerInterface $container = null)
    {
        $this->router = new LeagueRouter;

        $strategy = new ApplicationStrategy;
        if ($container) {
            $strategy->setContainer($container);
        }
        $this->router->setStrategy($strategy);
    }

    /**
     * Set middleware aliases for string-based middleware resolution
     *
     * @param array<string, class-string<MiddlewareInterface>> $aliases
     */
    public function setMiddlewareAliases(array $aliases): void
    {
        $this->middlewareAliases = $aliases;
    }

    /**
     * Resolve middleware from string alias or return as-is
     *
     * @param string|MiddlewareInterface $middleware Alias string or middleware instance
     * @throws InvalidArgumentException|BindingResolutionException If alias not found
     */
    public function resolveMiddleware(string|MiddlewareInterface $middleware): MiddlewareInterface
    {
        if ($middleware instanceof MiddlewareInterface) {
            return $middleware;
        }

        if (!isset($this->middlewareAliases[$middleware])) {
            throw new InvalidArgumentException("Middleware alias '{$middleware}' not found");
        }

        return app()->make($this->middlewareAliases[$middleware]);
    }

    /**
     * Add global middleware to the router
     *
     * @param string|MiddlewareInterface $middleware Middleware alias or instance
     * @throws BindingResolutionException
     */
    public function middleware(string|MiddlewareInterface $middleware): self
    {
        $this->router->middleware($this->resolveMiddleware($middleware));

        return $this;
    }

    /**
     * Map a route to a handler
     *
     * @param string|array $method HTTP method (GET, POST, etc.) or '*' for any
     * @param string $path Route path (e.g., '/terms', '/viewtopic/{id}')
     * @param callable|string $handler Route handler
     */
    public function map(string|array $method, string $path, callable|string $handler): Route
    {
        return $this->router->map($method, $path, $handler);
    }

    /**
     * Map a GET route
     */
    public function get(string $path, callable|string $handler): Route
    {
        return $this->router->get($path, $handler);
    }

    /**
     * Map a POST route
     */
    public function post(string $path, callable|string $handler): Route
    {
        return $this->router->post($path, $handler);
    }

    /**
     * Map a route that responds to any HTTP method
     */
    public function any(string $path, callable|string $handler): Route
    {
        return $this->router->map(['GET', 'POST', 'PUT', 'PATCH', 'DELETE'], $path, $handler);
    }

    /**
     * Create a route group with shared attributes
     *
     * Returns MiddlewareAwareGroup which supports string middleware aliases:
     *   $router->group('/api', fn($g) => ...)->middleware('session');
     *
     * @param string $prefix URL prefix for the group
     * @param callable $callback Callback to define routes in the group
     */
    public function group(string $prefix, callable $callback): MiddlewareAwareGroup
    {
        $leagueGroup = $this->router->group($prefix, function (LeagueRouteGroup $group) use ($callback) {
            $callback(new RouteGroup($group));
        });

        return new MiddlewareAwareGroup($leagueGroup, $this);
    }

    /**
     * Dispatch a request and return a response
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
     * Get all registered routes
     *
     * @return array<array{methods: string, path: string, handler: string}>
     */
    public function getRoutes(): array
    {
        $reflection = new ReflectionClass($this->router);
        $prop = $reflection->getProperty('routes');
        $routes = $prop->getValue($this->router);

        $result = [];
        foreach ($routes as $route) {
            $methods = $route->getMethod();
            $handler = $route->getCallable();

            $result[] = [
                'methods' => \is_array($methods) ? implode('|', $methods) : $methods,
                'path' => $route->getPath(),
                'handler' => \is_object($handler) ? \get_class($handler) : (\is_string($handler) ? $handler : \gettype($handler)),
            ];
        }

        return $result;
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
            method: $method,
        );

        try {
            $this->router->dispatch($request);

            return true;
        } catch (\League\Route\Http\Exception\NotFoundException) {
            return false;
        } catch (Throwable) {
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
