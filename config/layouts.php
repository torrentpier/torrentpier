<?php

/**
 * TorrentPier – Bull-powered BitTorrent tracker engine
 *
 * @copyright Copyright (c) 2005-2025 TorrentPier (https://torrentpier.com)
 * @link      https://github.com/torrentpier/torrentpier for the canonical source repository
 * @license   https://github.com/torrentpier/torrentpier/blob/master/LICENSE MIT License
 */

return [
    'show_sidebar1_on_every_page' => false,
    'show_sidebar2_on_every_page' => false,

    // Graphics
    'vote_graphic_length' => 205,
    'privmsg_graphic_length' => 175,
    'topic_left_column_width' => 150,
    'post_img_width_decr' => 52,
    'attach_img_width_decr' => 130,

    'page' => [
        'show_torhelp' => ['index' => true, 'tracker' => true],
        'show_sidebar1' => ['index' => true],
        'show_sidebar2' => ['index' => true],
    ],
];
