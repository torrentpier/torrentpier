<?php

declare(strict_types=1);

/**
 * Cache Configuration
 */

return [
    /*
    |--------------------------------------------------------------------------
    | Default Cache Store
    |--------------------------------------------------------------------------
    */
    'default' => env('CACHE_DRIVER', 'file'),

    /*
    |--------------------------------------------------------------------------
    | Cache Stores
    |--------------------------------------------------------------------------
    */
    'stores' => [
        'file' => [
            'driver' => 'file',
            'path' => storage_path('framework/cache'),
        ],

        'redis' => [
            'driver' => 'redis',
            'connection' => 'cache',
            'lock_connection' => 'default',
        ],

        'memcached' => [
            'driver' => 'memcached',
            'persistent_id' => env('MEMCACHED_PERSISTENT_ID'),
            'sasl' => [
                env('MEMCACHED_USERNAME'),
                env('MEMCACHED_PASSWORD'),
            ],
            'options' => [
                // Memcached::OPT_CONNECT_TIMEOUT => 2000,
            ],
            'servers' => [
                [
                    'host' => env('MEMCACHED_HOST', '127.0.0.1'),
                    'port' => env('MEMCACHED_PORT', 11211),
                    'weight' => 100,
                ],
            ],
        ],

        'array' => [
            'driver' => 'array',
            'serialize' => false,
        ],
    ],

    /*
    |--------------------------------------------------------------------------
    | Cache Key Prefix
    |--------------------------------------------------------------------------
    */
    'prefix' => env('CACHE_PREFIX', 'tp_cache'),

    /*
    |--------------------------------------------------------------------------
    | Cache Tags
    |--------------------------------------------------------------------------
    */
    'tags' => [
        'torrents' => 3600,
        'users' => 1800,
        'forums' => 900,
        'stats' => 300,
    ],
];