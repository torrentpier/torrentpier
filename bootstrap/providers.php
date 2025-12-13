<?php

/**
 * TorrentPier – Bull-powered BitTorrent tracker engine
 *
 * Application Service Providers
 *
 * This file returns the array of service providers to register.
 * The order of providers is important - providers registered first
 * will be available to providers registered later.
 *
 * @copyright Copyright (c) 2005-2025 TorrentPier (https://torrentpier.com)
 * @link      https://github.com/torrentpier/torrentpier for the canonical source repository
 * @license   https://github.com/torrentpier/torrentpier/blob/master/LICENSE MIT License
 */

return [
    /*
    |--------------------------------------------------------------------------
    | Core Service Providers
    |--------------------------------------------------------------------------
    |
    | These providers are essential for the application to function.
    | They should be registered first and in this specific order.
    |
    */

    // Configuration must be first - other providers depend on it
    App\Providers\ConfigServiceProvider::class,

    // Error handling should be early to catch boot errors
    App\Providers\ErrorHandlerServiceProvider::class,

    // Database and cache are core infrastructure
    App\Providers\DatabaseServiceProvider::class,
    App\Providers\CacheServiceProvider::class,

    // Session/User depends on DB and Cache
    App\Providers\SessionServiceProvider::class,

    // HTTP and routing
    App\Providers\HttpServiceProvider::class,

    // Template and view rendering
    App\Providers\TemplateServiceProvider::class,

    // Legacy services (BBCode, Ajax, etc.)
    App\Providers\LegacyServiceProvider::class,

    /*
    |--------------------------------------------------------------------------
    | Application Service Provider
    |--------------------------------------------------------------------------
    |
    | This provider is for application-specific bindings and should
    | be registered last after all core providers.
    |
    */

    App\Providers\AppServiceProvider::class,
];
