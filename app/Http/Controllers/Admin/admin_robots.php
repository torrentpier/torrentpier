<?php

/**
 * TorrentPier – Bull-powered BitTorrent tracker engine
 *
 * @copyright Copyright (c) 2005-2025 TorrentPier (https://torrentpier.com)
 * @link      https://github.com/torrentpier/torrentpier for the canonical source repository
 * @license   https://github.com/torrentpier/torrentpier/blob/master/LICENSE MIT License
 */

if (request()->post->has('save')) {
    $robots_txt = request()->post->get('robots_txt', '');

    bb_update_config(['robots_txt' => $robots_txt]);

    bb_die(__('ROBOTS_TXT_UPDATED_SUCCESSFULLY') . '<br /><br />' . sprintf(__('CLICK_RETURN_ROBOTS_TXT_CONFIG'), '<a href="admin_robots.php">', '</a>') . '<br /><br />' . sprintf(__('CLICK_RETURN_ADMIN_INDEX'), '<a href="index.php">', '</a>'));
}

$current_content = config()->get('robots_txt') ?? '';

template()->assign_vars([
    'S_ACTION' => 'admin_robots.php',
    'ROBOTS_TXT' => htmlCHR($current_content),
]);

print_page('admin_robots.tpl', 'admin');
