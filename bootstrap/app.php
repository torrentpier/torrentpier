<?php

/**
 * TorrentPier – Bull-powered BitTorrent tracker engine
 *
 * Application Bootstrap with Fluent Configuration
 *
 * This file creates and configures the Application instance using
 * a fluent API for clean, expressive configuration.
 *
 * @copyright Copyright (c) 2005-2025 TorrentPier (https://torrentpier.com)
 * @link      https://github.com/torrentpier/torrentpier for the canonical source repository
 * @license   https://github.com/torrentpier/torrentpier/blob/master/LICENSE MIT License
 */

use App\Bootstrap\BootApplication;
use App\Bootstrap\BootProviders;
use App\Bootstrap\HandleExceptions;
use App\Bootstrap\LoadConfiguration;
use App\Bootstrap\LoadEnvironmentVariables;
use App\Bootstrap\RegisterHelpers;
use App\Bootstrap\RegisterProviders;
use App\Bootstrap\SetTrustedProxies;
use App\Http\Middleware\Admin;
use App\Http\Middleware\Authenticate;
use App\Http\Middleware\StartSession;
use App\Http\Middleware\Tracker;
use TorrentPier\Application;
use TorrentPier\Exceptions\Handler;
use TorrentPier\Http\Middleware;

/*
|--------------------------------------------------------------------------
| Create The Application
|--------------------------------------------------------------------------
|
| Create and configure the TorrentPier application using the fluent API.
| This approach provides clean, expressive configuration similar to
| Laravel 12's bootstrap/app.php structure.
|
*/

return Application::configure(basePath: dirname(__DIR__))

    /*
    |--------------------------------------------------------------------------
    | Bootstrappers
    |--------------------------------------------------------------------------
    |
    | These classes are executed during application bootstrap in the order
    | listed. Each bootstrapper handles a specific initialization task:
    |
    | - LoadEnvironmentVariables: Loads .env, sets base constants
    | - SetTrustedProxies: Handles CDN/proxy IP extraction
    | - LoadConfiguration: Loads config files, defines FORUM_PATH
    | - RegisterHelpers: Loads helper and legacy functions (provides config())
    | - HandleExceptions: Sets up Whoops and Tracy error handlers
    | - RegisterProviders: Registers service providers
    | - BootProviders: Boots all registered providers
    | - BootApplication: Final initialization (init_bb/init_tr)
    |
    */
    ->withBootstrappers([
        LoadEnvironmentVariables::class,
        SetTrustedProxies::class,
        LoadConfiguration::class,
        RegisterHelpers::class,      // Must come before HandleExceptions (provides config() function)
        HandleExceptions::class,
        RegisterProviders::class,
        BootProviders::class,
        BootApplication::class,
    ])

    /*
    |--------------------------------------------------------------------------
    | Routing Configuration
    |--------------------------------------------------------------------------
    |
    | Configure the route files for the application.
    |
    | Route loading order matters for performance:
    | 1. tracker - Direct announce/scrape (no middleware, highest performance)
    | 2. admin - Admin panel routes
    | 3. api - API routes (when added)
    | 4. web - Main application routes (loaded last as catch-all)
    |
    */
    ->withRouting(
        web: __DIR__ . '/../routes/web.php',
        api: __DIR__ . '/../routes/api.php',
        admin: __DIR__ . '/../routes/admin.php',
        tracker: __DIR__ . '/../routes/tracker.php',
        commands: __DIR__ . '/../routes/console.php',
    )

    /*
    |--------------------------------------------------------------------------
    | Middleware Configuration
    |--------------------------------------------------------------------------
    |
    | Configure middleware for the application.
    |
    | Note: Static files and .php redirects are handled by the web server
    | (nginx/Caddy). See install/nginx.conf or install/docker/Caddyfile.
    |
    */
    ->withMiddleware(function (Middleware $middleware): void {
        // Middleware aliases for route definitions
        $middleware->alias('session', StartSession::class);
        $middleware->alias('auth', Authenticate::class);
        $middleware->alias('tracker', Tracker\BootTracker::class);
        $middleware->alias('admin', Admin\EnsureAdmin::class);
    })

    /*
    |--------------------------------------------------------------------------
    | Exception Handling
    |--------------------------------------------------------------------------
    |
    | Configure exception rendering and reporting.
    |
    */
    ->withExceptions(function (Handler $exceptions): void {
        // Custom exception handling can be configured here
        // Example:
        // $exceptions->render(function (NotFoundException $e, $request) {
        //     return Response::notFound();
        // });
    })

    /*
    |--------------------------------------------------------------------------
    | Create Application
    |--------------------------------------------------------------------------
    |
    | Finalize configuration and return the application instance.
    |
    */
    ->create();
