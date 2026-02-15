<?php

/**
 * TorrentPier – Bull-powered BitTorrent tracker engine
 *
 * @copyright Copyright (c) 2005-2025 TorrentPier (https://torrentpier.com)
 * @link      https://github.com/torrentpier/torrentpier for the canonical source repository
 * @license   https://github.com/torrentpier/torrentpier/blob/master/LICENSE MIT License
 */

return [
    'api_url' => env('MARKETPLACE_API_URL', 'https://torrentpier.com/api'),
    'api_key' => env('MARKETPLACE_API_KEY', 'k_4INHT5p2GWTPR1BeP1NEo4qo5innLA'),
    'cache_ttl' => 300,
    'categories_cache_ttl' => 3600,
    'per_page' => 20,
];
