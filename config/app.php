<?php

/**
 * TorrentPier – Bull-powered BitTorrent tracker engine
 *
 * @copyright Copyright (c) 2005-2025 TorrentPier (https://torrentpier.com)
 * @link      https://github.com/torrentpier/torrentpier for the canonical source repository
 * @license   https://github.com/torrentpier/torrentpier/blob/master/LICENSE MIT License
 */

return [
    'release_date' => 'xx-02-2026',
    'release_codename' => 'Dexter',
    'js_ver' => 1,
    'css_ver' => 1,
    'server_name' => env('TP_HOST', 'example.com'),
    'server_port' => env('TP_PORT', 80),
    'script_path' => '/',
    'first_logon_redirect_url' => '/',
    'terms_and_conditions_url' => '/terms',
    'user_agreement_url' => '/info?show=user_agreement',
    'copyright_holders_url' => '/info?show=copyright_holders',
    'advert_url' => '/info?show=advert',
    'help_urls' => [
        'how_to_download' => '/threads/1',
        'what_is_torrent' => '/threads/2',
        'ratio' => '/threads/3',
        'search' => '/threads/4',
    ],
    'use_word_censor' => true,
    'tidy_post' => env('TIDY_POST_ENABLED', false),
];
