<?php

/**
 * TorrentPier – Bull-powered BitTorrent tracker engine
 *
 * @copyright Copyright (c) 2005-2025 TorrentPier (https://torrentpier.com)
 * @link      https://github.com/torrentpier/torrentpier for the canonical source repository
 * @license   https://github.com/torrentpier/torrentpier/blob/master/LICENSE MIT License
 */

return [
    'registration' => [
        'disabled' => false,
        'unique_ip' => false,
        'restricted' => false,
        'restricted_hours' => [0, 1, 2, 3, 4, 5, 6, 7, 8, 11, 12, 13, 14, 15, 16, 17, 18, 19, 20, 21, 22, 23],
        'email_activation' => true,
    ],
    'invites' => [
        'enabled' => false,
        'codes' => [
            'new_year2023' => '2022-12-31 00:00:01',
            '340c4bb6ea2d284c13e085b60b990a8a' => '12 April 1961',
            'tp_birthday' => '2005-04-04',
            'endless' => 'permanent',
        ],
    ],
    'password' => [
        'symbols' => [
            'nums' => true,
            'spec_symbols' => false,
            'letters' => ['uppercase' => false, 'lowercase' => true],
        ],
        'hash_options' => [
            'algo' => PASSWORD_BCRYPT,
            'options' => ['cost' => 12],
        ],
    ],
    'first_logon_redirect_url' => '/',
    'invalid_logins' => 5,
    'sessions' => [
        'update_interval' => 180,
        'user_duration' => 1800,
        'admin_duration' => 6 * 3600,
        'gc_ttl' => 1800,
        'cache_gc_ttl' => 1200,
        'max_last_visit_days' => 14,
        'last_visit_update_interval' => 3600,
    ],
    'cookie' => [
        'prefix' => 'bb_',
        'domain' => env('COOKIE_DOMAIN', ''),
        'same_site' => 'Lax',
    ],
    'unlimited_users' => [
        2 => 'admin',
    ],
    'super_admins' => [
        2 => 'admin',
    ],
    'premium_users' => [
        2 => 'admin',
    ],
];
