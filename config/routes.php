<?php

declare(strict_types=1);

/**
 * Route Configuration
 *
 * This file contains routing configuration for the TorrentPier application.
 * It defines route patterns, middleware, and other routing-related settings.
 *
 * NOTE: This configuration file is currently not used by the routing system.
 * Routes are loaded directly from src/Presentation/Http/Routes/web.php
 * This file serves as a template for future routing configuration implementation.
 */

// Configuration is commented out as it's not currently integrated
/*
return [
    // Route caching configuration
    'cache' => [
        'enabled' => env('ROUTE_CACHE_ENABLED', false),
        'path' => env('ROUTE_CACHE_PATH', __DIR__ . '/../internal_data/cache/routes.php'),
        'ttl' => env('ROUTE_CACHE_TTL', 3600), // 1 hour
    ],

    // Global middleware (applied to all routes)
    'middleware' => [
        // 'TorrentPier\Infrastructure\Http\Middleware\CorsMiddleware',
        // 'TorrentPier\Infrastructure\Http\Middleware\SecurityHeadersMiddleware',
    ],

    // Route groups configuration
    'groups' => [
        // Web routes (HTML responses)
        'web' => [
            'prefix' => '',
            'middleware' => [
                // 'TorrentPier\Infrastructure\Http\Middleware\WebMiddleware',
                // 'TorrentPier\Infrastructure\Http\Middleware\StartSession',
                // 'TorrentPier\Infrastructure\Http\Middleware\VerifyCsrfToken',
            ],
        ],

        // API routes (JSON responses)
        'api' => [
            'prefix' => '/api',
            'middleware' => [
                // 'TorrentPier\Infrastructure\Http\Middleware\ApiMiddleware',
                // 'TorrentPier\Infrastructure\Http\Middleware\RateLimitMiddleware',
                // 'TorrentPier\Infrastructure\Http\Middleware\AuthenticationMiddleware',
            ],
        ],

        // Admin routes (Administrative interface)
        'admin' => [
            'prefix' => '/admin',
            'middleware' => [
                // 'TorrentPier\Infrastructure\Http\Middleware\AdminMiddleware',
                // 'TorrentPier\Infrastructure\Http\Middleware\RequireAdminAuth',
                // 'TorrentPier\Infrastructure\Http\Middleware\AuditLoggingMiddleware',
            ],
        ],
    ],

    // Route defaults
    'defaults' => [
        'namespace' => 'TorrentPier\Presentation\Http\Controllers',
        'timeout' => 30, // seconds
        'methods' => ['GET', 'POST', 'PUT', 'PATCH', 'DELETE', 'OPTIONS'],
    ],

    // Route constraints
    'constraints' => [
        'id' => '\d+',
        'hash' => '[a-fA-F0-9]+',
        'slug' => '[a-z0-9-]+',
        'uuid' => '[0-9a-f]{8}-[0-9a-f]{4}-[0-9a-f]{4}-[0-9a-f]{4}-[0-9a-f]{12}',
    ],

    // Route patterns for common use cases
    'patterns' => [
        // Forum routes
        'forum' => '/forum/{forum_id:\d+}',
        'topic' => '/topic/{topic_id:\d+}',
        'post' => '/post/{post_id:\d+}',

        // Torrent routes
        'torrent' => '/torrent/{torrent_id:\d+}',
        'torrent_hash' => '/torrent/{info_hash:[a-fA-F0-9]+}',

        // User routes
        'user' => '/user/{user_id:\d+}',
        'profile' => '/profile/{username:[a-zA-Z0-9_-]+}',
    ],

    // Fallback routes
    'fallback' => [
        'enabled' => true,
        'handler' => 'TorrentPier\Presentation\Http\Controllers\Web\FallbackController@handle',
    ],
];
*/
