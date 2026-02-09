<?php

/**
 * TorrentPier – Bull-powered BitTorrent tracker engine
 *
 * @copyright Copyright (c) 2005-2025 TorrentPier (https://torrentpier.com)
 * @link      https://github.com/torrentpier/torrentpier for the canonical source repository
 * @license   https://github.com/torrentpier/torrentpier/blob/master/LICENSE MIT License
 */

return [
    'bugsnag' => [
        'enabled' => !!env('BUGSNAG_API_KEY'),
        'api_key' => env('BUGSNAG_API_KEY', ''),
    ],
    'telegram' => [
        'enabled' => env('TELEGRAM_ENABLED', false),
        'token' => env('TELEGRAM_BOT_TOKEN', ''),
        'chat_id' => env('TELEGRAM_CHAT_ID', ''),
        'timeout' => 10,
    ],
    'ip2country' => [
        'enabled' => true,
        'endpoint' => 'https://freeipapi.com/api/json/',
        'api_token' => env('IP2COUNTRY_TOKEN', ''),
    ],
    'torrserver' => [
        'enabled' => env('TORRSERVER_ENABLED', false),
        'url' => env('TORRSERVER_URL', 'http://localhost:8090'),
        'timeout' => 3,
    ],
    'updater' => [
        'enabled' => true,
        'allow_pre_releases' => false,
    ],
    'whois_info' => env('WHOIS_INFO_URL', 'https://whatismyipaddress.com/ip/'),
];
