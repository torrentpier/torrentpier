<?php

/**
 * TorrentPier – Bull-powered BitTorrent tracker engine
 *
 * @copyright Copyright (c) 2005-2025 TorrentPier (https://torrentpier.com)
 * @link      https://github.com/torrentpier/torrentpier for the canonical source repository
 * @license   https://github.com/torrentpier/torrentpier/blob/master/LICENSE MIT License
 */

return [
    'enabled' => true,
    'short_circuit' => true,
    'check_admins' => true,
    'logging' => [
        'enabled' => true,
        'retention_days' => 30,
    ],

    'providers' => [
        'banned_users' => ['enabled' => true],

        'spam_phrases' => [
            'enabled' => false,

            // Plain strings are matched as whole words (case-insensitive):
            //   'viagra', 'casino online', 'buy cheap'
            //
            // Strings starting with "/" are treated as regular expressions:
            //   '/\b(viagra|cialis|levitra)\b/iu'
            //   '/https?:\/\/[^\s]*\.(xyz|top|buzz)\b/iu'
            //   '/(.)\1{5,}/u'  — repeated characters like "aaaaaa"
            //
            // Registration checks: matches against username and email (decision: always denied)
            // Content checks: matches against post/PM text (decision: see content_action below)
            'phrases' => [],

            // Action for content matches: 'moderated' (log only) or 'denied' (block post/PM)
            'content_action' => 'moderated',
        ],

        'stop_forum_spam' => [
            'enabled' => false,
            'api_key' => '',
            'confidence_threshold' => 65.0,
            'deny_threshold' => 90.0,
            'timeout' => 5,
            'cache_ttl' => 3600,
        ],

        'project_honeypot' => [
            'enabled' => false,
            'api_key' => '',
            'threat_threshold' => 25,
            'cache_ttl' => 3600,
        ],

        'dns_blacklist' => [
            'enabled' => false,
            'zones' => [],
            'cache_ttl' => 3600,
        ],

        'akismet' => [
            'enabled' => false,
            'api_key' => '',
            'timeout' => 5,
            'cache_ttl' => 0,
        ],
    ],
];
