<?php

declare(strict_types=1);

/**
 * BitTorrent Tracker Configuration
 */

return [
    /*
    |--------------------------------------------------------------------------
    | Tracker Settings
    |--------------------------------------------------------------------------
    */
    'announce_interval' => env('TRACKER_ANNOUNCE_INTERVAL', 900), // 15 minutes
    'min_interval' => env('TRACKER_MIN_INTERVAL', 300), // 5 minutes
    'max_peers' => env('TRACKER_MAX_PEERS', 50),
    'max_peers_per_torrent' => env('TRACKER_MAX_PEERS_PER_TORRENT', 100),

    /*
    |--------------------------------------------------------------------------
    | Peer Settings
    |--------------------------------------------------------------------------
    */
    'peer_timeout' => env('TRACKER_PEER_TIMEOUT', 1800), // 30 minutes
    'peer_compact' => env('TRACKER_PEER_COMPACT', true),
    'peer_no_peer_id' => env('TRACKER_PEER_NO_PEER_ID', false),

    /*
    |--------------------------------------------------------------------------
    | Ratio Requirements
    |--------------------------------------------------------------------------
    */
    'ratio_required' => env('TRACKER_RATIO_REQUIRED', false),
    'min_ratio' => env('TRACKER_MIN_RATIO', 0.5),
    'ratio_warning_threshold' => env('TRACKER_RATIO_WARNING', 0.8),

    /*
    |--------------------------------------------------------------------------
    | Upload/Download Limits
    |--------------------------------------------------------------------------
    */
    'max_upload_speed' => env('TRACKER_MAX_UPLOAD_SPEED', 0), // 0 = unlimited
    'max_download_speed' => env('TRACKER_MAX_DOWNLOAD_SPEED', 0), // 0 = unlimited
    'max_torrents_per_user' => env('TRACKER_MAX_TORRENTS_PER_USER', 0), // 0 = unlimited

    /*
    |--------------------------------------------------------------------------
    | Security Settings
    |--------------------------------------------------------------------------
    */
    'passkey_required' => env('TRACKER_PASSKEY_REQUIRED', true),
    'ip_validation' => env('TRACKER_IP_VALIDATION', true),
    'user_agent_validation' => env('TRACKER_USER_AGENT_VALIDATION', false),

    /*
    |--------------------------------------------------------------------------
    | Allowed Clients
    |--------------------------------------------------------------------------
    */
    'allowed_clients' => [
        'qBittorrent',
        'uTorrent',
        'BitTorrent',
        'Transmission',
        'Deluge',
        'rtorrent',
        'libtorrent',
    ],

    /*
    |--------------------------------------------------------------------------
    | Banned Clients
    |--------------------------------------------------------------------------
    */
    'banned_clients' => [
        'BitComet',
        'BitLord',
        'Thunder',
        'Xunlei',
    ],

    /*
    |--------------------------------------------------------------------------
    | Statistics
    |--------------------------------------------------------------------------
    */
    'stats_update_interval' => env('TRACKER_STATS_UPDATE_INTERVAL', 300), // 5 minutes
    'enable_scrape' => env('TRACKER_ENABLE_SCRAPE', true),
    'scrape_interval' => env('TRACKER_SCRAPE_INTERVAL', 600), // 10 minutes
];