<?php

/**
 * TorrentPier – Bull-powered BitTorrent tracker engine
 *
 * @copyright Copyright (c) 2005-2025 TorrentPier (https://torrentpier.com)
 * @link      https://github.com/torrentpier/torrentpier for the canonical source repository
 * @license   https://github.com/torrentpier/torrentpier/blob/master/LICENSE MIT License
 */

return [
    'default' => env('CACHE_DRIVER', 'file'),
    'stores' => [
        'file' => [
            'driver' => 'file',
            'path' => CACHE_DIR . '/filecache/',
        ],
        'memcached' => [
            'driver' => 'memcached',
            'host' => env('MEMCACHED_HOST', '127.0.0.1'),
            'port' => env('MEMCACHED_PORT', 11211),
            'connect_timeout' => 100,
            'poll_timeout' => 100,
        ],
    ],
    'prefix' => 'tp_',
    'engines' => [
        'bb_cache' => ['file'],
        'bb_config' => ['file'],
        'tr_cache' => ['file'],
        'session_cache' => ['file'],
        'bb_cap_sid' => ['file'],
        'bb_login_err' => ['file'],
        'bb_poll_data' => ['file'],
        'bb_ip2countries' => ['file'],
    ],
    'datastore_type' => 'file',
];
