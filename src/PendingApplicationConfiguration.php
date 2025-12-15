<?php

/**
 * TorrentPier – Bull-powered BitTorrent tracker engine
 *
 * @copyright Copyright (c) 2005-2025 TorrentPier (https://torrentpier.com)
 * @link      https://github.com/torrentpier/torrentpier for the canonical source repository
 * @license   https://github.com/torrentpier/torrentpier/blob/master/LICENSE MIT License
 */

declare(strict_types=1);

namespace TorrentPier;

use Closure;
use TorrentPier\Exceptions\Handler;
use TorrentPier\Http\Middleware;

/**
 * Fluent configuration builder for Application
 *
 * This class provides a fluent API for configuring
 * the application before it starts handling requests.
 *
 * Usage:
 *   return Application::configure(basePath: dirname(__DIR__))
 *       ->withBootstrappers([
 *           App\Bootstrap\LoadEnvironmentVariables::class,
 *           App\Bootstrap\LoadConfiguration::class,
 *           // ...
 *       ])
 *       ->withRouting(
 *           web: __DIR__ . '/../routes/web.php',
 *           admin: __DIR__ . '/../routes/admin.php',
 *           tracker: __DIR__ . '/../routes/tracker.php',
 *           commands: __DIR__ . '/../routes/console.php',
 *       )
 *       ->withMiddleware(function (Middleware $middleware): void {
 *           $middleware->web(append: [WebMiddleware::class]);
 *           $middleware->alias('auth', AuthMiddleware::class);
 *       })
 *       ->withExceptions(function (Handler $exceptions): void {
 *           $exceptions->render(fn ($e) => Response::error());
 *       })
 *       ->create();
 */
class PendingApplicationConfiguration
{
    /**
     * Create a new pending application configuration instance
     */
    public function __construct(
        protected Application $app,
    ) {}

    /**
     * Configure the bootstrappers for the application
     *
     * Bootstrappers are classes that perform initialization tasks
     * during application startup, such as:
     * - Loading environment variables
     * - Loading configuration
     * - Setting up error handlers
     * - Registering service providers
     *
     * @param string[] $bootstrappers Array of bootstrapper class names
     */
    public function withBootstrappers(array $bootstrappers): static
    {
        $this->app->setBootstrappers($bootstrappers);

        return $this;
    }

    /**
     * Configure the routing for the application
     *
     * @param string|null $web Path to a web routes file
     * @param string|null $api Path to API routes file
     * @param string|null $admin Path to an admin routes file
     * @param string|null $tracker Path to tracker routes file
     * @param string|null $commands Path to console commands routes file
     */
    public function withRouting(
        ?string $web = null,
        ?string $api = null,
        ?string $admin = null,
        ?string $tracker = null,
        ?string $commands = null,
    ): static {
        $config = [];

        if ($web !== null) {
            $config['web'] = $web;
        }
        if ($api !== null) {
            $config['api'] = $api;
        }
        if ($admin !== null) {
            $config['admin'] = $admin;
        }
        if ($tracker !== null) {
            $config['tracker'] = $tracker;
        }
        if ($commands !== null) {
            $config['commands'] = $commands;
        }

        $this->app->setRoutingConfig($config);

        return $this;
    }

    /**
     * Configure middleware for the application
     *
     * The callback receives a Middleware instance that can be used
     * to configure global middleware, middleware groups, and aliases.
     *
     * @param Closure(Middleware): void $callback
     */
    public function withMiddleware(Closure $callback): static
    {
        $this->app->setMiddlewareCallback($callback);

        return $this;
    }

    /**
     * Configure exception handling for the application
     *
     * The callback receives a Handler instance that can be used
     * to configure exception rendering and reporting.
     *
     * @param Closure(Handler): void $callback
     */
    public function withExceptions(Closure $callback): static
    {
        $this->app->setExceptionsCallback($callback);

        return $this;
    }

    /**
     * Create the configured application instance
     *
     * This method finalizes the configuration and returns
     * the fully configured Application instance.
     */
    public function create(): Application
    {
        return $this->app;
    }
}
