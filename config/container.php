<?php

return [
    // Container configuration
    'environment' => env('APP_ENV', 'development'),
    'debug' => env('APP_DEBUG', false),

    // Enable/disable features
    'autowiring' => true,
    'annotations' => false,

    // Compilation settings for production
    'compilation_dir' => __DIR__ . '/../internal_data/cache/container',
    'proxies_dir' => __DIR__ . '/../internal_data/cache/proxies',

    // Additional definition files to load
    'definition_files' => [
        // Add custom definition files here
        // __DIR__ . '/services/custom.php',
    ],

    // Container-specific settings
    'container' => [
        // Add any PHP-DI specific settings here
    ],
];
