<?php

/**
 * TorrentPier – Bull-powered BitTorrent tracker engine
 *
 * @copyright Copyright (c) 2005-2025 TorrentPier (https://torrentpier.com)
 * @link      https://github.com/torrentpier/torrentpier for the canonical source repository
 * @license   https://github.com/torrentpier/torrentpier/blob/master/LICENSE MIT License
 */

return [
    // Announce settings
    'announce_interval' => 1800,
    'scrape_interval' => 300,
    'max_scrapes' => 150,
    'passkey_key' => 'uk',

    // IP handling
    'ignore_reported_ip' => false,
    'verify_reported_ip' => true,
    'allow_internal_ip' => false,
    'disallowed_ports' => [
        8080,
        8081,
        1214,
        3389,
        4662,
        6346,
        6347,
        6699,
    ],

    // Client ban settings
    'client_ban' => [
        'enabled' => false,
        'only_allow_mode' => false,
        'clients' => [
            '-UT' => 'uTorrent — NOT ad-free and open-source',
            '-MG' => 'Mostly leeching client',
            '-ZO' => '',
        ],
    ],

    // Ratio settings
    'min_ratio_allow_dl' => 0.3,
    'min_ratio_warning' => 0.6,
    'ratio_null_enabled' => true,
    'ratio_to_null' => 0.3,

    // Display settings
    'show_dl_status_in_search' => true,
    'show_dl_status_in_forum' => true,
    'show_tor_info_in_dl_list' => true,
    'allow_dl_list_names_mode' => true,

    // Retention settings (days)
    'seeder_last_seen_days_keep' => 0,
    'seeder_never_seen_days_keep' => 0,
    'dl_will_days_keep' => 360,
    'dl_down_days_keep' => 180,
    'dl_complete_days_keep' => 180,
    'dl_cancel_days_keep' => 30,
    'torstat_days_keep' => 60,

    // Features
    'torhelp_enabled' => false,
    'tor_thank' => true,
    'tor_thanks_list_guests' => true,
    'tor_thank_limit_per_topic' => 50,

    // Download limits
    'torrent_dl' => [
        'daily_limit' => 50,
        'daily_limit_premium' => 100,
        'filename_with_title' => true,
    ],

    // Core tracker settings
    'autoclean' => true,
    'bt_off' => false,
    'bt_off_reason' => 'Tracker is temporarily disabled',
    'numwant' => 50,
    'update_dlstat' => true,
    'expire_factor' => 2.5,
    'compact_mode' => true,
    'upd_user_up_down_stat' => true,
    'browser_redirect_url' => '',
    'scrape' => true,
    'limit_active_tor' => true,
    'limit_seed_count' => 0,
    'limit_leech_count' => 8,
    'leech_expire_factor' => 60,
    'limit_concurrent_ips' => false,
    'limit_seed_ips' => 0,
    'limit_leech_ips' => 0,
    'tor_topic_up' => true,
    'retracker_enabled' => true,
    'retracker_host' => 'http://retracker.local/announce',
    'guest_tracker' => true,
    'search_by_tor_status' => true,
    'random_release_button' => true,
    'freeleech' => false,
    'gold_silver_enabled' => true,
    'hybrid_stat_protocol' => 1,
    'disabled_v1_torrents' => false,
    'disabled_v2_torrents' => false,
    // Rating tiers
    'rating' => [
        '0.4' => 1,
        '0.5' => 2,
        '0.6' => 3,
    ],

    // Status icons
    'tor_icons' => [
        TOR_NOT_APPROVED => '<span class="tor-icon tor-not-approved">*</span>',
        TOR_CLOSED => '<span class="tor-icon tor-closed">x</span>',
        TOR_APPROVED => '<span class="tor-icon tor-approved">&radic;</span>',
        TOR_NEED_EDIT => '<span class="tor-icon tor-need-edit">?</span>',
        TOR_NO_DESC => '<span class="tor-icon tor-no-desc">!</span>',
        TOR_DUP => '<span class="tor-icon tor-dup">D</span>',
        TOR_CLOSED_CPHOLD => '<span class="tor-icon tor-closed-cp">&copy;</span>',
        TOR_CONSUMED => '<span class="tor-icon tor-consumed">&sum;</span>',
        TOR_DOUBTFUL => '<span class="tor-icon tor-approved">#</span>',
        TOR_CHECKING => '<span class="tor-icon tor-checking">%</span>',
        TOR_TMP => '<span class="tor-icon tor-dup">T</span>',
        TOR_PREMOD => '<span class="tor-icon tor-dup">&#8719;</span>',
        TOR_REPLENISH => '<span class="tor-icon tor-dup">R</span>',
    ],

    // Frozen statuses (disallowed for downloading)
    'tor_frozen' => [
        TOR_CHECKING => true,
        TOR_CLOSED => true,
        TOR_CLOSED_CPHOLD => true,
        TOR_CONSUMED => true,
        TOR_DUP => true,
        TOR_NO_DESC => true,
        TOR_PREMOD => true,
    ],

    // Author download allowed for frozen
    'tor_frozen_author_download' => [
        TOR_CHECKING => true,
        TOR_NO_DESC => true,
        TOR_PREMOD => true,
    ],

    // Status restrictions
    'tor_cannot_edit' => [TOR_CHECKING, TOR_CLOSED, TOR_CONSUMED, TOR_DUP],
    'tor_cannot_new' => [TOR_NEED_EDIT, TOR_NO_DESC, TOR_DOUBTFUL],
    'tor_reply' => [TOR_NEED_EDIT, TOR_NO_DESC, TOR_DOUBTFUL],
    'tor_no_tor_act' => [
        TOR_CLOSED => true,
        TOR_DUP => true,
        TOR_CLOSED_CPHOLD => true,
        TOR_CONSUMED => true,
    ],

    // Attachments
    'attach' => [
        'upload_path' => UPLOADS_DIR,
        'max_size' => 5 * 1024 * 1024,
        'up_allowed' => true,
        'allowed_ext' => ['torrent'],
    ],
];
