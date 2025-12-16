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

use App\Http\Middleware\Authenticate;
use App\Http\Middleware\StartSession;
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
        // StartSession with dependencies (auto-wired)
        $this->app->singleton(StartSession::class);

        // Authenticate with dependencies (auto-wired)
        $this->app->singleton(Authenticate::class);
    }

    public function provides(): array
    {
        return [
            StartSession::class,
            Authenticate::class,
        ];
    }
}
