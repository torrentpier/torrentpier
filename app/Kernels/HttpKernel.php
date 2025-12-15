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
 *
 * Middleware configuration is defined in bootstrap/app.php via withMiddleware().
 */
class HttpKernel
{
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
        if (!$this->app->isBooted()) {
            $this->app->boot();
        }

        $this->bootstrap();

        return $this->app->make(Router::class)->dispatch($request);
    }

    /**
     * Perform any final actions after the response has been sent
     */
    public function terminate(ServerRequestInterface $request, ResponseInterface $response): void
    {
        // Cleanup after request handling (logging, session cleanup, etc.)
    }

    /**
     * Bootstrap the HTTP layer
     * @throws BindingResolutionException
     */
    protected function bootstrap(): void
    {
        $router = $this->app->make(Router::class);

        if (!$router->areRoutesLoaded()) {
            $router->setMiddlewareAliases($this->app->getMiddlewareConfig()->aliases);

            $this->loadRoutesFromConfig($router);
            $router->setRoutesLoaded();
        }
    }

    /**
     * Load routes from application routing configuration
     */
    protected function loadRoutesFromConfig(Router $router): void
    {
        foreach ($this->app->getRoutingConfig() as $path) {
            if (file_exists($path)) {
                (require $path)($router);
            }
        }
    }
}
