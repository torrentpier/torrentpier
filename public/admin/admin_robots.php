<?php

/**
 * TorrentPier – Bull-powered BitTorrent tracker engine
 *
 * @copyright Copyright (c) 2005-2025 TorrentPier (https://torrentpier.com)
 * @link      https://github.com/torrentpier/torrentpier for the canonical source repository
 * @license   https://github.com/torrentpier/torrentpier/blob/master/LICENSE MIT License
 */
if (!empty($setmodules)) {
    $module['MODS']['ROBOTS_TXT_EDITOR_TITLE'] = basename(__FILE__);

    return;
}

require __DIR__ . '/pagestart.php';

if (request()->post->has('save')) {
    // Check for demo mode
    if (IN_DEMO_MODE) {
        bb_die(__('CANT_EDIT_IN_DEMO_MODE'));
    }

    $robots_txt = request()->post->get('robots_txt', '');

    bb_update_config(['robots_txt' => $robots_txt]);

    bb_die(__('ROBOTS_TXT_UPDATED_SUCCESSFULLY') . '<br /><br />' . sprintf(__('CLICK_RETURN_ROBOTS_TXT_CONFIG'), '<a href="admin_robots.php">', '</a>') . '<br /><br />' . sprintf(__('CLICK_RETURN_ADMIN_INDEX'), '<a href="index.php?pane=right">', '</a>'));
}

$current_content = config()->get('robots_txt') ?? '';

template()->assign_vars([
    'S_ACTION' => 'admin_robots.php',
    'ROBOTS_TXT' => htmlCHR($current_content),
]);

print_page('admin_robots.tpl', 'admin');
