<?php

/**
 * TorrentPier – Bull-powered BitTorrent tracker engine
 *
 * @copyright Copyright (c) 2005-2025 TorrentPier (https://torrentpier.com)
 * @link      https://github.com/torrentpier/torrentpier for the canonical source repository
 * @license   https://github.com/torrentpier/torrentpier/blob/master/LICENSE MIT License
 */

declare(strict_types=1);

namespace App\Providers;

use App\Http\Middleware\AuthMiddleware;
use App\Http\Middleware\WebMiddleware;
use TorrentPier\ServiceProvider;

/**
 * Middleware Service Provider
 *
 * Registers HTTP middleware for the application.
 */
class MiddlewareServiceProvider extends ServiceProvider
{
    public function register(): void
    {
        // WebMiddleware with dependencies (auto-wired)
        $this->app->singleton(WebMiddleware::class);

        // AuthMiddleware without dependencies
        $this->app->singleton(AuthMiddleware::class);
    }

    public function provides(): array
    {
        return [
            WebMiddleware::class,
            AuthMiddleware::class,
        ];
    }
}
