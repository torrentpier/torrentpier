<?php

declare(strict_types=1);

namespace App\Providers;

use App\Http\Routing\Router;
use Illuminate\Support\ServiceProvider;
use Illuminate\Http\Request;

/**
 * Route Service Provider
 *
 * Loads and registers application routes using Illuminate Routing
 */
class RouteServiceProvider extends ServiceProvider
{
    /**
     * The path to the "home" route for your application
     */
    public const HOME = '/';

    /**
     * Register services
     */
    public function register(): void
    {
        // Register the Router
        $this->app->singleton(Router::class, function ($app) {
            return new Router($app);
        });

        // Alias for convenience
        $this->app->alias(Router::class, 'router');
    }

    /**
     * Define your route model bindings, pattern filters, etc.
     */
    public function boot(): void
    {
        $this->configureRateLimiting();

        $this->routes(function () {
            $this->mapApiRoutes();
            $this->mapWebRoutes();
            $this->mapAdminRoutes();
        });
    }

    /**
     * Configure the rate limiters for the application
     */
    protected function configureRateLimiting(): void
    {
        // Rate limiting can be configured here when needed
        // Example:
        // RateLimiter::for('api', function (Request $request) {
        //     return Limit::perMinute(60)->by($request->user()?->id ?: $request->ip());
        // });
    }

    /**
     * Define the routes for the application
     */
    protected function routes(\Closure $callback): void
    {
        $callback();
    }

    /**
     * Define the "web" routes for the application
     */
    protected function mapWebRoutes(): void
    {
        $router = $this->app->make(Router::class);
        $routeFile = $this->app->make('path.base') . '/routes/web.php';

        if (file_exists($routeFile)) {
            $router->group([], function () use ($routeFile) {
                require $routeFile;
            });
        }
    }

    /**
     * Define the "api" routes for the application
     */
    protected function mapApiRoutes(): void
    {
        $router = $this->app->make(Router::class);
        $routeFile = $this->app->make('path.base') . '/routes/api.php';

        if (file_exists($routeFile)) {
            $router->group([
                'prefix' => 'api',
            ], function () use ($routeFile) {
                require $routeFile;
            });
        }
    }

    /**
     * Define the "admin" routes for the application
     */
    protected function mapAdminRoutes(): void
    {
        $router = $this->app->make(Router::class);
        $routeFile = $this->app->make('path.base') . '/routes/admin.php';

        if (file_exists($routeFile)) {
            $router->group([
                'prefix' => 'admin',
                'middleware' => [
                    'auth',
                    'admin',
                ]
            ], function () use ($routeFile) {
                require $routeFile;
            });
        }
    }
}
