<?php

/**
 * TorrentPier – Bull-powered BitTorrent tracker engine
 *
 * @copyright Copyright (c) 2005-2025 TorrentPier (https://torrentpier.com)
 * @link      https://github.com/torrentpier/torrentpier for the canonical source repository
 * @license   https://github.com/torrentpier/torrentpier/blob/master/LICENSE MIT License
 */

declare(strict_types=1);

namespace App\Kernels;

use Illuminate\Contracts\Container\BindingResolutionException;
use Psr\Http\Message\ResponseInterface;
use Psr\Http\Message\ServerRequestInterface;
use TorrentPier\Application;
use TorrentPier\Router\Router;

/**
 * HTTP Kernel
 *
 * Handles HTTP requests by bootstrapping the application,
 * loading routes, and dispatching to the router.
 */
class HttpKernel
{
    /**
     * The middleware stack
     *
     * @var string[]
     */
    public array $middleware {
        get => $this->_middleware;
        set => $this->_middleware = $value;
    }

    private array $_middleware = [
        \App\Http\Middleware\WebMiddleware::class,
    ];

    /**
     * The middleware groups
     *
     * @var array<string, string[]>
     */
    public array $middlewareGroups {
        get => $this->_middlewareGroups;
        set => $this->_middlewareGroups = $value;
    }

    private array $_middlewareGroups = [
        'web' => [
            \App\Http\Middleware\WebMiddleware::class,
        ],
        'api' => [],
    ];

    /**
     * The route middleware aliases
     *
     * @var array<string, string>
     */
    public array $middlewareAliases {
        get => $this->_middlewareAliases;
        set => $this->_middlewareAliases = $value;
    }

    private array $_middlewareAliases = [
        'web' => \App\Http\Middleware\WebMiddleware::class,
        'auth' => \App\Http\Middleware\AuthMiddleware::class,
    ];

    /**
     * Create a new HTTP Kernel instance
     */
    public function __construct(
        private readonly Application $_app,
    ) {}

    /**
     * The application instance (read-only)
     */
    public Application $app {
        get => $this->_app;
    }

    /**
     * Handle an incoming HTTP request
     * @throws BindingResolutionException
     */
    public function handle(ServerRequestInterface $request): ResponseInterface
    {
        // Boot the application if not already booted
        if (!$this->app->isBooted()) {
            $this->app->boot();
        }

        // Bootstrap the HTTP layer
        $this->bootstrap();

        // Get the router and dispatch the request
        $router = $this->app->make(Router::class);

        return $router->dispatch($request);
    }

    /**
     * Perform any final actions after the response has been sent
     */
    public function terminate(ServerRequestInterface $request, ResponseInterface $response): void
    {
        // Perform cleanup after request handling
        // This can be used for:
        // - Logging
        // - Session cleanup
        // - Releasing resources
    }

    /**
     * Bootstrap the HTTP layer
     * @throws BindingResolutionException
     */
    protected function bootstrap(): void
    {
        // Load routes if not already loaded
        $router = $this->app->make(Router::class);

        if (!$router->areRoutesLoaded()) {
            // Register middleware aliases from kernel
            $router->setMiddlewareAliases($this->middlewareAliases);

            $routesPath = $this->app->routesPath('web.php');

            if (file_exists($routesPath)) {
                $routes = require $routesPath;
                $routes($router);
                $router->setRoutesLoaded();
            }
        }
    }
}
