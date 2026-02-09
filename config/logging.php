<?php

/**
 * TorrentPier – Bull-powered BitTorrent tracker engine
 *
 * @copyright Copyright (c) 2005-2025 TorrentPier (https://torrentpier.com)
 * @link      https://github.com/torrentpier/torrentpier for the canonical source repository
 * @license   https://github.com/torrentpier/torrentpier/blob/master/LICENSE MIT License
 */

return [
    'whoops' => [
        'error_message' => 'Sorry, something went wrong. Drink coffee and come back after some time...',
        'show_error_details' => false,
        'blacklist' => [
            '_COOKIE' => ['bb_t', 'bb_u', 'bb_data', 'bb_sid'],
            '_SERVER' => ['PHP_AUTH_PW', 'HTTP_AUTHORIZATION', 'HTTP_COOKIE', 'REMOTE_ADDR'],
            '_ENV' => ['DB_PASSWORD', 'MAIL_PASSWORD', 'APP_KEY'],
        ],
    ],
    'debug' => [
        'enable' => env('APP_DEBUG', false),
        'panels' => [
            'performance' => true,
            'database' => true,
            'cache' => true,
            'template' => true,
        ],
        'max_query_length' => 500,
    ],
    'log_redirects' => true,
    'log_days_keep' => 365,
];
