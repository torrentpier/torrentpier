<?php

return [
    'debug' => false,

    'services' => [
        // Database
        'db' => [
            'debug' => '{self.debug}',
            'driver' => 'Pdo_Mysql',
            'hostname' => '127.0.0.1',
            'database' => 'tp_220',
            'username' => 'user',
            'password' => 'pass',
            'charset' => 'utf8'
        ],

        // Cache
        'cache' => [
            'adapter' => \TorrentPier\Cache\FileAdapter::class,
            'options' => [
                'directory' => __DIR__ . '/../internal_data/cache',
            ],
        ],

        // Translation
        'translator' => [
            'dir_cache' => __DIR__ . '/../internal_data/cache',
            'resources' => [
                [
                    'resource' => __DIR__ . '/../messages/ru.php',
                    'locale' => 'ru',
                ],
                [
                    'resource' => __DIR__ . '/../messages/en.php',
                    'locale' => 'en',
                ]
            ]
        ],

        // Twig
        'twig' => [
            'dir_templates' => __DIR__ . '/../templates/default',
            'dir_cache' => __DIR__ . '/../internal_data/cache',
        ],

        // Sphinx
        'sphinx' => [
            'debug' => '{self.debug}',
            'driver' => '{self.db.driver}',
            'hostname' => '{self.db.hostname}',
            'username' => 'user',
            'password' => 'pass',
            'port' => 9306,
            'charset' => 'utf8'
        ],

        // Logger
        'log' => [
            'handlers' => [
                function () {
                    return new \Monolog\Handler\StreamHandler(
                        __DIR__ . '/../internal_data/log/app.log',
                        \Monolog\Logger::DEBUG
                    );
                }
            ]
        ],

        // Captcha
        // Get a Google reCAPTCHA API Key: https://www.google.com/recaptcha/admin
        'captcha' => [
            'disabled' => false,
            'public_key' => '', // your public key
            'secret_key' => '', // your secret key
            'theme' => 'light', // light or dark
        ],
    ]
];
