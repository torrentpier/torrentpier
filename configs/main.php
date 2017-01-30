<?php
/**
 * MIT License
 *
 * Copyright (c) 2005-2017 TorrentPier
 *
 * Permission is hereby granted, free of charge, to any person obtaining a copy
 * of this software and associated documentation files (the "Software"), to deal
 * in the Software without restriction, including without limitation the rights
 * to use, copy, modify, merge, publish, distribute, sublicense, and/or sell
 * copies of the Software, and to permit persons to whom the Software is
 * furnished to do so, subject to the following conditions:
 *
 * The above copyright notice and this permission notice shall be included in all
 * copies or substantial portions of the Software.
 *
 * THE SOFTWARE IS PROVIDED "AS IS", WITHOUT WARRANTY OF ANY KIND, EXPRESS OR
 * IMPLIED, INCLUDING BUT NOT LIMITED TO THE WARRANTIES OF MERCHANTABILITY,
 * FITNESS FOR A PARTICULAR PURPOSE AND NONINFRINGEMENT. IN NO EVENT SHALL THE
 * AUTHORS OR COPYRIGHT HOLDERS BE LIABLE FOR ANY CLAIM, DAMAGES OR OTHER
 * LIABILITY, WHETHER IN AN ACTION OF CONTRACT, TORT OR OTHERWISE, ARISING FROM,
 * OUT OF OR IN CONNECTION WITH THE SOFTWARE OR THE USE OR OTHER DEALINGS IN THE
 * SOFTWARE.
 */

return [
    'debug' => false,

    'services' => [
        // Database
        'db' => [
            'debug' => '{self.debug}',
            'type' => 'mysql',
            'hostname' => '127.0.0.1',
            'database' => 'tp_220',
            'username' => 'user',
            'password' => function () {
                return 'pass';
            },
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
