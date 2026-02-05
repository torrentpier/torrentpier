<?php

/**
 * TorrentPier – Bull-powered BitTorrent tracker engine
 *
 * @copyright Copyright (c) 2005-2025 TorrentPier (https://torrentpier.com)
 * @link      https://github.com/torrentpier/torrentpier for the canonical source repository
 * @license   https://github.com/torrentpier/torrentpier/blob/master/LICENSE MIT License
 */

return [
    'user' => [
        'allowed_ext' => ['gif', 'jpg', 'png', 'bmp', 'webp', 'avif'],
        'bot_avatar' => '/gallery/bot.gif',
        'max_size' => 100 * 1024,
        'max_height' => 100,
        'max_width' => 100,
        'no_avatar' => '/gallery/noavatar.png',
        'display_path' => '/storage/avatars',
        'upload_path' => AVATARS_DIR . '/',
        'up_allowed' => true,
    ],
    'group' => [
        'allowed_ext' => ['gif', 'jpg', 'png', 'bmp', 'webp', 'avif'],
        'max_size' => 300 * 1024,
        'max_height' => 300,
        'max_width' => 300,
        'no_avatar' => '/gallery/noavatar.png',
        'display_path' => '/storage/avatars',
        'upload_path' => AVATARS_DIR . '/',
        'up_allowed' => true,
    ],
];
