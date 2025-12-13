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

use TorrentPier\Http\HttpClient;
use TorrentPier\Http\Request;
use TorrentPier\Router\Router;
use TorrentPier\ServiceProvider;

/**
 * HTTP Service Provider
 *
 * Registers HTTP-related services including request handling,
 * HTTP client, and routing.
 */
class HttpServiceProvider extends ServiceProvider
{
    /**
     * Register HTTP services
     */
    public function register(): void
    {
        // HTTP Request singleton
        $this->app->singleton(Request::class, function () {
            return Request::getInstance();
        });

        // HTTP Client for external requests
        $this->app->singleton(HttpClient::class, function () {
            return HttpClient::getInstance();
        });

        // Router singleton
        $this->app->singleton(Router::class, function () {
            return Router::getInstance();
        });

        // Register aliases
        $this->app->alias(Request::class, 'request');
        $this->app->alias(HttpClient::class, 'http');
        $this->app->alias(HttpClient::class, 'http.client');
        $this->app->alias(Router::class, 'router');
    }

    /**
     * Get the services provided by this provider
     *
     * @return string[]
     */
    public function provides(): array
    {
        return [
            Request::class,
            HttpClient::class,
            Router::class,
            'request',
            'http',
            'http.client',
            'router',
        ];
    }
}
